<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any campaigns.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Tous les utilisateurs authentifiés peuvent voir la liste des campagnes
        // mais le filtrage sera fait dans le contrôleur selon le rôle
        return true;
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Campaign $campaign)
    {
        // Les admin, super admin et superviseurs peuvent voir toutes les campagnes
        if (in_array($user->role, ['admin', 'super_admin', 'supervisor', 'client'])) {
            return true;
        }
        
        // Les influenceurs peuvent voir les campagnes auxquelles ils sont assignés
        if ($user->role === 'influencer') {
            return $user->assignments()->where('campaign_id', $campaign->id)->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create campaigns.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les admin et super admin peuvent créer des campagnes
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the campaign.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Campaign $campaign)
    {
        // Les admin et super admin peuvent modifier toutes les campagnes
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the campaign.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Campaign $campaign)
    {
        // Seuls les admin et super admin peuvent supprimer des campagnes
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view the campaign dashboard.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDashboard(User $user, Campaign $campaign)
    {
        // La même règle que pour la visualisation
        return $this->view($user, $campaign);
    }
}
