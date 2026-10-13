<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegalDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $influencer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
        $this->influencer = User::factory()->influencer()->create();
    }

    private function upload(User $as, array $overrides = [])
    {
        return $this->actingAs($as)->post(route('influencers.legal-documents.store', $this->influencer), array_merge([
            'type' => 'identity',
            'label' => 'CNI recto-verso',
            'expires_at' => '2030-05-01',
            'file' => UploadedFile::fake()->create('cni.pdf', 300, 'application/pdf'),
        ], $overrides));
    }

    public function test_admin_uploads_document_to_private_disk(): void
    {
        $this->upload($this->admin)
            ->assertRedirect(route('influencers.show', $this->influencer).'#documents')
            ->assertSessionHas('success');

        $document = LegalDocument::firstOrFail();
        $this->assertSame($this->influencer->id, $document->influencer_id);
        $this->assertSame('identity', $document->type);
        $this->assertSame('CNI recto-verso', $document->label);
        $this->assertSame('cni.pdf', $document->original_name);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame('2030-05-01', $document->expires_at->toDateString());
        $this->assertSame($this->admin->id, $document->uploaded_by);
        $this->assertStringStartsWith("legal-documents/{$this->influencer->id}/", $document->file_path);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_upload_validation(): void
    {
        $this->upload($this->admin, ['type' => 'zzz', 'file' => UploadedFile::fake()->create('x.exe', 10)])
            ->assertSessionHasErrors(['type', 'file']);

        $this->upload($this->admin, ['file' => null])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('legal_documents', 0);
    }

    public function test_upload_target_must_be_an_influencer(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->actingAs($this->admin)->post(route('influencers.legal-documents.store', $supervisor), [
            'type' => 'rib',
            'file' => UploadedFile::fake()->create('rib.pdf', 10, 'application/pdf'),
        ])->assertNotFound();
    }

    public function test_supervisors_and_influencers_cannot_upload(): void
    {
        $linked = User::factory()->supervisor()->create();
        $linked->influencers()->attach($this->influencer->id);

        $this->upload($linked)->assertForbidden();
        $this->upload($this->influencer)->assertForbidden();
        $this->assertDatabaseCount('legal_documents', 0);
    }

    public function test_download_rights(): void
    {
        Storage::disk('local')->put('legal-documents/x/rib.pdf', 'rib');
        $document = LegalDocument::factory()->create([
            'influencer_id' => $this->influencer->id,
            'file_path' => 'legal-documents/x/rib.pdf',
            'original_name' => 'RIB Jane.pdf',
        ]);
        $linked = User::factory()->supervisor()->create();
        $linked->influencers()->attach($this->influencer->id);
        $other = User::factory()->influencer()->create();

        foreach ([$this->admin, $this->influencer] as $user) {
            $this->actingAs($user)->get(route('legal-documents.download', $document))
                ->assertOk()->assertDownload('RIB Jane.pdf');
        }

        // Données sensibles : même un superviseur lié n'y a pas accès.
        foreach ([$linked, $other] as $user) {
            $this->actingAs($user)->get(route('legal-documents.download', $document))->assertForbidden();
        }
    }

    public function test_admin_deletes_document_and_file(): void
    {
        Storage::disk('local')->put('legal-documents/x/cni.pdf', 'cni');
        $document = LegalDocument::factory()->create([
            'influencer_id' => $this->influencer->id,
            'file_path' => 'legal-documents/x/cni.pdf',
        ]);

        $this->actingAs($this->influencer)->delete(route('legal-documents.destroy', $document))->assertForbidden();

        $this->actingAs($this->admin)->delete(route('legal-documents.destroy', $document))
            ->assertRedirect(route('influencers.show', $this->influencer).'#documents');

        $this->assertDatabaseCount('legal_documents', 0);
        Storage::disk('local')->assertMissing('legal-documents/x/cni.pdf');
    }
}
