<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SupervisorInfluencer extends Pivot
{
    /**
     * Indique si le modèle utilise des timestamps.
     *
     * @var bool
     */
    public $timestamps = true;
    
    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'supervisor_influencer';
    
    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'supervisor_id',
        'influencer_id',
    ];
    
    /**
     * Obtenir le superviseur associé à cette relation.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
    
    /**
     * Obtenir l'influenceur associé à cette relation.
     */
    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }
}
