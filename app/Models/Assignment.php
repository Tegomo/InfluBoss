<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Note;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'influencer_id',
        'supervisor_id',
        'content_id',
        'status',
        'assigned_at',
        'accepted_at',
        'created_by',
        'deadline',
        'compensation',
        'description',
        'instructions',
        'required_posts',
        'platforms',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
        'compensation' => 'decimal:2',
        'platforms' => 'json',
    ];

    /**
     * Get the campaign that this assignment belongs to
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the influencer user for this assignment
     */
    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    /**
     * Get the supervisor user for this assignment
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the content associated with this assignment
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the publications associated with this assignment
     */
    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Get the notes associated with this assignment
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get completion rate attribute (percentage of required posts completed)
     */
    public function getCompletionRateAttribute()
    {
        $requiredPosts = $this->required_posts ?? 0;
        $completedPosts = $this->publications()->where('status', 'verified')->count();
        
        if ($requiredPosts > 0) {
            return min(100, round(($completedPosts / $requiredPosts) * 100));
        }
        
        return 0;
    }

    /**
     * Scope for pending assignments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for accepted assignments
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for in progress assignments
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed assignments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
