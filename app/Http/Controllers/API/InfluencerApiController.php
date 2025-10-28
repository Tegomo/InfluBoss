<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class InfluencerApiController extends Controller
{
    /**
     * Liste des influenceurs accessibles à l'utilisateur.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = User::where('role', 'influencer');
        
        // Filtrer par statut si fourni
        if ($request->has('status')) {
            // Pour le statut, nous devons vérifier dans les assignations
            if ($request->status === 'active') {
                $query->whereHas('assignments', function ($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->status === 'inactive') {
                $query->whereDoesntHave('assignments', function ($q) {
                    $q->where('status', 'active');
                });
            }
        }
        
        // Filtrer par campagne si fourni
        if ($request->has('campaign_id')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('campaign_id', $request->campaign_id);
            });
        }
        
        // Filtrer selon le rôle de l'utilisateur
        if ($user->role === 'supervisor') {
            // Les superviseurs ne voient que leurs influenceurs
            $query->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            });
        }
        // Admin et super_admin voient tous les influenceurs
        
        // Trier les influenceurs du plus récent au plus ancien
        $query->orderBy('created_at', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $influencers = $query->paginate($perPage);
        
        return response()->json($influencers);
    }

    /**
     * Enregistrer un nouvel influenceur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('create', [User::class, 'influencer'])) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à créer un influenceur'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_twitter' => 'nullable|string|max:255',
            'social_tiktok' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'social_linkedin' => 'nullable|string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);
        
        // Préparer les liens sociaux
        $socialLinks = [];
        foreach (['facebook', 'instagram', 'twitter', 'tiktok', 'youtube', 'linkedin'] as $platform) {
            $key = 'social_' . $platform;
            if ($request->filled($key)) {
                $socialLinks[$platform] = $request->$key;
            }
        }
        
        // Créer l'influenceur
        $influencer = new User();
        $influencer->name = $validated['name'];
        $influencer->email = $validated['email'];
        $influencer->password = Hash::make($validated['password']);
        $influencer->role = 'influencer';
        $influencer->phone = $validated['phone'] ?? null;
        $influencer->bio = $validated['bio'] ?? null;
        $influencer->social_links = !empty($socialLinks) ? json_encode($socialLinks) : null;
        $influencer->save();
        
        // Assigner un superviseur si fourni
        if ($request->filled('supervisor_id')) {
            $influencer->supervisors()->attach($request->supervisor_id);
        }
        
        return response()->json($influencer, 201);
    }

    /**
     * Afficher les détails d'un influenceur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $influencer = User::where('id', $id)
            ->where('role', 'influencer')
            ->firstOrFail();
        
        // Vérifier l'autorisation
        if (Gate::denies('view', $influencer)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir cet influenceur'], 403);
        }
        
        // Charger les relations pertinentes
        $influencer->load([
            'supervisors', 
            'assignments.campaign',
            'publications' => function ($query) {
                $query->orderBy('published_at', 'desc')
                      ->take(5);
            },
            'publications.latestStats'
        ]);
        
        // Ajouter les statistiques de l'influenceur
        $stats = [
            'total_publications' => $influencer->publications()->count(),
            'total_campaigns' => $influencer->assignments()->count(),
            'active_campaigns' => $influencer->assignments()->where('status', 'active')->count(),
            'total_engagement' => $influencer->publications()
                ->with('latestStats')
                ->get()
                ->sum(function ($pub) {
                    return $pub->latestStats ? $pub->latestStats->total_engagement : 0;
                }),
        ];
        
        $data = $influencer->toArray();
        $data['stats'] = $stats;
        
        return response()->json($data);
    }

    /**
     * Mettre à jour un influenceur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $influencer = User::where('id', $id)
            ->where('role', 'influencer')
            ->firstOrFail();
        
        // Vérifier l'autorisation
        if (Gate::denies('update', $influencer)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à modifier cet influenceur'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $influencer->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_twitter' => 'nullable|string|max:255',
            'social_tiktok' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'social_linkedin' => 'nullable|string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);
        
        // Mettre à jour les champs standards
        if ($request->has('name')) $influencer->name = $validated['name'];
        if ($request->has('email')) $influencer->email = $validated['email'];
        if ($request->filled('password')) $influencer->password = Hash::make($validated['password']);
        if ($request->has('phone')) $influencer->phone = $validated['phone'];
        if ($request->has('bio')) $influencer->bio = $validated['bio'];
        
        // Mettre à jour les liens sociaux
        $currentSocialLinks = $influencer->social_links ? json_decode($influencer->social_links, true) : [];
        
        foreach (['facebook', 'instagram', 'twitter', 'tiktok', 'youtube', 'linkedin'] as $platform) {
            $key = 'social_' . $platform;
            if ($request->has($key)) {
                if ($request->filled($key)) {
                    $currentSocialLinks[$platform] = $request->$key;
                } else {
                    unset($currentSocialLinks[$platform]);
                }
            }
        }
        
        $influencer->social_links = !empty($currentSocialLinks) ? json_encode($currentSocialLinks) : null;
        $influencer->save();
        
        // Mettre à jour le superviseur si fourni
        if ($request->has('supervisor_id')) {
            $influencer->supervisors()->sync([$request->supervisor_id]);
        }
        
        return response()->json($influencer);
    }

    /**
     * Supprimer un influenceur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $influencer = User::where('id', $id)
            ->where('role', 'influencer')
            ->firstOrFail();
        
        // Vérifier l'autorisation
        if (Gate::denies('delete', $influencer)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cet influenceur'], 403);
        }
        
        // Supprimer l'influenceur (soft delete)
        $influencer->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Obtenir les campagnes d'un influenceur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function campaigns(Request $request, $id)
    {
        $influencer = User::where('id', $id)
            ->where('role', 'influencer')
            ->firstOrFail();
        
        // Vérifier l'autorisation
        if (Gate::denies('view', $influencer)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir les campagnes de cet influenceur'], 403);
        }
        
        // Construire la requête
        $query = Campaign::whereHas('assignments', function ($q) use ($influencer) {
            $q->where('user_id', $influencer->id);
        });
        
        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Trier par date
        $query->orderBy('start_date', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $campaigns = $query->paginate($perPage);
        
        return response()->json($campaigns);
    }
    
    /**
     * Obtenir les publications d'un influenceur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function publications(Request $request, $id)
    {
        $influencer = User::where('id', $id)
            ->where('role', 'influencer')
            ->firstOrFail();
        
        // Vérifier l'autorisation
        if (Gate::denies('view', $influencer)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir les publications de cet influenceur'], 403);
        }
        
        // Construire la requête
        $query = $influencer->publications()->with(['campaign', 'latestStats']);
        
        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrer par plateforme
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }
        
        // Filtrer par campagne
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Trier par date de publication
        $query->orderBy('published_at', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $publications = $query->paginate($perPage);
        
        return response()->json($publications);
    }
    
    /**
     * Obtenir les statistiques d'un influenceur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stats($id)
    {
        $influencer = User::where('id', $id)
            ->where('role', 'influencer')
            ->firstOrFail();
        
        // Vérifier l'autorisation
        if (Gate::denies('view', $influencer)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir les statistiques de cet influenceur'], 403);
        }
        
        // Récupérer toutes les publications vérifiées avec leurs statistiques
        $publications = $influencer->publications()
            ->where('status', 'verified')
            ->with(['latestStats', 'campaign'])
            ->get();
            
        // Calculer les statistiques agrégées
        $totalLikes = 0;
        $totalComments = 0;
        $totalShares = 0;
        $totalViews = 0;
        $totalEngagement = 0;
        $totalPublications = $publications->count();
        $platforms = [];
        $campaigns = [];
        
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
                
                // Compter par campagne
                if ($publication->campaign) {
                    $campaignId = $publication->campaign_id;
                    $campaignName = $publication->campaign->name;
                    
                    if (!isset($campaigns[$campaignId])) {
                        $campaigns[$campaignId] = [
                            'id' => $campaignId,
                            'name' => $campaignName,
                            'count' => 0,
                            'engagement' => 0,
                            'views' => 0
                        ];
                    }
                    
                    $campaigns[$campaignId]['count']++;
                    $campaigns[$campaignId]['engagement'] += $publication->latestStats->total_engagement;
                    $campaigns[$campaignId]['views'] += $publication->latestStats->views;
                }
            }
        }
        
        // Calculer le taux d'engagement moyen
        $avgEngagementRate = $totalPublications > 0 
            ? ($totalViews > 0 ? $totalEngagement / $totalViews * 100 : 0) 
            : 0;
            
        // Convertir les campagnes en tableau indexé
        $campaignsArray = array_values($campaigns);
        
        // Ajouter le taux d'engagement à chaque campagne
        foreach ($campaignsArray as &$campaign) {
            $campaign['engagement_rate'] = $campaign['views'] > 0 
                ? round($campaign['engagement'] / $campaign['views'] * 100, 2) 
                : 0;
        }
        
        return response()->json([
            'influencer_id' => $influencer->id,
            'influencer_name' => $influencer->name,
            'total_publications' => $totalPublications,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'total_shares' => $totalShares,
            'total_views' => $totalViews,
            'total_engagement' => $totalEngagement,
            'avg_engagement_rate' => round($avgEngagementRate, 2),
            'platforms' => $platforms,
            'campaigns' => $campaignsArray
        ]);
    }
}
