<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Contrat liant un influenceur (User de rôle « influencer ») à la marque.
 */
class Contract extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_NEGOTIATION = 'negotiation';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_TERMINATED = 'terminated';

    /** Disque privé où sont stockés les contrats scannés. */
    public const DISK = 'local';

    protected $fillable = [
        'influencer_id',
        'type',
        'start_date',
        'end_date',
        'amount',
        'payment_terms',
        'status',
        'file_path',
        'file_original_name',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(ContractAlert::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Contrats actifs dont la date de fin est comprise entre aujourd'hui et dans $days jours.
     */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->active()
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereDate('end_date', '<=', now()->addDays($days)->toDateString());
    }

    /**
     * Contrats visibles par un utilisateur : tout pour les admins, ceux des influenceurs liés pour un
     * superviseur, les siens pour un influenceur.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'admin', 'super_admin' => $query,
            'supervisor' => $query->whereIn('influencer_id', $user->influencers()->pluck('users.id')),
            'influencer' => $query->where('influencer_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return config("influboss.contract_types.{$this->type}") ?? ucfirst((string) $this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return config("influboss.contract_statuses.{$this->status}.label") ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return config("influboss.contract_statuses.{$this->status}.badge") ?? 'secondary';
    }

    /**
     * Jours restants avant la fin d'un contrat actif (négatif si dépassée), null sinon.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->status !== self::STATUS_ACTIVE || $this->end_date === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }

    /**
     * Contrat actif qui arrive à échéance dans la fenêtre d'alerte la plus large (30 jours par défaut).
     */
    public function isExpiringSoon(): bool
    {
        $days = $this->days_remaining;
        $window = max(config('influboss.contract_alert_days', [30]));

        return $days !== null && $days >= 0 && $days <= $window;
    }
}
