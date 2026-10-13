<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentShowInfluencerCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_page_shows_influencer_categories_followers_and_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $influencer = User::factory()->influencer()->create([
            'name' => 'Jane Doe',
            'phone' => '+237699000000',
            'categories' => ['tech', 'humour'],
        ]);
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'instagram', 'handle' => 'janedoe', 'followers_count' => 1200]);
        SocialAccount::factory()->create(['user_id' => $influencer->id, 'platform' => 'tiktok', 'handle' => 'jane_tok', 'followers_count' => 800]);
        $assignment = Assignment::factory()->create(['influencer_id' => $influencer->id]);

        $response = $this->actingAs($admin)->get(route('assignments.show', $assignment));

        $response->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('Tech, Humour')
            ->assertSee('2,000')
            ->assertSee('+237699000000')
            ->assertSee('@janedoe')
            ->assertSee('@jane_tok')
            ->assertDontSee('Spécialité non définie');
    }

    public function test_assignment_page_without_accounts_shows_placeholders(): void
    {
        $admin = User::factory()->admin()->create();
        $assignment = Assignment::factory()->create();

        $this->actingAs($admin)->get(route('assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Catégorie non définie')
            ->assertSee('Aucun réseau social');
    }
}
