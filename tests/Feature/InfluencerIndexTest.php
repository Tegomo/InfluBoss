<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfluencerIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $alice;

    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->alice = User::factory()->influencer()->create(['name' => 'Alice Techno', 'pseudo' => 'zebra_queen', 'categories' => ['high_level']]);
        $this->bob = User::factory()->influencer()->create(['name' => 'Bob Sportif', 'pseudo' => 'bobby', 'categories' => ['anen']]);
        SocialAccount::factory()->create(['user_id' => $this->alice->id, 'platform' => 'tiktok', 'followers_count' => 500]);
        SocialAccount::factory()->create(['user_id' => $this->alice->id, 'platform' => 'instagram', 'followers_count' => 1000]);
        SocialAccount::factory()->create(['user_id' => $this->bob->id, 'platform' => 'instagram', 'followers_count' => 20]);
    }

    public function test_filter_by_category(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.index', ['category' => 'high_level']))
            ->assertOk()
            ->assertSee('Alice Techno')
            ->assertDontSee('Bob Sportif');
    }

    public function test_filter_by_platform_uses_social_accounts(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.index', ['platform' => 'tiktok']))
            ->assertOk()
            ->assertSee('Alice Techno')
            ->assertDontSee('Bob Sportif');
    }

    public function test_index_shows_total_followers_and_category_badges(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.index'))
            ->assertOk()
            ->assertSee('1,500')
            ->assertSee('High Level')
            ->assertSee('ANEN');
    }

    public function test_search_matches_pseudo(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.index', ['search' => 'zebra']))
            ->assertOk()
            ->assertSee('Alice Techno')
            ->assertDontSee('Bob Sportif');
    }

    public function test_unknown_filter_values_are_ignored(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.index', ['category' => 'zzz', 'platform' => 'pinterest']))
            ->assertOk()
            ->assertSee('Alice Techno')
            ->assertSee('Bob Sportif');
    }

    public function test_supervisor_sees_only_linked_influencers(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->alice->id);

        $this->actingAs($supervisor)->get(route('influencers.index'))
            ->assertOk()
            ->assertSee('Alice Techno')
            ->assertDontSee('Bob Sportif');
    }

    public function test_filters_are_built_from_config(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.index'))
            ->assertOk()
            ->assertSee('name="category"', false)
            ->assertSee('<option value="x"', false)
            ->assertDontSee('<option value="twitter"', false);
    }
}
