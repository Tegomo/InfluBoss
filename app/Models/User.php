<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'pseudo',
        'birthdate',
        'email',
        'phone',
        'city',
        'password',
        'role',
        'social_links',
        'profile_photo',
        'bio',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthdate' => 'date',
            'password' => 'hashed',
            'social_links' => 'json',
            'is_active' => 'boolean',
        ];
    }
    
    /**
     * Determine if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    
    /**
     * Determine if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Determine if user is a supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }
    
    /**
     * Determine if user is an influencer
     */
    public function isInfluencer(): bool
    {
        return $this->role === 'influencer';
    }
    
    /**
     * Get campaigns created by this user (admin)
     */
    public function createdCampaigns()
    {
        return $this->hasMany(Campaign::class, 'created_by');
    }
    
    /**
     * Get campaigns where this user is assigned as an influencer
     */
    public function assignedCampaigns()
    {
        return $this->hasManyThrough(
            Campaign::class,
            Assignment::class,
            'influencer_id',
            'id',
            'id',
            'campaign_id'
        );
    }
    
    /**
     * Get campaigns where this user is assigned as a supervisor
     */
    public function supervisedCampaigns()
    {
        return $this->hasManyThrough(
            Campaign::class, 
            Assignment::class,
            'supervisor_id',
            'id',
            'id',
            'campaign_id'
        )->distinct();
    }
    

    
    /**
     * Get assignments where this user is an influencer
     */
    public function influencerAssignments()
    {
        return $this->hasMany(Assignment::class, 'influencer_id');
    }
    
    /**
     * Get assignments where this user is a supervisor
     */
    public function supervisorAssignments()
    {
        return $this->hasMany(Assignment::class, 'supervisor_id');
    }
    
    /**
     * Alias method for backwards compatibility
     * Get influencers supervised by this user
     */
    public function supervisedInfluencers()
    {
        return $this->hasManyThrough(
            User::class,
            Assignment::class,
            'supervisor_id',
            'id',
            'id',
            'influencer_id'
        )->distinct();
    }
    
    /**
     * Get publications created by this user
     */
    public function publications()
    {
        // Utilise influencer_id au lieu de user_id car la table publications
        // n'a pas de colonne user_id
        return $this->hasMany(Publication::class, 'influencer_id');
    }
    
    /**
     * Get all assignments related to this user as influencer
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'influencer_id');
    }
    

    

    
    /**
     * Get supervisors of this influencer
     */
    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'supervisor_influencer', 'influencer_id', 'supervisor_id');
    }
    
    /**
     * Get influencers supervised by this user through the pivot table
     */
    public function influencers()
    {
        return $this->belongsToMany(User::class, 'supervisor_influencer', 'supervisor_id', 'influencer_id');
    }
}
