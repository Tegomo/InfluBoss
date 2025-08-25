<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Publication;
use App\Models\ApiConfiguration;
use App\Services\BrightDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord d'administration
     */
    public function dashboard()
    {
        // Seuls les admin et super admin peuvent accéder à cette page
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('dashboard');
        }
        
        // Statistiques globales
        $stats = [
            'users' => [
                'total' => User::count(),
                'admin' => User::where('role', 'admin')->count(),
                'supervisor' => User::where('role', 'supervisor')->count(),
                'influencer' => User::where('role', 'influencer')->count(),
            ],
            'campaigns' => [
                'total' => Campaign::count(),
                'active' => Campaign::where('status', 'active')->count(),
                'draft' => Campaign::where('status', 'draft')->count(),
                'completed' => Campaign::where('status', 'completed')->count(),
                'paused' => Campaign::where('status', 'paused')->count(),
            ],
            'publications' => [
                'total' => Publication::count(),
                'pending' => Publication::where('status', 'pending')->count(),
                'verified' => Publication::where('status', 'verified')->count(),
                'rejected' => Publication::where('status', 'rejected')->count(),
            ]
        ];
        
        // Récupérer les dernières publications
        $latestPublications = Publication::with(['influencer', 'assignment.campaign', 'latestStats'])
            ->latest('published_at')
            ->take(5)
            ->get();
            
        // Récupérer les dernières campagnes
        $latestCampaigns = Campaign::with('creator')
            ->latest('created_at')
            ->take(5)
            ->get();
            
        return view('admin.dashboard', compact('stats', 'latestPublications', 'latestCampaigns'));
    }
    
    /**
     * Affiche la liste des administrateurs
     */
    public function administrators()
    {
        // Seuls les super admin peuvent gérer d'autres administrateurs
        Gate::authorize('viewAny', [User::class, 'admin']);
        
        $administrators = User::where('role', 'admin')
            ->orWhere('role', 'super_admin')
            ->orderBy('role')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.administrators', compact('administrators'));
    }
    
    /**
     * Affiche le formulaire de création d'un administrateur
     */
    public function createAdministrator()
    {
        // Seuls les super admin peuvent créer d'autres administrateurs
        Gate::authorize('create', [User::class, 'admin']);
        
        return view('admin.create-administrator');
    }
    
    /**
     * Enregistre un nouvel administrateur dans la base de données
     */
    public function storeAdministrator(Request $request)
    {
        // Seuls les super admin peuvent créer d'autres administrateurs
        Gate::authorize('create', [User::class, 'admin']);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
            'role' => 'required|in:admin',
        ]);
        
        // Traiter la photo de profil si fournie
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo'] = $path;
        }
        
        // Créer l'administrateur
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'profile_photo' => $validated['profile_photo'] ?? null,
            'is_active' => true,
        ]);
        
        return redirect()->route('admin.administrators')
            ->with('success', 'Administrateur créé avec succès.');
    }
    
    /**
     * Affiche les configurations d'API
     */
    public function apiConfigurations()
    {
        // Seuls les super admin peuvent gérer les configurations d'API
        Gate::authorize('viewApiConfigurations');
        
        $apiConfigurations = ApiConfiguration::all();
        
        return view('admin.api-configurations', compact('apiConfigurations'));
    }
    
    /**
     * Affiche le formulaire de création d'une configuration d'API
     */
    public function createApiConfiguration()
    {
        // Seuls les super admin peuvent créer des configurations d'API
        Gate::authorize('createApiConfiguration');
        
        return view('admin.create-api-configuration');
    }
    
    /**
     * Enregistre une nouvelle configuration d'API dans la base de données
     */
    public function storeApiConfiguration(Request $request)
    {
        // Seuls les super admin peuvent créer des configurations d'API
        Gate::authorize('createApiConfiguration');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_type' => 'required|string|in:brightdata_social,brightdata_serp,brightdata_web_unlocker',
            'api_key' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'endpoint_url' => 'required|string|url',
            'additional_config' => 'nullable|json',
            'is_active' => 'required|boolean',
        ]);
        
        // Créer la configuration d'API
        ApiConfiguration::create($validated);
        
        return redirect()->route('admin.api-configurations')
            ->with('success', 'Configuration d\'API créée avec succès.');
    }
    
    /**
     * Affiche le formulaire d'édition d'une configuration d'API
     */
    public function editApiConfiguration(ApiConfiguration $apiConfiguration)
    {
        // Seuls les super admin peuvent modifier des configurations d'API
        Gate::authorize('updateApiConfiguration');
        
        return view('admin.edit-api-configuration', compact('apiConfiguration'));
    }
    
    /**
     * Met à jour une configuration d'API dans la base de données
     */
    public function updateApiConfiguration(Request $request, ApiConfiguration $apiConfiguration)
    {
        // Seuls les super admin peuvent modifier des configurations d'API
        Gate::authorize('updateApiConfiguration');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_type' => 'required|string|in:brightdata_social,brightdata_serp,brightdata_web_unlocker',
            'api_key' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'endpoint_url' => 'required|string|url',
            'additional_config' => 'nullable|json',
            'is_active' => 'required|boolean',
        ]);
        
        // Mettre à jour la configuration d'API
        $apiConfiguration->update($validated);
        
        return redirect()->route('admin.api-configurations')
            ->with('success', 'Configuration d\'API mise à jour avec succès.');
    }
    
    /**
     * Supprime une configuration d'API
     */
    public function destroyApiConfiguration(ApiConfiguration $apiConfiguration)
    {
        // Seuls les super admin peuvent supprimer des configurations d'API
        Gate::authorize('deleteApiConfiguration');
        
        $apiConfiguration->delete();
        
        return redirect()->route('admin.api-configurations')
            ->with('success', 'Configuration d\'API supprimée avec succès.');
    }
    
    /**
     * Teste une configuration d'API
     */
    public function testApiConfiguration(ApiConfiguration $apiConfiguration)
    {
        // Seuls les super admin peuvent tester des configurations d'API
        Gate::authorize('updateApiConfiguration');
        
        // Si ce n'est pas une API Bright Data
        if (!str_starts_with($apiConfiguration->api_type, 'brightdata_')) {
            return back()->withErrors(['error' => 'Type d\'API non supporté pour les tests.']);
        }
        
        try {
            // Instancier le service Bright Data avec cette configuration
            $brightDataService = new BrightDataService($apiConfiguration->api_type);
            
            // On simule un appel simple à l'API
            $response = $brightDataService->testApiConnection();
            
            if ($response) {
                return back()->with('success', 'La connexion à l\'API a été établie avec succès.');
            } else {
                return back()->withErrors(['error' => 'Impossible de se connecter à l\'API. Veuillez vérifier vos informations de connexion.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du test de l\'API: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Affiche les statistiques globales
     */
    public function statistics()
    {
        // Seuls les admin et super admin peuvent accéder à cette page
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('dashboard');
        }
        
        // Statistiques des publications par plateforme
        $platformStats = Publication::where('status', 'verified')
            ->selectRaw('platform, count(*) as count')
            ->groupBy('platform')
            ->get();
            
        // Statistiques d'engagement par plateforme
        $platformEngagement = Publication::where('status', 'verified')
            ->join('stats', 'publications.id', '=', 'stats.publication_id')
            ->selectRaw('platform, AVG(engagement_rate) as avg_engagement')
            ->groupBy('platform')
            ->get();
            
        // Top influenceurs par nombre de publications
        $topInfluencers = User::where('role', 'influencer')
            ->withCount(['publications' => function($query) {
                $query->where('status', 'verified');
            }])
            ->orderByDesc('publications_count')
            ->take(10)
            ->get();
            
        return view('admin.statistics', compact('platformStats', 'platformEngagement', 'topInfluencers'));
    }
}
