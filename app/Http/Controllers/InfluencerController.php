<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Campaign;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class InfluencerController extends Controller
{
    /**
     * Affiche la liste des influenceurs
     */
    public function index(Request $request)
    {
        // Seuls les admin, super admin et superviseurs peuvent accéder à cette page
        Gate::authorize('viewAny', User::class);
        
        $query = User::where('role', 'influencer');
        
        // Filtrer par état actif/inactif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Filtrer par plateforme sociale
        if ($request->filled('platform')) {
            $platform = $request->platform;
            $query->whereJsonContains('social_links', [$platform]);
        }
        
        // Recherche par nom ou email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Si c'est un superviseur, ne montrer que les influenceurs qu'il supervise
        if (Auth::user()->isSupervisor()) {
            $influencersIds = Auth::user()->supervisedInfluencers()->pluck('users.id');
            $query->whereIn('id', $influencersIds);
        }
        
        $influencers = $query->paginate(10);
        
        return view('influencers.index', compact('influencers'));
    }

    /**
     * Affiche le formulaire de création d'un influenceur
     */
    public function create()
    {
        // Seuls les admin et super admin peuvent créer des influenceurs
        Gate::authorize('create', User::class);
        
        return view('influencers.create');
    }

    /**
     * Enregistre un nouvel influenceur dans la base de données
     */
    public function store(Request $request)
    {
        // Seuls les admin et super admin peuvent créer des influenceurs
        Gate::authorize('create', User::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'bio' => 'nullable|string',
            'social_links' => 'nullable|array',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        // Gérer les liens sociaux
        $socialLinks = [];
        if ($request->filled('social_facebook')) {
            $socialLinks['facebook'] = $request->social_facebook;
        }
        if ($request->filled('social_instagram')) {
            $socialLinks['instagram'] = $request->social_instagram;
        }
        if ($request->filled('social_twitter')) {
            $socialLinks['twitter'] = $request->social_twitter;
        }
        if ($request->filled('social_tiktok')) {
            $socialLinks['tiktok'] = $request->social_tiktok;
        }
        if ($request->filled('social_youtube')) {
            $socialLinks['youtube'] = $request->social_youtube;
        }
        if ($request->filled('social_linkedin')) {
            $socialLinks['linkedin'] = $request->social_linkedin;
        }
        
        // Traiter la photo de profil si fournie
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo'] = $path;
        }
        
        // Créer l'influenceur
        $influencer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'pseudo' => $validated['pseudo'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'city' => $validated['city'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'influencer',
            'bio' => $validated['bio'] ?? null,
            'social_links' => !empty($socialLinks) ? json_encode($socialLinks) : null,
            'profile_photo' => $validated['profile_photo'] ?? null,
            'is_active' => true,
        ]);
        
        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'Influenceur créé avec succès.');
    }

    /**
     * Affiche les détails d'un influenceur
     */
    public function show(User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('view', $influencer);
        
        // Charger les campagnes associées et les publications
        $influencer->load([
            'influencerAssignments.campaign', 
            'publications.latestStats',
            'influencerAssignments.supervisor'
        ]);
        
        // Récupérer les missions (assignments) de l'influenceur
        $assignments = $influencer->influencerAssignments;
        
        // Récupérer les publications de l'influenceur
        $publications = $influencer->publications;
        
        // Calculer les statistiques globales
        $totalPublications = $publications->count();
        $totalEngagement = $influencer->publications()
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->sum(\DB::raw('stats.likes + stats.comments + stats.shares + stats.saves'));
        $avgEngagementRate = $influencer->publications()
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->avg('stats.engagement_rate') ?? 0;
        $totalReach = $influencer->publications()
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->sum('stats.reach') ?? 0;
        
        return view('influencers.show', compact(
            'influencer', 
            'assignments',
            'publications',
            'totalPublications', 
            'totalEngagement', 
            'avgEngagementRate', 
            'totalReach'
        ));
        
    }

    /**
     * Affiche le formulaire d'édition d'un influenceur
     */
    public function edit(User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('update', $influencer);
        
        return view('influencers.edit', compact('influencer'));
    }

    /**
     * Met à jour un influenceur dans la base de données
     */
    public function update(Request $request, User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('update', $influencer);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $influencer->id,
            'phone' => 'nullable|string|max:20',
            'pseudo' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'bio' => 'nullable|string',
            'social_links' => 'nullable|array',
            'profile_photo' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);
        
        // Gérer les liens sociaux
        $socialLinks = [];
        if ($request->filled('social_facebook')) {
            $socialLinks['facebook'] = $request->social_facebook;
        }
        if ($request->filled('social_instagram')) {
            $socialLinks['instagram'] = $request->social_instagram;
        }
        if ($request->filled('social_twitter')) {
            $socialLinks['twitter'] = $request->social_twitter;
        }
        if ($request->filled('social_tiktok')) {
            $socialLinks['tiktok'] = $request->social_tiktok;
        }
        if ($request->filled('social_youtube')) {
            $socialLinks['youtube'] = $request->social_youtube;
        }
        if ($request->filled('social_linkedin')) {
            $socialLinks['linkedin'] = $request->social_linkedin;
        }
        
        // Traiter la photo de profil si fournie
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $influencer->profile_photo = $path;
        }
        
        // Mettre à jour l'influenceur
        $influencer->name = $validated['name'];
        $influencer->email = $validated['email'];
        $influencer->phone = $validated['phone'] ?? $influencer->phone;
        $influencer->pseudo = $validated['pseudo'] ?? $influencer->pseudo;
        $influencer->birthdate = $validated['birthdate'] ?? $influencer->birthdate;
        $influencer->city = $validated['city'] ?? $influencer->city;
        
        if (!empty($validated['password'])) {
            $influencer->password = Hash::make($validated['password']);
        }
        
        $influencer->bio = $validated['bio'] ?? $influencer->bio;
        $influencer->social_links = !empty($socialLinks) ? json_encode($socialLinks) : $influencer->social_links;
        $influencer->is_active = $validated['is_active'];
        
        $influencer->save();
        
        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'Influenceur mis à jour avec succès.');
    }

    /**
     * Supprime un influenceur
     */
    public function destroy(User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('delete', $influencer);
        
        // On utilise soft delete
        $influencer->delete();
        
        return redirect()->route('influencers.index')
            ->with('success', 'Influenceur supprimé avec succès.');
    }
    
    /**
     * Affiche la page d'assignation de campagnes à un influenceur
     */
    public function assignCampaigns(User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('assignCampaigns', $influencer);
        
        // Récupérer les campagnes auxquelles l'influenceur n'est pas encore assigné
        $assignedCampaignIds = $influencer->influencerAssignments->pluck('campaign_id')->toArray();
        
        $availableCampaigns = Campaign::whereNotIn('id', $assignedCampaignIds)
            ->where('status', '!=', 'completed')
            ->where('end_date', '>=', now())
            ->get();
            
        // Récupérer les superviseurs disponibles
        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->get();
        
        return view('influencers.assign-campaigns', compact('influencer', 'availableCampaigns', 'supervisors'));
    }
    
    /**
     * Assigne un influenceur à une campagne
     */
    public function storeAssignment(Request $request, User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('assignCampaigns', $influencer);
        
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'supervisor_id' => 'required|exists:users,id',
            'compensation' => 'nullable|numeric|min:0',
            'required_deliverables' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        
        // Vérifier que le superviseur a bien le rôle "supervisor"
        $supervisor = User::findOrFail($validated['supervisor_id']);
        if ($supervisor->role !== 'supervisor') {
            return back()->withErrors(['supervisor_id' => 'L\'utilisateur sélectionné n\'est pas un superviseur.']);
        }
        
        // Vérifier que l'influenceur n'est pas déjà assigné à cette campagne
        $existingAssignment = Assignment::where('campaign_id', $validated['campaign_id'])
            ->where('influencer_id', $influencer->id)
            ->first();
            
        if ($existingAssignment) {
            return back()->withErrors(['campaign_id' => 'Cet influenceur est déjà assigné à cette campagne.']);
        }
        
        // Créer l'assignation
        $requiredDeliverables = $request->input('required_deliverables') ?? [];
        
        Assignment::create([
            'campaign_id' => $validated['campaign_id'],
            'influencer_id' => $influencer->id,
            'supervisor_id' => $validated['supervisor_id'],
            'status' => 'pending',
            'compensation' => $validated['compensation'] ?? null,
            'required_deliverables' => !empty($requiredDeliverables) ? json_encode($requiredDeliverables) : null,
            'assigned_date' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);
        
        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'Influenceur assigné à la campagne avec succès.');
    }
    
    /**
     * Affiche le tableau de bord des statistiques d'un influenceur
     */
    public function dashboard(User $influencer)
    {
        // Vérifier qu'il s'agit bien d'un influenceur
        if ($influencer->role !== 'influencer') {
            abort(404);
        }
        
        // Vérifier les droits d'accès
        Gate::authorize('view', $influencer);
        
        // Charger les publications et statistiques
        $publications = $influencer->publications()
            ->with(['latestStats', 'content', 'assignment.campaign'])
            ->get();
            
        // Calculer les statistiques par plateforme
        $platformStats = $publications
            ->groupBy('platform')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'engagement' => $items->sum(function($item) {
                        return $item->latestStats ? 
                               ($item->latestStats->likes + 
                                $item->latestStats->comments + 
                                $item->latestStats->shares + 
                                $item->latestStats->saves) : 0;
                    }),
                    'reach' => $items->sum(function($item) {
                        return $item->latestStats ? $item->latestStats->reach : 0;
                    }),
                ];
            });
            
        // Calculer les statistiques d'évolution dans le temps
        $timeStats = $influencer->publications()
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->selectRaw('DATE(stats.collected_at) as date, SUM(stats.likes + stats.comments + stats.shares + stats.saves) as engagement, AVG(stats.engagement_rate) as avg_rate')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        return view('influencers.dashboard', compact('influencer', 'publications', 'platformStats', 'timeStats'));
    }
}
