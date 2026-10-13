<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Documents légaux d'un influenceur, gérés depuis l'onglet « Documents » de sa fiche.
 */
class LegalDocumentController extends Controller
{
    public function store(Request $request, User $influencer)
    {
        if ($influencer->role !== 'influencer') {
            abort(404);
        }

        Gate::authorize('create', LegalDocument::class);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(config('influboss.legal_document_types')))],
            'label' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'file' => 'required|file|mimes:'.config('influboss.upload_mimes').'|max:'.config('influboss.upload_max_kb'),
        ], [
            'type.in' => 'Type de document inconnu.',
            'file.required' => 'Choisissez un fichier.',
            'file.mimes' => 'Le document doit être un PDF, JPG ou PNG.',
            'file.max' => 'Le document ne doit pas dépasser 10 Mo.',
        ]);

        $file = $request->file('file');

        LegalDocument::create([
            'influencer_id' => $influencer->id,
            'type' => $validated['type'],
            'label' => $validated['label'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'file_path' => $file->store("legal-documents/{$influencer->id}", LegalDocument::DISK),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->to(route('influencers.show', $influencer).'#documents')
            ->with('success', 'Document ajouté.');
    }

    public function download(LegalDocument $legalDocument)
    {
        Gate::authorize('download', $legalDocument);

        if (! Storage::disk(LegalDocument::DISK)->exists($legalDocument->file_path)) {
            abort(404);
        }

        return Storage::disk(LegalDocument::DISK)->download($legalDocument->file_path, $legalDocument->original_name);
    }

    public function destroy(LegalDocument $legalDocument)
    {
        Gate::authorize('delete', $legalDocument);

        $influencerId = $legalDocument->influencer_id;
        $legalDocument->delete(); // supprime aussi le fichier (LegalDocument::booted)

        return redirect()->to(route('influencers.show', $influencerId).'#documents')
            ->with('success', 'Document supprimé.');
    }
}
