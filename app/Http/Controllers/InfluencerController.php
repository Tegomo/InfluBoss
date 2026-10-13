<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\FollowerSnapshot;
use App\Models\User;
use App\Services\SocialAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InfluencerController extends Controller
{
    public function __construct(private SocialAccountService $socialAccounts) {}

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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Si c'est un superviseur, ne montrer que les influenceurs qu'il supervise
        if (Auth::user()->role === 'supervisor') {
            // Récupérer les IDs des influenceurs assignés à ce superviseur depuis la table supervisor_influencer
            $influencersIds = DB::table('supervisor_influencer')
                ->where('supervisor_id', Auth::id())
                ->pluck('influencer_id')
                ->toArray();

            // Si aucun influenceur n'est assigné, utiliser un ID impossible pour que la requête ne retourne rien
            if (empty($influencersIds)) {
                $query->where('id', 0); // Aucun utilisateur n'aura cet ID
            } else {
                $query->whereIn('id', $influencersIds);
            }
        }

        // Trier les influenceurs du plus récent au plus ancien
        $query->orderBy('created_at', 'desc');

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

        return view('influencers.create', $this->formData());
    }

    /**
     * Enregistre un nouvel influenceur dans la base de données
     */
    public function store(Request $request)
    {
        // Seuls les admin et super admin peuvent créer des influenceurs
        Gate::authorize('create', User::class);

        $validated = $request->validate($this->rules(), $this->messages());

        $attributes = $this->profileAttributes($validated) + [
            'password' => Hash::make($validated['password']),
            'role' => 'influencer',
            'is_active' => true,
        ];

        if ($request->hasFile('profile_photo')) {
            $attributes['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $influencer = User::create($attributes);

        $this->socialAccounts->syncForUser($influencer, $validated['social'] ?? [], Auth::id());

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
            'influencerAssignments.supervisor',
        ]);

        // Récupérer les missions (assignments) de l'influencer
        $assignments = $influencer->influencerAssignments;

        // Récupérer les publications de l'influenceur
        $publications = $influencer->publications;

        // Calculer les statistiques globales
        $totalPublications = $publications->count();
        // Calculer l'engagement total en additionnant les statistiques individuelles
        $stats = $influencer->publications()
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->select('stats.likes', 'stats.comments', 'stats.shares', 'stats.saves')
            ->get();

        $totalEngagement = $stats->sum('likes') + $stats->sum('comments') + $stats->sum('shares') + $stats->sum('saves');
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

        return view('influencers.edit', $this->formData() + [
            'influencer' => $influencer,
            'accounts' => $influencer->socialAccounts->keyBy('platform'),
        ]);
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

        $validated = $request->validate($this->rules($influencer), $this->messages());

        // Les champs du formulaire sont tous envoyés : une valeur vide efface la précédente.
        $influencer->fill($this->profileAttributes($validated));

        if (! empty($validated['password'])) {
            $influencer->password = Hash::make($validated['password']);
        }

        // La case décochée n'est pas envoyée par le navigateur : absence = inactif.
        $influencer->is_active = $request->boolean('is_active');

        if ($request->hasFile('profile_photo')) {
            if ($influencer->profile_photo) {
                Storage::disk('public')->delete($influencer->profile_photo);
            }
            $influencer->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $influencer->save();

        $this->socialAccounts->syncForUser($influencer, $validated['social'] ?? [], Auth::id());

        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'Influenceur mis à jour avec succès.');
    }

    /**
     * Enregistre un nouveau relevé d'abonnés pour les plateformes saisies (formulaire de la fiche).
     */
    public function updateFollowers(Request $request, User $influencer)
    {
        if ($influencer->role !== 'influencer') {
            abort(404);
        }

        Gate::authorize('updateFollowers', $influencer);

        $platforms = config('influboss.platforms');
        $validated = $request->validate([
            'followers' => ['required', 'array:'.implode(',', array_keys($platforms))],
            'followers.*' => 'nullable|integer|min:0',
        ], [
            'followers.required' => 'Saisissez au moins un nombre d\'abonnés.',
            'followers.array' => 'Plateforme inconnue.',
            'followers.*.integer' => 'Le nombre d\'abonnés doit être un nombre entier.',
            'followers.*.min' => 'Le nombre d\'abonnés ne peut pas être négatif.',
        ]);

        $counts = array_filter($validated['followers'], fn ($value) => $value !== null && $value !== '');
        if ($counts === []) {
            return back()->withErrors(['followers' => 'Saisissez au moins un nombre d\'abonnés.'])->withInput();
        }

        $accounts = $influencer->socialAccounts->keyBy('platform');
        foreach (array_keys($counts) as $platform) {
            if (! isset($accounts[$platform])) {
                $label = $platforms[$platform]['label'];

                return back()
                    ->withErrors(['followers' => "Aucun compte {$label} enregistré pour cet influenceur."])
                    ->withInput();
            }
        }

        foreach ($counts as $platform => $count) {
            $accounts[$platform]->recordFollowers((int) $count, FollowerSnapshot::SOURCE_MANUAL, Auth::id());
        }

        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'Abonnés mis à jour.');
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
            'required_deliverables' => ! empty($requiredDeliverables) ? json_encode($requiredDeliverables) : null,
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
                    'engagement' => $items->sum(function ($item) {
                        return $item->latestStats ?
                               ($item->latestStats->likes +
                                $item->latestStats->comments +
                                $item->latestStats->shares +
                                $item->latestStats->saves) : 0;
                    }),
                    'reach' => $items->sum(function ($item) {
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

    /**
     * Référentiels passés aux formulaires de création et d'édition.
     */
    private function formData(): array
    {
        return [
            'categories' => config('influboss.categories'),
            'platforms' => config('influboss.platforms'),
        ];
    }

    /**
     * Règles de validation du formulaire influenceur (création si $influencer est null).
     */
    private function rules(?User $influencer = null): array
    {
        $platformKeys = implode(',', array_keys(config('influboss.platforms')));

        return [
            'name' => 'required|string|max:255',
            'pseudo' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($influencer?->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => ['string', Rule::in(array_keys(config('influboss.categories')))],
            'password' => ($influencer ? 'nullable' : 'required').'|string|min:8|confirmed',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
            'social' => ['nullable', 'array:'.$platformKeys],
            'social.*.url' => 'nullable|url|max:255',
            'social.*.followers' => 'nullable|integer|min:0',
        ];
    }

    private function messages(): array
    {
        return [
            'categories.*.in' => 'Catégorie inconnue.',
            'social.array' => 'Plateforme inconnue.',
            'social.*.url.url' => 'L\'URL du profil n\'est pas valide.',
            'social.*.url.max' => 'L\'URL du profil est trop longue.',
            'social.*.followers.integer' => 'Le nombre d\'abonnés doit être un nombre entier.',
            'social.*.followers.min' => 'Le nombre d\'abonnés ne peut pas être négatif.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }

    /**
     * Attributs du profil issus des données validées (hors mot de passe, photo et statut).
     */
    private function profileAttributes(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'pseudo' => $validated['pseudo'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'categories' => array_values($validated['categories'] ?? []),
        ];
    }
}
