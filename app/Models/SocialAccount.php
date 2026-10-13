<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Compte d'un influenceur sur une plateforme sociale (un par plateforme),
 * avec le dernier nombre d'abonnés connu.
 */
class SocialAccount extends Model
{
    use HasFactory;

    /** Segments d'URL qui ne sont pas un identifiant de profil. */
    private const URL_PATH_PREFIXES = ['channel', 'c', 'user', 'in', 'company'];

    protected $fillable = [
        'user_id',
        'platform',
        'url',
        'handle',
        'followers_count',
        'last_synced_at',
    ];

    protected $casts = [
        'followers_count' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(FollowerSnapshot::class)->orderByDesc('collected_at')->orderByDesc('id');
    }

    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(FollowerSnapshot::class)->latestOfMany('collected_at');
    }

    public function scopePlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /**
     * Enregistre un relevé d'abonnés et met à jour le dernier nombre connu.
     * Point d'entrée unique pour la saisie manuelle et les synchronisations API.
     */
    public function recordFollowers(
        int $count,
        string $source = FollowerSnapshot::SOURCE_MANUAL,
        ?int $recordedBy = null,
        ?DateTimeInterface $collectedAt = null
    ): FollowerSnapshot {
        if (! in_array($source, FollowerSnapshot::SOURCES, true)) {
            throw new InvalidArgumentException("Source de relevé inconnue : {$source}");
        }
        if ($count < 0) {
            throw new InvalidArgumentException('Le nombre d\'abonnés ne peut pas être négatif.');
        }

        $collectedAt ??= now();

        return DB::transaction(function () use ($count, $source, $recordedBy, $collectedAt) {
            $snapshot = $this->snapshots()->create([
                'followers_count' => $count,
                'collected_at' => $collectedAt,
                'source' => $source,
                'recorded_by' => $recordedBy,
            ]);

            $this->forceFill([
                'followers_count' => $count,
                'last_synced_at' => $collectedAt,
            ])->save();

            return $snapshot;
        });
    }

    /**
     * Configuration d'affichage de la plateforme (libellé, icône, couleur, préfixe d'URL).
     */
    public function getConfigAttribute(): array
    {
        return config("influboss.platforms.{$this->platform}") ?? [
            'label' => ucfirst($this->platform),
            'icon' => 'fas fa-link',
            'color' => '#6c757d',
            'url_prefix' => '',
        ];
    }

    public function getLabelAttribute(): string
    {
        return $this->config['label'];
    }

    /**
     * Déduit l'URL et le handle à partir d'une saisie libre (URL complète ou @handle).
     *
     * @return array{url: string, handle: ?string}
     */
    public static function parseInput(string $platform, string $value): array
    {
        $value = trim($value);

        if (preg_match('#^https?://#i', $value)) {
            return ['url' => $value, 'handle' => self::handleFromUrl($platform, $value)];
        }

        $handle = ltrim($value, '@');
        $prefix = config("influboss.platforms.{$platform}.url_prefix", '');

        return ['url' => $prefix.$handle, 'handle' => $handle !== '' ? $handle : null];
    }

    /**
     * Extrait l'identifiant de profil d'une URL de plateforme, ou null s'il n'est pas identifiable.
     */
    public static function handleFromUrl(string $platform, string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));

        $candidates = array_values(array_filter(
            $segments,
            fn ($s) => ! in_array(strtolower($s), self::URL_PATH_PREFIXES, true)
        ));

        $last = $candidates[count($candidates) - 1] ?? null;
        if ($last === null || str_contains($last, '.php')) {
            return null;
        }

        $handle = ltrim(urldecode($last), '@');

        return $handle !== '' ? $handle : null;
    }
}
