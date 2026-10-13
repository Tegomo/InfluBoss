<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Seuil d'alerte d'expiration (30, 15, 7 jours…) déjà traité pour un contrat.
 */
class ContractAlert extends Model
{
    protected $fillable = ['contract_id', 'days_before', 'sent_at'];

    protected $casts = [
        'days_before' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
