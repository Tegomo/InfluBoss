<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerte envoyée avant la fin d'un contrat actif (seuils 30, 15, 7 jours).
 * Envoi synchrone : l'hébergement mutualisé ne fait pas tourner de worker de file d'attente.
 */
class ContractExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public Contract $contract, public int $daysRemaining) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contract = $this->contract;
        $name = $contract->influencer?->name ?? 'un ambassadeur';

        return (new MailMessage)
            ->subject("Contrat de {$name} : expiration {$this->delayText()}")
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("Le contrat {$this->typeText()} de {$name} arrive à échéance le {$contract->end_date->format('d/m/Y')}, {$this->delayText()}.")
            ->line('Pensez à le renouveler ou à préparer sa clôture.')
            ->action('Voir le contrat', route('contracts.show', $contract))
            ->salutation('InfluBoss');
    }

    /**
     * Données stockées pour la cloche de notifications.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $name = $this->contract->influencer?->name ?? 'un ambassadeur';

        return [
            'contract_id' => $this->contract->id,
            'days_remaining' => $this->daysRemaining,
            'icon' => 'fas fa-file-signature',
            'title' => 'Contrat bientôt expiré',
            'message' => "Le contrat de {$name} expire {$this->delayText()}.",
            'url' => route('contracts.show', $this->contract),
        ];
    }

    private function delayText(): string
    {
        return match (true) {
            $this->daysRemaining <= 0 => "aujourd'hui",
            $this->daysRemaining === 1 => 'demain',
            default => "dans {$this->daysRemaining} jours",
        };
    }

    private function typeText(): string
    {
        return mb_strtolower($this->contract->type_label);
    }
}
