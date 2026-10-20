<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\Assignment;
use App\Models\Publication;
use App\Models\Note;
use Carbon\Carbon;
use App\Exports\StatisticsExport;
use App\Exports\RawDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StatisticsController extends Controller
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        // La protection par middleware est déjà gérée dans les routes
    }
    
    /**
     * Récupère les statistiques pour l'affichage et l'exportation
     */
    private function getStatistics()
    {
        // Statistiques sur les utilisateurs
        $userStats = [
            'total' => User::count(),
            'influencers' => User::where('role', 'influencer')->count(),
            'supervisors' => User::where('role', 'supervisor')->count(),
            'admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'recentlyActive' => User::where('created_at', '>=', Carbon::now()->subDays(30))->count(), // Utilisateurs créés récemment
        ];
        
        // Statistiques sur les campagnes
        $campaignStats = [
            'total' => Campaign::count(),
            'active' => Campaign::where('status', 'active')->count(),
            'draft' => Campaign::where('status', 'draft')->count(),
            'completed' => Campaign::where('status', 'completed')->count(),
            'archived' => Campaign::where('status', 'archived')->count(),
            'byMonth' => Campaign::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),
        ];
        
        // Statistiques sur les contenus
        $contentStats = [
            'total' => Content::count(),
            'approved' => Content::where('status', 'approved')->count(), // Utilisation de 'approved' au lieu de 'ready'
            'draft' => Content::where('status', 'draft')->count(),
            'byType' => Content::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
        
        // Statistiques sur les assignations
        $assignmentStats = [
            'total' => Assignment::count(),
            'pending' => Assignment::where('status', 'pending')->count(),
            'accepted' => Assignment::where('status', 'accepted')->count(),
            'rejected' => Assignment::where('status', 'rejected')->count(),
            'completed' => Assignment::where('status', 'completed')->count(),
            'byMonth' => Assignment::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),
        ];
        
        // Statistiques sur les publications
        $publicationStats = [
            'total' => Publication::count(),
            'pending' => Publication::where('status', 'pending')->count(),
            'verified' => Publication::where('status', 'verified')->count(),
            'rejected' => Publication::where('status', 'rejected')->count(),
            'byPlatform' => Publication::selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
            'byMonth' => Publication::selectRaw('MONTH(published_at) as month, COUNT(*) as count')
                ->whereYear('published_at', date('Y'))
                ->groupBy(DB::raw('MONTH(published_at)'))
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),
        ];
        
        // Statistiques sur les notes
        $noteStats = [
            'total' => Note::count(),
            'byMonth' => Note::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),
        ];
        
        // Statistiques d'engagement (likes, commentaires, partages, vues, clics)
        $engagementStats = [
            'total' => [
                'likes' => DB::table('stats')->sum('likes'),
                'comments' => DB::table('stats')->sum('comments'),
                'shares' => DB::table('stats')->sum('shares'),
                'views' => DB::table('stats')->sum('views'),
                'clicks' => DB::table('stats')->sum('clicks'),
            ],
            'byMonth' => DB::table('stats')
                ->selectRaw('MONTH(collected_at) as month, SUM(likes) as likes, SUM(comments) as comments, SUM(shares) as shares, SUM(views) as views, SUM(clicks) as clicks')
                ->whereYear('collected_at', date('Y'))
                ->groupBy(DB::raw('MONTH(collected_at)'))
                ->orderBy('month')
                ->get()
                ->keyBy('month')
                ->toArray(),
            'byPlatform' => DB::table('stats')
                ->join('publications', 'stats.publication_id', '=', 'publications.id')
                ->selectRaw('platform, SUM(likes) as likes, SUM(comments) as comments, SUM(shares) as shares, SUM(views) as views, SUM(clicks) as clicks')
                ->groupBy('platform')
                ->get()
                ->keyBy('platform')
                ->toArray(),
        ];
        
        // Top influenceurs - sera calculé dynamiquement dans index()
        $topInfluencers = collect();
        
        // Top superviseurs - sera calculé dynamiquement dans index()
        $topSupervisors = collect();
        
        // Top campagnes par nombre de publications
        $topCampaigns = Campaign::withCount('publications')
            ->orderBy('publications_count', 'desc')
            ->limit(10)
            ->get();
            
        return [
            'userStats' => $userStats,
            'campaignStats' => $campaignStats,
            'contentStats' => $contentStats,
            'assignmentStats' => $assignmentStats,
            'publicationStats' => $publicationStats,
            'noteStats' => $noteStats,
            'engagementStats' => $engagementStats,
            'topInfluencers' => $topInfluencers,
            'topSupervisors' => $topSupervisors,
            'topCampaigns' => $topCampaigns
        ];
    }
    
    /**
     * Récupère les statistiques d'engagement avec filtres
     */
    private function getEngagementStats(Request $request)
    {
        // Récupérer les filtres
        $dateFrom = $request->input('engagement_date_from');
        $dateTo = $request->input('engagement_date_to');
        $campaignId = $request->input('engagement_campaign_id');
        $supervisorId = $request->input('engagement_supervisor_id');
        $influencerId = $request->input('engagement_influencer_id');
        
        // Construire la requête de base pour les stats
        $statsQuery = DB::table('stats')
            ->join('publications', 'stats.publication_id', '=', 'publications.id')
            ->join('assignments', 'publications.assignment_id', '=', 'assignments.id');
        
        // Appliquer les filtres
        if ($dateFrom) {
            $statsQuery->where('stats.collected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $statsQuery->where('stats.collected_at', '<=', $dateTo . ' 23:59:59');
        }
        if ($campaignId) {
            $statsQuery->where('assignments.campaign_id', $campaignId);
        }
        if ($supervisorId) {
            $statsQuery->where('assignments.supervisor_id', $supervisorId);
        }
        if ($influencerId) {
            $statsQuery->where('publications.influencer_id', $influencerId);
        }
        
        // Total des engagements
        $total = (clone $statsQuery)->selectRaw('
            COALESCE(SUM(likes), 0) as likes,
            COALESCE(SUM(comments), 0) as comments,
            COALESCE(SUM(shares), 0) as shares,
            COALESCE(SUM(views), 0) as views,
            COALESCE(SUM(clicks), 0) as clicks
        ')->first();
        
        // Par mois
        $byMonth = (clone $statsQuery)
            ->selectRaw('MONTH(stats.collected_at) as month, SUM(likes) as likes, SUM(comments) as comments, SUM(shares) as shares, SUM(views) as views, SUM(clicks) as clicks')
            ->whereYear('stats.collected_at', date('Y'))
            ->groupBy(DB::raw('MONTH(stats.collected_at)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();
        
        // Par plateforme
        $byPlatform = (clone $statsQuery)
            ->selectRaw('publications.platform, SUM(likes) as likes, SUM(comments) as comments, SUM(shares) as shares, SUM(views) as views, SUM(clicks) as clicks')
            ->groupBy('publications.platform')
            ->get()
            ->keyBy('platform')
            ->toArray();
        
        return [
            'total' => [
                'likes' => $total->likes ?? 0,
                'comments' => $total->comments ?? 0,
                'shares' => $total->shares ?? 0,
                'views' => $total->views ?? 0,
                'clicks' => $total->clicks ?? 0,
            ],
            'byMonth' => $byMonth,
            'byPlatform' => $byPlatform,
        ];
    }
    
    /**
     * Récupère les statistiques des publications avec filtres
     */
    private function getPublicationStats(Request $request)
    {
        // Récupérer les filtres
        $dateFrom = $request->input('publication_date_from');
        $dateTo = $request->input('publication_date_to');
        $campaignId = $request->input('publication_campaign_id');
        $supervisorId = $request->input('publication_supervisor_id');
        
        // Construire la requête de base
        $query = Publication::query()
            ->join('assignments', 'publications.assignment_id', '=', 'assignments.id');
        
        // Appliquer les filtres
        if ($dateFrom) {
            $query->where('publications.published_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('publications.published_at', '<=', $dateTo . ' 23:59:59');
        }
        if ($campaignId) {
            $query->where('assignments.campaign_id', $campaignId);
        }
        if ($supervisorId) {
            $query->where('assignments.supervisor_id', $supervisorId);
        }
        
        // Total et statuts
        $total = (clone $query)->count();
        $pending = (clone $query)->where('publications.status', 'pending')->count();
        $verified = (clone $query)->where('publications.status', 'verified')->count();
        $rejected = (clone $query)->where('publications.status', 'rejected')->count();
        
        // Par plateforme
        $byPlatform = (clone $query)
            ->select('publications.platform', DB::raw('COUNT(*) as count'))
            ->groupBy('publications.platform')
            ->pluck('count', 'publications.platform')
            ->toArray();
        
        // Par mois
        $byMonth = (clone $query)
            ->select(DB::raw('MONTH(publications.published_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('publications.published_at', date('Y'))
            ->groupBy(DB::raw('MONTH(publications.published_at)'))
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'verified' => $verified,
            'rejected' => $rejected,
            'byPlatform' => $byPlatform,
            'byMonth' => $byMonth,
        ];
    }
    
    /**
     * Récupère le Top des influenceurs avec filtres
     */
    private function getTopInfluencers(Request $request)
    {
        $limit = $request->input('influencer_limit', 10);
        $dateFrom = $request->input('influencer_date_from');
        $dateTo = $request->input('influencer_date_to');
        
        // Requête pour calculer l'engagement par influenceur
        $query = DB::table('users')
            ->where('users.role', 'influencer')
            ->leftJoin('publications', 'users.id', '=', 'publications.influencer_id')
            ->leftJoin('stats', 'publications.id', '=', 'stats.publication_id');
        
        // Appliquer les filtres de date
        if ($dateFrom) {
            $query->where('stats.collected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('stats.collected_at', '<=', $dateTo . ' 23:59:59');
        }
        
        $results = $query
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT publications.id) as publications_count'),
                DB::raw('COALESCE(SUM(stats.likes), 0) + COALESCE(SUM(stats.comments), 0) + COALESCE(SUM(stats.shares), 0) + COALESCE(SUM(stats.views), 0) as total_engagement')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_engagement', 'desc')
            ->limit($limit)
            ->get();
        
        return $results;
    }
    
    /**
     * Récupère le Top des superviseurs avec filtres
     */
    private function getTopSupervisors(Request $request)
    {
        $limit = $request->input('supervisor_limit', 10);
        $dateFrom = $request->input('supervisor_date_from');
        $dateTo = $request->input('supervisor_date_to');
        
        // Requête pour calculer l'engagement par superviseur
        $query = DB::table('users')
            ->where('users.role', 'supervisor')
            ->leftJoin('assignments', 'users.id', '=', 'assignments.supervisor_id')
            ->leftJoin('publications', 'assignments.id', '=', 'publications.assignment_id')
            ->leftJoin('stats', 'publications.id', '=', 'stats.publication_id');
        
        // Appliquer les filtres de date
        if ($dateFrom) {
            $query->where('stats.collected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('stats.collected_at', '<=', $dateTo . ' 23:59:59');
        }
        
        $results = $query
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT assignments.id) as supervisor_assignments_count'),
                DB::raw('COALESCE(SUM(stats.likes), 0) + COALESCE(SUM(stats.comments), 0) + COALESCE(SUM(stats.shares), 0) + COALESCE(SUM(stats.views), 0) as total_engagement')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_engagement', 'desc')
            ->limit($limit)
            ->get();
        
        return $results;
    }
    
    /**
     * Affiche la page des statistiques
     */
    public function index(Request $request)
    {
        // L'accès est déjà protégé par le middleware role:admin,super_admin
        $statistics = $this->getStatistics();
        
        // Remplacer les stats d'engagement par celles filtrées
        $statistics['engagementStats'] = $this->getEngagementStats($request);
        
        // Remplacer les stats de publications par celles filtrées
        $statistics['publicationStats'] = $this->getPublicationStats($request);
        
        // Remplacer les tops par ceux filtrés
        $statistics['topInfluencers'] = $this->getTopInfluencers($request);
        $statistics['topSupervisors'] = $this->getTopSupervisors($request);
        
        // Récupérer les listes pour les filtres
        $campaigns = Campaign::orderBy('name')->get();
        $supervisors = User::where('role', 'supervisor')->orderBy('name')->get();
        $influencers = User::where('role', 'influencer')->orderBy('name')->get();
        
        // Passer les filtres actuels à la vue
        $engagementFilters = [
            'date_from' => $request->input('engagement_date_from'),
            'date_to' => $request->input('engagement_date_to'),
            'campaign_id' => $request->input('engagement_campaign_id'),
            'supervisor_id' => $request->input('engagement_supervisor_id'),
            'influencer_id' => $request->input('engagement_influencer_id'),
        ];
        
        $publicationFilters = [
            'date_from' => $request->input('publication_date_from'),
            'date_to' => $request->input('publication_date_to'),
            'campaign_id' => $request->input('publication_campaign_id'),
            'supervisor_id' => $request->input('publication_supervisor_id'),
        ];
        
        $influencerFilters = [
            'limit' => $request->input('influencer_limit', 10),
            'date_from' => $request->input('influencer_date_from'),
            'date_to' => $request->input('influencer_date_to'),
        ];
        
        $supervisorFilters = [
            'limit' => $request->input('supervisor_limit', 10),
            'date_from' => $request->input('supervisor_date_from'),
            'date_to' => $request->input('supervisor_date_to'),
        ];
        
        return view('statistics.index', array_merge($statistics, [
            'campaigns' => $campaigns,
            'supervisors' => $supervisors,
            'influencers' => $influencers,
            'engagementFilters' => $engagementFilters,
            'publicationFilters' => $publicationFilters,
            'influencerFilters' => $influencerFilters,
            'supervisorFilters' => $supervisorFilters,
        ]));
    }

    /**
     * Exporte les statistiques au format Excel
     */
    public function export()
    {
        // L'accès est déjà protégé par le middleware role:admin,super_admin
        $statistics = $this->getStatistics();
        
        $export = new StatisticsExport(
            $statistics['userStats'],
            $statistics['campaignStats'],
            $statistics['contentStats'],
            $statistics['assignmentStats'],
            $statistics['publicationStats'],
            $statistics['noteStats'],
            $statistics['topInfluencers'],
            $statistics['topSupervisors'],
            $statistics['topCampaigns']
        );
        
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download($export, "statistiques_influboss_{$date}.xlsx");
    }
    
    /**
     * Exporte les statistiques au format PDF
     */
    public function exportPdf()
    {
        // L'accès est déjà protégé par le middleware role:admin,super_admin
        $statistics = $this->getStatistics();
        
        $pdf = Pdf::loadView('statistics.pdf', $statistics);
        $pdf->setPaper('A4', 'portrait');
        
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        return $pdf->download("statistiques_influboss_{$date}.pdf");
    }
    
    /**
     * Exporte toutes les données brutes au format Excel avec plusieurs feuilles
     */
    public function exportRawData()
    {
        // L'accès est déjà protégé par le middleware role:admin,super_admin
        $export = new RawDataExport();
        
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download($export, "donnees_brutes_influboss_{$date}.xlsx");
    }
    
    /**
     * Exporte les statistiques d'engagement filtrées au format CSV
     */
    public function exportEngagement(Request $request)
    {
        $stats = $this->getEngagementStats($request);
        
        $filename = 'engagement_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($stats) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            
            // En-têtes
            fputcsv($file, ['Métrique', 'Total'], ';');
            
            // Données totales
            fputcsv($file, ['Likes', $stats['total']['likes']], ';');
            fputcsv($file, ['Commentaires', $stats['total']['comments']], ';');
            fputcsv($file, ['Partages', $stats['total']['shares']], ';');
            fputcsv($file, ['Vues', $stats['total']['views']], ';');
            fputcsv($file, ['Clics', $stats['total']['clicks']], ';');
            
            fputcsv($file, [], ';');
            fputcsv($file, ['Par Plateforme'], ';');
            fputcsv($file, ['Plateforme', 'Likes', 'Commentaires', 'Partages', 'Vues', 'Clics'], ';');
            
            foreach ($stats['byPlatform'] as $platform => $data) {
                fputcsv($file, [
                    $platform,
                    $data->likes ?? 0,
                    $data->comments ?? 0,
                    $data->shares ?? 0,
                    $data->views ?? 0,
                    $data->clicks ?? 0
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Exporte les statistiques des publications filtrées au format CSV
     */
    public function exportPublications(Request $request)
    {
        $stats = $this->getPublicationStats($request);
        
        $filename = 'publications_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($stats) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            
            // En-têtes
            fputcsv($file, ['Statut', 'Nombre'], ';');
            
            // Données
            fputcsv($file, ['Total', $stats['total']], ';');
            fputcsv($file, ['Vérifiées', $stats['verified']], ';');
            fputcsv($file, ['En attente', $stats['pending']], ';');
            fputcsv($file, ['Rejetées', $stats['rejected']], ';');
            
            fputcsv($file, [], ';');
            fputcsv($file, ['Par Plateforme'], ';');
            fputcsv($file, ['Plateforme', 'Nombre'], ';');
            
            foreach ($stats['byPlatform'] as $platform => $count) {
                fputcsv($file, [$platform, $count], ';');
            }
            
            fputcsv($file, [], ';');
            fputcsv($file, ['Par Mois'], ';');
            fputcsv($file, ['Mois', 'Nombre'], ';');
            
            $monthNames = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            foreach ($stats['byMonth'] as $month => $count) {
                fputcsv($file, [$monthNames[$month] ?? $month, $count], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Exporte le Top Ambassadeurs filtré au format CSV
     */
    public function exportTopInfluencers(Request $request)
    {
        $topInfluencers = $this->getTopInfluencers($request);
        
        $filename = 'top_ambassadeurs_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($topInfluencers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            
            // En-têtes
            fputcsv($file, ['Rang', 'Nom', 'Publications', 'Engagement'], ';');
            
            // Données
            $rank = 1;
            foreach ($topInfluencers as $influencer) {
                fputcsv($file, [
                    $rank++,
                    $influencer->name,
                    $influencer->publications_count,
                    $influencer->total_engagement
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Exporte le Top Superviseurs filtré au format CSV
     */
    public function exportTopSupervisors(Request $request)
    {
        $topSupervisors = $this->getTopSupervisors($request);
        
        $filename = 'top_superviseurs_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($topSupervisors) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            
            // En-têtes
            fputcsv($file, ['Rang', 'Nom', 'Missions supervisées', 'Engagement'], ';');
            
            // Données
            $rank = 1;
            foreach ($topSupervisors as $supervisor) {
                fputcsv($file, [
                    $rank++,
                    $supervisor->name,
                    $supervisor->supervisor_assignments_count,
                    $supervisor->total_engagement
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
