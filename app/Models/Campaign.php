<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'platforms',
        'target_audience',
        'budget',
        'objectives',
        'created_by',
        'updated_by',
        'brand_tone',
        'visual_guidelines',
        'required_hashtags',
        'required_mentions',
        'products',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'objectives' => 'json',
        'platforms' => 'json',
        'required_hashtags' => 'array',
        'required_mentions' => 'array',
    ];

    /**
     * Transforme une saisie libre (« Orange, #4G\n#Orange ») en liste dédoublonnée préfixée (#… ou @…).
     *
     * @return array<int, string>
     */
    public static function parseTags(?string $input, string $prefix): array
    {
        $tags = [];
        foreach (preg_split('/[\s,;]+/u', (string) $input, -1, PREG_SPLIT_NO_EMPTY) as $raw) {
            $tag = ltrim($raw, '#@');
            if ($tag !== '') {
                $tags[mb_strtolower($tag)] ??= $prefix.$tag;
            }
        }

        return array_values($tags);
    }

    /**
     * La campagne a-t-elle au moins un élément de charte de marque renseigné ?
     */
    public function hasBrandGuidelines(): bool
    {
        return filled($this->brand_tone) || filled($this->visual_guidelines) || filled($this->products)
            || ! empty($this->required_hashtags) || ! empty($this->required_mentions);
    }
    
    /**
     * Get the admin user who created the campaign
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the contents associated with this campaign
     */
    public function contents()
    {
        return $this->hasMany(Content::class);
    }
    
    /**
     * Get the assignments for this campaign
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    
    /**
     * Get the influencers assigned to this campaign
     */
    public function influencers()
    {
        return $this->belongsToMany(User::class, 'assignments', 'campaign_id', 'influencer_id')
                    ->wherePivot('status', '!=', 'declined');
    }
    
    /**
     * Get the supervisors assigned to this campaign
     */
    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'assignments', 'campaign_id', 'supervisor_id')
                    ->distinct();
    }
    
    /**
     * Get all publications associated with this campaign through assignments
     */
    public function publications()
    {
        return $this->hasManyThrough(
            Publication::class,
            Assignment::class,
            'campaign_id',
            'assignment_id',
            'id',
            'id'
        );
    }
    
    /**
     * Get total reach for this campaign
     */
    public function getTotalReachAttribute()
    {
        return $this->publications()
                    ->join('stats', 'publications.id', '=', 'stats.publication_id')
                    ->sum('stats.reach');
    }
    
    /**
     * Get total engagement for this campaign
     */
    public function getTotalEngagementAttribute()
    {
        return $this->publications()
                    ->join('stats', 'publications.id', '=', 'stats.publication_id')
                    ->sum(DB::raw('stats.likes + stats.comments + stats.shares + stats.saves'));
    }
    
    /**
     * Scope active campaigns
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope upcoming campaigns
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }
    
    /**
     * Scope completed campaigns
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
