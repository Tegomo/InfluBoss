<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\LegalDocument;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractsUiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create(['name' => 'Jane Doe']);
    }

    public function test_bell_shows_real_unread_notifications(): void
    {
        $contract = Contract::factory()->create(['influencer_id' => $this->influencer->id]);
        $this->admin->notify(new ContractExpiringNotification($contract, 15));

        $this->actingAs($this->admin)->get(route('contracts.index'))
            ->assertOk()
            ->assertSee('Le contrat de Jane Doe expire dans 15 jours.')
            ->assertSee('navbar-badge">1<', false)
            ->assertDontSee('1 nouveau message');
    }

    public function test_bell_without_notifications(): void
    {
        $this->actingAs($this->admin)->get(route('contracts.index'))
            ->assertOk()
            ->assertSee('Aucune nouvelle notification')
            ->assertDontSee('navbar-badge', false);
    }

    public function test_opening_a_notification_marks_it_read_and_redirects(): void
    {
        $contract = Contract::factory()->create(['influencer_id' => $this->influencer->id]);
        $this->admin->notify(new ContractExpiringNotification($contract, 7));
        $notification = $this->admin->notifications()->first();

        $this->actingAs($this->admin)->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('contracts.show', $contract));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_someone_elses_notification(): void
    {
        $contract = Contract::factory()->create();
        $this->admin->notify(new ContractExpiringNotification($contract, 7));
        $other = User::factory()->admin()->create();

        $this->actingAs($other)->get(route('notifications.read', $this->admin->notifications()->first()->id))
            ->assertNotFound();
    }

    public function test_mark_all_as_read(): void
    {
        $contract = Contract::factory()->create();
        $this->admin->notify(new ContractExpiringNotification($contract, 30));
        $this->admin->notify(new ContractExpiringNotification($contract, 15));

        $this->actingAs($this->admin)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $this->admin->unreadNotifications()->count());
    }

    public function test_menu_entry_by_role(): void
    {
        $this->actingAs($this->admin)->get(route('contracts.index'))->assertSee('<p>Contrats</p>', false);
        $this->actingAs($this->influencer)->get(route('contracts.index'))->assertSee('<p>Mes contrats</p>', false);
    }

    public function test_admin_sees_contracts_and_documents_tabs_on_profile(): void
    {
        Contract::factory()->create(['influencer_id' => $this->influencer->id, 'type' => 'exclusive', 'amount' => 750000]);
        LegalDocument::factory()->create(['influencer_id' => $this->influencer->id, 'type' => 'rib', 'original_name' => 'rib-jane.pdf']);

        $this->actingAs($this->admin)->get(route('influencers.show', $this->influencer))
            ->assertOk()
            ->assertSee('href="#contracts"', false)
            ->assertSee('Exclusif')
            ->assertSee('750 000')
            ->assertSee(route('contracts.create', ['influencer' => $this->influencer->id]), false)
            ->assertSee('href="#documents"', false)
            ->assertSee('rib-jane.pdf')
            ->assertSee(route('influencers.legal-documents.store', $this->influencer), false)
            ->assertSee('Contrat en cours');
    }

    public function test_linked_supervisor_sees_contracts_but_not_documents(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->influencer->id);
        Contract::factory()->create(['influencer_id' => $this->influencer->id, 'type' => 'exclusive']);
        LegalDocument::factory()->create(['influencer_id' => $this->influencer->id, 'original_name' => 'cni-jane.pdf']);

        $this->actingAs($supervisor)->get(route('influencers.show', $this->influencer))
            ->assertOk()
            ->assertSee('Exclusif')
            ->assertDontSee('href="#documents"', false)
            ->assertDontSee('cni-jane.pdf')
            ->assertDontSee(route('contracts.create', ['influencer' => $this->influencer->id]), false);
    }

    public function test_influencer_sees_own_documents_without_upload_form(): void
    {
        LegalDocument::factory()->create(['influencer_id' => $this->influencer->id, 'original_name' => 'cni-jane.pdf']);

        $this->actingAs($this->influencer)->get(route('influencers.show', $this->influencer))
            ->assertOk()
            ->assertSee('cni-jane.pdf')
            ->assertDontSee(route('influencers.legal-documents.store', $this->influencer), false);
    }
}
