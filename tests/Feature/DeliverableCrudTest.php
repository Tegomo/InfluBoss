<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Deliverable;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliverableCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create();
        $campaign = Campaign::factory()->create([
            'brand_tone' => 'Chaleureux et inclusif',
            'visual_guidelines' => 'Logo Orange visible dans les 3 premières secondes',
            'required_hashtags' => ['#OrangeCameroun', '#4GPourTous'],
            'required_mentions' => ['@orangecameroun'],
            'products' => 'Forfaits Max It',
        ]);
        $this->assignment = Assignment::factory()->create([
            'campaign_id' => $campaign->id,
            'influencer_id' => $this->influencer->id,
            'required_posts' => 1,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'story',
            'platform' => 'instagram',
            'quantity' => '3',
            'due_date' => now()->addWeek()->toDateString(),
            'description' => 'Stories avec lien swipe-up',
        ], $overrides);
    }

    public function test_admin_adds_deliverable(): void
    {
        $this->actingAs($this->admin)
            ->post(route('assignments.deliverables.store', $this->assignment), $this->payload())
            ->assertRedirect(route('assignments.show', $this->assignment).'#deliverables')
            ->assertSessionHas('success');

        $deliverable = Deliverable::firstOrFail();
        $this->assertSame('story', $deliverable->type);
        $this->assertSame(3, $deliverable->quantity);
        $this->assertSame('todo', $deliverable->status);
        $this->assertSame(3, $this->assignment->fresh()->required_posts);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('assignments.deliverables.store', $this->assignment), $this->payload([
                'type' => 'zzz', 'platform' => 'pinterest', 'quantity' => '0', 'due_date' => 'pas une date',
            ]))
            ->assertSessionHasErrors(['type', 'platform', 'quantity', 'due_date']);

        $this->assertDatabaseCount('deliverables', 0);
    }

    public function test_supervisor_rights(): void
    {
        $assigned = User::factory()->supervisor()->create();
        $this->assignment->update(['supervisor_id' => $assigned->id]);
        $linked = User::factory()->supervisor()->create();
        $linked->influencers()->attach($this->influencer->id);
        $other = User::factory()->supervisor()->create();

        $this->actingAs($assigned)->post(route('assignments.deliverables.store', $this->assignment), $this->payload())->assertRedirect();
        $this->actingAs($linked)->post(route('assignments.deliverables.store', $this->assignment), $this->payload())->assertRedirect();
        $this->actingAs($other)->post(route('assignments.deliverables.store', $this->assignment), $this->payload())->assertForbidden();
        $this->actingAs($this->influencer)->post(route('assignments.deliverables.store', $this->assignment), $this->payload())->assertForbidden();

        $this->assertDatabaseCount('deliverables', 2);
    }

    public function test_update_with_manual_status_and_delete(): void
    {
        $deliverable = Deliverable::factory()->create(['assignment_id' => $this->assignment->id, 'quantity' => 2]);

        $this->actingAs($this->admin)->get(route('deliverables.edit', $deliverable))->assertOk()->assertSee('Statut');

        $this->actingAs($this->admin)
            ->put(route('deliverables.update', $deliverable), $this->payload(['quantity' => '4', 'status' => 'in_progress']))
            ->assertRedirect(route('assignments.show', $this->assignment).'#deliverables');

        $deliverable->refresh();
        $this->assertSame(4, $deliverable->quantity);
        $this->assertSame('in_progress', $deliverable->status);
        $this->assertTrue($deliverable->status_set_manually);

        // Sans changement de statut, l'indicateur manuel n'est pas posé.
        $other = Deliverable::factory()->create(['assignment_id' => $this->assignment->id]);
        $this->actingAs($this->admin)->put(route('deliverables.update', $other), $this->payload(['status' => 'todo']));
        $this->assertFalse($other->fresh()->status_set_manually);

        $this->actingAs($this->influencer)->delete(route('deliverables.destroy', $deliverable))->assertForbidden();
        $this->actingAs($this->admin)->delete(route('deliverables.destroy', $deliverable))
            ->assertRedirect(route('assignments.show', $this->assignment).'#deliverables');
        $this->assertDatabaseMissing('deliverables', ['id' => $deliverable->id]);
    }

    public function test_mission_page_shows_deliverables_progress_and_brand_charter(): void
    {
        $late = Deliverable::factory()->create([
            'assignment_id' => $this->assignment->id, 'type' => 'reel', 'quantity' => 3,
            'due_date' => now()->subDays(2)->toDateString(),
        ]);
        Publication::factory()->create([
            'assignment_id' => $this->assignment->id, 'influencer_id' => $this->influencer->id,
            'deliverable_id' => $late->id, 'status' => 'verified',
        ]);

        $this->actingAs($this->influencer)->get(route('assignments.show', $this->assignment))
            ->assertOk()
            ->assertSee('id="deliverables"', false)
            ->assertSee('Reel')
            ->assertSee('1 / 1 / 3')            // vérifiées / soumises / attendues
            ->assertSee('En retard')
            ->assertSee('En cours')
            ->assertSee('Charte de marque')
            ->assertSee('Chaleureux et inclusif')
            ->assertSee('#OrangeCameroun')
            ->assertSee('@orangecameroun')
            ->assertSee('Forfaits Max It')
            ->assertDontSee(route('assignments.deliverables.store', $this->assignment), false);

        $this->actingAs($this->admin)->get(route('assignments.show', $this->assignment))
            ->assertOk()
            ->assertSee(route('assignments.deliverables.store', $this->assignment), false)
            ->assertSee(route('deliverables.edit', $late), false);
    }
}
