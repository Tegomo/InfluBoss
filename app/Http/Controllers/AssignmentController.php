<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Supervisor;
use App\Models\Publication;
use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campaignId = $request->query('campaign_id');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Assignment::query()
            // Si l'utilisateur est un influenceur, ne montrer que ses missions
            ->when(Auth::user()->role === 'influencer', function($q) {
                return $q->where('influencer_id', Auth::id());
            })
            // Si l'utilisateur est un superviseur, montrer les missions qu'il supervise OU les missions de ses influenceurs assignés
            ->when(Auth::user()->role === 'supervisor', function($q) {
                // Récupérer les IDs des influenceurs assignés à ce superviseur
                $influencerIds = \DB::table('supervisor_influencer')
                    ->where('supervisor_id', Auth::id())
                    ->pluck('influencer_id')
                    ->toArray();
                
                // Montrer les missions où le superviseur est directement assigné OU les missions de ses influenceurs
                return $q->where(function($query) use ($influencerIds) {
                    $query->where('supervisor_id', Auth::id())
                          ->orWhereIn('influencer_id', $influencerIds);
                });
            })
            ->when($campaignId, function($q) use ($campaignId) {
                return $q->where('campaign_id', $campaignId);
            })
            ->when($status, function($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($search, function($q) use ($search) {
                return $q->whereHas('influencer', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->with(['campaign', 'influencer', 'supervisor'])
            ->withCount('publications') // Ajouter le comptage des publications
            ->orderBy('created_at', 'desc');

        $assignments = $query->paginate(15);

        return view('assignments.index', [
            'assignments' => $assignments,
            'campaigns' => Campaign::select('id', 'name as title')->get(),
            'statuses' => ['pending', 'accepted', 'in_progress', 'completed'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Récupérer les campagnes avec toutes les colonnes nécessaires
        $campaigns = Campaign::select('id', 'name as title', 'description')->get();
        
        // Récupérer les influenceurs selon le rôle de l'utilisateur
        if (Auth::user()->role === 'supervisor') {
            // Pour les superviseurs, récupérer uniquement leurs influenceurs assignés
            // Récupérer les IDs des influenceurs supervisés via la table supervisor_influencer
            $influencerIds = DB::table('supervisor_influencer')
                ->where('supervisor_id', Auth::id())
                ->pluck('influencer_id')
                ->toArray();
            
            if (empty($influencerIds)) {
                $influencers = collect(); // Collection vide si aucun influenceur n'est assigné
            } else {
                $influencers = \App\Models\User::where('role', 'influencer')
                    ->whereIn('id', $influencerIds)
                    ->get();
            }
            $supervisors = collect([Auth::user()]); // Seul le superviseur connecté
        } else {
            // Pour les admins, récupérer tous les influenceurs et superviseurs
            $influencers = \App\Models\User::where('role', 'influencer')->get();
            $supervisors = \App\Models\User::where('role', 'supervisor')->get();
        }
        
        // Par défaut, pas de contenus sélectionnés puisque aucune campagne n'est sélectionnée initialement
        $contents = [];
        
        // Si une campagne est présélectionnée (depuis un autre formulaire par exemple)
        $campaignId = request('campaign_id');
        if ($campaignId) {
            $contents = \App\Models\Content::where('campaign_id', $campaignId)
                ->where('status', \App\Models\Content::STATUS_APPROVED)
                ->get();
        }
        
        return view('assignments.create', [
            'campaigns' => $campaigns,
            'influencers' => $influencers,
            'supervisors' => $supervisors,
            'contents' => $contents,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'influencer_ids' => 'required|array|min:1',
            'influencer_ids.*' => 'exists:users,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'content_id' => 'required|exists:contents,id',
            'status' => 'required|in:pending,accepted,in_progress,completed',
            'deadline' => 'nullable|date',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'required_posts' => 'nullable|integer|min:1',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|in:instagram,facebook,tiktok,youtube,twitter,linkedin,blog,other',
        ]);
        
        // Si l'utilisateur est un superviseur, on l'assigne automatiquement comme superviseur de la mission
        if (Auth::user()->role === 'supervisor' && empty($validated['supervisor_id'])) {
            $validated['supervisor_id'] = Auth::id();
        }
        
        // Récupérer les IDs des influenceurs sélectionnés
        $influencerIds = $validated['influencer_ids'];
        unset($validated['influencer_ids']); // Retirer du tableau pour éviter des erreurs lors de la création
        
        $createdCount = 0;
        $errors = [];
        
        // Créer une mission pour chaque influenceur sélectionné
        foreach ($influencerIds as $influencerId) {
            try {
                $assignmentData = array_merge($validated, ['influencer_id' => $influencerId]);
                Assignment::create($assignmentData);
                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Erreur lors de la création de la mission pour l'influenceur ID: {$influencerId}. {$e->getMessage()}";
            }
        }
        
        if ($createdCount > 0) {
            $message = $createdCount === 1 
                ? 'Mission créée avec succès.' 
                : "{$createdCount} missions créées avec succès.";
                
            if (!empty($errors)) {
                return redirect()->route('assignments.index')
                    ->with('warning', $message . ' Certaines missions n\'ont pas pu être créées.')
                    ->with('errors', $errors);
            }
            
            return redirect()->route('assignments.index')
                ->with('success', $message);
        } else {
            return redirect()->route('assignments.create')
                ->with('error', 'Aucune mission n\'a pu être créée. Veuillez réessayer.')
                ->with('errors', $errors)
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Assignment $assignment)
    {
        $assignment->load([
            'campaign',
            'influencer',
            'supervisor',
            'publications' => function($q) {
                $q->with(['latestStats']);
            },
            'notes' => function($q) {
                $q->with('user')->latest();
            }
        ]);

        return view('assignments.show', [
            'assignment' => $assignment,
            'campaigns' => Campaign::all(),
            'influencers' => Influencer::all(),
            'supervisors' => Supervisor::all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Assignment $assignment)
    {
        // Vérifier que l'utilisateur a le droit de modifier (admin, super_admin ou superviseur)
        if (Auth::user()->role === 'influencer') {
            abort(403, 'Vous n\'avez pas l\'autorisation de modifier cette mission.');
        }
        
        // Si c'est un superviseur, vérifier qu'il supervise bien cet assignment
        if (Auth::user()->role === 'supervisor' && $assignment->supervisor_id !== Auth::id()) {
            abort(403, 'Vous ne supervisez pas cette mission.');
        }
        
        // Charger l'assignment avec ses relations
        $assignment->load(['campaign', 'influencer', 'supervisor']);
        
        // Récupérer les campagnes avec les colonnes nécessaires
        $campaigns = Campaign::select('id', 'name as title', 'description')->get();
        
        // Récupérer les influenceurs selon le rôle de l'utilisateur
        if (Auth::user()->role === 'supervisor') {
            // Pour les superviseurs, récupérer uniquement leurs influenceurs assignés
            $influencerIds = Assignment::where('supervisor_id', Auth::id())
                ->distinct('influencer_id')
                ->pluck('influencer_id')
                ->toArray();
            $influencers = \App\Models\User::where('role', 'influencer')
                ->whereIn('id', $influencerIds)
                ->get();
            $supervisors = collect([Auth::user()]); // Seul le superviseur connecté
        } else {
            // Pour les admins, récupérer tous les influenceurs et superviseurs
            $influencers = \App\Models\User::where('role', 'influencer')->get();
            $supervisors = \App\Models\User::where('role', 'supervisor')->get();
        }
        
        // Charger les contenus associés à la campagne de l'assignment
        $contents = [];
        if ($assignment->campaign_id) {
            $contents = \App\Models\Content::where('campaign_id', $assignment->campaign_id)
                ->where('status', \App\Models\Content::STATUS_APPROVED)
                ->get();
        }
        
        return view('assignments.edit', [
            'assignment' => $assignment,
            'campaigns' => $campaigns,
            'influencers' => $influencers,
            'supervisors' => $supervisors,
            'contents' => $contents,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assignment $assignment)
    {
        // Vérifier que l'utilisateur a le droit de modifier (admin, super_admin ou superviseur)
        if (Auth::user()->role === 'influencer') {
            abort(403, 'Vous n\'avez pas l\'autorisation de modifier cette mission.');
        }
        
        // Si c'est un superviseur, vérifier qu'il supervise bien cet assignment
        if (Auth::user()->role === 'supervisor' && $assignment->supervisor_id !== Auth::id()) {
            abort(403, 'Vous ne supervisez pas cette mission.');
        }
        
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'influencer_id' => 'required|exists:users,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'content_id' => 'required|exists:contents,id',
            'status' => 'required|in:pending,accepted,in_progress,completed',
            'deadline' => 'nullable|date',
            'compensation' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'required_posts' => 'required|integer|min:1',
            'platforms' => 'nullable|array',
            'platforms.*' => 'in:instagram,facebook,twitter,tiktok,youtube,linkedin,blog,other',
        ]);

        $assignment->update($validated);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Mission mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        return redirect()->route('assignments.index')
            ->with('success', 'Mission supprimée avec succès.');
    }

    /**
     * Afficher le tableau de bord de la mission
     */
    public function dashboard(Assignment $assignment)
    {
        $assignment->load([
            'campaign',
            'influencer',
            'supervisor',
            'publications' => function($q) {
                $q->with(['latestStats']);
            },
            'notes' => function($q) {
                $q->with('user')->latest();
            }
        ]);

        // Statistiques de performance
        $performanceStats = [
            'total_publications' => $assignment->publications->count(),
            'required_publications' => $assignment->required_posts,
            'completion_rate' => $assignment->publications->count() / $assignment->required_posts * 100,
            'total_engagement' => $assignment->publications->sum(function($pub) {
                return ($pub->latestStats ? $pub->latestStats->likes + $pub->latestStats->comments + $pub->latestStats->shares : 0);
            }),
            'average_engagement' => $assignment->publications->avg(function($pub) {
                return ($pub->latestStats ? $pub->latestStats->likes + $pub->latestStats->comments + $pub->latestStats->shares : 0);
            }),
        ];

        return view('assignments.dashboard', [
            'assignment' => $assignment,
            'performanceStats' => $performanceStats,
        ]);
    }
}
