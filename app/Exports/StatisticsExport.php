<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\Assignment;
use App\Models\Publication;
use App\Models\Note;
use Carbon\Carbon;

class StatisticsExport implements WithMultipleSheets
{
    protected $userStats;
    protected $campaignStats;
    protected $contentStats;
    protected $assignmentStats;
    protected $publicationStats;
    protected $noteStats;
    protected $topInfluencers;
    protected $topSupervisors;
    protected $topCampaigns;

    public function __construct(
        $userStats,
        $campaignStats,
        $contentStats,
        $assignmentStats,
        $publicationStats,
        $noteStats,
        $topInfluencers,
        $topSupervisors,
        $topCampaigns
    ) {
        $this->userStats = $userStats;
        $this->campaignStats = $campaignStats;
        $this->contentStats = $contentStats;
        $this->assignmentStats = $assignmentStats;
        $this->publicationStats = $publicationStats;
        $this->noteStats = $noteStats;
        $this->topInfluencers = $topInfluencers;
        $this->topSupervisors = $topSupervisors;
        $this->topCampaigns = $topCampaigns;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Feuille pour les statistiques des utilisateurs
        $sheets[] = new class($this->userStats) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents {
            private $userStats;
            
            public function __construct($userStats) {
                $this->userStats = $userStats;
            }
            
            public function array(): array {
                return [
                    ['Total', $this->userStats['total']],
                    ['Ambassadeurs', $this->userStats['influencers']],
                    ['Superviseurs', $this->userStats['supervisors']],
                    ['Administrateurs', $this->userStats['admins']],
                    ['Actifs', $this->userStats['active']],
                    ['Inactifs', $this->userStats['inactive']],
                    ['Récemment actifs (30 jours)', $this->userStats['recentlyActive']],
                ];
            }
            
            public function headings(): array {
                return ['Catégorie', 'Nombre'];
            }
            
            public function title(): string {
                return 'Utilisateurs';
            }
            
            public function registerEvents(): array {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        $this->addUserChart($event);
                    },
                ];
            }
            
            private function addUserChart(AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                
                // Créer un graphique en secteurs pour les types d'utilisateurs
                $dataSeriesLabels = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Utilisateurs!$A$2:$A$5', null, 4),
                ];
                
