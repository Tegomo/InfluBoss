<?php

namespace Tests\Feature;

use App\Models\FollowerSnapshot;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SocialAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_followers_creates_snapshot_and_updates_account(): void
    {
        $admin = User::factory()->admin()->create();
        $account = SocialAccount::factory()->create(['platform' => 'instagram', 'followers_count' => null]);

        $snapshot = $account->recordFollowers(1200, FollowerSnapshot::SOURCE_MANUAL, $admin->id);

        $this->assertSame(1200, $snapshot->followers_count);
        $this->assertSame('manual', $snapshot->source);
        $this->assertSame($admin->id, $snapshot->recorded_by);
        $this->assertDatabaseCount('follower_snapshots', 1);

        $account->refresh();
        $this->assertSame(1200, $account->followers_count);
        $this->assertNotNull($account->last_synced_at);
    }

    public function test_record_followers_rejects_unknown_source(): void
    {
        $account = SocialAccount::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $account->recordFollowers(10, 'scraper');
    }

    public function test_record_followers_rejects_negative_count(): void
    {
        $account = SocialAccount::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $account->recordFollowers(-1);
    }

    public function test_parse_input_derives_handle_and_url(): void
    {
        $this->assertSame(
            ['url' => 'https://www.instagram.com/foo/', 'handle' => 'foo'],
            SocialAccount::parseInput('instagram', 'https://www.instagram.com/foo/')
        );
        $this->assertSame(
            ['url' => 'https://www.tiktok.com/@bar', 'handle' => 'bar'],
            SocialAccount::parseInput('tiktok', '@bar')
        );
        $this->assertSame(
            ['url' => 'https://youtube.com/channel/UC123', 'handle' => 'UC123'],
            SocialAccount::parseInput('youtube', 'https://youtube.com/channel/UC123')
        );
        $this->assertSame(
            ['url' => 'https://linkedin.com/in/jane', 'handle' => 'jane'],
            SocialAccount::parseInput('linkedin', 'https://linkedin.com/in/jane')
        );
        $this->assertSame(
            ['url' => 'https://www.facebook.com/profile.php?id=42', 'handle' => null],
            SocialAccount::parseInput('facebook', 'https://www.facebook.com/profile.php?id=42')
        );
    }

    public function test_user_total_followers_and_category_labels(): void
    {
        $influencer = User::factory()->influencer()->create(['categories' => ['noso', 'zzz']]);
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'instagram', 'followers_count' => 1000]);
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'tiktok', 'followers_count' => 500]);

        $influencer->refresh();

        $this->assertSame(1500, $influencer->total_followers);
        $this->assertSame(['NOSO'], $influencer->category_labels);
        $this->assertCount(2, $influencer->socialAccounts);
    }

    public function test_deleting_account_cascades_to_snapshots(): void
    {
        $account = SocialAccount::factory()->create();
        $account->recordFollowers(100);
        $account->recordFollowers(200);

        $account->delete();

        $this->assertDatabaseCount('follower_snapshots', 0);
    }
}
