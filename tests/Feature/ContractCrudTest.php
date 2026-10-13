<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create(['name' => 'Jane Doe']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'influencer_id' => $this->influencer->id,
            'type' => 'exclusive',
            'start_date' => '2026-11-01',
            'end_date' => '2027-10-31',
            'amount' => '1500000',
            'payment_terms' => 'Mensuel, virement le 5 du mois',
            'status' => 'active',
            'notes' => 'Renouvelable',
        ], $overrides);
    }

    public function test_admin_creates_contract_with_private_file(): void
    {
        $response = $this->actingAs($this->admin)->post(route('contracts.store'), $this->payload([
            'file' => UploadedFile::fake()->create('contrat signé.pdf', 200, 'application/pdf'),
        ]));

        $contract = Contract::firstOrFail();
        $response->assertRedirect(route('contracts.show', $contract));
        $this->assertSame($this->influencer->id, $contract->influencer_id);
        $this->assertSame('exclusive', $contract->type);
        $this->assertSame('1500000.00', $contract->amount);
        $this->assertSame($this->admin->id, $contract->created_by);
        $this->assertSame('contrat signé.pdf', $contract->file_original_name);
        $this->assertStringStartsWith("contracts/{$this->influencer->id}/", $contract->file_path);
        Storage::disk('local')->assertExists($contract->file_path);
    }

    public function test_validation_rejects_bad_dates_wrong_role_and_unknown_values(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->actingAs($this->admin)->post(route('contracts.store'), $this->payload([
            'influencer_id' => $supervisor->id,
            'end_date' => '2026-10-01',
            'type' => 'zzz',
            'status' => 'zzz',
            'file' => UploadedFile::fake()->create('virus.exe', 10),
        ]))->assertSessionHasErrors(['influencer_id', 'end_date', 'type', 'status', 'file']);

        $this->assertDatabaseCount('contracts', 0);
    }

    public function test_create_form_prefills_influencer(): void
    {
        $this->actingAs($this->admin)->get(route('contracts.create', ['influencer' => $this->influencer->id]))
            ->assertOk()
            ->assertSee('value="'.$this->influencer->id.'" selected', false)
            ->assertSee('Exclusif');
    }

    public function test_supervisor_and_influencer_cannot_create_update_or_delete(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->influencer->id);
        $contract = Contract::factory()->create(['influencer_id' => $this->influencer->id]);

        foreach ([$supervisor, $this->influencer] as $user) {
            $this->actingAs($user)->get(route('contracts.create'))->assertForbidden();
            $this->actingAs($user)->post(route('contracts.store'), $this->payload())->assertForbidden();
            $this->actingAs($user)->put(route('contracts.update', $contract), $this->payload())->assertForbidden();
            $this->actingAs($user)->delete(route('contracts.destroy', $contract))->assertForbidden();
        }
    }

    public function test_view_and_download_rights(): void
    {
        Storage::disk('local')->put('contracts/1/c.pdf', 'pdf');
        $contract = Contract::factory()->create([
            'influencer_id' => $this->influencer->id,
            'file_path' => 'contracts/1/c.pdf',
            'file_original_name' => 'contrat.pdf',
        ]);
        $linked = User::factory()->supervisor()->create();
        $linked->influencers()->attach($this->influencer->id);
        $unlinked = User::factory()->supervisor()->create();
        $otherInfluencer = User::factory()->influencer()->create();

        foreach ([$this->admin, $linked, $this->influencer] as $user) {
            $this->actingAs($user)->get(route('contracts.show', $contract))->assertOk()->assertSee('Jane Doe');
            $this->actingAs($user)->get(route('contracts.download', $contract))
                ->assertOk()
                ->assertDownload('contrat.pdf');
        }

        foreach ([$unlinked, $otherInfluencer] as $user) {
            $this->actingAs($user)->get(route('contracts.show', $contract))->assertForbidden();
            $this->actingAs($user)->get(route('contracts.download', $contract))->assertForbidden();
        }
    }

    public function test_download_without_file_returns_404(): void
    {
        $contract = Contract::factory()->create(['file_path' => null]);

        $this->actingAs($this->admin)->get(route('contracts.download', $contract))->assertNotFound();
    }

    public function test_index_is_scoped_and_filterable(): void
    {
        $other = User::factory()->influencer()->create(['name' => 'Bob Other']);
        Contract::factory()->create(['influencer_id' => $this->influencer->id, 'end_date' => now()->addDays(10)->toDateString()]);
        Contract::factory()->create(['influencer_id' => $other->id, 'end_date' => now()->addYear()->toDateString()]);

        $supervisor = User::factory()->supervisor()->create();
        $supervisor->influencers()->attach($this->influencer->id);

        $this->actingAs($this->admin)->get(route('contracts.index'))
            ->assertOk()->assertSee('Jane Doe')->assertSee('Bob Other');

        $this->actingAs($this->admin)->get(route('contracts.index', ['expiring' => 1]))
            ->assertOk()->assertSee('Jane Doe')->assertDontSee('Bob Other');

        $this->actingAs($supervisor)->get(route('contracts.index'))
            ->assertOk()->assertSee('Jane Doe')->assertDontSee('Bob Other');

        $this->actingAs($other)->get(route('contracts.index'))
            ->assertOk()->assertSee('Bob Other')->assertDontSee('Jane Doe');
    }

    public function test_changing_end_date_resets_alerts_and_replacing_file_deletes_old_one(): void
    {
        Storage::disk('local')->put('contracts/1/old.pdf', 'old');
        $contract = Contract::factory()->create([
            'influencer_id' => $this->influencer->id,
            'file_path' => 'contracts/1/old.pdf',
            'file_original_name' => 'old.pdf',
        ]);
        ContractAlert::create(['contract_id' => $contract->id, 'days_before' => 30, 'sent_at' => now()]);

        $this->actingAs($this->admin)->put(route('contracts.update', $contract), $this->payload([
            'end_date' => '2028-01-31',
            'file' => UploadedFile::fake()->create('new.pdf', 50, 'application/pdf'),
        ]))->assertRedirect(route('contracts.show', $contract));

        $contract->refresh();
        $this->assertSame('2028-01-31', $contract->end_date->toDateString());
        $this->assertSame(0, $contract->alerts()->count());
        $this->assertSame($this->admin->id, $contract->updated_by);
        Storage::disk('local')->assertMissing('contracts/1/old.pdf');
        Storage::disk('local')->assertExists($contract->file_path);
    }

    public function test_update_without_date_change_keeps_alerts(): void
    {
        $contract = Contract::factory()->create(['influencer_id' => $this->influencer->id, 'end_date' => '2027-10-31']);
        ContractAlert::create(['contract_id' => $contract->id, 'days_before' => 30, 'sent_at' => now()]);

        $this->actingAs($this->admin)->put(route('contracts.update', $contract), $this->payload(['notes' => 'Modifié']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $contract->alerts()->count());
    }

    public function test_admin_deletes_contract_softly(): void
    {
        $contract = Contract::factory()->create();

        $this->actingAs($this->admin)->delete(route('contracts.destroy', $contract))
            ->assertRedirect(route('contracts.index'));

        $this->assertSoftDeleted($contract);
    }
}
