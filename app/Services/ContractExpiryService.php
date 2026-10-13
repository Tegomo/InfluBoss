<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractAlert;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Passe en « expiré » les contrats actifs échus et envoie les alertes d'expiration (30/15/7 jours).
 */
final class ContractExpiryService
{
    /**
     * @return array{expired: int, alerts: int}
     */
    public function run(): array
    {
        return [
            'expired' => $this->expirePastContracts(),
            'alerts' => $this->sendDueAlerts(),
        ];
    }

    /**
     * Les contrats actifs dont la date de fin est passée (hier ou avant) deviennent « expirés ».
     */
    public function expirePastContracts(): int
    {
        return Contract::active()
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => Contract::STATUS_EXPIRED, 'updated_at' => now()]);
    }

    /**
     * Pour chaque contrat actif dans la fenêtre d'alerte, envoie l'alerte du plus petit seuil atteint s'il
     * n'a pas déjà été traité, puis marque ce seuil et tous les seuils supérieurs comme traités.
     */
    public function sendDueAlerts(): int
    {
        $thresholds = collect(config('influboss.contract_alert_days', [30, 15, 7]))->map(fn ($d) => (int) $d)->sort()->values();
        $sent = 0;

        $contracts = Contract::expiringWithin($thresholds->max())->with(['influencer', 'alerts'])->get();

        foreach ($contracts as $contract) {
            $days = $contract->days_remaining;
            $threshold = $thresholds->first(fn ($t) => $days <= $t);

            if ($threshold === null || $contract->alerts->contains('days_before', $threshold)) {
                continue;
            }

            $recipients = $this->recipientsFor($contract);

            DB::transaction(function () use ($contract, $thresholds, $threshold) {
                foreach ($thresholds->filter(fn ($t) => $t >= $threshold) as $t) {
                    ContractAlert::firstOrCreate(
                        ['contract_id' => $contract->id, 'days_before' => $t],
                        ['sent_at' => now()]
                    );
                }
            });

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new ContractExpiringNotification($contract, $days));
            }
            $sent++;
        }

        return $sent;
    }

    /**
     * Admins et super_admins actifs, plus les superviseurs actifs liés à l'influenceur.
     *
     * @return Collection<int, User>
     */
    private function recipientsFor(Contract $contract): Collection
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])->where('is_active', true)->get();
        $supervisors = $contract->influencer
            ? $contract->influencer->supervisors()->where('is_active', true)->get()
            : collect();

        return $admins->concat($supervisors)->unique('id')->values();
    }
}
