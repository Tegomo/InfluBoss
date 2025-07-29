<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($stat) {
            // Définir collected_at à la date actuelle s'il n'est pas déjà défini
            if (!$stat->collected_at) {
                $stat->collected_at = now();
            }
        });
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'publication_id',
        'collected_at',
        'likes',
        'comments',
        'shares',
        'saves',
        'views',
        'clicks',
        'engagement_rate',
        'reach',
        'impressions',
        'demographics',
        'raw_data'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'collected_at' => 'datetime',
        'likes' => 'integer',
        'comments' => 'integer',
        'shares' => 'integer',
        'saves' => 'integer',
        'views' => 'integer',
        'clicks' => 'integer',
        'engagement_rate' => 'decimal:4',
        'reach' => 'decimal:2',
        'impressions' => 'decimal:2',
        'demographics' => 'json',
        'raw_data' => 'json',
    ];
    
    /**
     * Get the publication that these stats belong to
     */
    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
    
    /**
     * Get the campaign this stat is associated with (through publication -> assignment -> campaign)
     */
    public function campaign()
    {
        return $this->publication->assignment->campaign;
    }
    
    /**
     * Get the influencer this stat is associated with (through publication)
     */
    public function influencer()
    {
        return $this->publication->influencer;
    }
    
    /**
     * Get total engagement (likes + comments + shares + saves)
     */
    public function getTotalEngagementAttribute()
    {
        return $this->likes + $this->comments + $this->shares + $this->saves;
    }
    
    /**
     * Calculate engagement rate if not already set
     */
    public function getEngagementRateAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        if ($this->reach && $this->reach > 0) {
            return ($this->getTotalEngagementAttribute() / $this->reach) * 100;
        }
        
        return 0;
    }
    
    /**
     * Scope for stats collected today
     */
    public function scopeCollectedToday($query)
    {
        return $query->whereDate('collected_at', now());
    }
    
    /**
     * Scope for stats from the last week
     */
    public function scopeLastWeek($query)
    {
        return $query->whereBetween('collected_at', [now()->subDays(7), now()]);
    }
    
    /**
     * Scope for stats from the last month
     */
    public function scopeLastMonth($query)
    {
        return $query->whereBetween('collected_at', [now()->subDays(30), now()]);
    }
}
