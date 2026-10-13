<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema(): void
    {
        $this->assertTrue(Schema::hasColumns('payments', [
            'influencer_id', 'contract_id', 'assignment_id', 'label', 'amount', 'due_date', 'status',
            'paid_at', 'method', 'reference', 'receipt_path', 'receipt_original_name', 'notes',
            'created_by', 'deleted_at',
        ]));
    }

    public function test_labels_and_overdue(): void
    {
        $overdue = Payment::factory()->create(['status' => 'pending', 'due_date' => now()->subDay()->toDateString()]);
        $upcoming = Payment::factory()->create(['status' => 'pending', 'due_date' => now()->addDay()->toDateString()]);
        $paid = Payment::factory()->paid()->create(['due_date' => now()->subMonth()->toDateString(), 'method' => 'mobile_money']);

        $this->assertTrue($overdue->is_overdue);
        $this->assertSame('En retard', $overdue->display_status_label);
        $this->assertSame('danger', $overdue->display_status_badge);
        $this->assertFalse($upcoming->is_overdue);
        $this->assertSame('En attente', $upcoming->display_status_label);
        $this->assertFalse($paid->is_overdue);
        $this->assertSame('Payé', $paid->display_status_label);
        $this->assertSame('Mobile Money', $paid->method_label);
    }

    public function test_status_scopes_including_overdue(): void
    {
        $overdue = Payment::factory()->create(['status' => 'pending', 'due_date' => now()->subDays(3)->toDateString()]);
        $pending = Payment::factory()->create(['status' => 'pending', 'due_date' => now()->addDays(3)->toDateString()]);
        $noDate = Payment::factory()->create(['status' => 'pending', 'due_date' => null]);
        Payment::factory()->paid()->create();
        Payment::factory()->create(['status' => 'cancelled', 'due_date' => now()->subDays(3)->toDateString()]);

        $this->assertSame([$overdue->id], Payment::overdue()->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$overdue->id, $pending->id, $noDate->id], Payment::pending()->pluck('id')->all());
    }

    public function test_visible_to_scope(): void
    {
        $admin = User::factory()->admin()->create();
        $supervisor = User::factory()->supervisor()->create();
        $mine = User::factory()->influencer()->create();
        $supervisor->influencers()->attach($mine->id);
        $a = Payment::factory()->create(['influencer_id' => $mine->id]);
        $b = Payment::factory()->create();

        $this->assertEqualsCanonicalizing([$a->id, $b->id], Payment::visibleTo($admin)->pluck('id')->all());
        $this->assertSame([$a->id], Payment::visibleTo($mine)->pluck('id')->all());
        $this->assertSame([], Payment::visibleTo($supervisor)->pluck('id')->all());
    }

    public function test_contract_payment_summary(): void
    {
        $contract = Contract::factory()->create(['amount' => 1000000]);
        Payment::factory()->paid()->create(['contract_id' => $contract->id, 'influencer_id' => $contract->influencer_id, 'amount' => 500000]);
        Payment::factory()->create(['contract_id' => $contract->id, 'influencer_id' => $contract->influencer_id, 'amount' => 300000, 'status' => 'pending']);
        Payment::factory()->create(['contract_id' => $contract->id, 'influencer_id' => $contract->influencer_id, 'amount' => 900000, 'status' => 'cancelled']);

        $summary = $contract->fresh()->paymentSummary();

        $this->assertSame(1000000.0, $summary['contract_amount']);
        $this->assertSame(500000.0, $summary['paid']);
        $this->assertSame(300000.0, $summary['pending']);
        $this->assertSame(200000.0, $summary['remaining_to_schedule']);
        $this->assertCount(3, $contract->payments);
    }

    public function test_totals_helper(): void
    {
        Payment::factory()->paid()->create(['amount' => 100]);
        Payment::factory()->create(['amount' => 40, 'status' => 'pending', 'due_date' => now()->subDay()->toDateString()]);
        Payment::factory()->create(['amount' => 60, 'status' => 'pending', 'due_date' => now()->addDay()->toDateString()]);
        Payment::factory()->create(['amount' => 999, 'status' => 'cancelled']);

        $this->assertSame(['paid' => 100.0, 'pending' => 100.0, 'overdue' => 40.0], Payment::totalsFor(Payment::query()));
    }
}
