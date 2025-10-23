<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampaignController extends Controller
{
    /**
     * Affiche la liste des campagnes
     */
    public function index(Request $request)
    {
        $query = Campaign::query();
        
        // Filtrage par statut si spécifié
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par plateforme si spécifié
        if ($request->filled('platform')) {
            $query->whereJsonContains('platforms', $request->platform);
        }
        
        // Trier les campagnes du plus récent au plus ancien
        $query->orderBy('created_at', 'desc');
        
        // Si l'utilisateur est un influenceur, montrer uniquement les campagnes auxquelles il est assigné
        //var_dump("role de l'utilisateur connecté ".Auth::user()->role);
        if (Auth::user()->role == "influencer") {
            // Récupérer les IDs des campagnes assignées à cet influenceur
            $campaignIds = \App\Models\Assignment::where('influencer_id', Auth::id())
                ->distinct('campaign_id')
                ->pluck('campaign_id')
                ->toArray();
                
            if (empty($campaignIds)) {
                // Si aucune campagne n'est assignée, utiliser une requête qui ne retourne rien
                $campaigns = Campaign::whereRaw('1 = 0')->paginate(10);
            } else {
                $campaigns = Campaign::whereIn('id', $campaignIds)
                    ->with('contents')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            }
        } 
        // Pour les superviseurs, administrateurs et super admin, montrer toutes les campagnes
        else {
            $campaigns = $query->with(['creator', 'influencers'])->paginate(10);
        }
        
        return view('campaigns.index', compact('campaigns'));
    }

    /**
     * Affiche le formulaire de création d'une campagne
     */
    public function create()
    {
        // Vérifier que seuls les admins et super admins peuvent créer une campagne
        Gate::authorize('create', Campaign::class);
        
        // Récupérer la liste des superviseurs pour le formulaire
        $supervisors = User::where('role', 'supervisor')->get();
        
        return view('campaigns.create', compact('supervisors'));
    }

    /**
     * Enregistre une nouvelle campagne dans la base de données
     */
    public function store(Request $request)
    {
        // Vérifier que seuls les admins et super admins peuvent créer une campagne
        Gate::authorize('create', Campaign::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|string|max:255',
            'objectives' => 'nullable|array',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,instagram,twitter,tiktok,youtube,linkedin'
        ]);
        
        // Ajouter l'ID du créateur
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';
        
        // Convertir les objectifs en JSON
        if (isset($validated['objectives'])) {
            $validated['objectives'] = json_encode($validated['objectives']);
        }
        
        // Convertir les plateformes en JSON
        $validated['platforms'] = json_encode($validated['platforms']);
        
        $campaign = Campaign::create($validated);
        
        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campagne créée avec succès.');
    }

    /**
     * Affiche les détails d'une campagne
     */
    public function show(Campaign $campaign)
    {
        // Vérifier que l'utilisateur a le droit de voir cette campagne
        Gate::authorize('view', $campaign);
        
        $campaign->load(['creator', 'contents', 'influencers', 'supervisors', 'assignments.influencer', 'assignments.supervisor']);
        
        // Récupérer les statistiques globales de la campagne
        $stats = [
            'totalPublications' => $campaign->publications()->count(),
            'totalInfluencers' => $campaign->influencers()->count(),
            'totalReach' => $campaign->getTotalReachAttribute() ?? 0,
            'totalEngagement' => $campaign->getTotalEngagementAttribute() ?? 0
        ];
        
        return view('campaigns.show', compact('campaign', 'stats'));
    }

    /**
     * Affiche le formulaire d'édition d'une campagne
     */
    public function edit(Campaign $campaign)
    {
        // Vérifier que l'utilisateur a le droit de modifier cette campagne
        Gate::authorize('update', $campaign);
        
        // Récupérer la liste des superviseurs pour le formulaire
        $supervisors = User::where('role', 'supervisor')->get();
        
        return view('campaigns.edit', compact('campaign', 'supervisors'));
    }

    /**
     * Met à jour une campagne dans la base de données
     */
    public function update(Request $request, Campaign $campaign)
    {
        // Vérifier que l'utilisateur a le droit de modifier cette campagne
        Gate::authorize('update', $campaign);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,paused,completed',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|string|max:255',
            'objectives' => 'nullable|array',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,instagram,twitter,tiktok,youtube,linkedin'
        ]);
        
        // Convertir les objectifs en JSON
        if (isset($validated['objectives'])) {
            $validated['objectives'] = json_encode($validated['objectives']);
        }
        
        // Convertir les plateformes en JSON
        $validated['platforms'] = json_encode($validated['platforms']);
        
        $campaign->update($validated);
        
        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campagne mise à jour avec succès.');
    }

    /**
     * Supprime une campagne
     */
    public function destroy(Campaign $campaign)
    {
        // Vérifier que l'utilisateur a le droit de supprimer cette campagne
        Gate::authorize('delete', $campaign);
        
        $campaign->delete();
        
        return redirect()->route('campaigns.index')
            ->with('success', 'Campagne supprimée avec succès.');
    }
    
    /**
     * Affiche le tableau de bord des statistiques d'une campagne
     */
    public function dashboard(Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $campaign)) {
            abort(403, 'Vous n\'êtes pas autorisé à voir cette campagne');
        }
        
        return view('campaigns.dashboard', compact('campaign'));
    }
    
    /**
     * Récupère les contenus approuvés d'une campagne pour les formulaires
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContents(Campaign $campaign)
    {
        // Vérification simplifiée pour permettre aux utilisateurs authentifiés d'accéder aux contenus
        // Les superviseurs et admins ont besoin d'accéder à toutes les campagnes pour créer des missions
        if (!Auth::check()) {
            abort(403, 'Vous devez être connecté pour accéder aux contenus');
        }
        
        // Récupérer les contenus approuvés
        $contents = $campaign->contents()
            ->where('status', \App\Models\Content::STATUS_APPROVED)
            ->select('id', 'title', 'description', 'type')
            ->get();
        
        $formattedContents = [];
        foreach ($contents as $content) {
            $formattedContents[] = [
                'id' => $content->id,
                'text' => $content->title . ' (' . $content->type . ')'
            ];
        }
        
        return response()->json($formattedContents);
    }
    
    /**
     * Affiche le tableau de bord des statistiques d'une campagne
     */
    public function dashboardStats(Campaign $campaign)
    {
        // Vérifier que l'utilisateur a le droit de voir cette campagne
        Gate::authorize('view', $campaign);
        
        $campaign->load(['publications.latestStats', 'influencers']);
        
        // Calculer les métriques pour le tableau de bord
        $totalPublications = $campaign->publications()->count();
        $totalInfluencers = $campaign->influencers()->count();
        $totalReach = $campaign->getTotalReachAttribute();
        $totalEngagement = $campaign->getTotalEngagementAttribute();
        
        // Répartition par plateforme
        $platformStats = $campaign->publications()
            ->selectRaw('platform, count(*) as count')
            ->groupBy('platform')
            ->get()
            ->pluck('count', 'platform')
            ->toArray();
            
        // Top influencers par engagement
        $topInfluencers = $campaign->publications()
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->join('users', 'publications.influencer_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, users.profile_photo, SUM(stats.likes + stats.comments + stats.shares + stats.saves) as total_engagement')
            ->groupBy('users.id', 'users.name', 'users.profile_photo')
            ->orderByDesc('total_engagement')
            ->limit(5)
            ->get();
            
        return view('campaigns.dashboard', compact(
            'campaign', 
            'totalPublications', 
            'totalInfluencers', 
            'totalReach',
            'totalEngagement',
            'platformStats',
            'topInfluencers'
        ));
    }
}
