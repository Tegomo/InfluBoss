<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SocialAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocialAccountService $service;

    private User $admin;

    private User $influencer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SocialAccountService;
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create();
    }

    public function test_sync_creates_accounts_and_first_snapshot(): void
    {
        $this->service->syncForUser($this->influencer, [
            'instagram' => ['url' => 'https://www.instagram.com/foo', 'followers' => '1500'],
            'tiktok' => ['url' => 'https://www.tiktok.com/@bar', 'followers' => ''],
        ], $this->admin->id);

        $accounts = $this->influencer->socialAccounts()->get()->keyBy('platform');
        $this->assertCount(2, $accounts);
        $this->assertSame('foo', $accounts['instagram']->handle);
        $this->assertSame(1500, $accounts['instagram']->followers_count);
        $this->assertNull($accounts['tiktok']->followers_count);

        $this->assertDatabaseCount('follower_snapshots', 1);
        $this->assertDatabaseHas('follower_snapshots', [
            'social_account_id' => $accounts['instagram']->id,
            'followers_count' => 1500,
            'source' => 'manual',
            'recorded_by' => $this->admin->id,
        ]);
    }

    public function test_sync_does_not_duplicate_snapshot_when_value_unchanged(): void
    {
        $payload = ['instagram' => ['url' => 'https://www.instagram.com/foo', 'followers' => '1500']];

        $this->service->syncForUser($this->influencer, $payload, $this->admin->id);
        $this->service->syncForUser($this->influencer, $payload, $this->admin->id);
        $this->assertDatabaseCount('follower_snapshots', 1);

        $payload['instagram']['followers'] = '1600';
        $this->service->syncForUser($this->influencer, $payload, $this->admin->id);
        $this->assertDatabaseCount('follower_snapshots', 2);
        $this->assertSame(1600, $this->influencer->socialAccounts()->first()->followers_count);
    }

    public function test_always_snapshot_flag_creates_snapshot_even_if_unchanged(): void
    {
        $payload = ['instagram' => ['url' => 'https://www.instagram.com/foo', 'followers' => '1500']];

        $this->service->syncForUser($this->influencer, $payload, $this->admin->id);
        $this->service->syncForUser($this->influencer, $payload, $this->admin->id, alwaysSnapshot: true);

        $this->assertDatabaseCount('follower_snapshots', 2);
    }

    public function test_sync_removes_account_when_url_cleared(): void
    {
        $account = SocialAccount::factory()->create(['user_id' => $this->influencer->id, 'platform' => 'instagram']);
        $account->recordFollowers(100);

        $this->service->syncForUser($this->influencer, [
            'instagram' => ['url' => '', 'followers' => ''],
        ], $this->admin->id);

        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertDatabaseCount('follower_snapshots', 0);
    }

    public function test_sync_keeps_accounts_for_platforms_absent_from_payload(): void
    {
        SocialAccount::factory()->create(['user_id' => $this->influencer->id, 'platform' => 'youtube']);

        $this->service->syncForUser($this->influencer, [
            'instagram' => ['url' => 'https://www.instagram.com/foo', 'followers' => ''],
        ], $this->admin->id);

        $this->assertDatabaseCount('social_accounts', 2);
    }

    public function test_followers_without_url_are_ignored(): void
    {
        $this->service->syncForUser($this->influencer, [
            'instagram' => ['url' => '', 'followers' => '100'],
        ], $this->admin->id);

        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertDatabaseCount('follower_snapshots', 0);
    }

    public function test_upsert_updates_existing_account_url(): void
    {
        $this->service->upsert($this->influencer, 'instagram', 'https://www.instagram.com/old');
        $account = $this->service->upsert($this->influencer, 'instagram', '@new');

        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertSame('new', $account->handle);
        $this->assertSame('https://www.instagram.com/new', $account->url);
    }
}