                $dataSeriesValues = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Utilisateurs!$B$2:$B$5', null, 4),
                ];
                
                $series = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    DataSeries::GROUPING_STANDARD,
                    range(0, count($dataSeriesValues) - 1),
                    $dataSeriesLabels,
                    [],
                    $dataSeriesValues
                );
                
                $plotArea = new PlotArea(null, [$series]);
                $legend = new Legend(Legend::POSITION_RIGHT, null, false);
                $title = new Title('Répartition des utilisateurs par type');
                
                $chart = new Chart(
                    'userChart',
                    $title,
                    $legend,
                    $plotArea,
                    true,
                    0,
                    null,
                    null
                );
                
                $chart->setTopLeftPosition('D2');
                $chart->setBottomRightPosition('K15');
                
                $worksheet->addChart($chart);
            }
        };
        
        // Feuille pour les statistiques des campagnes
        $sheets[] = new class($this->campaignStats) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents {
            private $campaignStats;
            
            public function __construct($campaignStats) {
                $this->campaignStats = $campaignStats;
            }
            
            public function array(): array {
                $data = [
                    ['Total', $this->campaignStats['total']],
                    ['Actives', $this->campaignStats['active']],
                    ['Brouillons', $this->campaignStats['draft']],
                    ['Terminées', $this->campaignStats['completed']],
                    ['Archivées', $this->campaignStats['archived']],
                ];
                
                // Ajouter les données mensuelles
                $data[] = ['', ''];
                $data[] = ['Mois', 'Nombre de campagnes'];
                
                $months = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                ];
                
                foreach ($months as $num => $name) {
                    $count = $this->campaignStats['byMonth'][$num] ?? 0;
                    $data[] = [$name, $count];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Catégorie', 'Nombre'];
            }
            
            public function title(): string {
                return 'Campagnes';
            }
            
            public function registerEvents(): array {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        $this->addCampaignCharts($event);
                    },
                ];
            }
            
            private function addCampaignCharts(AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                
                // Graphique en secteurs pour les statuts des campagnes
                $statusLabels = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Campagnes!$A$2:$A$6', null, 5),
                ];
                
                $statusValues = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Campagnes!$B$2:$B$6', null, 5),
                ];
                
                $statusSeries = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    DataSeries::GROUPING_STANDARD,
                    range(0, count($statusValues) - 1),
                    $statusLabels,
                    [],
                    $statusValues
                );
                
                $statusPlotArea = new PlotArea(null, [$statusSeries]);
                $statusLegend = new Legend(Legend::POSITION_RIGHT, null, false);
                $statusTitle = new Title('Répartition des campagnes par statut');
                
                $statusChart = new Chart(
                    'campaignStatusChart',
                    $statusTitle,
                    $statusLegend,
                    $statusPlotArea,
                    true,
                    0,
                    null,
                    null
                );
                
                $statusChart->setTopLeftPosition('D2');
                $statusChart->setBottomRightPosition('K15');
                
                // Graphique en barres pour l'évolution mensuelle
                $monthLabels = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Campagnes!$A$8:$A$19', null, 12),
                ];
                
                $monthValues = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Campagnes!$B$8:$B$19', null, 12),
                ];
                
                $monthSeries = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_STANDARD,
                    range(0, count($monthValues) - 1),
                    $monthLabels,
                    [],
                    $monthValues
                );
                
                $monthPlotArea = new PlotArea(null, [$monthSeries]);
                $monthLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
                $monthTitle = new Title('Évolution mensuelle des campagnes');
                
                $monthChart = new Chart(
                    'campaignMonthChart',
                    $monthTitle,
                    $monthLegend,
                    $monthPlotArea,
                    true,
                    0,
                    null,
                    null
                );
                
                $monthChart->setTopLeftPosition('D17');
                $monthChart->setBottomRightPosition('K30');
                
                $worksheet->addChart($statusChart);
                $worksheet->addChart($monthChart);
            }
        };
        
        // Feuille pour les statistiques des contenus
        $sheets[] = new class($this->contentStats) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize {
            private $contentStats;
            
            public function __construct($contentStats) {
                $this->contentStats = $contentStats;
            }
            
            public function array(): array {
                $data = [
                    ['Total', $this->contentStats['total']],
                    ['Approuvés', $this->contentStats['approved']],
                    ['Brouillons', $this->contentStats['draft']],
                ];
                
                // Ajouter les données par type
                $data[] = ['', ''];
                $data[] = ['Type', 'Nombre'];
                
                foreach ($this->contentStats['byType'] as $type => $count) {
                    $typeLabel = ucfirst($type);
                    $data[] = [$typeLabel, $count];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Catégorie', 'Nombre'];
            }
            
            public function title(): string {
                return 'Contenus';
            }
        };
        
        // Feuille pour les statistiques des assignations
        $sheets[] = new class($this->assignmentStats) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize {
            private $assignmentStats;
            
            public function __construct($assignmentStats) {
                $this->assignmentStats = $assignmentStats;
            }
            
            public function array(): array {
                $data = [
                    ['Total', $this->assignmentStats['total']],
                    ['En attente', $this->assignmentStats['pending']],
                    ['Acceptées', $this->assignmentStats['accepted']],
                    ['Rejetées', $this->assignmentStats['rejected']],
                    ['Terminées', $this->assignmentStats['completed']],
                ];
                
                // Ajouter les données mensuelles
                $data[] = ['', ''];
                $data[] = ['Mois', 'Nombre d\'assignations'];
                
                $months = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                ];
                
                foreach ($months as $num => $name) {
                    $count = $this->assignmentStats['byMonth'][$num] ?? 0;
                    $data[] = [$name, $count];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Catégorie', 'Nombre'];
            }
            
            public function title(): string {
                return 'Assignations';
            }
        };
        
        // Feuille pour les statistiques des publications
        $sheets[] = new class($this->publicationStats) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents {
            private $publicationStats;
            
            public function __construct($publicationStats) {
                $this->publicationStats = $publicationStats;
            }
            
            public function array(): array {
                $data = [
                    ['Total', $this->publicationStats['total']],
                    ['En attente', $this->publicationStats['pending']],
                    ['Vérifiées', $this->publicationStats['verified']],
                    ['Rejetées', $this->publicationStats['rejected']],
                ];
                
                // Ajouter les données par plateforme
                $data[] = ['', ''];
                $data[] = ['Plateforme', 'Nombre'];
                
                foreach ($this->publicationStats['byPlatform'] as $platform => $count) {
                    $platformLabel = ucfirst($platform);
                    $data[] = [$platformLabel, $count];
                }
                
                // Ajouter les données mensuelles
                $data[] = ['', ''];
                $data[] = ['Mois', 'Nombre de publications'];
                
                $months = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                ];
                
                foreach ($months as $num => $name) {
                    $count = $this->publicationStats['byMonth'][$num] ?? 0;
                    $data[] = [$name, $count];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Catégorie', 'Nombre'];
            }
            
            public function title(): string {
                return 'Publications';
            }
            
            public function registerEvents(): array {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        $this->addPublicationCharts($event);
                    },
                ];
            }
            
            private function addPublicationCharts(AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                
                // Graphique en secteurs pour les statuts des publications
                $statusLabels = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Publications!$A$2:$A$5', null, 4),
                ];
                
                $statusValues = [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Publications!$B$2:$B$5', null, 4),
                ];
                
                $statusSeries = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    DataSeries::GROUPING_STANDARD,
                    range(0, count($statusValues) - 1),
                    $statusLabels,
                    [],
                    $statusValues
                );
                
                $statusPlotArea = new PlotArea(null, [$statusSeries]);
                $statusLegend = new Legend(Legend::POSITION_RIGHT, null, false);
                $statusTitle = new Title('Répartition des publications par statut');
                
                $statusChart = new Chart(
                    'publicationStatusChart',
                    $statusTitle,
                    $statusLegend,
                    $statusPlotArea,
                    true,
                    0,
                    null,
                    null
                );
                
                $statusChart->setTopLeftPosition('D2');
                $statusChart->setBottomRightPosition('K15');
                
                // Graphique en barres pour les plateformes
                $platformCount = count($this->publicationStats['byPlatform']);
                if ($platformCount > 0) {
                    $platformLabels = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Publications!$A$7:$A$' . (6 + $platformCount), null, $platformCount),
                    ];
                    
                    $platformValues = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Publications!$B$7:$B$' . (6 + $platformCount), null, $platformCount),
                    ];
                    
                    $platformSeries = new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        DataSeries::GROUPING_STANDARD,
                        range(0, count($platformValues) - 1),
                        $platformLabels,
                        [],
                        $platformValues
                    );
                    
                    $platformPlotArea = new PlotArea(null, [$platformSeries]);
                    $platformLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
                    $platformTitle = new Title('Publications par plateforme');
                    
                    $platformChart = new Chart(
                        'publicationPlatformChart',
                        $platformTitle,
                        $platformLegend,
                        $platformPlotArea,
                        true,
                        0,
                        null,
                        null
                    );
                    
                    $platformChart->setTopLeftPosition('D17');
                    $platformChart->setBottomRightPosition('K30');
                    
                    $worksheet->addChart($platformChart);
                }
                
                $worksheet->addChart($statusChart);
            }
        };
        
        // Feuille pour les top influenceurs
        $sheets[] = new class($this->topInfluencers) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents {
            private $topInfluencers;
            
            public function __construct($topInfluencers) {
                $this->topInfluencers = $topInfluencers;
            }
            
            public function array(): array {
                $data = [];
                
                foreach ($this->topInfluencers as $influencer) {
                    $data[] = [
                        $influencer->name,
                        $influencer->email,
                        $influencer->publications_count
                    ];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Nom', 'Email', 'Nombre de publications'];
            }
            
            public function title(): string {
                return 'Top Ambassadeurs';
            }
            
            public function registerEvents(): array {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        $this->addInfluencerChart($event);
                    },
                ];
            }
            
            private function addInfluencerChart(AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                
                $influencerCount = count($this->topInfluencers);
                if ($influencerCount > 0) {
                    // Graphique en barres horizontales pour les top influenceurs
                    $nameLabels = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Top Ambassadeurs!$A$2:$A$' . (1 + $influencerCount), null, $influencerCount),
                    ];
                    
                    $publicationValues = [
                        new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Top Ambassadeurs!$C$2:$C$' . (1 + $influencerCount), null, $influencerCount),
                    ];
                    
                    $series = new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        DataSeries::GROUPING_STANDARD,
                        range(0, count($publicationValues) - 1),
                        $nameLabels,
                        [],
                        $publicationValues
                    );
                    
                    $plotArea = new PlotArea(null, [$series]);
                    $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
                    $title = new Title('Top 10 Ambassadeurs par nombre de publications');
                    
                    $chart = new Chart(
                        'topInfluencersChart',
                        $title,
                        $legend,
                        $plotArea,
                        true,
                        0,
                        null,
                        null
                    );
                    
                    $chart->setTopLeftPosition('E2');
                    $chart->setBottomRightPosition('M20');
                    
                    $worksheet->addChart($chart);
                }
            }
        };
        
        // Feuille pour les top superviseurs
        $sheets[] = new class($this->topSupervisors) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize {
            private $topSupervisors;
            
            public function __construct($topSupervisors) {
                $this->topSupervisors = $topSupervisors;
            }
            
            public function array(): array {
                $data = [];
                
                foreach ($this->topSupervisors as $supervisor) {
                    $data[] = [
                        $supervisor->name,
                        $supervisor->email,
                        $supervisor->supervisor_assignments_count
                    ];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Nom', 'Email', 'Nombre d\'assignations'];
            }
            
            public function title(): string {
                return 'Top Superviseurs';
            }
        };
        
        // Feuille pour les top campagnes
        $sheets[] = new class($this->topCampaigns) implements FromArray, WithHeadings, WithTitle, ShouldAutoSize {
            private $topCampaigns;
            
            public function __construct($topCampaigns) {
                $this->topCampaigns = $topCampaigns;
            }
            
            public function array(): array {
                $data = [];
                
                foreach ($this->topCampaigns as $campaign) {
                    $data[] = [
                        $campaign->name,
                        $campaign->status,
                        $campaign->publications_count
                    ];
                }
                
                return $data;
            }
            
            public function headings(): array {
                return ['Nom', 'Statut', 'Nombre de publications'];
            }
            
            public function title(): string {
                return 'Top Campagnes';
            }
        };
        
        return $sheets;
    }
}
