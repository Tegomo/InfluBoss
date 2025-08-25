<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class SupervisorController extends Controller
{
    /**
     * Affiche la liste des superviseurs
     */
    public function index()
    {
        // Seuls les admin et super admin peuvent voir tous les superviseurs
        Gate::authorize('viewAny', [User::class, 'supervisor']);
        
        $supervisors = User::where('role', 'supervisor')
            ->withCount('influencers')
            ->withCount('supervisorAssignments')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('supervisors.index', compact('supervisors'));
    }

    /**
     * Affiche le formulaire de création d'un superviseur
     */
    public function create()
    {
        // Seuls les admin et super admin peuvent créer des superviseurs
        Gate::authorize('create', [User::class, 'supervisor']);
        
        return view('supervisors.create');
    }

    /**
     * Enregistre un nouveau superviseur dans la base de données
     */
    public function store(Request $request)
    {
        // Seuls les admin et super admin peuvent créer des superviseurs
        Gate::authorize('create', [User::class, 'supervisor']);
        
        // Validation des données du formulaire
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'password' => 'required|min:8|confirmed',
        ]);
        
        // Création du superviseur
        $supervisor = new User();
        $supervisor->name = $validated['name'];
        $supervisor->email = $validated['email'];
        $supervisor->phone = $validated['phone'] ?? null;
        $supervisor->is_active = $validated['is_active'] ?? true;
        $supervisor->role = 'supervisor';
        $supervisor->password = Hash::make($validated['password']);
        $supervisor->save();
        
        return redirect()->route('supervisors.index')
            ->with('success', "Le superviseur {$supervisor->name} a été créé avec succès.");
    }

    /**
     * Affiche les détails d'un superviseur
     */
    public function show(User $supervisor)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('view', $supervisor);
        
        // Charger les influenceurs supervisés via la table pivot supervisor_influencer
        $influencers = $supervisor->influencers;
        
        // Charger les campagnes supervisées via les assignments
        $campaigns = Campaign::whereHas('assignments', function($query) use ($supervisor) {
            $query->where('supervisor_id', $supervisor->id);
        })->get();
        
        // On garde ces propriétés pour rétrocompatibilité
        $supervisor->supervisedInfluencers = $influencers;
        $supervisor->supervisedCampaigns = $campaigns;
        
        // Récupérer les dernières publications à vérifier
        $pendingPublications = Publication::whereHas('assignment', function($query) use ($supervisor) {
            $query->where('supervisor_id', $supervisor->id);
        })
        ->where('status', 'pending')
        ->with(['influencer', 'assignment.campaign'])
        ->latest('published_at')
        ->take(5)
        ->get();
        
        // Récupérer les influenceurs qui ne sont assignés à AUCUN superviseur
        $unassignedInfluencers = User::where('role', 'influencer')
            ->where('is_active', true)
            ->whereDoesntHave('supervisors') // Pas d'entrée dans la table pivot
            ->orderBy('name')
            ->get();
        
        return view('supervisors.show', compact('supervisor', 'influencers', 'campaigns', 'pendingPublications', 'unassignedInfluencers'));
    }

    /**
     * Affiche le formulaire d'édition d'un superviseur
     */
    public function edit(User $supervisor)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('update', $supervisor);
        
        return view('supervisors.edit', compact('supervisor'));
    }

    /**
     * Met à jour un superviseur dans la base de données
     */
    public function update(Request $request, User $supervisor)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('update', $supervisor);
        
        // Validation des données du formulaire
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $supervisor->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'password' => 'nullable|min:8|confirmed',
        ]);
        
        // Mise à jour du superviseur
        $supervisor->name = $validated['name'];
        $supervisor->email = $validated['email'];
        $supervisor->phone = $validated['phone'] ?? null;
        $supervisor->is_active = $validated['is_active'] ?? $supervisor->is_active;
        
        // Mise à jour du mot de passe uniquement s'il est fourni
        if (!empty($validated['password'])) {
            $supervisor->password = Hash::make($validated['password']);
        }
        
        $supervisor->save();
        
        return redirect()->route('supervisors.show', $supervisor)
            ->with('success', "Le superviseur {$supervisor->name} a été mis à jour avec succès.");
    }

    /**
     * Supprime un superviseur
     */
    public function destroy(User $supervisor)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('delete', $supervisor);
        
        // Suppression du superviseur
        $name = $supervisor->name;
        $supervisor->delete();
        
        return redirect()->route('supervisors.index')
            ->with('success', "Le superviseur {$name} a été supprimé avec succès.");
    }
    
    /**
     * Affiche le tableau de bord d'un superviseur
     */
    public function dashboard()
    {
        // Cette méthode est accessible uniquement aux superviseurs
        if (Auth::user()->role !== 'supervisor') {
            abort(403);
        }
        
        $supervisor = Auth::user();
        
        // Récupérer les influenceurs supervisés
        $influencers = $supervisor->influencers;
        
        // Récupérer les campagnes supervisées
        $campaigns = Campaign::whereHas('assignments', function($query) use ($supervisor) {
            $query->where('supervisor_id', $supervisor->id);
        })->get();
        
        // Récupérer les dernières publications à vérifier
        $pendingPublications = Publication::whereHas('assignment', function($query) use ($supervisor) {
                $query->where('supervisor_id', $supervisor->id);
            })
            ->where('status', 'pending')
            ->with(['influencer', 'assignment.campaign'])
            ->latest('published_at')
            ->take(5)
            ->get();
            
        // Récupérer les statistiques
        $stats = [
            'influencers' => $influencers->count(),
            'campaigns' => $campaigns->count(),
            'pending_publications' => $pendingPublications->count(),
        ];
        
        return view('supervisors.dashboard', compact('supervisor', 'influencers', 'campaigns', 'pendingPublications', 'stats'));
    }
    
    /**
     * Affiche la liste des influenceurs supervisés
     */
    public function influencers(User $supervisor)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('view', $supervisor);
        
        // Récupérer les influenceurs supervisés
        $influencers = $supervisor->influencers()->paginate(10);
        
        return view('supervisors.influencers', compact('supervisor', 'influencers'));
    }
    
    /**
     * Assigner un influenceur à un superviseur
     */
    public function assignInfluencer(Request $request, User $supervisor)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('update', $supervisor);
        
        // Validation des données
        $validated = $request->validate([
            'influencer_id' => 'required|exists:users,id',
        ]);
        
        // Récupérer l'influenceur
        $influencer = User::where('id', $validated['influencer_id'])
            ->where('role', 'influencer')
            ->firstOrFail();
            
        // Vérifier si l'influenceur est déjà assigné à un superviseur
        $existingSupervisor = DB::table('supervisor_influencer')
            ->where('influencer_id', $influencer->id)
            ->first();
            
        // Ne permettre l'assignation que pour les influenceurs sans superviseur
        if ($existingSupervisor) {
            return redirect()->route('supervisors.show', $supervisor)
                ->with('error', "L'influenceur {$influencer->name} est déjà assigné à un autre superviseur. Utilisez l'écran de gestion de cet influenceur pour le réassigner.");
        }
        
        // Créer une nouvelle assignation
        DB::table('supervisor_influencer')->insert([
            'supervisor_id' => $supervisor->id,
            'influencer_id' => $influencer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('supervisors.show', $supervisor)
            ->with('success', "L'influenceur {$influencer->name} a été assigné avec succès à ce superviseur.");
    }
    
    /**
     * Affiche la liste des publications en attente de vérification
     */
    public function pendingPublications()
    {
        // Cette méthode est accessible uniquement aux superviseurs
        if (Auth::user()->role !== 'supervisor') {
            abort(403);
        }
        
        $supervisor = Auth::user();
        
        // Récupérer toutes les publications en attente
        $pendingPublications = Publication::whereHas('assignment', function($query) use ($supervisor) {
                $query->where('supervisor_id', $supervisor->id);
            })
            ->where('status', 'pending')
            ->with(['influencer', 'assignment.campaign'])
            ->latest('published_at')
            ->paginate(10);
            
        return view('supervisors.pending-publications', compact('supervisor', 'pendingPublications'));
    }
    
    /**
     * Désassigner un influenceur d'un superviseur
     */
    public function unassignInfluencer(User $supervisor, User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un superviseur
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }
        
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('update', $supervisor);
        
        // Vérifier que l'influenceur est bien assigné à ce superviseur
        $exists = DB::table('supervisor_influencer')
            ->where('supervisor_id', $supervisor->id)
            ->where('influencer_id', $influencer->id)
            ->exists();
            
        if (!$exists) {
            return redirect()->route('supervisors.show', $supervisor)
                ->with('error', "L'influenceur {$influencer->name} n'est pas assigné à ce superviseur.");
        }
        
        // Supprimer l'assignation
        DB::table('supervisor_influencer')
            ->where('supervisor_id', $supervisor->id)
            ->where('influencer_id', $influencer->id)
            ->delete();
            
        return redirect()->route('supervisors.show', $supervisor)
            ->with('success', "L'influenceur {$influencer->name} a été retiré avec succès de ce superviseur.");
    }
}
