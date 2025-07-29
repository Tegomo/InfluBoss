<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampaignApiController extends Controller
{
    /**
     * Liste des campagnes accessibles à l'utilisateur.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Campaign::with(['supervisor']);
        
        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrer par date
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }
        
        // Filtrer selon le rôle de l'utilisateur
        if ($user->role === 'influencer') {
            // Les influenceurs ne voient que les campagnes auxquelles ils sont assignés
            $query->whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } 
        elseif ($user->role === 'supervisor') {
            // Les superviseurs voient les campagnes qu'ils supervisent
            $query->where('supervisor_id', $user->id);
        }
        // Admin et super_admin voient tout
        
        // Pagination des résultats
        $perPage = $request->input('per_page', 15);
        $campaigns = $query->paginate($perPage);
        
        return response()->json($campaigns);
    }

    /**
     * Enregistrer une nouvelle campagne.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('create', Campaign::class)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à créer une campagne'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,paused,completed',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|json',
            'objectives' => 'nullable|json',
            'supervisor_id' => 'nullable|exists:users,id',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email',
            'brief' => 'nullable|string',
        ]);
        
        // Créer la campagne
        $campaign = Campaign::create($validated);
        
        return response()->json($campaign, 201);
    }

    /**
     * Afficher les détails d'une campagne.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $campaign)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir cette campagne'], 403);
        }
        
        // Charger les relations nécessaires
        $campaign->load([
            'supervisor', 
            'assignments', 
            'assignments.user', 
            'contents',
            'publications' => function ($query) {
                $query->where('status', 'verified');
            }
        ]);
        
        return response()->json($campaign);
    }

    /**
     * Mettre à jour une campagne.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('update', $campaign)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à modifier cette campagne'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'status' => 'sometimes|required|in:draft,active,paused,completed',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|json',
            'objectives' => 'nullable|json',
            'supervisor_id' => 'nullable|exists:users,id',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email',
            'brief' => 'nullable|string',
        ]);
        
        // Mettre à jour la campagne
        $campaign->update($validated);
        
        return response()->json($campaign);
    }

    /**
     * Supprimer une campagne.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('delete', $campaign)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cette campagne'], 403);
        }
        
        $campaign->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Obtenir les statistiques d'une campagne.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function stats(Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $campaign)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir les statistiques de cette campagne'], 403);
        }
        
        // Récupérer toutes les publications vérifiées avec leurs statistiques
        $publications = $campaign->publications()
            ->where('status', 'verified')
            ->with(['latestStats'])
            ->get();
            
        // Calculer les statistiques agrégées
        $totalLikes = 0;
        $totalComments = 0;
        $totalShares = 0;
        $totalViews = 0;
        $totalEngagement = 0;
        $totalPublications = $publications->count();
        $platforms = [];
        
        foreach ($publications as $publication) {
            if ($publication->latestStats) {
                $totalLikes += $publication->latestStats->likes;
                $totalComments += $publication->latestStats->comments;
                $totalShares += $publication->latestStats->shares;
                $totalViews += $publication->latestStats->views;
                $totalEngagement += $publication->latestStats->total_engagement;
                
                // Compter par plateforme
                $platform = $publication->platform;
                if (!isset($platforms[$platform])) {
                    $platforms[$platform] = [
                        'count' => 0,
                        'likes' => 0,
                        'comments' => 0,
                        'shares' => 0,
                        'views' => 0
                    ];
                }
                $platforms[$platform]['count']++;
                $platforms[$platform]['likes'] += $publication->latestStats->likes;
                $platforms[$platform]['comments'] += $publication->latestStats->comments;
                $platforms[$platform]['shares'] += $publication->latestStats->shares;
                $platforms[$platform]['views'] += $publication->latestStats->views;
            }
        }
        
        // Calculer le taux d'engagement moyen
        $avgEngagementRate = $totalPublications > 0 
            ? ($totalViews > 0 ? $totalEngagement / $totalViews * 100 : 0) 
            : 0;
        
        return response()->json([
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'status' => $campaign->status,
            'start_date' => $campaign->start_date,
            'end_date' => $campaign->end_date,
            'total_publications' => $totalPublications,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'total_shares' => $totalShares,
            'total_views' => $totalViews,
            'total_engagement' => $totalEngagement,
            'avg_engagement_rate' => round($avgEngagementRate, 2),
            'platforms' => $platforms
        ]);
    }
    
    /**
     * Obtenir les influenceurs d'une campagne.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function influencers(Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $campaign)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir les influenceurs de cette campagne'], 403);
        }
        
        // Récupérer tous les influenceurs assignés à cette campagne
        $influencers = User::whereHas('assignments', function ($query) use ($campaign) {
            $query->where('campaign_id', $campaign->id);
        })
        ->where('role', 'influencer')
        ->with(['assignments' => function ($query) use ($campaign) {
            $query->where('campaign_id', $campaign->id);
        }])
        ->get()
        ->map(function ($influencer) {
            // Personnaliser la réponse pour ne renvoyer que les informations pertinentes
            $assignment = $influencer->assignments->first();
            return [
                'id' => $influencer->id,
                'name' => $influencer->name,
                'email' => $influencer->email,
                'profile_photo' => $influencer->profile_photo,
                'social_links' => $influencer->social_links,
                'assignment_status' => $assignment ? $assignment->status : null,
                'assignment_date' => $assignment ? $assignment->created_at : null,
                'stats' => [
                    'publications_count' => $influencer->publications()
                        ->where('campaign_id', $assignment->campaign_id ?? null)
                        ->count(),
                ]
            ];
        });
        
        return response()->json($influencers);
    }
    
    /**
     * Obtenir les publications d'une campagne.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function publications(Request $request, Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $campaign)) {
            return response()->json(['message' => 'Vous n\'etes pas autorisé à voir les publications de cette campagne'], 403);
        }
        
        // Construire la requête
        $query = $campaign->publications()->with(['user', 'latestStats']);
        
        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrer par plateforme
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }
        
        // Filtrer par influenceur
        if ($request->has('influencer_id')) {
            $query->where('user_id', $request->influencer_id);
        }
        
        // Trier par date de publication
        $query->orderBy('published_at', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $publications = $query->paginate($perPage);
        
        return response()->json($publications);
    }
    
    /**
     * Obtenir les contenus disponibles d'une campagne.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function contents(Campaign $campaign)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $campaign)) {
            return response()->json(['message' => 'Vous n\'etes pas autorisé à voir les contenus de cette campagne'], 403);
        }
        
        // Récupérer les contenus approuvés de la campagne
        $contents = $campaign->contents()
            ->where('status', \App\Models\Content::STATUS_APPROVED)
            ->select('id', 'title', 'description', 'type', 'file_path', 'guidelines')
            ->get();
            
        return response()->json($contents);
    }
}
