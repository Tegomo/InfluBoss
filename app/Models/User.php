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
        'address',
        'city',
        'password',
        'role',
        'profile_photo',
        'bio',
        'categories',
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
            'social_links' => 'json', // legacy, lecture seule : les liens vivent dans social_accounts
            'categories' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Comptes sociaux de l'influenceur (un par plateforme).
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class)->orderBy('platform');
    }

    /**
     * Contrats de l'influenceur, du plus récent au plus ancien.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'influencer_id')->orderByDesc('end_date');
    }

    /**
     * Contrat actif le plus récent de l'influenceur.
     */
    public function currentContract()
    {
        return $this->hasOne(Contract::class, 'influencer_id')
            ->where('status', Contract::STATUS_ACTIVE)
            ->latestOfMany('end_date');
    }

    /**
     * Paiements de l'influenceur, par échéance.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'influencer_id')->orderBy('due_date')->orderBy('id');
    }

    /**
     * Documents légaux de l'influenceur (pièce d'identité, RIB…).
     */
    public function legalDocuments()
    {
        return $this->hasMany(LegalDocument::class, 'influencer_id')->orderBy('type')->orderByDesc('created_at');
    }

    /**
     * Historique des abonnés, toutes plateformes confondues.
     */
    public function followerSnapshots()
    {
        return $this->hasManyThrough(FollowerSnapshot::class, SocialAccount::class);
    }

    /**
     * Somme des derniers nombres d'abonnés connus sur toutes les plateformes.
     */
    public function getTotalFollowersAttribute(): int
    {
        return (int) $this->socialAccounts->sum('followers_count');
    }

    /**
     * Libellés des catégories (niches) de l'influenceur, clés inconnues ignorées.
     *
     * @return array<int, string>
     */
    public function getCategoryLabelsAttribute(): array
    {
        $labels = config('influboss.categories', []);

        return array_values(array_filter(array_map(
            fn ($key) => $labels[$key] ?? null,
            $this->categories ?? []
        )));
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
     * Determine if user is the read-only client (advertiser)
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
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
        // Récupérer les IDs des influenceurs supervisés par ce superviseur
        $influencerIds = \Illuminate\Support\Facades\DB::table('supervisor_influencer')
            ->where('supervisor_id', $this->id)
            ->pluck('influencer_id')
            ->toArray();

        if (empty($influencerIds)) {
            // Si aucun influenceur n'est assigné, retourner une collection vide
            return Campaign::whereRaw('1 = 0');
        }

        // Récupérer les campagnes associées à ces influenceurs via la table assignments
        return Campaign::whereHas('assignments', function ($query) use ($influencerIds) {
            $query->whereIn('influencer_id', $influencerIds);
        })->distinct();
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
