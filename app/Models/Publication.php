<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Note;

class Publication extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignment_id',
        'content_id',
        'influencer_id',
        'platform',
        'post_url',
        'post_id',
        'post_content',
        'post_type',
        'published_at',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
    
    /**
     * Get the assignment that this publication belongs to
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
    
    /**
     * Get the content that was used for this publication
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }
    
    /**
     * Get the influencer who made this publication
     */
    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }
    
    /**
     * Get the user who verified this publication
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    
    /**
     * Get the stats for this publication
     */
    public function stats()
    {
        return $this->hasMany(Stat::class);
    }
    
    /**
     * Get latest stats for the publication
     */
    public function latestStats()
    {
        return $this->hasOne(Stat::class)->latest('collected_at');
    }
    
    /**
     * Get the campaign this publication belongs to (through assignment)
     */
    public function campaign()
    {
        return $this->hasOneThrough(Campaign::class, Assignment::class, 'id', 'id', 'assignment_id', 'campaign_id');
    }
    
    /**
     * Get the notes associated with this publication (through assignment)
     */
    public function notes()
    {
        return $this->hasManyThrough(Note::class, Assignment::class, 'id', 'assignment_id', 'assignment_id');
    }
    
    /**
     * Get total engagement (likes + comments + shares + saves)
     */
    public function getTotalEngagementAttribute()
    {
        if ($this->latestStats) {
            return $this->latestStats->likes + $this->latestStats->comments + 
                   $this->latestStats->shares + $this->latestStats->saves;
        }
        
        return 0;
    }
    
    /**
     * Scope for pending verification publications
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }
    
    /**
     * Scope for verified publications
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
    
    /**
     * Scope for rejected publications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
