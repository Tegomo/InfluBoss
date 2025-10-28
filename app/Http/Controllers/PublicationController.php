<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\Assignment;
use App\Models\Content;
use App\Services\BrightDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class PublicationController extends Controller
{
    /**
     * Ajoute une note à la publication via son assignment
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function addNote(Request $request, Publication $publication)
    {
        // Vérifier que l'utilisateur a le droit de voir cette publication
        if (Gate::denies('view', $publication)) {
            abort(403, 'Vous n\'avez pas l\'autorisation de voir cette publication.');
        }
        
        // Valider les données de la requête
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);
        
        // Récupérer l'assignment associé à la publication
        $assignment = $publication->assignment;
        
        if (!$assignment) {
            return back()->withErrors(['error' => 'Impossible de trouver la mission associée à cette publication.']);
        }
        
        // Créer la note
        $note = new \App\Models\Note([
            'content' => $validated['note'],
            'user_id' => Auth::id()
        ]);
        
        // Associer la note à l'assignment
        $assignment->notes()->save($note);
        
        return back()->with('success', 'Note ajoutée avec succès.');
    }
    /**
     * Affiche la liste des publications
     */
    public function index(Request $request)
    {
        $query = Publication::query();
        
        // Filtrage par campagne si spécifié
        if ($request->filled('campaign_id')) {
            $query->whereHas('assignment', function($q) use ($request) {
                $q->where('campaign_id', $request->campaign_id);
            });
        }
        
        // Filtrage par influenceur si spécifié
        if ($request->filled('influencer_id')) {
            $query->where('influencer_id', $request->influencer_id);
        }
        
        // Filtrage par statut si spécifié
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par plateforme si spécifié
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        
        // Pour les influenceurs, montrer seulement leurs propres publications
        if (Auth::user()->role === 'influencer') {
            $query->where('influencer_id', Auth::id());
        }
        // Pour les superviseurs, montrer seulement les publications des influenceurs qu'ils supervisent
        elseif (Auth::user()->role === 'supervisor') {
            // Récupérer les IDs des influenceurs supervisés via la table supervisor_influencer
            $influencerIds = DB::table('supervisor_influencer')
                ->where('supervisor_id', Auth::id())
                ->pluck('influencer_id')
                ->toArray();
                
            // Si aucun influenceur n'est assigné, utiliser un ID impossible pour que la requête ne retourne rien
            if (empty($influencerIds)) {
                $query->where('influencer_id', 0); // Aucun utilisateur n'aura cet ID
            } else {
                $query->whereIn('influencer_id', $influencerIds);
            }
        }
        
        $publications = $query->with([
                'influencer', 
                'content', 
                'assignment.campaign',
                'verifier',
                'latestStats'
            ])
            ->orderByDesc('published_at')
            ->paginate(10);
            
        // Récupérer les campagnes pour le filtre
        $campaigns = \App\Models\Campaign::select('id', 'name as title')->get();
        
        // Calculer les statistiques globales
        $globalStats = [
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
            'total_publications' => $publications->total()
        ];
        
        // Collecter les statistiques de toutes les publications
        foreach ($publications as $publication) {
            if ($publication->latestStats) {
                $globalStats['likes'] += $publication->latestStats->likes ?? 0;
                $globalStats['comments'] += $publication->latestStats->comments ?? 0;
                $globalStats['shares'] += $publication->latestStats->shares ?? 0;
            }
        }
            
        return view('publications.index', compact('publications', 'campaigns', 'globalStats'));
    }

    /**
     * Affiche le formulaire de création d'une publication
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $assignments = collect();
        
        // Déterminer les assignations à afficher selon le rôle
        if ($user->role === 'influencer') {
            // Pour les influenceurs : uniquement leurs propres assignations
            $assignments = Assignment::where('influencer_id', Auth::id())
                ->with('campaign')
                ->where('status', 'accepted')
                ->get();
        } elseif ($user->role === 'supervisor') {
            // Pour les superviseurs : les assignations qu'ils supervisent
            $assignments = Assignment::where('supervisor_id', Auth::id())
                ->with(['campaign', 'influencer'])
                ->where('status', 'accepted')
                ->get();
        } elseif (in_array($user->role, ['admin', 'super_admin'])) {
            // Pour les admins : toutes les assignations
            $assignments = Assignment::with(['campaign', 'influencer', 'supervisor'])
                ->where('status', 'accepted')
                ->get();
        }
            
        // Si aucune assignation, rediriger avec message
        if ($assignments->isEmpty()) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'Aucune assignation active disponible.']);
        }
        
        // Pré-sélectionner une assignation si fournie
        $selectedAssignment = null;
        
        // Récupérer tous les contenus approuvés par défaut
        $contents = Content::where('status', Content::STATUS_APPROVED)
            ->orderBy('campaign_id')
            ->orderBy('title')
            ->get();
            
        if ($request->filled('assignment_id')) {
            $selectedAssignment = Assignment::findOrFail($request->assignment_id);
            
            // Vérifier que l'utilisateur est autorisé à accéder à cette assignation selon son rôle
            if ($user->role === 'influencer' && $selectedAssignment->influencer_id !== Auth::id()) {
                abort(403, 'Vous n\'êtes pas autorisé à publier pour cette assignation.');
            } elseif ($user->role === 'supervisor' && $selectedAssignment->supervisor_id !== Auth::id()) {
                abort(403, 'Vous n\'êtes pas autorisé à publier pour cette assignation.');
            } 
            // Les admins peuvent accéder à toutes les assignations, pas besoin de vérification
        }
        
        return view('publications.create', compact('assignments', 'selectedAssignment', 'contents'));
    }

    /**
     * Enregistre une nouvelle publication dans la base de données
     */
    public function store(Request $request)
    {
        // Tous les utilisateurs authentifiés peuvent désormais créer des publications
        $user = Auth::user();
        
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'content_id' => 'nullable|exists:contents,id',
            'platform' => 'required|string|in:facebook,instagram,twitter,tiktok,youtube,linkedin',
            'post_url' => 'required|url',
            'post_content' => 'nullable|string',
            'post_type' => 'required|string|in:post,story,reel,video,short,article',
            'published_at' => 'required|date',
            'screenshot' => 'nullable|image|max:5120', // Max 5MB
            'embed_code' => 'nullable|string',
            'likes' => 'nullable|integer|min:0',
            'comments' => 'nullable|integer|min:0',
            'shares' => 'nullable|integer|min:0',
            'views' => 'nullable|integer|min:0',
            'engagement_rate' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,verified',
        ]);
        
        // Récupérer l'assignment
        $assignment = Assignment::findOrFail($validated['assignment_id']);
        
        // Vérifier les autorisations selon le rôle de l'utilisateur
        if ($user->role === 'influencer' && $assignment->influencer_id !== $user->id) {
            return back()->withErrors(['assignment_id' => 'Cette assignation ne vous appartient pas.']);
        } elseif ($user->role === 'supervisor' && $assignment->supervisor_id !== $user->id) {
            return back()->withErrors(['assignment_id' => 'Cette assignation n\'est pas sous votre supervision.']);
        }
        // Les admins peuvent accéder à toutes les assignations, pas besoin de vérification
        
        // Vérifier que le contenu appartient bien à la campagne de l'assignation
        if (!empty($validated['content_id'])) {
            $content = Content::findOrFail($validated['content_id']);
            if ($content->campaign_id !== $assignment->campaign_id) {
                return back()->withErrors(['content_id' => 'Ce contenu n\'appartient pas à la campagne de cette assignation.']);
            }
        }
        
        // Déterminer l'ID de l'influenceur
        $influencerId = $user->role === 'influencer' ? $user->id : $assignment->influencer_id;
        
        // Créer la publication
        $publicationData = [
            'assignment_id' => $validated['assignment_id'],
            'content_id' => $validated['content_id'] ?? null,
            'influencer_id' => $influencerId,
            'platform' => $validated['platform'],
            'post_url' => $validated['post_url'],
            'post_content' => $validated['post_content'] ?? null,
            'post_type' => $validated['post_type'],
            'published_at' => $validated['published_at'],
            'embed_code' => $validated['embed_code'] ?? null,
            // Si l'utilisateur est un superviseur ou un admin, la publication est automatiquement vérifiée
            'status' => in_array($user->role, ['supervisor', 'admin', 'super_admin']) ? 'verified' : 'pending',
        ];
        
        $publication = Publication::create($publicationData);
        
        // Traiter l'upload de la capture d'écran
        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('publications/screenshots', 'public');
            $publication->screenshot_path = $path;
            $publication->save();
        }
        
        // Créer les statistiques initiales
        if (isset($validated['likes']) || isset($validated['comments']) || isset($validated['shares']) || 
            isset($validated['views']) || isset($validated['engagement_rate'])) {
            
            $publication->stats()->create([
                'likes' => $validated['likes'] ?? 0,
                'comments' => $validated['comments'] ?? 0,
                'shares' => $validated['shares'] ?? 0,
                'views' => $validated['views'] ?? 0,
                'engagement_rate' => $validated['engagement_rate'] ?? 0,
                'recorded_at' => now(),
            ]);
        }
        
        // Si la publication est automatiquement vérifiée (superviseur/admin), vérifier si l'assignment est terminé
        if (in_array($user->role, ['supervisor', 'admin', 'super_admin'])) {
            $assignment = Assignment::find($validated['assignment_id']);
            if ($assignment) {
                $requiredPosts = $assignment->required_posts ?? 0;
                $verifiedPosts = $assignment->publications()->where('status', 'verified')->count();
                
                // Si toutes les publications requises sont vérifiées et que le statut n'est pas déjà "completed"
                if ($requiredPosts > 0 && $verifiedPosts >= $requiredPosts && $assignment->status !== 'completed') {
                    $assignment->status = 'completed';
                    $assignment->save();
                }
            }
        }
        
        // Message différent selon le rôle de l'utilisateur
        $message = in_array($user->role, ['supervisor', 'admin', 'super_admin'])
            ? 'Publication enregistrée et vérifiée avec succès.'
            : 'Publication déclarée avec succès. Elle sera vérifiée par un superviseur.';
        
        return redirect()->route('publications.show', $publication)
            ->with('success', $message);
    }

    /**
     * Affiche les détails d'une publication
     */
    public function show(Publication $publication)
    {
        // Vérifier que l'utilisateur a le droit de voir cette publication
        Gate::authorize('view', $publication);
        
        $publication->load([
            'influencer', 
            'content', 
            'assignment.campaign', 
            'verifier',
            'stats' => function($query) {
                $query->latest('collected_at');
            }
        ]);
        
        return view('publications.show', compact('publication'));
    }

    /**
     * Affiche le formulaire d'édition d'une publication
     */
    public function edit(Publication $publication)
    {
        // Vérifier que l'utilisateur a le droit de modifier cette publication
        Gate::authorize('update', $publication);
        
        // Pour les influenceurs : ne peuvent modifier que leurs propres publications en attente
        if (Auth::user()->role === 'influencer') {
            if (Auth::id() !== $publication->influencer_id || $publication->status !== 'pending') {
                return redirect()->route('publications.show', $publication)
                    ->withErrors(['error' => 'Vous ne pouvez pas modifier cette publication.']);
            }
        }
        
        // Les superviseurs peuvent modifier toutes les publications des influenceurs qu'ils supervisent
        // Les admins peuvent modifier toutes les publications
        
        $user = Auth::user();
        $assignments = collect();
        
        // Déterminer les assignations à afficher selon le rôle
        if ($user->role === 'influencer') {
            // Pour les influenceurs : uniquement leurs propres assignations
            $assignments = Assignment::where('influencer_id', Auth::id())
                ->with('campaign')
                ->where('status', 'accepted')
                ->get();
        } elseif ($user->role === 'supervisor') {
            // Pour les superviseurs : les assignations qu'ils supervisent
            $assignments = Assignment::where('supervisor_id', Auth::id())
                ->with(['campaign', 'influencer'])
                ->where('status', 'accepted')
                ->get();
        } elseif (in_array($user->role, ['admin', 'super_admin'])) {
            // Pour les admins : toutes les assignations
            $assignments = Assignment::with(['campaign', 'influencer', 'supervisor'])
                ->where('status', 'accepted')
                ->get();
        }
        
        $availableContents = Content::where('campaign_id', $publication->assignment->campaign_id)
            ->where('status', 'ready')
            ->get();
            
        return view('publications.edit', compact('publication', 'availableContents', 'assignments'));
    }

    /**
     * Met à jour une publication dans la base de données
     */
    public function update(Request $request, Publication $publication)
    {
        // Vérifier que l'utilisateur a le droit de modifier cette publication
        Gate::authorize('update', $publication);
        
        $validated = $request->validate([
            'content_id' => 'nullable|exists:contents,id',
            'platform' => 'required|string|in:facebook,instagram,twitter,tiktok,youtube,linkedin',
            'post_url' => 'required|url',
            'post_id' => 'nullable|string',
            'post_content' => 'nullable|string',
            'post_type' => 'required|string|in:post,story,reel,video,short,article',
            'published_at' => 'required|date',
        ]);
        
        // Si un contenu est spécifié, vérifier qu'il appartient bien à la campagne
        if (!empty($validated['content_id'])) {
            $content = Content::findOrFail($validated['content_id']);
            if ($content->campaign_id !== $publication->assignment->campaign_id) {
                return back()->withErrors(['content_id' => 'Ce contenu n\'appartient pas à la campagne de cette assignation.']);
            }
        }
        
        // Mettre à jour la publication
        $publication->update($validated);
        
        return redirect()->route('publications.show', $publication)
            ->with('success', 'Publication mise à jour avec succès.');
    }

    /**
     * Vérifie ou rejette une publication
     */
    public function verify(Publication $publication, Request $request)
    {
        // Vérifier que l'utilisateur a le droit de vérifier cette publication
        Gate::authorize('verify', $publication);
        
        // Vérifier que l'utilisateur est bien un superviseur ou admin
        if (Auth::user()->role !== 'supervisor' && Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin') {
            return redirect()->route('publications.show', $publication)
                ->withErrors(['error' => 'Seuls les superviseurs et administrateurs peuvent vérifier les publications.']);
        }
        
        // Si l'utilisateur est superviseur, vérifier qu'il supervise bien cet influenceur
        if (Auth::user()->role === 'supervisor') {
            $assignment = $publication->assignment;
            if ($assignment->supervisor_id !== Auth::id()) {
                return redirect()->route('publications.show', $publication)
                    ->withErrors(['error' => 'Vous ne supervisez pas cet influenceur.']);
            }
        }
        
        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);
        
        if ($validated['action'] === 'verify') {
            $publication->status = 'verified';
            $publication->verified_by = Auth::id();
            $publication->verified_at = now();
            $publication->rejection_reason = null;
        } else {
            $publication->status = 'rejected';
            $publication->verified_by = Auth::id();
            $publication->verified_at = now();
            $publication->rejection_reason = $validated['rejection_reason'];
        }
        
        $publication->save();
        
        // Si la publication est vérifiée, récupérer les statistiques via Bright Data
        if ($validated['action'] === 'verify') {
            // Lancer la récupération des statistiques en arrière-plan
            $brightDataService = app(BrightDataService::class);
            $brightDataService->fetchStats($publication);
            
            // Vérifier si toutes les publications requises sont vérifiées pour marquer l'assignment comme terminé
            $assignment = $publication->assignment;
            if ($assignment) {
                $requiredPosts = $assignment->required_posts ?? 0;
                $verifiedPosts = $assignment->publications()->where('status', 'verified')->count();
                
                // Si toutes les publications requises sont vérifiées et que le statut n'est pas déjà "completed"
                if ($requiredPosts > 0 && $verifiedPosts >= $requiredPosts && $assignment->status !== 'completed') {
                    $assignment->status = 'completed';
                    $assignment->save();
                }
            }
        }
        
        $action = $validated['action'] === 'verify' ? 'vérifiée' : 'rejetée';
        return redirect()->route('publications.show', $publication)
            ->with('success', "Publication $action avec succès.");
    }

    /**
     * Rafraîchir les statistiques d'une publication
     */
    public function refreshStats(Publication $publication)
    {
        // Vérifier que l'utilisateur a le droit de voir cette publication
        Gate::authorize('view', $publication);
        
        // Vérifier que l'utilisateur est bien un superviseur ou admin
        if (Auth::user()->role !== 'supervisor' && Auth::user()->role !== 'admin' && Auth::user()->role !== 'super_admin') {
            return redirect()->route('publications.show', $publication)
                ->withErrors(['error' => 'Seuls les superviseurs et administrateurs peuvent vérifier les publications.']);
        }
        
        // Si l'utilisateur est superviseur, vérifier qu'il supervise bien cet influenceur
        if (Auth::user()->role === 'supervisor') {
            $assignment = $publication->assignment;
            if ($assignment->supervisor_id !== Auth::id()) {
                return redirect()->route('publications.show', $publication)
                    ->withErrors(['error' => 'Vous ne supervisez pas cet influenceur.']);
            }
        }
        
        // Vérifier que la publication est bien vérifiée
        if ($publication->status !== 'verified') {
            return redirect()->route('publications.show', $publication)
                ->withErrors(['error' => 'Seules les publications vérifiées peuvent avoir leurs statistiques rafraîchies.']);
        }
        
        // Récupérer les statistiques via Bright Data
        $brightDataService = app(BrightDataService::class);
        $result = $brightDataService->fetchStats($publication);
        
        if ($result) {
            return redirect()->route('publications.show', $publication)
                ->with('success', 'Statistiques mises à jour avec succès.');
        } else {
            return redirect()->route('publications.show', $publication)
                ->withErrors(['error' => 'Impossible de récupérer les statistiques. Veuillez réessayer plus tard.']);
        }
    }

    /**
     * Supprime une publication
     */
    public function destroy(Publication $publication)
    {
        // Vérifier que l'utilisateur a le droit de supprimer cette publication
        Gate::authorize('delete', $publication);
        
        $publication->delete();
        
        return redirect()->route('publications.index')
            ->with('success', 'Publication supprimée avec succès.');
    }
}
