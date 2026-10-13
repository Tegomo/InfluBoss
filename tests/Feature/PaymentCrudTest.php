<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create(['name' => 'Jane Doe']);
        $this->contract = Contract::factory()->create(['influencer_id' => $this->influencer->id, 'amount' => 1000000]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'influencer_id' => $this->influencer->id,
            'contract_id' => $this->contract->id,
            'label' => 'Acompte 50 %',
            'amount' => '500000',
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'pending',
        ], $overrides);
    }

    public function test_admin_records_a_paid_payment_with_receipt(): void
    {
        $response = $this->actingAs($this->admin)->post(route('payments.store'), $this->payload([
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
            'method' => 'mobile_money',
            'reference' => 'MP2610.1234',
            'receipt' => UploadedFile::fake()->create('recu.pdf', 50, 'application/pdf'),
        ]));

        $response->assertRedirect(route('contracts.show', $this->contract).'#payments');
        $payment = Payment::firstOrFail();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('mobile_money', $payment->method);
        $this->assertSame('recu.pdf', $payment->receipt_original_name);
        $this->assertSame($this->admin->id, $payment->created_by);
        Storage::disk('local')->assertExists($payment->receipt_path);
    }

    public function test_payment_without_contract_returns_to_influencer_profile(): void
    {
        $this->actingAs($this->admin)->post(route('payments.store'), $this->payload(['contract_id' => '']))
            ->assertRedirect(route('influencers.show', $this->influencer).'#payments');
    }

    public function test_validation_rules(): void
    {
        $other = User::factory()->influencer()->create();
        $foreignContract = Contract::factory()->create(['influencer_id' => $other->id]);
        $foreignMission = Assignment::factory()->create(['influencer_id' => $other->id]);

        $this->actingAs($this->admin)->post(route('payments.store'), $this->payload([
            'contract_id' => $foreignContract->id,
            'assignment_id' => $foreignMission->id,
            'amount' => '0',
            'status' => 'paid',
            'method' => 'bitcoin',
        ]))->assertSessionHasErrors(['contract_id', 'assignment_id', 'amount', 'paid_at', 'method']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_paid_date_is_cleared_when_not_paid(): void
    {
        $this->actingAs($this->admin)->post(route('payments.store'), $this->payload([
            'status' => 'pending', 'paid_at' => now()->toDateString(), 'method' => 'cash',
        ]));

        $this->assertNull(Payment::firstOrFail()->paid_at);
    }

    public function test_access_by_role(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->influencer->id);
        $mine = Payment::factory()->create(['influencer_id' => $this->influencer->id, 'label' => 'Mon paiement']);
        Payment::factory()->create(['label' => 'Paiement d\'un autre']);

        $this->actingAs($supervisor)->get(route('payments.index'))->assertForbidden();

        $this->actingAs($this->influencer)->get(route('payments.index'))
            ->assertOk()->assertSee('Mon paiement')->assertDontSee('Paiement d&#039;un autre', false);

        $this->actingAs($this->influencer)->get(route('payments.create'))->assertForbidden();
        $this->actingAs($this->influencer)->post(route('payments.store'), $this->payload())->assertForbidden();
        $this->actingAs($this->influencer)->put(route('payments.update', $mine), $this->payload())->assertForbidden();
        $this->actingAs($this->influencer)->delete(route('payments.destroy', $mine))->assertForbidden();
        $this->actingAs($this->influencer)->get(route('payments.export'))->assertForbidden();
    }

    public function test_index_filters_and_totals(): void
    {
        Payment::factory()->create(['influencer_id' => $this->influencer->id, 'label' => 'Echeance depassee', 'amount' => 40000, 'due_date' => now()->subDays(5)->toDateString()]);
        Payment::factory()->create(['influencer_id' => $this->influencer->id, 'label' => 'Echeance future', 'amount' => 60000]);
        Payment::factory()->paid()->create(['influencer_id' => $this->influencer->id, 'label' => 'Deja paye', 'amount' => 100000]);

        $this->actingAs($this->admin)->get(route('payments.index'))
            ->assertOk()
            ->assertSee('100 000')   // payé
            ->assertSee('40 000')    // en retard
            ->assertSee('En retard');

        $this->actingAs($this->admin)->get(route('payments.index', ['status' => 'overdue']))
            ->assertOk()->assertSee('Echeance depassee')->assertDontSee('Echeance future')->assertDontSee('Deja paye');

        $this->actingAs($this->admin)->get(route('payments.index', ['status' => 'paid']))
            ->assertOk()->assertSee('Deja paye')->assertDontSee('Echeance future');
    }

    public function test_mark_paid_prefills_the_edit_form(): void
    {
        $payment = Payment::factory()->create(['influencer_id' => $this->influencer->id]);

        $this->actingAs($this->admin)->get(route('payments.edit', [$payment, 'mark_paid' => 1]))
            ->assertOk()
            ->assertSee('<option value="paid" selected', false)
            ->assertSee('value="'.now()->toDateString().'"', false);
    }

    public function test_update_replaces_receipt_and_delete_is_soft(): void
    {
        Storage::disk('local')->put('payments/1/old.pdf', 'old');
        $payment = Payment::factory()->create([
            'influencer_id' => $this->influencer->id,
            'contract_id' => $this->contract->id,
            'receipt_path' => 'payments/1/old.pdf',
            'receipt_original_name' => 'old.pdf',
        ]);

        $this->actingAs($this->admin)->put(route('payments.update', $payment), $this->payload([
            'status' => 'paid', 'paid_at' => now()->toDateString(), 'method' => 'bank_transfer',
            'receipt' => UploadedFile::fake()->create('new.pdf', 20, 'application/pdf'),
        ]))->assertSessionHasNoErrors();

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame($this->admin->id, $payment->updated_by);
        Storage::disk('local')->assertMissing('payments/1/old.pdf');
        Storage::disk('local')->assertExists($payment->receipt_path);

        $this->actingAs($this->admin)->delete(route('payments.destroy', $payment))->assertRedirect();
        $this->assertSoftDeleted($payment);
    }

    public function test_receipt_download_rights(): void
    {
        Storage::disk('local')->put('payments/1/recu.pdf', 'pdf');
        $payment = Payment::factory()->create([
            'influencer_id' => $this->influencer->id,
            'receipt_path' => 'payments/1/recu.pdf',
            'receipt_original_name' => 'recu.pdf',
        ]);
        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->influencer->id);

        $this->actingAs($this->admin)->get(route('payments.receipt', $payment))->assertOk()->assertDownload('recu.pdf');
        $this->actingAs($this->influencer)->get(route('payments.receipt', $payment))->assertOk();
        $this->actingAs($supervisor)->get(route('payments.receipt', $payment))->assertForbidden();
        $this->actingAs(User::factory()->influencer()->create())->get(route('payments.receipt', $payment))->assertForbidden();
    }

    public function test_csv_export_follows_filters(): void
    {
        Payment::factory()->paid()->create(['influencer_id' => $this->influencer->id, 'label' => 'Solde', 'amount' => 500000, 'reference' => 'VIR-42']);
        Payment::factory()->create(['influencer_id' => $this->influencer->id, 'label' => 'Bonus']);

        $content = $this->actingAs($this->admin)->get(route('payments.export', ['status' => 'paid']))
            ->assertOk()->streamedContent();

        $this->assertStringContainsString('Ambassadeur', $content);
        $this->assertStringContainsString('Jane Doe', $content);
        $this->assertStringContainsString('VIR-42', $content);
        $this->assertStringNotContainsString('Bonus', $content);
    }

    public function test_menu_entry(): void
    {
        $this->actingAs($this->admin)->get(route('payments.index'))->assertSee('<p>Paiements</p>', false);
        $this->actingAs($this->influencer)->get(route('payments.index'))->assertSee('<p>Mes paiements</p>', false);
        $supervisor = User::factory()->supervisor()->create();
        $this->actingAs($supervisor)->get(route('contracts.index'))->assertDontSee('<p>Paiements</p>', false);
    }
}
