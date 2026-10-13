<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Publication;
use App\Models\SocialAccount;
use App\Models\Stat;
use App\Models\User;
use App\Services\InfluencerStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfluencerShowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_show_displays_categories_address_and_platform_cards(): void
    {
        $influencer = User::factory()->influencer()->create([
            'categories' => ['tech', 'humour'],
            'address' => 'Akwa, rue de la Joie',
        ]);
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'instagram', 'handle' => 'janedoe'])
            ->recordFollowers(1500);

        $response = $this->actingAs($this->admin)->get(route('influencers.show', $influencer));

        $response->assertOk()
            ->assertSee('Tech')
            ->assertSee('Humour')
            ->assertSee('Akwa, rue de la Joie')
            ->assertSee('Instagram')
            ->assertSee('@janedoe')
            ->assertSee('1,500')
            ->assertSee('Abonnés totaux')
            ->assertDontSee('Date de naissance')
            ->assertDontSee('3 jours');
    }

    public function test_show_displays_delta_against_previous_snapshot(): void
    {
        $influencer = User::factory()->influencer()->create();
        $account = SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'tiktok']);
        $account->recordFollowers(1000, collectedAt: now()->subDay());
        $account->recordFollowers(1300);

        $this->actingAs($this->admin)->get(route('influencers.show', $influencer))
            ->assertOk()
            ->assertSee('+300');
    }

    public function test_campaign_results_use_latest_stats_of_verified_publications_only(): void
    {
        $influencer = User::factory()->influencer()->create();
        $campaign = Campaign::factory()->create(['name' => 'Campagne Été Orange']);
        $assignment = Assignment::factory()->create([
            'campaign_id' => $campaign->id,
            'influencer_id' => $influencer->id,
            'required_posts' => 2,
        ]);

        $verified = Publication::factory()->create(['assignment_id' => $assignment->id, 'influencer_id' => $influencer->id]);
        Stat::factory()->create(['publication_id' => $verified->id, 'likes' => 10, 'collected_at' => now()->subDays(2)]);
        Stat::factory()->create(['publication_id' => $verified->id, 'likes' => 50, 'comments' => 5, 'shares' => 2, 'saves' => 1, 'views' => 700, 'collected_at' => now()->subDay()]);

        $pending = Publication::factory()->pendingVerification()->create(['assignment_id' => $assignment->id, 'influencer_id' => $influencer->id]);
        Stat::factory()->create(['publication_id' => $pending->id, 'likes' => 999, 'collected_at' => now()]);

        $response = $this->actingAs($this->admin)->get(route('influencers.show', $influencer));

        $response->assertOk()
            ->assertSee('Campagne Été Orange')
            ->assertSee('1 / 2 / 2')          // vérifiées / publiées / requises
            ->assertSee('<td class="text-right">50</td>', false)   // likes du dernier relevé
            ->assertSee('<td class="text-right">58</td>', false)   // engagement = 50 + 5 + 2 + 1
            ->assertDontSee('<td class="text-right">10</td>', false) // relevé précédent ignoré
            ->assertDontSee('999')
            ->assertSee('Total');
    }

    public function test_follower_chart_has_twelve_labels_and_carries_values_forward(): void
    {
        $influencer = User::factory()->influencer()->create();
        $account = SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'instagram']);
        $account->recordFollowers(100, collectedAt: now()->startOfMonth()->subMonths(3)->addDays(10));
        $account->recordFollowers(200, collectedAt: now());

        $chart = (new InfluencerStatsService)->followerChart($influencer);

        $this->assertCount(12, $chart['labels']);
        $this->assertSame(now()->format('m/Y'), $chart['labels'][11]);
        $this->assertCount(1, $chart['datasets']);
        $this->assertSame('Instagram', $chart['datasets'][0]['label']);

        $data = $chart['datasets'][0]['data'];
        $this->assertCount(12, $data);
        $this->assertNull($data[7]);
        $this->assertSame(100, $data[8]);
        $this->assertSame(100, $data[9], 'la dernière valeur connue est reportée');
        $this->assertSame(100, $data[10]);
        $this->assertSame(200, $data[11]);
    }

    public function test_follower_chart_without_snapshots_has_no_datasets(): void
    {
        $influencer = User::factory()->influencer()->create();
        SocialAccount::factory()->create(['user_id' => $influencer->id]);

        $chart = (new InfluencerStatsService)->followerChart($influencer);

        $this->assertCount(12, $chart['labels']);
        $this->assertSame([], $chart['datasets']);
    }

    public function test_followers_form_visible_for_linked_supervisor_only(): void
    {
        $influencer = User::factory()->influencer()->create();
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'instagram']);

        $linked = User::factory()->supervisor()->create();
        $linked->influencers()->attach($influencer->id);
        $unlinked = User::factory()->supervisor()->create();

        $this->actingAs($linked)->get(route('influencers.show', $influencer))
            ->assertOk()
            ->assertSee('Mettre à jour les abonnés');

        $this->actingAs($unlinked)->get(route('influencers.show', $influencer))
            ->assertOk()
            ->assertDontSee('Mettre à jour les abonnés');
    }

    public function test_show_without_accounts_or_assignments_renders(): void
    {
        $influencer = User::factory()->influencer()->create();

        $this->actingAs($this->admin)->get(route('influencers.show', $influencer))
            ->assertOk()
            ->assertSee('Aucun réseau social enregistré')
            ->assertSee('Aucune catégorie');
    }
}
