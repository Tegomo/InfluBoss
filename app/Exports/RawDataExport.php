<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Content;
use App\Models\Assignment;
use App\Models\Publication;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

class RawDataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        
        // Feuille Utilisateurs
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return User::select([
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                    'created_at',
                    'updated_at'
                ])->get()->map(function($user) {
                    return [
                        'ID' => $user->id,
                        'Nom' => $user->name,
                        'Email' => $user->email,
                        'Rôle' => $user->role,
                        'Actif' => $user->is_active ? 'Oui' : 'Non',
                        'Date de création' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '',
                        'Date de modification' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                });
            }
            
            public function headings(): array
            {
                return ['ID', 'Nom', 'Email', 'Rôle', 'Actif', 'Date de création', 'Date de modification'];
            }
            
            public function title(): string
            {
                return 'Utilisateurs';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Campagnes
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return Campaign::with('creator')->get()->map(function($campaign) {
                    return [
                        'ID' => $campaign->id,
                        'Nom' => $campaign->name,
                        'Description' => $campaign->description,
                        'Statut' => $campaign->status,
                        'Budget' => $campaign->budget,
                        'Date de début' => $campaign->start_date ? $campaign->start_date->format('Y-m-d') : '',
                        'Date de fin' => $campaign->end_date ? $campaign->end_date->format('Y-m-d') : '',
                        'Créé par' => $campaign->creator ? $campaign->creator->name : '',
                        'Date de création' => $campaign->created_at ? $campaign->created_at->format('Y-m-d H:i:s') : '',
                        'Date de modification' => $campaign->updated_at ? $campaign->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                });
            }
            
            public function headings(): array
            {
                return ['ID', 'Nom', 'Description', 'Statut', 'Budget', 'Date de début', 'Date de fin', 'Créé par', 'Date de création', 'Date de modification'];
            }
            
            public function title(): string
            {
                return 'Campagnes';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Contenus
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return Content::with(['campaign', 'creator'])->get()->map(function($content) {
                    return [
                        'ID' => $content->id,
                        'Titre' => $content->title,
                        'Description' => $content->description,
                        'Type' => $content->type,
                        'Statut' => $content->status,
                        'Campagne' => $content->campaign ? $content->campaign->name : '',
                        'Créé par' => $content->creator ? $content->creator->name : '',
                        'Date de création' => $content->created_at ? $content->created_at->format('Y-m-d H:i:s') : '',
                        'Date de modification' => $content->updated_at ? $content->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                });
            }
            
            public function headings(): array
            {
                return ['ID', 'Titre', 'Description', 'Type', 'Statut', 'Campagne', 'Créé par', 'Date de création', 'Date de modification'];
            }
            
            public function title(): string
            {
                return 'Contenus';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Missions (Assignments)
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return Assignment::with(['campaign', 'influencer', 'supervisor'])->get()->map(function($assignment) {
                    return [
                        'ID' => $assignment->id,
                        'Campagne' => $assignment->campaign ? $assignment->campaign->name : '',
                        'Influenceur' => $assignment->influencer ? $assignment->influencer->name : '',
                        'Superviseur' => $assignment->supervisor ? $assignment->supervisor->name : '',
                        'Statut' => $assignment->status,
                        'Date limite' => $assignment->deadline ? $assignment->deadline->format('Y-m-d') : '',
                        'Rémunération' => $assignment->compensation,
                        'Instructions' => $assignment->instructions,
                        'Date de création' => $assignment->created_at ? $assignment->created_at->format('Y-m-d H:i:s') : '',
                        'Date de modification' => $assignment->updated_at ? $assignment->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                });
            }
            
            public function headings(): array
            {
                return ['ID', 'Campagne', 'Influenceur', 'Superviseur', 'Statut', 'Date limite', 'Rémunération', 'Instructions', 'Date de création', 'Date de modification'];
            }
            
            public function title(): string
            {
                return 'Missions';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Publications
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return Publication::with(['assignment.campaign', 'influencer', 'content', 'latestStats'])->get()->map(function($publication) {
                    // Récupérer les dernières statistiques
                    $stats = $publication->latestStats;
                    $likes = $stats ? $stats->likes : 0;
                    $comments = $stats ? $stats->comments : 0;
                    $shares = $stats ? $stats->shares : 0;
                    $views = $stats ? $stats->views : 0;
                    $clicks = $stats ? $stats->clicks : 0;
                    
                    // Calculer l'engagement total
                    $totalEngagement = $likes + $comments + $shares + $views + $clicks;
                    
                    return [
                        'ID' => $publication->id,
                        'Campagne' => $publication->assignment && $publication->assignment->campaign ? $publication->assignment->campaign->name : '',
                        'Influenceur' => $publication->influencer ? $publication->influencer->name : '',
                        'Contenu' => $publication->content ? $publication->content->title : '',
                        'Plateforme' => $publication->platform,
                        'Type de post' => $publication->post_type,
                        'URL du post' => $publication->post_url,
                        'Contenu du post' => $publication->post_content,
                        'Statut' => $publication->status,
                        'Likes' => $likes,
                        'Commentaires' => $comments,
                        'Partages' => $shares,
                        'Vues' => $views,
                        'Clics' => $clicks,
                        'Engagement Total' => $totalEngagement,
                        'Date de publication' => $publication->published_at ? $publication->published_at->format('Y-m-d H:i:s') : '',
                        'Date de création' => $publication->created_at ? $publication->created_at->format('Y-m-d H:i:s') : '',
                        'Date de modification' => $publication->updated_at ? $publication->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                });
            }
            
            public function headings(): array
            {
                return ['ID', 'Campagne', 'Influenceur', 'Contenu', 'Plateforme', 'Type de post', 'URL du post', 'Contenu du post', 'Statut', 'Likes', 'Commentaires', 'Partages', 'Vues', 'Clics', 'Engagement Total', 'Date de publication', 'Date de création', 'Date de modification'];
            }
            
            public function title(): string
            {
                return 'Publications';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Statistiques des publications
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return DB::table('stats')
                    ->join('publications', 'stats.publication_id', '=', 'publications.id')
                    ->select([
                        'stats.id',
                        'stats.publication_id',
                        'publications.platform',
                        'stats.likes',
                        'stats.comments',
                        'stats.shares',
                        'stats.views',
                        'stats.clicks',
                        'stats.engagement_rate',
                        'stats.collected_at',
                        'stats.created_at'
                    ])
                    ->get()
                    ->map(function($stat) {
                        return [
                            'ID' => $stat->id,
                            'ID Publication' => $stat->publication_id,
                            'Plateforme' => $stat->platform,
                            'Likes' => $stat->likes,
                            'Commentaires' => $stat->comments,
                            'Partages' => $stat->shares,
                            'Vues' => $stat->views,
                            'Clics' => $stat->clicks,
                            'Taux d\'engagement' => $stat->engagement_rate,
                            'Date de collecte' => $stat->collected_at,
                            'Date de création' => $stat->created_at,
                        ];
                    });
            }
            
            public function headings(): array
            {
                return ['ID', 'ID Publication', 'Plateforme', 'Likes', 'Commentaires', 'Partages', 'Vues', 'Clics', 'Taux d\'engagement', 'Date de collecte', 'Date de création'];
            }
            
            public function title(): string
            {
                return 'Statistiques';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Notes
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return Note::with(['author', 'notable'])->get()->map(function($note) {
                    $notableType = '';
                    $notableId = '';
                    
                    if ($note->notable) {
                        $notableType = class_basename($note->notable_type);
                        $notableId = $note->notable_id;
                    }
                    
                    return [
                        'ID' => $note->id,
                        'Type d\'objet' => $notableType,
                        'ID de l\'objet' => $notableId,
                        'Auteur' => $note->author ? $note->author->name : '',
                        'Contenu' => $note->content,
                        'Date de création' => $note->created_at ? $note->created_at->format('Y-m-d H:i:s') : '',
                        'Date de modification' => $note->updated_at ? $note->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                });
            }
            
            public function headings(): array
            {
                return ['ID', 'Type d\'objet', 'ID de l\'objet', 'Auteur', 'Contenu', 'Date de création', 'Date de modification'];
            }
            
            public function title(): string
            {
                return 'Notes';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Statistiques par Influenceur
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                // Récupérer tous les influenceurs avec leurs statistiques agrégées
                return User::where('role', 'influencer')
                    ->with(['publications.latestStats'])
                    ->get()
                    ->map(function($influencer) {
                        $totalLikes = 0;
                        $totalComments = 0;
                        $totalShares = 0;
                        $totalViews = 0;
                        $totalClicks = 0;
                        $publicationsCount = 0;
                        
                        // Parcourir toutes les publications de l'influenceur
                        foreach ($influencer->publications as $publication) {
                            if ($publication->latestStats) {
                                $totalLikes += $publication->latestStats->likes ?? 0;
                                $totalComments += $publication->latestStats->comments ?? 0;
                                $totalShares += $publication->latestStats->shares ?? 0;
                                $totalViews += $publication->latestStats->views ?? 0;
                                $totalClicks += $publication->latestStats->clicks ?? 0;
                                $publicationsCount++;
                            }
                        }
                        
                        // Calculer l'engagement total
                        $totalEngagement = $totalLikes + $totalComments + $totalShares + $totalViews + $totalClicks;
                        
                        return [
                            'ID' => $influencer->id,
                            'Nom' => $influencer->name,
                            'Email' => $influencer->email,
                            'Nombre de publications' => $influencer->publications->count(),
                            'Publications avec stats' => $publicationsCount,
                            'Total Likes' => $totalLikes,
                            'Total Commentaires' => $totalComments,
                            'Total Partages' => $totalShares,
                            'Total Vues' => $totalViews,
                            'Total Clics' => $totalClicks,
                            'Engagement Total' => $totalEngagement,
                        ];
                    })
                    ->sortByDesc('Engagement Total')
                    ->values();
            }
            
            public function headings(): array
            {
                return ['ID', 'Nom', 'Email', 'Nombre de publications', 'Publications avec stats', 'Total Likes', 'Total Commentaires', 'Total Partages', 'Total Vues', 'Total Clics', 'Engagement Total'];
            }
            
            public function title(): string
            {
                return 'Stats par Influenceur';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Statistiques par Plateforme
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                // Récupérer toutes les plateformes distinctes
                $platforms = Publication::select('platform')
                    ->distinct()
                    ->whereNotNull('platform')
                    ->pluck('platform');
                
                return $platforms->map(function($platform) {
                    $totalLikes = 0;
                    $totalComments = 0;
                    $totalShares = 0;
                    $totalViews = 0;
                    $totalClicks = 0;
                    $publicationsCount = 0;
                    $publicationsWithStatsCount = 0;
                    
                    // Récupérer toutes les publications de cette plateforme avec leurs stats
                    $publications = Publication::where('platform', $platform)
                        ->with('latestStats')
                        ->get();
                    
                    $publicationsCount = $publications->count();
                    
                    foreach ($publications as $publication) {
                        if ($publication->latestStats) {
                            $totalLikes += $publication->latestStats->likes ?? 0;
                            $totalComments += $publication->latestStats->comments ?? 0;
                            $totalShares += $publication->latestStats->shares ?? 0;
                            $totalViews += $publication->latestStats->views ?? 0;
                            $totalClicks += $publication->latestStats->clicks ?? 0;
                            $publicationsWithStatsCount++;
                        }
                    }
                    
                    // Calculer l'engagement total
                    $totalEngagement = $totalLikes + $totalComments + $totalShares + $totalViews + $totalClicks;
                    
                    // Calculer les moyennes
                    $avgLikes = $publicationsWithStatsCount > 0 ? round($totalLikes / $publicationsWithStatsCount, 2) : 0;
                    $avgComments = $publicationsWithStatsCount > 0 ? round($totalComments / $publicationsWithStatsCount, 2) : 0;
                    $avgShares = $publicationsWithStatsCount > 0 ? round($totalShares / $publicationsWithStatsCount, 2) : 0;
                    $avgViews = $publicationsWithStatsCount > 0 ? round($totalViews / $publicationsWithStatsCount, 2) : 0;
                    $avgClicks = $publicationsWithStatsCount > 0 ? round($totalClicks / $publicationsWithStatsCount, 2) : 0;
                    $avgEngagement = $publicationsWithStatsCount > 0 ? round($totalEngagement / $publicationsWithStatsCount, 2) : 0;
                    
                    return [
                        'Plateforme' => ucfirst($platform),
                        'Nombre de publications' => $publicationsCount,
                        'Publications avec stats' => $publicationsWithStatsCount,
                        'Total Likes' => $totalLikes,
                        'Total Commentaires' => $totalComments,
                        'Total Partages' => $totalShares,
                        'Total Vues' => $totalViews,
                        'Total Clics' => $totalClicks,
                        'Engagement Total' => $totalEngagement,
                        'Moy. Likes' => $avgLikes,
                        'Moy. Commentaires' => $avgComments,
                        'Moy. Partages' => $avgShares,
                        'Moy. Vues' => $avgViews,
                        'Moy. Clics' => $avgClicks,
                        'Moy. Engagement' => $avgEngagement,
                    ];
                })
                ->sortByDesc('Engagement Total')
                ->values();
            }
            
            public function headings(): array
            {
                return [
                    'Plateforme', 
                    'Nombre de publications', 
                    'Publications avec stats', 
                    'Total Likes', 
                    'Total Commentaires', 
                    'Total Partages', 
                    'Total Vues', 
                    'Total Clics', 
                    'Engagement Total',
                    'Moy. Likes',
                    'Moy. Commentaires',
                    'Moy. Partages',
                    'Moy. Vues',
                    'Moy. Clics',
                    'Moy. Engagement'
                ];
            }
            
            public function title(): string
            {
                return 'Stats par Plateforme';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        // Feuille Relations Superviseur-Influenceur
        $sheets[] = new class implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles {
            public function collection()
            {
                return DB::table('supervisor_influencer')
                    ->join('users as supervisors', 'supervisor_influencer.supervisor_id', '=', 'supervisors.id')
                    ->join('users as influencers', 'supervisor_influencer.influencer_id', '=', 'influencers.id')
                    ->select([
                        'supervisor_influencer.id',
                        'supervisors.name as supervisor_name',
                        'supervisors.email as supervisor_email',
                        'influencers.name as influencer_name',
                        'influencers.email as influencer_email',
                        'supervisor_influencer.created_at'
                    ])
                    ->get()
                    ->map(function($relation) {
                        return [
                            'ID' => $relation->id,
                            'Superviseur' => $relation->supervisor_name,
                            'Email Superviseur' => $relation->supervisor_email,
                            'Influenceur' => $relation->influencer_name,
                            'Email Influenceur' => $relation->influencer_email,
                            'Date d\'assignation' => $relation->created_at,
                        ];
                    });
            }
            
            public function headings(): array
            {
                return ['ID', 'Superviseur', 'Email Superviseur', 'Influenceur', 'Email Influenceur', 'Date d\'assignation'];
            }
            
            public function title(): string
            {
                return 'Superviseur-Influenceur';
            }
            
            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };
        
        return $sheets;
    }
}
