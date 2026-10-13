<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    private User $supervisor;

    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->supervisor->influencers()->attach($this->influencer->id);
        $this->contract = Contract::factory()->create(['influencer_id' => $this->influencer->id, 'amount' => 1000000]);
        Payment::factory()->paid()->create(['influencer_id' => $this->influencer->id, 'contract_id' => $this->contract->id, 'label' => 'Acompte signature', 'amount' => 400000]);
        Payment::factory()->create(['influencer_id' => $this->influencer->id, 'contract_id' => $this->contract->id, 'label' => 'Solde fin', 'amount' => 350000]);
    }

    public function test_contract_page_shows_payment_schedule_to_admin(): void
    {
        $this->actingAs($this->admin)->get(route('contracts.show', $this->contract))
            ->assertOk()
            ->assertSee('id="payments"', false)
            ->assertSee('Acompte signature')
            ->assertSee('Solde fin')
            ->assertSee('400 000')        // payé
            ->assertSee('350 000')        // en attente
            ->assertSee('250 000')        // reste à planifier
            ->assertSee(e(route('payments.create', ['contract' => $this->contract->id])), false);
    }

    public function test_contract_page_hides_payments_from_supervisor_and_add_link_from_influencer(): void
    {
        $this->actingAs($this->supervisor)->get(route('contracts.show', $this->contract))
            ->assertOk()->assertDontSee('id="payments"', false)->assertDontSee('Acompte signature');

        $this->actingAs($this->influencer)->get(route('contracts.show', $this->contract))
            ->assertOk()->assertSee('Acompte signature')
            ->assertDontSee(e(route('payments.create', ['contract' => $this->contract->id])), false);
    }

    public function test_influencer_profile_payments_tab(): void
    {
        $this->actingAs($this->admin)->get(route('influencers.show', $this->influencer))
            ->assertOk()
            ->assertSee('href="#payments"', false)
            ->assertSee('Solde fin')
            ->assertSee(e(route('payments.create', ['influencer' => $this->influencer->id])), false);

        $this->actingAs($this->supervisor)->get(route('influencers.show', $this->influencer))
            ->assertOk()->assertDontSee('href="#payments"', false)->assertDontSee('Solde fin');

        $this->actingAs($this->influencer)->get(route('influencers.show', $this->influencer))
            ->assertOk()->assertSee('href="#payments"', false)->assertSee('Solde fin')
            ->assertDontSee(e(route('payments.create', ['influencer' => $this->influencer->id])), false);
    }

    public function test_create_form_prefilled_from_contract(): void
    {
        $this->actingAs($this->admin)->get(route('payments.create', ['contract' => $this->contract->id]))
            ->assertOk()
            ->assertSee('<option value="'.$this->influencer->id.'" selected', false)
            ->assertSee('data-influencer-id="'.$this->influencer->id.'" selected', false);
    }
}
