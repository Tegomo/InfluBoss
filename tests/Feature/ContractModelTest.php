<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tables_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('contracts', [
            'influencer_id', 'type', 'start_date', 'end_date', 'amount', 'payment_terms',
            'status', 'file_path', 'file_original_name', 'notes', 'created_by', 'deleted_at',
        ]));
        $this->assertTrue(Schema::hasColumns('contract_alerts', ['contract_id', 'days_before', 'sent_at']));
        $this->assertTrue(Schema::hasColumns('legal_documents', [
            'influencer_id', 'type', 'label', 'file_path', 'original_name', 'mime_type', 'size', 'expires_at', 'uploaded_by',
        ]));
        $this->assertTrue(Schema::hasTable('notifications'));
    }

    public function test_labels_and_days_remaining(): void
    {
        $contract = Contract::factory()->create([
            'type' => 'ambassador',
            'status' => 'active',
            'end_date' => now()->addDays(12)->toDateString(),
        ]);

        $this->assertSame('Ambassadeur', $contract->type_label);
        $this->assertSame('Actif', $contract->status_label);
        $this->assertSame('success', $contract->status_badge);
        $this->assertSame(12, $contract->days_remaining);
        $this->assertTrue($contract->isExpiringSoon());
    }

    public function test_days_remaining_is_null_when_not_active(): void
    {
        $contract = Contract::factory()->create(['status' => 'terminated', 'end_date' => now()->addDays(5)->toDateString()]);

        $this->assertNull($contract->days_remaining);
        $this->assertFalse($contract->isExpiringSoon());
    }

    public function test_expiring_within_scope(): void
    {
        $soon = Contract::factory()->create(['status' => 'active', 'end_date' => now()->addDays(20)->toDateString()]);
        Contract::factory()->create(['status' => 'active', 'end_date' => now()->addDays(60)->toDateString()]);
        Contract::factory()->create(['status' => 'negotiation', 'end_date' => now()->addDays(10)->toDateString()]);
        Contract::factory()->create(['status' => 'active', 'end_date' => now()->subDay()->toDateString()]);

        $this->assertSame([$soon->id], Contract::expiringWithin(30)->pluck('id')->all());
    }

    public function test_visible_to_scope_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $supervisor = User::factory()->supervisor()->create();
        $mine = User::factory()->influencer()->create();
        $other = User::factory()->influencer()->create();
        $supervisor->influencers()->attach($mine->id);

        $a = Contract::factory()->create(['influencer_id' => $mine->id]);
        $b = Contract::factory()->create(['influencer_id' => $other->id]);

        $this->assertEqualsCanonicalizing([$a->id, $b->id], Contract::visibleTo($admin)->pluck('id')->all());
        $this->assertSame([], Contract::visibleTo($supervisor)->pluck('id')->all());
        $this->assertSame([], Contract::visibleTo($mine)->pluck('id')->all());
    }

    public function test_user_relations_and_current_contract(): void
    {
        $influencer = User::factory()->influencer()->create();
        Contract::factory()->create(['influencer_id' => $influencer->id, 'status' => 'expired', 'end_date' => now()->subYear()->toDateString()]);
        $active = Contract::factory()->create(['influencer_id' => $influencer->id, 'status' => 'active']);
        LegalDocument::factory()->create(['influencer_id' => $influencer->id]);

        $this->assertCount(2, $influencer->contracts);
        $this->assertCount(1, $influencer->legalDocuments);
        $this->assertTrue($influencer->currentContract->is($active));
    }

    public function test_deleting_legal_document_removes_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('legal-documents/1/cni.pdf', 'x');
        $document = LegalDocument::factory()->create(['file_path' => 'legal-documents/1/cni.pdf', 'type' => 'identity']);

        $this->assertSame("Pièce d'identité", $document->type_label);
        $document->delete();

        Storage::disk('local')->assertMissing('legal-documents/1/cni.pdf');
        $this->assertDatabaseCount('legal_documents', 0);
    }

    public function test_legal_document_expiry_flag(): void
    {
        $expired = LegalDocument::factory()->create(['expires_at' => now()->subDay()->toDateString()]);
        $valid = LegalDocument::factory()->create(['expires_at' => now()->addYear()->toDateString()]);

        $this->assertTrue($expired->is_expired);
        $this->assertFalse($valid->is_expired);
    }
}
