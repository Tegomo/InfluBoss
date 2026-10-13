<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfluencerFollowersUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    private SocialAccount $instagram;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create();
        $this->instagram = SocialAccount::factory()->create(['user_id' => $this->influencer->id, 'platform' => 'instagram']);
        $this->instagram->recordFollowers(1000);
    }

    private function url(?User $target = null): string
    {
        return route('influencers.update-followers', $target ?? $this->influencer);
    }

    public function test_admin_update_creates_snapshot_and_updates_account(): void
    {
        $response = $this->actingAs($this->admin)->post($this->url(), ['followers' => ['instagram' => '1300']]);

        $response->assertRedirect(route('influencers.show', $this->influencer));
        $response->assertSessionHas('success');
        $this->assertSame(1300, $this->instagram->fresh()->followers_count);
        $this->assertDatabaseCount('follower_snapshots', 2);
        $this->assertDatabaseHas('follower_snapshots', [
            'social_account_id' => $this->instagram->id,
            'followers_count' => 1300,
            'source' => 'manual',
            'recorded_by' => $this->admin->id,
        ]);

        // Le formulaire dédié historise toujours, même une valeur identique.
        $this->actingAs($this->admin)->post($this->url(), ['followers' => ['instagram' => '1300']]);
        $this->assertDatabaseCount('follower_snapshots', 3);
    }

    public function test_linked_supervisor_can_update(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->influencer->id);

        $this->actingAs($supervisor)->post($this->url(), ['followers' => ['instagram' => '1100']])
            ->assertRedirect(route('influencers.show', $this->influencer));

        $this->assertSame(1100, $this->instagram->fresh()->followers_count);
    }

    public function test_unlinked_supervisor_is_forbidden(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->actingAs($supervisor)->post($this->url(), ['followers' => ['instagram' => '1100']])
            ->assertForbidden();

        $this->assertSame(1000, $this->instagram->fresh()->followers_count);
        $this->assertDatabaseCount('follower_snapshots', 1);
    }

    public function test_influencer_is_forbidden(): void
    {
        $this->actingAs($this->influencer)->post($this->url(), ['followers' => ['instagram' => '9999']])
            ->assertForbidden();
    }

    public function test_platform_without_account_is_rejected(): void
    {
        $this->actingAs($this->admin)->post($this->url(), ['followers' => ['tiktok' => '5']])
            ->assertSessionHasErrors('followers');

        $this->assertDatabaseCount('follower_snapshots', 1);
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->actingAs($this->admin)->post($this->url(), ['followers' => ['instagram' => '']])
            ->assertSessionHasErrors('followers');

        $this->actingAs($this->admin)->post($this->url(), [])
            ->assertSessionHasErrors('followers');

        $this->assertDatabaseCount('follower_snapshots', 1);
    }

    public function test_negative_value_is_rejected(): void
    {
        $this->actingAs($this->admin)->post($this->url(), ['followers' => ['instagram' => '-3']])
            ->assertSessionHasErrors('followers.instagram');
    }

    public function test_target_must_be_an_influencer(): void
    {
        $other = User::factory()->supervisor()->create();

        $this->actingAs($this->admin)->post($this->url($other), ['followers' => ['instagram' => '10']])
            ->assertNotFound();
    }
}
