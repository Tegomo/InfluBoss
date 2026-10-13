<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\FollowerSnapshot;
use App\Models\Publication;
use App\Models\SocialAccount;
use App\Models\Stat;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Agrégations affichées sur la fiche influenceur : résultats par campagne
 * (dernier relevé de chaque publication vérifiée) et courbe des abonnés.
 */
final class InfluencerStatsService
{
    /**
     * Résultats obtenus par mission, calculés sur le dernier relevé `stats`
     * de chaque publication vérifiée.
     *
     * @param  Collection<int, Assignment>  $assignments  chargées avec `campaign` et `publications.latestStats`
     * @return Collection<int, array<string, mixed>>
     */
    public function campaignResults(Collection $assignments): Collection
    {
        return $assignments->map(function (Assignment $assignment) {
            $publications = $assignment->publications;
            $verified = $publications->where('status', 'verified');
            $stats = $verified->map(fn (Publication $publication) => $publication->latestStats)->filter();

            return [
                'assignment' => $assignment,
                'campaign' => $assignment->campaign,
                'required_posts' => (int) $assignment->required_posts,
                'published' => $publications->count(),
                'verified' => $verified->count(),
                'likes' => (int) $stats->sum('likes'),
                'comments' => (int) $stats->sum('comments'),
                'shares' => (int) $stats->sum('shares'),
                'saves' => (int) $stats->sum('saves'),
                'views' => (int) $stats->sum('views'),
                'reach' => (float) $stats->sum('reach'),
                'engagement' => (int) $stats->sum(fn (Stat $stat) => $stat->likes + $stat->comments + $stat->shares + $stat->saves),
                'engagement_rate' => $this->averageRate($stats->map(fn (Stat $stat) => (float) $stat->engagement_rate)),
            ];
        })->values();
    }

    /**
     * Totaux (et taux moyen) d'un ensemble de résultats par campagne.
     *
     * @param  Collection<int, array<string, mixed>>  $results
     * @return array<string, mixed>
     */
    public function totals(Collection $results): array
    {
        $totals = [];
        foreach (['required_posts', 'published', 'verified', 'likes', 'comments', 'shares', 'saves', 'views', 'engagement'] as $key) {
            $totals[$key] = (int) $results->sum($key);
        }
        $totals['reach'] = (float) $results->sum('reach');
        $totals['engagement_rate'] = $this->averageRate(
            $results->pluck('engagement_rate')->filter(fn ($rate) => $rate !== null)
        );

        return $totals;
    }

    /**
     * Séries Chart.js de l'évolution des abonnés par plateforme sur les N derniers mois.
     * Valeur du mois = dernier relevé du mois ; sinon dernière valeur connue ; null avant le premier relevé.
     *
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    public function followerChart(User $influencer, int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $keys = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $keys[] = $month->format('Y-m');
            $labels[] = $month->format('m/Y');
        }

        $accounts = $influencer->socialAccounts()->get();
        $snapshots = FollowerSnapshot::whereIn('social_account_id', $accounts->pluck('id'))
            ->orderBy('collected_at')
            ->orderBy('id')
            ->get()
            ->groupBy('social_account_id');

        $datasets = [];
        foreach ($accounts as $account) {
            /** @var Collection<int, FollowerSnapshot>|null $series */
            $series = $snapshots->get($account->id);
            if ($series === null || $series->isEmpty()) {
                continue;
            }

            $lastOfMonth = [];
            foreach ($series as $snapshot) {
                $lastOfMonth[$snapshot->collected_at->format('Y-m')] = $snapshot->followers_count;
            }

            $carry = null;
            foreach ($lastOfMonth as $month => $count) {
                if ($month < $keys[0]) {
                    $carry = $count;
                }
            }

            $data = [];
            foreach ($keys as $month) {
                if (array_key_exists($month, $lastOfMonth)) {
                    $carry = $lastOfMonth[$month];
                }
                $data[] = $carry;
            }

            $datasets[] = $this->dataset($account, $data);
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * @param  array<int, ?int>  $data
     * @return array<string, mixed>
     */
    private function dataset(SocialAccount $account, array $data): array
    {
        $color = $account->config['color'];

        return [
            'label' => $account->label,
            'data' => $data,
            'borderColor' => $color,
            'backgroundColor' => $color,
            'spanGaps' => true,
            'tension' => 0.2,
        ];
    }

    /**
     * Moyenne des taux strictement positifs (un taux nul correspond à une donnée absente), ou null.
     */
    private function averageRate(Collection $rates): ?float
    {
        $known = $rates->filter(fn ($rate) => $rate !== null && $rate > 0);

        return $known->isEmpty() ? null : round((float) $known->avg(), 2);
    }
}
