<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\LegacySocialLinksMigrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacySocialLinksMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function setLegacyColumns(User $user, array $columns): void
    {
        DB::table('users')->where('id', $user->id)->update($columns);
    }

    public function test_double_encoded_social_links_are_converted(): void
    {
        $user = User::factory()->influencer()->create();
        // Reproduit le double encodage produit par json_encode() + cast json de l'ancien contrôleur.
        $this->setLegacyColumns($user, [
            'social_links' => json_encode(json_encode([
                'instagram' => 'https://instagram.com/foo',
                'twitter' => 'https://twitter.com/bar',
            ])),
        ]);

        $result = (new LegacySocialLinksMigrator)->migrate();

        $this->assertSame(['created' => 2, 'skipped' => 0], $result);
        $accounts = SocialAccount::where('user_id', $user->id)->get()->keyBy('platform');
        $this->assertSame('foo', $accounts['instagram']->handle);
        $this->assertSame('https://instagram.com/foo', $accounts['instagram']->url);
        $this->assertSame('bar', $accounts['x']->handle);
        $this->assertSame('https://twitter.com/bar', $accounts['x']->url);
        $this->assertNull($accounts['x']->followers_count);
    }

    public function test_plain_json_and_handle_columns_are_converted(): void
    {
        $user = User::factory()->influencer()->create();
        $this->setLegacyColumns($user, [
            'social_links' => json_encode(['instagram' => 'https://instagram.com/from-json']),
            'instagram_handle' => 'from-column',
            'tiktok_handle' => '@baz',
            'youtube_channel' => 'https://youtube.com/@qux',
        ]);

        (new LegacySocialLinksMigrator)->migrate();

        $accounts = SocialAccount::where('user_id', $user->id)->get()->keyBy('platform');
        $this->assertCount(3, $accounts);
        $this->assertSame('from-json', $accounts['instagram']->handle, 'social_links prime sur la colonne');
        $this->assertSame('https://www.tiktok.com/@baz', $accounts['tiktok']->url);
        $this->assertSame('baz', $accounts['tiktok']->handle);
        $this->assertSame('qux', $accounts['youtube']->handle);
    }

    public function test_migration_is_idempotent(): void
    {
        $user = User::factory()->influencer()->create();
        $this->setLegacyColumns($user, [
            'social_links' => json_encode(['instagram' => 'https://instagram.com/foo']),
        ]);

        (new LegacySocialLinksMigrator)->migrate();
        $second = (new LegacySocialLinksMigrator)->migrate();

        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertSame(0, $second['created']);
        $this->assertSame(1, $second['skipped']);
    }

    public function test_unknown_platforms_and_empty_values_are_ignored(): void
    {
        $user = User::factory()->influencer()->create();
        $this->setLegacyColumns($user, [
            'social_links' => json_encode(['pinterest' => 'https://pinterest.com/foo', 'facebook' => '']),
        ]);

        (new LegacySocialLinksMigrator)->migrate();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_users_without_legacy_data_are_untouched(): void
    {
        User::factory()->influencer()->create();
        User::factory()->admin()->create();

        $result = (new LegacySocialLinksMigrator)->migrate();

        $this->assertSame(['created' => 0, 'skipped' => 0], $result);
        $this->assertDatabaseCount('social_accounts', 0);
    }
}
