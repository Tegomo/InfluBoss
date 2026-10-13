<?php

namespace App\Http\Controllers;

use App\Models\FollowerSnapshot;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\InfluencerStatsService;
use App\Services\SocialAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InfluencerController extends Controller
{
    public function __construct(
        private SocialAccountService $socialAccounts,
        private InfluencerStatsService $stats,
    ) {}

    /**
     * Affiche la liste des influenceurs
     */
    public function index(Request $request)
    {
        // Seuls les admin, super admin et superviseurs peuvent accéder à cette page
        Gate::authorize('viewAny', User::class);

        $categories = config('influboss.categories');
        $platforms = config('influboss.platforms');

        $query = User::where('role', 'influencer')
            ->with('socialAccounts')
            ->withSum('socialAccounts as followers_total', 'followers_count');

        // Filtrer par état actif/inactif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filtrer par plateforme sociale (clé connue uniquement)
        if ($request->filled('platform') && array_key_exists($request->platform, $platforms)) {
            $query->whereHas('socialAccounts', fn ($q) => $q->where('platform', $request->platform));
        }

        // Filtrer par catégorie (clé connue uniquement)
        if ($request->filled('category') && array_key_exists($request->category, $categories)) {
            $query->whereJsonContains('categories', $request->category);
        }

        // Recherche par nom, pseudo ou email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('pseudo', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Un superviseur ne voit que les influenceurs qui lui sont liés (pivot supervisor_influencer)
        if (Auth::user()->role === 'supervisor') {
            $query->whereIn('id', Auth::user()->influencers()->pluck('users.id'));
        }

        $influencers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('influencers.index', compact('influencers', 'categories', 'platforms'));
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

        $influencer->load('socialAccounts.snapshots');

        $assignments = $influencer->influencerAssignments()
            ->with(['campaign', 'supervisor', 'publications.latestStats'])
            ->orderByDesc('created_at')
            ->get();

        $publications = $influencer->publications()
            ->with(['assignment.campaign', 'latestStats'])
            ->orderByDesc('published_at')
            ->get();

        $campaignResults = $this->stats->campaignResults($assignments);
        $campaignTotals = $this->stats->totals($campaignResults);
        $followerChart = $this->stats->followerChart($influencer);

        // Cartes plateformes dans l'ordre de la config, avec la variation depuis le relevé précédent.
        $platformOrder = array_keys(config('influboss.platforms'));
        $platformCards = $influencer->socialAccounts
            ->sortBy(fn (SocialAccount $account) => array_search($account->platform, $platformOrder, true))
            ->values()
            ->map(function (SocialAccount $account) {
                $previous = $account->snapshots->skip(1)->first();

                return [
                    'account' => $account,
                    'delta' => $previous !== null && $account->followers_count !== null
                        ? $account->followers_count - $previous->followers_count
                        : null,
                ];
            });

        return view('influencers.show', compact(
            'influencer',
            'assignments',
            'publications',
            'campaignResults',
            'campaignTotals',
            'followerChart',
            'platformCards'
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
