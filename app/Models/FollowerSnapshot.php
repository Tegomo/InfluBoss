<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un relevé du nombre d'abonnés d'un compte social à un instant donné.
 */
class FollowerSnapshot extends Model
{
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_API = 'api';

    public const SOURCES = [self::SOURCE_MANUAL, self::SOURCE_API];

    protected $fillable = [
        'social_account_id',
        'followers_count',
        'collected_at',
        'source',
        'recorded_by',
    ];

    protected $casts = [
        'followers_count' => 'integer',
        'collected_at' => 'datetime',
    ];

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
