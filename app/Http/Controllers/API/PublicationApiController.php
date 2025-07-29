<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Services\BrightDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PublicationApiController extends Controller
{
    /**
     * Liste des publications accessibles à l'utilisateur.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Publication::with(['user', 'campaign', 'latestStats']);
        
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
        
        // Filtrer par influenceur
        if ($request->has('influencer_id')) {
            $query->where('user_id', $request->influencer_id);
        }
        
        // Filtrer selon le rôle de l'utilisateur
        if ($user->role === 'influencer') {
            // L'influenceur ne voit que ses propres publications
            $query->where('user_id', $user->id);
        } 
        elseif ($user->role === 'supervisor') {
            // Le superviseur voit les publications des influenceurs qu'il supervise
            $query->whereHas('user', function ($q) use ($user) {
                $q->whereHas('supervisors', function ($q) use ($user) {
                    $q->where('supervisor_id', $user->id);
                });
            });
        }
        // Admin et super_admin voient toutes les publications
        
        // Trier par date de publication
        $query->orderBy('published_at', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $publications = $query->paginate($perPage);
        
        return response()->json($publications);
    }

    /**
     * Enregistrer une nouvelle publication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('create', Publication::class)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à créer une publication'], 403);
        }
        
        $user = Auth::user();
        
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'platform' => 'required|string|in:instagram,tiktok,youtube,twitter,facebook,linkedin',
            'content_type' => 'required|string|in:post,story,reel,video,tweet',
            'published_at' => 'required|date',
            'url' => 'required|url',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'mentions' => 'nullable|string',
            'additional_info' => 'nullable|json',
        ]);
        
        // Vérifier que l'utilisateur est bien assigné à la campagne
        $isAssigned = $user->assignments()->where('campaign_id', $request->campaign_id)->exists();
        
        if (!$isAssigned && $user->role === 'influencer') {
            return response()->json([
                'message' => 'Vous n\'êtes pas assigné à cette campagne'
            ], 403);
        }
        
        // Créer la publication
        $publication = new Publication();
        $publication->user_id = $user->id;
        $publication->campaign_id = $validated['campaign_id'];
        $publication->platform = $validated['platform'];
        $publication->content_type = $validated['content_type'];
        $publication->published_at = $validated['published_at'];
        $publication->url = $validated['url'];
        $publication->caption = $validated['caption'] ?? null;
        $publication->hashtags = $validated['hashtags'] ?? null;
        $publication->mentions = $validated['mentions'] ?? null;
        $publication->additional_info = $validated['additional_info'] ?? null;
        
        // Le statut initial est toujours "pending"
        $publication->status = 'pending';
        
        $publication->save();
        
        // Si l'utilisateur est admin ou super_admin, auto-vérifier la publication
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $publication->status = 'verified';
            $publication->verified_at = now();
            $publication->verified_by = $user->id;
            $publication->save();
            
            // Optionnellement, tenter de récupérer les statistiques
            try {
                $brightDataService = new BrightDataService();
                $brightDataService->fetchStats($publication);
            } catch (\Exception $e) {
                // Juste enregistrer l'erreur sans interrompre le flux
                \Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            }
        }
        
        return response()->json($publication, 201);
    }

    /**
     * Afficher les détails d'une publication.
     *
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function show(Publication $publication)
    {
        // Vérifier l'autorisation
        if (Gate::denies('view', $publication)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à voir cette publication'], 403);
        }
        
        // Charger les relations nécessaires
        $publication->load(['user', 'campaign', 'stats' => function ($query) {
            $query->orderBy('collected_at', 'desc');
        }]);
        
        return response()->json($publication);
    }

    /**
     * Mettre à jour une publication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Publication $publication)
    {
        // Vérifier l'autorisation
        if (Gate::denies('update', $publication)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à modifier cette publication'], 403);
        }
        
        $validated = $request->validate([
            'platform' => 'sometimes|required|string|in:instagram,tiktok,youtube,twitter,facebook,linkedin',
            'content_type' => 'sometimes|required|string|in:post,story,reel,video,tweet',
            'published_at' => 'sometimes|required|date',
            'url' => 'sometimes|required|url',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'mentions' => 'nullable|string',
            'additional_info' => 'nullable|json',
        ]);
        
        // Mettre à jour uniquement les champs fournis
        $publication->fill($validated);
        
        // Si la publication était déjà vérifiée et qu'on modifie l'URL, repasser en attente
        if ($publication->status === 'verified' && $request->has('url') && $publication->url !== $request->url) {
            $publication->status = 'pending';
            $publication->verified_at = null;
            $publication->verified_by = null;
        }
        
        $publication->save();
        
        return response()->json($publication);
    }

    /**
     * Supprimer une publication.
     *
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function destroy(Publication $publication)
    {
        // Vérifier l'autorisation
        if (Gate::denies('delete', $publication)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cette publication'], 403);
        }
        
        $publication->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Vérifier une publication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, Publication $publication)
    {
        // Vérifier l'autorisation
        if (Gate::denies('verify', $publication)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à vérifier cette publication'], 403);
        }
        
        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'feedback' => 'nullable|string',
        ]);
        
        $user = Auth::user();
        
        // Mettre à jour le statut de la publication
        $publication->status = $validated['status'];
        $publication->feedback = $validated['feedback'] ?? null;
        
        if ($validated['status'] === 'verified') {
            $publication->verified_at = now();
            $publication->verified_by = $user->id;
            
            // Tenter de récupérer les statistiques immédiatement
            try {
                $brightDataService = new BrightDataService();
                $result = $brightDataService->fetchStats($publication);
                
                $message = $result 
                    ? 'Publication vérifiée et statistiques récupérées avec succès.' 
                    : 'Publication vérifiée mais erreur lors de la récupération des statistiques.';
                    
                $publication->save();
                
                return response()->json([
                    'message' => $message,
                    'publication' => $publication,
                ]);
                
            } catch (\Exception $e) {
                // Sauvegarder quand même la publication même si erreur stats
                $publication->save();
                
                return response()->json([
                    'message' => 'Publication vérifiée mais erreur lors de la récupération des statistiques: ' . $e->getMessage(),
                    'publication' => $publication,
                ]);
            }
        } else {
            $publication->save();
            
            return response()->json([
                'message' => 'Publication rejetée.',
                'publication' => $publication,
            ]);
        }
    }
    
    /**
     * Rafraîchir les statistiques d'une publication.
     *
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function refreshStats(Publication $publication)
    {
        // Vérifier l'autorisation
        if (Gate::denies('refreshStats', $publication)) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à rafraîchir les statistiques de cette publication'], 403);
        }
        
        // Vérifier que la publication est bien vérifiée
        if ($publication->status !== 'verified') {
            return response()->json([
                'message' => 'Impossible de rafraîchir les statistiques d\'une publication non vérifiée.',
            ], 400);
        }
        
        try {
            $brightDataService = new BrightDataService();
            $result = $brightDataService->fetchStats($publication);
            
            if ($result) {
                return response()->json([
                    'message' => 'Statistiques rafraîchies avec succès.',
                    'publication' => $publication->load('latestStats'),
                ]);
            } else {
                return response()->json([
                    'message' => 'Erreur lors du rafraîchissement des statistiques.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du rafraîchissement des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }
}
