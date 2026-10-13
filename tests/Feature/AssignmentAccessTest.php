<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    private User $otherInfluencer;

    private User $assignedSupervisor;

    private User $linkedSupervisor;

    private User $otherSupervisor;

    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create();
        $this->otherInfluencer = User::factory()->influencer()->create();
        $this->assignedSupervisor = User::factory()->supervisor()->create();
        $this->linkedSupervisor = User::factory()->supervisor()->create();
        $this->otherSupervisor = User::factory()->supervisor()->create();
        $this->linkedSupervisor->influencers()->attach($this->influencer->id);

        $this->assignment = Assignment::factory()->create([
            'influencer_id' => $this->influencer->id,
            'supervisor_id' => $this->assignedSupervisor->id,
            'compensation' => 250000,
        ]);
    }

    public function test_mission_page_access_by_role(): void
    {
        foreach ([$this->admin, $this->influencer, $this->assignedSupervisor, $this->linkedSupervisor] as $user) {
            $this->actingAs($user)->get(route('assignments.show', $this->assignment))->assertOk();
        }

        foreach ([$this->otherInfluencer, $this->otherSupervisor] as $user) {
            $this->actingAs($user)->get(route('assignments.show', $this->assignment))->assertForbidden();
        }
    }

    public function test_only_admins_can_delete_a_mission(): void
    {
        foreach ([$this->influencer, $this->otherInfluencer, $this->assignedSupervisor, $this->linkedSupervisor] as $user) {
            $this->actingAs($user)->delete(route('assignments.destroy', $this->assignment))->assertForbidden();
        }
        $this->assertNotSoftDeleted($this->assignment);

        $this->actingAs($this->admin)->delete(route('assignments.destroy', $this->assignment))
            ->assertRedirect(route('assignments.index'));
        $this->assertSoftDeleted($this->assignment);
    }

    public function test_mission_notes_follow_mission_access(): void
    {
        $this->actingAs($this->otherInfluencer)->get(route('notes.index', $this->assignment))->assertForbidden();
        $this->actingAs($this->otherInfluencer)->get(route('notes.create', $this->assignment))->assertForbidden();
        $this->actingAs($this->otherInfluencer)->post(route('notes.store', $this->assignment), ['content' => 'Intrus'])->assertForbidden();
        $this->actingAs($this->otherSupervisor)->post(route('api.notes.store', $this->assignment), ['content' => 'Intrus'])->assertForbidden();
        $this->assertDatabaseCount('notes', 0);

        $this->actingAs($this->linkedSupervisor)->post(route('notes.store', $this->assignment), ['content' => 'Point d\'étape'])
            ->assertRedirect(route('assignments.show', $this->assignment));
        $this->assertDatabaseCount('notes', 1);
    }

    public function test_mission_list_shows_edit_and_delete_buttons_only_when_allowed(): void
    {
        $edit = route('assignments.edit', $this->assignment);
        $delete = route('assignments.destroy', $this->assignment);

        $this->actingAs($this->influencer)->get(route('assignments.index'))
            ->assertOk()
            ->assertSee(route('assignments.show', $this->assignment), false)
            ->assertDontSee($edit, false)
            ->assertDontSee('action="'.$delete.'"', false);

        $this->actingAs($this->assignedSupervisor)->get(route('assignments.index'))
            ->assertOk()
            ->assertSee($edit, false)
            ->assertDontSee('action="'.$delete.'"', false);

        $this->actingAs($this->admin)->get(route('assignments.index'))
            ->assertOk()
            ->assertSee($edit, false)
            ->assertSee('action="'.$delete.'"', false);
    }
}
