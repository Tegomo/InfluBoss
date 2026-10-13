<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Deliverable;
use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeliverableModelTest extends TestCase
{
    use RefreshDatabase;

    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignment = Assignment::factory()->create(['required_posts' => 1]);
    }

    private function deliverable(array $attributes = []): Deliverable
    {
        return Deliverable::factory()->create(array_merge(['assignment_id' => $this->assignment->id], $attributes));
    }

    private function publication(Deliverable $deliverable, string $status = 'pending_verification'): Publication
    {
        return Publication::factory()->create([
            'assignment_id' => $this->assignment->id,
            'influencer_id' => $this->assignment->influencer_id,
            'deliverable_id' => $deliverable->id,
            'status' => $status,
        ]);
    }

    public function test_schema(): void
    {
        $this->assertTrue(Schema::hasColumns('deliverables', [
            'assignment_id', 'type', 'platform', 'quantity', 'due_date', 'description', 'status',
        ]));
        $this->assertTrue(Schema::hasColumn('publications', 'deliverable_id'));
        $this->assertTrue(Schema::hasColumns('campaigns', [
            'brand_tone', 'visual_guidelines', 'required_hashtags', 'required_mentions', 'products',
        ]));
    }

    public function test_labels_and_overdue(): void
    {
        $late = $this->deliverable(['type' => 'story', 'platform' => 'instagram', 'status' => 'todo', 'due_date' => now()->subDay()->toDateString()]);
        $done = $this->deliverable(['status' => 'validated', 'due_date' => now()->subDay()->toDateString()]);
        $future = $this->deliverable(['status' => 'todo', 'due_date' => now()->addDay()->toDateString()]);

        $this->assertSame('Story', $late->type_label);
        $this->assertSame('À faire', $late->status_label);
        $this->assertSame('Instagram', $late->platform_label);
        $this->assertTrue($late->is_overdue);
        $this->assertFalse($done->is_overdue);
        $this->assertFalse($future->is_overdue);
    }

    public function test_status_follows_linked_publications(): void
    {
        $deliverable = $this->deliverable(['quantity' => 2, 'status' => 'todo']);

        $first = $this->publication($deliverable);
        $this->assertSame('in_progress', $deliverable->fresh()->status);

        $second = $this->publication($deliverable);
        $this->assertSame('delivered', $deliverable->fresh()->status);

        $first->update(['status' => 'verified']);
        $this->assertSame('delivered', $deliverable->fresh()->status);

        $second->update(['status' => 'verified']);
        $this->assertSame('validated', $deliverable->fresh()->status);

        $second->update(['status' => 'rejected']);
        $this->assertSame('in_progress', $deliverable->fresh()->status);

        $second->delete();
        $first->delete();
        $this->assertSame('todo', $deliverable->fresh()->status);
    }

    public function test_manual_status_is_kept_until_a_publication_is_linked(): void
    {
        $deliverable = $this->deliverable(['quantity' => 2, 'status' => 'in_progress', 'status_set_manually' => true]);
        $deliverable->refreshStatus();
        $this->assertSame('in_progress', $deliverable->fresh()->status);

        $this->publication($deliverable->fresh());

        $fresh = $deliverable->fresh();
        $this->assertSame('in_progress', $fresh->status);
        $this->assertFalse($fresh->status_set_manually, 'le calcul automatique reprend la main');
    }

    public function test_moving_a_publication_refreshes_both_deliverables(): void
    {
        $a = $this->deliverable(['quantity' => 1]);
        $b = $this->deliverable(['quantity' => 1]);
        $publication = $this->publication($a, 'verified');
        $this->assertSame('validated', $a->fresh()->status);

        $publication->update(['deliverable_id' => $b->id]);

        $this->assertSame('todo', $a->fresh()->status);
        $this->assertSame('validated', $b->fresh()->status);
    }

    public function test_required_posts_follows_deliverable_quantities(): void
    {
        $story = $this->deliverable(['quantity' => 3]);
        $this->deliverable(['quantity' => 2]);
        $this->assertSame(5, $this->assignment->fresh()->required_posts);

        $story->update(['quantity' => 1]);
        $this->assertSame(3, $this->assignment->fresh()->required_posts);

        $story->delete();
        $this->assertSame(2, $this->assignment->fresh()->required_posts);
    }

    public function test_assignment_deliverables_relation_and_progress_counts(): void
    {
        $deliverable = $this->deliverable(['quantity' => 3]);
        $this->publication($deliverable, 'verified');
        $this->publication($deliverable, 'pending_verification');
        $this->publication($deliverable, 'rejected');

        $loaded = $this->assignment->deliverables()->withProgress()->first();

        $this->assertSame(2, (int) $loaded->submitted_count);
        $this->assertSame(1, (int) $loaded->verified_count);
    }

    public function test_campaign_brand_tags_parsing(): void
    {
        $this->assertSame(['#Orange', '#4GPourTous'], Campaign::parseTags("Orange, #4GPourTous\n#Orange", '#'));
        $this->assertSame(['@orangecameroun', '@max_it'], Campaign::parseTags('orangecameroun @max_it', '@'));
        $this->assertSame([], Campaign::parseTags('  ', '#'));

        $campaign = Campaign::factory()->create(['required_hashtags' => ['#Orange'], 'required_mentions' => ['@orangecameroun']]);
        $this->assertSame(['#Orange'], $campaign->fresh()->required_hashtags);
        $this->assertTrue($campaign->fresh()->hasBrandGuidelines());
        $this->assertFalse(Campaign::factory()->create()->hasBrandGuidelines());
    }
}
