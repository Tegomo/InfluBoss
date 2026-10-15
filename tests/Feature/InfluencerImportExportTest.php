<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InfluencerImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function csv(array $headers, array $rows): UploadedFile
    {
        $handle = fopen('php://memory', 'w+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return UploadedFile::fake()->createWithContent('import.csv', $content);
    }

    public function test_import_creates_user_social_accounts_and_snapshots(): void
    {
        $file = $this->csv(
            ['nom', 'pseudo', 'email', 'telephone', 'adresse', 'ville', 'categories', 'instagram', 'instagram_abonnes', 'twitter', 'actif'],
            [['Jane Doe', 'janedoe', 'jane@example.com', '+237600000000', 'Akwa', 'Douala', 'Business|story tellers', 'https://www.instagram.com/foo', '2500', '@bar', 'Oui']]
        );

        $response = $this->actingAs($this->admin)->post(route('influencers.import'), ['file' => $file]);

        $response->assertRedirect(route('influencers.import-export'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame('influencer', $user->role);
        $this->assertSame(['business', 'story_tellers'], $user->categories);
        $this->assertSame('Akwa', $user->address);
        $this->assertSame('Douala', $user->city);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->birthdate);

        $accounts = $user->socialAccounts()->get()->keyBy('platform');
        $this->assertCount(2, $accounts);
        $this->assertSame('foo', $accounts['instagram']->handle);
        $this->assertSame(2500, $accounts['instagram']->followers_count);
        $this->assertSame('https://x.com/bar', $accounts['x']->url);
        $this->assertNull($accounts['x']->followers_count);

        $this->assertDatabaseCount('follower_snapshots', 1);
        $this->assertDatabaseHas('follower_snapshots', [
            'social_account_id' => $accounts['instagram']->id,
            'followers_count' => 2500,
            'source' => 'manual',
            'recorded_by' => $this->admin->id,
        ]);
    }

    public function test_import_rejects_unknown_category_and_imports_nothing(): void
    {
        $file = $this->csv(
            ['nom', 'email', 'categories'],
            [['Jane Doe', 'jane@example.com', 'Inconnue']]
        );

        $this->actingAs($this->admin)->post(route('influencers.import'), ['file' => $file])
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    }

    public function test_import_rejects_invalid_followers_count(): void
    {
        $file = $this->csv(
            ['nom', 'email', 'instagram', 'instagram_abonnes'],
            [['Jane Doe', 'jane@example.com', 'https://www.instagram.com/foo', 'beaucoup']]
        );

        $this->actingAs($this->admin)->post(route('influencers.import'), ['file' => $file])
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_import_with_minimal_columns_creates_user_without_accounts(): void
    {
        $file = $this->csv(['nom', 'email'], [['Jane Doe', 'jane@example.com']]);

        $this->actingAs($this->admin)->post(route('influencers.import'), ['file' => $file])
            ->assertSessionHas('success');

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame([], $user->categories ?? []);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_export_contains_new_columns_and_no_birthdate(): void
    {
        $influencer = User::factory()->influencer()->create([
            'name' => 'Jane Doe',
            'categories' => ['business', 'story_tellers'],
            'address' => 'Akwa',
            'birthdate' => '1990-01-15',
        ]);
        SocialAccount::factory()->create([
            'user_id' => $influencer->id,
            'platform' => 'instagram',
            'url' => 'https://www.instagram.com/foo',
            'followers_count' => 2500,
        ]);

        $response = $this->actingAs($this->admin)->get(route('influencers.export'));
        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Adresse', $content);
        $this->assertStringContainsString('Catégories', $content);
        $this->assertStringContainsString('Instagram (URL)', $content);
        $this->assertStringContainsString('Instagram (abonnés)', $content);
        $this->assertStringContainsString('business|story_tellers', $content);
        $this->assertStringContainsString('https://www.instagram.com/foo', $content);
        $this->assertStringContainsString('2500', $content);
        $this->assertStringNotContainsString('Date de naissance', $content);
        $this->assertStringNotContainsString('15/01/1990', $content);
    }

    public function test_template_has_new_headers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('influencers.template'));
        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('categories', $content);
        $this->assertStringContainsString('adresse', $content);
        $this->assertStringContainsString('instagram_abonnes', $content);
        $this->assertStringContainsString('x_abonnes', $content);
        $this->assertStringNotContainsString('date_naissance', $content);
    }

    public function test_supervisor_cannot_import(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $file = $this->csv(['nom', 'email'], [['Jane Doe', 'jane@example.com']]);

        $this->actingAs($supervisor)->post(route('influencers.import'), ['file' => $file])
            ->assertForbidden();
    }
}
