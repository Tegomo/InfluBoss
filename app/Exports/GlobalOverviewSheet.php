<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Assignment;
use App\Models\Publication;
use App\Models\Content;
use App\Models\Stat;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GlobalOverviewSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        // KPIs globaux
        $totalUsers = User::count();
        $totalInfluencers = User::where('role', 'influencer')->count();
        $totalSupervisors = User::where('role', 'supervisor')->count();
        $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();
        
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $completedCampaigns = Campaign::where('status', 'completed')->count();
        
        $totalAssignments = Assignment::count();
        $acceptedAssignments = Assignment::where('status', 'accepted')->count();
        $completedAssignments = Assignment::where('status', 'completed')->count();
        
        $totalPublications = Publication::count();
        $verifiedPublications = Publication::where('status', 'verified')->count();
        $pendingPublications = Publication::where('status', 'pending')->count();
        
        $totalContents = Content::count();
        $approvedContents = Content::where('status', 'approved')->count();
        
        // Statistiques d'engagement
        $totalLikes = Stat::sum('likes');
        $totalComments = Stat::sum('comments');
        $totalShares = Stat::sum('shares');
        $totalViews = Stat::sum('views');
        $totalEngagement = $totalLikes + $totalComments + $totalShares;
        
        $avgEngagementRate = Stat::avg('engagement_rate');

        return collect([
            ['Indicateur', 'Valeur'],
            ['', ''],
            ['=== UTILISATEURS ===', ''],
            ['Total utilisateurs', $totalUsers],
            ['Influenceurs', $totalInfluencers],
            ['Superviseurs', $totalSupervisors],
            ['Administrateurs', $totalAdmins],
            ['', ''],
            ['=== CAMPAGNES ===', ''],
            ['Total campagnes', $totalCampaigns],
            ['Campagnes actives', $activeCampaigns],
            ['Campagnes terminées', $completedCampaigns],
            ['', ''],
            ['=== MISSIONS ===', ''],
            ['Total missions', $totalAssignments],
            ['Missions acceptées', $acceptedAssignments],
            ['Missions terminées', $completedAssignments],
            ['Taux de complétion (%)', $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0],
            ['', ''],
            ['=== PUBLICATIONS ===', ''],
            ['Total publications', $totalPublications],
            ['Publications vérifiées', $verifiedPublications],
            ['Publications en attente', $pendingPublications],
            ['Taux de vérification (%)', $totalPublications > 0 ? round(($verifiedPublications / $totalPublications) * 100, 2) : 0],
            ['', ''],
            ['=== CONTENUS ===', ''],
            ['Total contenus', $totalContents],
            ['Contenus approuvés', $approvedContents],
            ['', ''],
            ['=== ENGAGEMENT ===', ''],
            ['Total likes', number_format($totalLikes)],
            ['Total commentaires', number_format($totalComments)],
            ['Total partages', number_format($totalShares)],
            ['Total vues', number_format($totalViews)],
            ['Engagement total', number_format($totalEngagement)],
            ['Taux d\'engagement moyen (%)', round($avgEngagementRate, 2)],
            ['', ''],
            ['Date d\'export', now()->format('d/m/Y H:i:s')],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Vue d\'ensemble';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17A2B8']]],
            9 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17A2B8']]],
            14 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17A2B8']]],
            19 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17A2B8']]],
            25 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17A2B8']]],
            29 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17A2B8']]],
        ];
    }
}
