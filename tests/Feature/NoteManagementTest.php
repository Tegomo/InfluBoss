<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $supervisor;

    private User $influencer;

    private Assignment $assignment;

    private Note $note;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->influencer = User::factory()->influencer()->create();
        $this->assignment = Assignment::factory()->create([
            'influencer_id' => $this->influencer->id,
            'supervisor_id' => $this->supervisor->id,
        ]);
        $this->note = Note::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->supervisor->id,
            'content' => 'Note du superviseur',
        ]);
    }

    public function test_author_can_edit_update_and_delete_own_note(): void
    {
        $this->actingAs($this->supervisor)->get(route('notes.edit', $this->note))->assertOk()->assertSee('Note du superviseur');

        $this->actingAs($this->supervisor)->put(route('notes.update', $this->note), ['content' => 'Note corrigée'])
            ->assertRedirect(route('assignments.show', $this->assignment));
        $this->assertSame('Note corrigée', $this->note->fresh()->content);

        $this->actingAs($this->supervisor)->delete(route('notes.destroy', $this->note))
            ->assertRedirect(route('assignments.show', $this->assignment));
        $this->assertSoftDeleted($this->note);
    }

    public function test_admin_can_manage_any_note(): void
    {
        $this->actingAs($this->admin)->put(route('notes.update', $this->note), ['content' => 'Modérée'])->assertRedirect();
        $this->assertSame('Modérée', $this->note->fresh()->content);

        $this->actingAs($this->admin)->deleteJson(route('api.notes.destroy', $this->note))
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSoftDeleted($this->note);
    }

    public function test_other_users_cannot_edit_or_delete_someone_elses_note(): void
    {
        $otherSupervisor = User::factory()->supervisor()->create();

        foreach ([$this->influencer, $otherSupervisor] as $user) {
            $this->actingAs($user)->get(route('notes.edit', $this->note))->assertForbidden();
            $this->actingAs($user)->put(route('notes.update', $this->note), ['content' => 'Piratée'])->assertForbidden();
            $this->actingAs($user)->delete(route('notes.destroy', $this->note))->assertForbidden();
        }

        $this->assertSame('Note du superviseur', $this->note->fresh()->content);
        $this->assertNotSoftDeleted($this->note);
    }

    public function test_author_who_lost_access_to_the_mission_cannot_change_the_note(): void
    {
        $this->assignment->update(['supervisor_id' => User::factory()->supervisor()->create()->id]);

        $this->actingAs($this->supervisor)->put(route('notes.update', $this->note), ['content' => 'Trop tard'])->assertForbidden();
    }

    public function test_notes_list_shows_buttons_only_for_allowed_notes(): void
    {
        $influencerNote = Note::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->influencer->id,
            'content' => 'Note de l\'ambassadeur',
        ]);

        $this->actingAs($this->influencer)->get(route('notes.index', $this->assignment))
            ->assertOk()
            ->assertSee(route('notes.edit', $influencerNote), false)
            ->assertDontSee(route('notes.edit', $this->note), false)
            ->assertDontSee(route('notes.destroy', $this->note), false);

        $this->actingAs($this->admin)->get(route('notes.index', $this->assignment))
            ->assertOk()
            ->assertSee(route('notes.edit', $this->note), false)
            ->assertSee(route('notes.edit', $influencerNote), false);
    }
}
