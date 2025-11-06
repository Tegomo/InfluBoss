<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Publication;
use App\Models\Stat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatApiController extends Controller
{
    /**
     * Obtenir les statistiques globales.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function global(Request $request)
    {
        $user = Auth::user();
        
        // Période (par défaut, les 30 derniers jours)
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Base des requêtes selon le rôle de l'utilisateur
        if ($user->role === 'influencer') {
            $publicationsQuery = Publication::where('influencer_id', $user->id);
        } elseif ($user->role === 'supervisor') {
            $publicationsQuery = Publication::whereHas('influencer', function ($query) use ($user) {
                $query->whereHas('supervisors', function ($q) use ($user) {
                    $q->where('supervisor_id', $user->id);
                });
            });
        } else {
            // Admin et super_admin voient tout
            $publicationsQuery = Publication::query();
        }
        
        // Filtre par date de publication
        $publicationsQuery->whereBetween('published_at', [$startDate, $endDate]);
        $publicationsQuery->where('status', 'verified');
        
        // Statistiques de base
        $totalPublications = $publicationsQuery->count();
        $totalCampaigns = $publicationsQuery->distinct('campaign_id')->count('campaign_id');
        
        // Statistiques par plateforme
        $platformStats = $publicationsQuery
            ->select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->get()
            ->pluck('count', 'platform')
            ->toArray();
            
        // Statistiques d'engagement
        $statsData = Stat::whereHas('publication', function ($query) use ($publicationsQuery) {
                $query->whereIn('id', $publicationsQuery->pluck('id'));
            })
            ->select(
                DB::raw('SUM(likes) as total_likes'),
                DB::raw('SUM(comments) as total_comments'),
                DB::raw('SUM(shares) as total_shares'),
                DB::raw('SUM(views) as total_views'),
                DB::raw('SUM(likes + comments + shares + saves) as total_engagement')
            )
            ->first();
            
        // Évolution dans le temps
        $timeStats = Stat::whereHas('publication', function ($query) use ($publicationsQuery) {
                $query->whereIn('id', $publicationsQuery->pluck('id'));
            })
            ->selectRaw('DATE(collected_at) as date, SUM(likes + comments + shares + saves) as engagement, SUM(views) as views')
            ->whereBetween('collected_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Formater les données pour le graphique
        $timeseriesData = [
            'dates' => $timeStats->pluck('date')->toArray(),
            'engagement' => $timeStats->pluck('engagement')->toArray(),
            'views' => $timeStats->pluck('views')->toArray(),
        ];
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_publications' => $totalPublications,
                'total_campaigns' => $totalCampaigns,
                'total_likes' => $statsData ? $statsData->total_likes : 0,
                'total_comments' => $statsData ? $statsData->total_comments : 0,
                'total_shares' => $statsData ? $statsData->total_shares : 0,
                'total_views' => $statsData ? $statsData->total_views : 0,
                'total_engagement' => $statsData ? $statsData->total_engagement : 0,
                'engagement_rate' => $statsData && $statsData->total_views > 0 
                    ? round(($statsData->total_engagement / $statsData->total_views) * 100, 2) 
                    : 0,
            ],
            'platforms' => $platformStats,
            'timeseries' => $timeseriesData,
        ]);
    }
    
    /**
     * Obtenir les statistiques par plateforme.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function platforms(Request $request)
    {
        $user = Auth::user();
        
        // Période (par défaut, les 30 derniers jours)
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Base des requêtes selon le rôle de l'utilisateur
        if ($user->role === 'influencer') {
            $publicationsQuery = Publication::where('influencer_id', $user->id);
        } elseif ($user->role === 'supervisor') {
            $publicationsQuery = Publication::whereHas('influencer', function ($query) use ($user) {
                $query->whereHas('supervisors', function ($q) use ($user) {
                    $q->where('supervisor_id', $user->id);
                });
            });
        } else {
            // Admin et super_admin voient tout
            $publicationsQuery = Publication::query();
        }
        
        // Filtre par date de publication
        $publicationsQuery->whereBetween('published_at', [$startDate, $endDate]);
        $publicationsQuery->where('status', 'verified');
        
        // Statistiques par plateforme
        $platforms = ['instagram', 'tiktok', 'youtube', 'twitter', 'facebook', 'linkedin'];
        $platformData = [];
        
        foreach ($platforms as $platform) {
            $platformPublications = clone $publicationsQuery;
            $platformPublications->where('platform', $platform);
            
            $publicationIds = $platformPublications->pluck('id')->toArray();
            
            if (empty($publicationIds)) {
                continue;
            }
            
            $stats = Stat::whereIn('publication_id', $publicationIds)
                ->select(
                    DB::raw('SUM(likes) as likes'),
                    DB::raw('SUM(comments) as comments'),
                    DB::raw('SUM(shares) as shares'),
                    DB::raw('SUM(views) as views'),
                    DB::raw('SUM(likes + comments + shares + saves) as engagement')
                )
                ->first();
            
            $platformData[$platform] = [
                'count' => count($publicationIds),
                'likes' => $stats ? $stats->likes : 0,
                'comments' => $stats ? $stats->comments : 0,
                'shares' => $stats ? $stats->shares : 0,
                'views' => $stats ? $stats->views : 0,
                'engagement' => $stats ? $stats->engagement : 0,
                'engagement_rate' => $stats && $stats->views > 0 
                    ? round(($stats->engagement / $stats->views) * 100, 2) 
                    : 0,
            ];
        }
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'platforms' => $platformData
        ]);
    }
    
    /**
     * Obtenir les tendances d'engagement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function trends(Request $request)
    {
        $user = Auth::user();
        
        // Période (par défaut, les 90 derniers jours)
        $startDate = $request->input('start_date', Carbon::now()->subDays(90)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Intervalle (jour, semaine, mois)
        $interval = $request->input('interval', 'week');
        
        $dateFormat = 'Y-m-d';
        $groupBy = 'DATE(collected_at)';
        
        if ($interval === 'week') {
            $dateFormat = 'Y-W';
            $groupBy = 'YEARWEEK(collected_at)';
        } elseif ($interval === 'month') {
            $dateFormat = 'Y-m';
            $groupBy = 'DATE_FORMAT(collected_at, "%Y-%m")';
        }
        
        // Base des requêtes selon le rôle de l'utilisateur
        if ($user->role === 'influencer') {
            $publicationsQuery = Publication::where('influencer_id', $user->id);
        } elseif ($user->role === 'supervisor') {
            $publicationsQuery = Publication::whereHas('influencer', function ($query) use ($user) {
                $query->whereHas('supervisors', function ($q) use ($user) {
                    $q->where('supervisor_id', $user->id);
                });
            });
        } else {
            // Admin et super_admin voient tout
            $publicationsQuery = Publication::query();
        }
        
        // Filtre par date de publication et statut
        $publicationsQuery->whereBetween('published_at', [$startDate, $endDate]);
        $publicationsQuery->where('status', 'verified');
        
        $publicationIds = $publicationsQuery->pluck('id')->toArray();
        
        if (empty($publicationIds)) {
            return response()->json([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'interval' => $interval,
                'trends' => [],
            ]);
        }
        
        // Récupérer les tendances
        $trends = Stat::whereIn('publication_id', $publicationIds)
            ->selectRaw("{$groupBy} as period, 
                    SUM(likes) as likes, 
                    SUM(comments) as comments, 
                    SUM(shares) as shares, 
                    SUM(views) as views, 
                    SUM(likes + comments + shares + saves) as engagement,
                    COUNT(DISTINCT publication_id) as publications")
            ->whereBetween('collected_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->map(function ($item) {
                $item->engagement_rate = $item->views > 0 
                    ? round(($item->engagement / $item->views) * 100, 2) 
                    : 0;
                return $item;
            });
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'interval' => $interval,
            'trends' => $trends,
        ]);
    }
    
    /**
     * Obtenir les statistiques d'engagement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function engagement(Request $request)
    {
        $user = Auth::user();
        
        // Période (par défaut, les 30 derniers jours)
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Filtres optionnels
        $platform = $request->input('platform');
        $campaignId = $request->input('campaign_id');
        $influencerId = $request->input('influencer_id');
        
        // Base des requêtes selon le rôle de l'utilisateur
        if ($user->role === 'influencer') {
            $publicationsQuery = Publication::where('influencer_id', $user->id);
        } elseif ($user->role === 'supervisor') {
            $publicationsQuery = Publication::whereHas('influencer', function ($query) use ($user) {
                $query->whereHas('supervisors', function ($q) use ($user) {
                    $q->where('supervisor_id', $user->id);
                });
            });
        } else {
            // Admin et super_admin voient tout
            $publicationsQuery = Publication::query();
        }
        
        // Appliquer les filtres
        $publicationsQuery->whereBetween('published_at', [$startDate, $endDate]);
        $publicationsQuery->where('status', 'verified');
        
        if ($platform) {
            $publicationsQuery->where('platform', $platform);
        }
        
        if ($campaignId) {
            $publicationsQuery->where('campaign_id', $campaignId);
        }
        
        if ($influencerId) {
            $publicationsQuery->where('influencer_id', $influencerId);
        }
        
        $publicationIds = $publicationsQuery->pluck('id')->toArray();
        
        if (empty($publicationIds)) {
            return response()->json([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'filters' => [
                    'platform' => $platform,
                    'campaign_id' => $campaignId,
                    'influencer_id' => $influencerId,
                ],
                'engagement' => [
                    'total' => 0,
                    'likes' => 0,
                    'comments' => 0,
                    'shares' => 0,
                    'views' => 0,
                    'rate' => 0,
                ],
                'publications' => [],
            ]);
        }
        
        // Récupérer les statistiques agrégées
        $aggregatedStats = Stat::whereIn('publication_id', $publicationIds)
            ->select(
                DB::raw('SUM(likes) as likes'),
                DB::raw('SUM(comments) as comments'),
                DB::raw('SUM(shares) as shares'),
                DB::raw('SUM(saves) as saves'),
                DB::raw('SUM(views) as views'),
                DB::raw('SUM(likes + comments + shares + saves) as engagement')
            )
            ->first();
        
        // Récupérer les publications avec leurs statistiques, triées par engagement
        $publications = Publication::whereIn('id', $publicationIds)
            ->with(['influencer:id,name,email,profile_photo', 'campaign:id,name', 'latestStats'])
            ->get()
            ->map(function ($publication) {
                $stats = $publication->latestStats;
                $engagement = $stats ? ($stats->likes + $stats->comments + $stats->shares + $stats->saves) : 0;
                $views = $stats ? $stats->views : 0;
                $rate = $views > 0 ? ($engagement / $views * 100) : 0;
                
                return [
                    'id' => $publication->id,
                    'url' => $publication->url,
                    'platform' => $publication->platform,
                    'content_type' => $publication->content_type,
                    'published_at' => $publication->published_at,
                    'campaign' => $publication->campaign ? [
                        'id' => $publication->campaign->id,
                        'name' => $publication->campaign->name,
                    ] : null,
                    'influencer' => $publication->user ? [
                        'id' => $publication->user->id,
                        'name' => $publication->user->name,
                    ] : null,
                    'stats' => [
                        'likes' => $stats ? $stats->likes : 0,
                        'comments' => $stats ? $stats->comments : 0,
                        'shares' => $stats ? $stats->shares : 0,
                        'views' => $views,
                        'engagement' => $engagement,
                        'engagement_rate' => round($rate, 2),
                    ]
                ];
            })
            ->sortByDesc(function ($publication) {
                return $publication['stats']['engagement'];
            })
            ->values()
            ->all();
        
        // Calculer le taux d'engagement global
        $totalViews = $aggregatedStats ? $aggregatedStats->views : 0;
        $totalEngagement = $aggregatedStats ? $aggregatedStats->engagement : 0;
        $engagementRate = $totalViews > 0 ? ($totalEngagement / $totalViews * 100) : 0;
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'filters' => [
                'platform' => $platform,
                'campaign_id' => $campaignId,
                'influencer_id' => $influencerId,
            ],
            'engagement' => [
                'total' => $totalEngagement,
                'likes' => $aggregatedStats ? $aggregatedStats->likes : 0,
                'comments' => $aggregatedStats ? $aggregatedStats->comments : 0,
                'shares' => $aggregatedStats ? $aggregatedStats->shares : 0,
                'saves' => $aggregatedStats ? $aggregatedStats->saves : 0,
                'views' => $totalViews,
                'rate' => round($engagementRate, 2),
            ],
            'publications' => $publications,
        ]);
    }
}
