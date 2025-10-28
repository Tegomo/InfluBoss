<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any contents.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Tous les utilisateurs authentifiés peuvent voir la liste des contenus
        // mais le filtrage sera fait dans le contrôleur selon le rôle
        return true;
    }

    /**
     * Determine whether the user can view the content.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Content $content)
    {
        // Les admin, super admin et superviseurs peuvent voir tous les contenus
        if (in_array($user->role, ['admin', 'super_admin', 'supervisor'])) {
            return true;
        }
        
        // Les influenceurs peuvent voir les contenus des campagnes auxquelles ils sont assignés
        if ($user->role === 'influencer') {
            return $user->assignments()
                ->where('campaign_id', $content->campaign_id)
                ->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create contents.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les admin et super admin peuvent créer des contenus
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the content.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Content $content)
    {
        // Les admin et super admin peuvent modifier tous les contenus
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the content.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Content $content)
    {
        // Seuls les admin et super admin peuvent supprimer des contenus
        return in_array($user->role, ['admin', 'super_admin']);
    }
    
    /**
     * Determine whether the user can download the content file.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function download(User $user, Content $content)
    {
        // La même politique que pour la visualisation
        return $this->view($user, $content);
    }
}
