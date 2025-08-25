<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Campaign;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Affiche la liste des contenus
     */
    public function index(Request $request)
    {
        $query = Content::query();
        
        // Filtrage par campagne si spécifié
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Filtrage par statut si spécifié
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par type si spécifié
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Pour les influenceurs, montrer seulement les contenus des campagnes auxquelles ils sont assignés
        if (Auth::user()->role === 'influencer') {
            $campaignIds = Assignment::where('influencer_id', Auth::id())
                ->distinct('campaign_id')
                ->pluck('campaign_id')
                ->toArray();
            $query->whereIn('campaign_id', $campaignIds);
        }
        // Pour les superviseurs, montrer seulement les contenus des campagnes qu'ils supervisent
        elseif (Auth::user()->role === 'supervisor') {
            // Récupérer les IDs des campagnes supervisées
            $campaignIds = Campaign::whereHas('assignments', function($query) {
                $query->where('supervisor_id', Auth::id());
            })->pluck('id')->toArray();
            $query->whereIn('campaign_id', $campaignIds);
        }
        
        $contents = $query->with(['campaign', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(10);
            
        // Récupérer toutes les campagnes pour le filtre
        $campaigns = Campaign::orderBy('name')->get();
            
        return view('contents.index', compact('contents', 'campaigns'));
    }

    /**
     * Affiche le formulaire de création d'un contenu
     */
    public function create(Request $request)
    {
        // Seuls les admins et super admins peuvent créer des contenus
        Gate::authorize('create', Content::class);
        
        // Si une campagne est spécifiée, la pré-sélectionner dans le formulaire
        $selectedCampaign = null;
        if ($request->filled('campaign_id')) {
            $selectedCampaign = Campaign::findOrFail($request->campaign_id);
        }
        
        // Récupérer toutes les campagnes actives pour le formulaire
        $campaigns = Campaign::where('status', '!=', 'completed')
            ->orderBy('name')
            ->get();
            
        return view('contents.create', compact('campaigns', 'selectedCampaign'));
    }

    /**
     * Enregistre un nouveau contenu dans la base de données
     */
    public function store(Request $request)
    {
        // Seuls les admins et super admins peuvent créer des contenus
        Gate::authorize('create', Content::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'campaign_id' => 'required|exists:campaigns,id',
            'type' => 'required|in:image,video,text,link,other',
            'file' => 'required_if:type,image,video|file|max:10240',
            'content_text' => 'required_if:type,text,link|nullable|string',
            'guidelines' => 'nullable|array',
            'status' => 'required|in:draft,approved,archived',
            'publication_date' => 'nullable|date',
        ]);
        
        // Traiter le fichier si fourni
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('content-files', 'public');
            $validated['file_path'] = $path;
        }
        
        // Convertir les directives en JSON si fournies
        if (isset($validated['guidelines'])) {
            $validated['guidelines'] = json_encode($validated['guidelines']);
        }
        
        // Ajouter l'ID du créateur
        $validated['created_by'] = Auth::id();
        
        $content = Content::create($validated);
        
        return redirect()->route('contents.show', $content)
            ->with('success', 'Contenu créé avec succès.');
    }

    /**
     * Affiche les détails d'un contenu
     */
    public function show(Content $content)
    {
        // Vérifier que l'utilisateur a le droit de voir ce contenu
        Gate::authorize('view', $content);
        
        $content->load(['campaign', 'creator', 'publications.influencer']);
        
        return view('contents.show', compact('content'));
    }

    /**
     * Affiche le formulaire d'édition d'un contenu
     */
    public function edit(Content $content)
    {
        // Vérifier que l'utilisateur a le droit de modifier ce contenu
        Gate::authorize('update', $content);
        
        // Récupérer toutes les campagnes actives pour le formulaire
        $campaigns = Campaign::where('status', '!=', 'completed')
            ->orderBy('name')
            ->get();
            
        return view('contents.edit', compact('content', 'campaigns'));
    }

    /**
     * Mise à jour d'un contenu existant
     */
    public function update(Request $request, Content $content)
    {
        // Vérifier que l'utilisateur a le droit de modifier ce contenu
        Gate::authorize('update', $content);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'campaign_id' => 'required|exists:campaigns,id',
            'type' => 'required|in:image,video,text,link,other',
            'file' => 'nullable|file|max:10240',
            'content_text' => 'nullable|string',
            'guidelines' => 'nullable|array',
            'status' => 'required|in:draft,approved,archived',
            'publication_date' => 'nullable|date',
        ]);
        
        // Traiter le fichier si fourni
        if ($request->hasFile('file')) {
            // Supprimer l'ancien fichier si existant
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }
            
            $path = $request->file('file')->store('content-files', 'public');
            $validated['file_path'] = $path;
        }
        
        // Convertir les directives en JSON si fournies
        if (isset($validated['guidelines'])) {
            $validated['guidelines'] = json_encode($validated['guidelines']);
        }
        
        $content->update($validated);
        
        return redirect()->route('contents.show', $content)
            ->with('success', 'Contenu mis à jour avec succès.');
    }

    /**
     * Supprime un contenu
     */
    public function destroy(Content $content)
    {
        // Vérifier que l'utilisateur a le droit de supprimer ce contenu
        Gate::authorize('delete', $content);
        
        // Supprimer le fichier associé si existant
        if ($content->file_path) {
            Storage::disk('public')->delete($content->file_path);
        }
        
        $content->delete();
        
        return redirect()->route('contents.index')
            ->with('success', 'Contenu supprimé avec succès.');
    }
}
