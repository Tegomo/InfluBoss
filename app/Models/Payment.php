<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Paiement prévu ou effectué à un influenceur.
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    /** Disque privé des justificatifs de paiement. */
    public const DISK = 'local';

    protected $fillable = [
        'influencer_id',
        'contract_id',
        'assignment_id',
        'label',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'method',
        'reference',
        'receipt_path',
        'receipt_original_name',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * En attente avec une échéance dépassée.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->pending()->whereNotNull('due_date')->whereDate('due_date', '<', now()->toDateString());
    }

    /**
     * Admins : tout ; influenceur : les siens ; autres rôles (superviseurs compris) : rien.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'admin', 'super_admin' => $query,
            'influencer' => $query->where('influencer_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * Sommes payées, en attente et en retard d'une requête de paiements.
     *
     * @return array{paid: float, pending: float, overdue: float}
     */
    public static function totalsFor(Builder $query): array
    {
        return [
            'paid' => (float) (clone $query)->paid()->sum('amount'),
            'pending' => (float) (clone $query)->pending()->sum('amount'),
            'overdue' => (float) (clone $query)->overdue()->sum('amount'),
        ];
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->due_date !== null
            && $this->due_date->lt(now()->startOfDay());
    }

    public function getDisplayStatusLabelAttribute(): string
    {
        return $this->is_overdue
            ? 'En retard'
            : (config("influboss.payment_statuses.{$this->status}.label") ?? ucfirst((string) $this->status));
    }

    public function getDisplayStatusBadgeAttribute(): string
    {
        return $this->is_overdue
            ? 'danger'
            : (config("influboss.payment_statuses.{$this->status}.badge") ?? 'secondary');
    }

    public function getMethodLabelAttribute(): ?string
    {
        return $this->method ? (config("influboss.payment_methods.{$this->method}") ?? ucfirst($this->method)) : null;
    }
}
