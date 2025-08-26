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
        
        // Top influenceurs par nombre de publications
        $topInfluencers = User::where('role', 'influencer')
            ->withCount('publications')
            ->orderBy('publications_count', 'desc')
            ->limit(10)
            ->get();
        
        // Top superviseurs par nombre d'assignations
        $topSupervisors = User::where('role', 'supervisor')
            ->withCount('supervisorAssignments')
            ->orderBy('supervisor_assignments_count', 'desc')
            ->limit(10)
            ->get();
        
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
            'topInfluencers' => $topInfluencers,
            'topSupervisors' => $topSupervisors,
            'topCampaigns' => $topCampaigns
        ];
    }
    
    /**
     * Affiche la page des statistiques
     */
    public function index()
    {
        // L'accès est déjà protégé par le middleware role:admin,super_admin
        $statistics = $this->getStatistics();
        
        return view('statistics.index', $statistics);
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
}
