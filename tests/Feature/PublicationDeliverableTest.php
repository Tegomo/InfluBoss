<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Deliverable;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationDeliverableTest extends TestCase
{
    use RefreshDatabase;

    private User $influencer;

    private Assignment $assignment;

    private Deliverable $deliverable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->influencer = User::factory()->influencer()->create();
        $this->assignment = Assignment::factory()->create(['influencer_id' => $this->influencer->id, 'status' => 'accepted']);
        $this->deliverable = Deliverable::factory()->create([
            'assignment_id' => $this->assignment->id, 'type' => 'story', 'quantity' => 2,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'assignment_id' => $this->assignment->id,
            'deliverable_id' => $this->deliverable->id,
            'platform' => 'instagram',
            'post_url' => 'https://www.instagram.com/p/abc123',
            'post_type' => 'story',
            'published_at' => now()->toDateTimeString(),
            'status' => 'pending_verification',
        ], $overrides);
    }

    public function test_influencer_links_publication_to_deliverable(): void
    {
        $this->actingAs($this->influencer)->post(route('publications.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $publication = Publication::firstOrFail();
        $this->assertSame($this->deliverable->id, $publication->deliverable_id);
        $this->assertSame('in_progress', $this->deliverable->fresh()->status);
    }

    public function test_deliverable_must_belong_to_the_mission(): void
    {
        $otherDeliverable = Deliverable::factory()->create();

        $this->actingAs($this->influencer)
            ->post(route('publications.store'), $this->payload(['deliverable_id' => $otherDeliverable->id]))
            ->assertSessionHasErrors('deliverable_id');

        $this->assertDatabaseCount('publications', 0);
    }

    public function test_publication_without_deliverable_still_works(): void
    {
        $this->actingAs($this->influencer)->post(route('publications.store'), $this->payload(['deliverable_id' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Publication::firstOrFail()->deliverable_id);
    }

    public function test_create_form_lists_deliverables_and_preselects_from_query(): void
    {
        $this->actingAs($this->influencer)
            ->get(route('publications.create', ['assignment_id' => $this->assignment->id, 'deliverable_id' => $this->deliverable->id]))
            ->assertOk()
            ->assertSee('name="deliverable_id"', false)
            ->assertSee('data-assignment-id="'.$this->assignment->id.'"', false)
            ->assertSee('value="'.$this->deliverable->id.'" selected', false)
            ->assertSee('2 × Story');
    }

    public function test_admin_can_move_publication_to_another_deliverable(): void
    {
        $admin = User::factory()->admin()->create();
        $other = Deliverable::factory()->create(['assignment_id' => $this->assignment->id, 'quantity' => 1]);
        $publication = Publication::factory()->create([
            'assignment_id' => $this->assignment->id,
            'influencer_id' => $this->influencer->id,
            'deliverable_id' => $this->deliverable->id,
            'status' => 'verified',
        ]);

        $this->actingAs($admin)->get(route('publications.edit', $publication))
            ->assertOk()->assertSee('name="deliverable_id"', false);

        $this->actingAs($admin)->put(route('publications.update', $publication), [
            'platform' => 'instagram',
            'post_url' => $publication->post_url,
            'post_type' => 'post',
            'published_at' => now()->toDateTimeString(),
            'deliverable_id' => $other->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($other->id, $publication->fresh()->deliverable_id);
        $this->assertSame('validated', $other->fresh()->status);
        $this->assertSame('todo', $this->deliverable->fresh()->status);
    }

    public function test_mission_card_offers_declare_link_to_the_influencer(): void
    {
        $this->actingAs($this->influencer)->get(route('assignments.show', $this->assignment))
            ->assertOk()
            ->assertSee(e(route('publications.create', ['assignment_id' => $this->assignment->id, 'deliverable_id' => $this->deliverable->id])), false);
    }
}
