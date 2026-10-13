<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Livrable attendu d'une mission (ex. « 3 stories Instagram avant le 15/11 »).
 * Son statut suit automatiquement les publications qui lui sont rattachées.
 */
class Deliverable extends Model
{
    use HasFactory;

    public const STATUS_TODO = 'todo';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_VALIDATED = 'validated';

    protected $fillable = [
        'assignment_id',
        'type',
        'platform',
        'quantity',
        'due_date',
        'description',
        'status',
        'status_set_manually',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'due_date' => 'date',
        'status_set_manually' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_TODO,
        'quantity' => 1,
    ];

    protected static function booted(): void
    {
        // La mission exige autant de publications que la somme des quantités de ses livrables.
        static::saved(fn (Deliverable $deliverable) => $deliverable->syncAssignmentRequiredPosts());
        static::deleted(fn (Deliverable $deliverable) => $deliverable->syncAssignmentRequiredPosts());
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Ajoute submitted_count (publications non rejetées) et verified_count.
     */
    public function scopeWithProgress(Builder $query): Builder
    {
        return $query->withCount([
            'publications as submitted_count' => fn ($q) => $q->where('status', '!=', 'rejected'),
            'publications as verified_count' => fn ($q) => $q->where('status', 'verified'),
        ]);
    }

    /**
     * Recalcule le statut à partir des publications rattachées. Un statut saisi à la main est conservé
     * tant qu'aucune publication (non rejetée) n'est rattachée au livrable.
     */
    public function refreshStatus(): void
    {
        $submitted = $this->publications()->where('status', '!=', 'rejected')->count();
        $verified = $this->publications()->where('status', 'verified')->count();

        if ($submitted === 0 && $this->status_set_manually) {
            return;
        }

        $status = match (true) {
            $verified >= $this->quantity => self::STATUS_VALIDATED,
            $submitted >= $this->quantity => self::STATUS_DELIVERED,
            $submitted > 0 => self::STATUS_IN_PROGRESS,
            default => self::STATUS_TODO,
        };

        if ($status !== $this->status || $this->status_set_manually) {
            $this->status = $status;
            $this->status_set_manually = false;
            $this->saveQuietly();
        }
    }

    public function syncAssignmentRequiredPosts(): void
    {
        $assignment = Assignment::find($this->assignment_id);
        if (! $assignment) {
            return;
        }

        $total = (int) static::where('assignment_id', $assignment->id)->sum('quantity');
        if ($total > 0 && (int) $assignment->required_posts !== $total) {
            $assignment->required_posts = $total;
            $assignment->saveQuietly();
        }
    }

    public function getTypeLabelAttribute(): string
    {
        return config("influboss.deliverable_types.{$this->type}") ?? ucfirst((string) $this->type);
    }

    public function getPlatformLabelAttribute(): ?string
    {
        return $this->platform ? (config("influboss.platforms.{$this->platform}.label") ?? ucfirst($this->platform)) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return config("influboss.deliverable_statuses.{$this->status}.label") ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return config("influboss.deliverable_statuses.{$this->status}.badge") ?? 'secondary';
    }

    /**
     * Date limite dépassée alors que le livrable n'est ni livré ni validé.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->lt(now()->startOfDay())
            && ! in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_VALIDATED], true);
    }
}
