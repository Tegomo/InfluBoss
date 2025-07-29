<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\Publication;
use App\Models\User;
use App\Models\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord adapté au rôle de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les statistiques
        $stats = [
            'campaigns' => Campaign::count(),
            'contents' => Content::count(),
            'publications' => Publication::count(),
            'influencers' => User::where('role', 'influencer')->count()
        ];
        
        // Données pour le graphique des campagnes par statut
        $campaignsByStatus = $this->getCampaignsByStatus();
        
        // Données pour le graphique des publications mensuelles
        $monthlyPublications = $this->getMonthlyPublications();
        
        // Données pour le graphique des interactions (likes, commentaires, etc.)
        $interactionStats = $this->getInteractionStats();
        
        // Données pour le graphique des plateformes les plus utilisées
        $platformStats = $this->getPlatformStats();

        // Préparer les données pour le JavaScript
        $dashboardData = [
            'campaignsByStatus' => $campaignsByStatus,
            'monthlyPublications' => $monthlyPublications,
            'interactionStats' => $interactionStats,
            'platformStats' => $platformStats
        ];
        
        // Récupérer les campagnes récentes
        $recentCampaigns = Campaign::with('creator')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Récupérer les publications récentes
        $recentPublications = Publication::with('influencer', 'content')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Pour les super_admin et admin, on utilise la vue dashboard.index
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            // Activer les scripts pour les graphiques
        $pageScripts = ['dashboard-charts'];
        
        return view('dashboard.index', compact(
                'stats', 
                'recentCampaigns', 
                'recentPublications', 
                'dashboardData',
                'pageScripts'
            ));
        }

        // Pour les superviseurs, on affiche leurs campagnes et influenceurs
        if ($user->role === 'supervisor') {
            // Récupérer les campagnes supervisées
            $campaigns = Campaign::whereHas('assignments', function($query) use ($user) {
                $query->where('supervisor_id', $user->id);
            })->with('creator')->get();
            
            // Récupérer les influenceurs supervisés
            $influencers = User::whereHas('assignments', function($query) use ($user) {
                $query->where('supervisor_id', $user->id);
            })->where('role', 'influencer')->get();
            
            return view('dashboard.supervisor', compact('campaigns', 'influencers'));
        }

        // Pour les influenceurs, on affiche leurs assignments et publications
        if ($user->role === 'influencer') {
            // Récupérer les missions assignées à cet influenceur
            $assignments = Assignment::where('influencer_id', $user->id)
                ->with('campaign')
                ->where('status', '!=', 'completed')
                ->get();
                
            // Récupérer les publications de cet influenceur
            $publications = Publication::where('user_id', $user->id)
                ->with(['assignment.campaign', 'latestStats'])
                ->latest('published_at')
                ->take(5)
                ->get();
                
            // Compter les publications par statut
            $pendingCount = Publication::where('user_id', $user->id)->where('status', 'pending')->count();
            $verifiedCount = Publication::where('user_id', $user->id)->where('status', 'verified')->count();
            $rejectedCount = Publication::where('user_id', $user->id)->where('status', 'rejected')->count();
            
            return view('dashboard.influencer', compact('assignments', 'publications', 'pendingCount', 'verifiedCount', 'rejectedCount'));
        }
        
        // Fallback pour tout autre rôle non défini - rediriger vers la page d'accueil
        return redirect()->route('login');
    }
    
    /**
     * Récupère les données pour le graphique des campagnes par statut
     */
    private function getCampaignsByStatus()
    {
        return Campaign::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
    }
    
    /**
     * Récupère les données pour le graphique des publications mensuelles
     */
    private function getMonthlyPublications()
    {
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        
        return Publication::select(
                DB::raw('MONTH(published_at) as month'), 
                DB::raw('YEAR(published_at) as year'),
                DB::raw('count(*) as count')
            )
            ->where('published_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'count' => $item->count
                ];
            })
            ->pluck('count', 'month')
            ->toArray();
    }
    
    /**
     * Récupère les statistiques d'interaction (likes, commentaires, etc.)
     */
    private function getInteractionStats()
    {
        $stats = Stat::select(
                DB::raw('SUM(likes) as total_likes'),
                DB::raw('SUM(comments) as total_comments'),
                DB::raw('SUM(shares) as total_shares'),
                DB::raw('SUM(views) as total_views'),
                DB::raw('SUM(clicks) as total_clicks')
            )
            ->whereHas('publication', function($query) {
                $query->where('status', 'verified');
            })
            ->first();
            
        return [
            'likes' => $stats->total_likes ?? 0,
            'comments' => $stats->total_comments ?? 0,
            'shares' => $stats->total_shares ?? 0,
            'views' => $stats->total_views ?? 0,
            'clicks' => $stats->total_clicks ?? 0
        ];
    }
    
    /**
     * Récupère les statistiques par plateforme
     */
    private function getPlatformStats()
    {
        return Publication::select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->pluck('count', 'platform')
            ->toArray();
    }
}
