<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Document légal d'un influenceur (pièce d'identité, RIB…) stocké sur le disque privé « local ».
 */
class LegalDocument extends Model
{
    use HasFactory;

    public const DISK = 'local';

    protected $fillable = [
        'influencer_id',
        'type',
        'label',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'expires_at',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'expires_at' => 'date',
    ];

    protected static function booted(): void
    {
        // Données sensibles : supprimer l'enregistrement supprime aussi le fichier.
        static::deleted(function (LegalDocument $document) {
            Storage::disk(self::DISK)->delete($document->file_path);
        });
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return config("influboss.legal_document_types.{$this->type}") ?? ucfirst((string) $this->type);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(now()->startOfDay());
    }
}
