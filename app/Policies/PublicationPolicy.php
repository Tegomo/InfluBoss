<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any publications.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Tous les utilisateurs authentifiés peuvent voir la liste des publications
        // mais le filtrage sera fait dans le contrôleur selon le rôle
        return true;
    }

    /**
     * Determine whether the user can view the publication.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Publication $publication)
    {
        // Les admin et super admin peuvent voir toutes les publications
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Les superviseurs peuvent voir les publications des influenceurs qu'ils supervisent
        if ($user->role === 'supervisor') {
            // Vérifier si le superviseur supervise l'influenceur de cette publication via les assignments
            return \App\Models\Assignment::where('supervisor_id', $user->id)
                ->where('influencer_id', $publication->influencer_id)
                ->exists();
        }
        
        // Les influenceurs ne peuvent voir que leurs propres publications
        if ($user->role === 'influencer') {
            return $user->id === $publication->influencer_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create publications.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Les admins et super admins peuvent créer des publications
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Les superviseurs peuvent créer des publications pour les influenceurs qu'ils supervisent
        if ($user->role === 'supervisor') {
            return true; // Les vérifications spécifiques seront effectuées dans le contrôleur
        }
        
        // Les influenceurs peuvent créer leurs propres publications
        return $user->role === 'influencer';
    }

    /**
     * Determine whether the user can update the publication.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Publication $publication)
    {
        // Les admin et super admin peuvent modifier toutes les publications
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Les superviseurs peuvent modifier les publications des influenceurs qu'ils supervisent
        if ($user->role === 'supervisor') {
            // Vérifier si le superviseur supervise l'influenceur de cette publication via les assignments
            return \App\Models\Assignment::where('supervisor_id', $user->id)
                ->where('influencer_id', $publication->influencer_id)
                ->exists();
        }
        
        // L'influenceur ne peut modifier que ses propres publications
        if ($user->role === 'influencer') {
            return $user->id === $publication->influencer_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the publication.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Publication $publication)
    {
        // Les admin et super admin peuvent supprimer toutes les publications
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Les superviseurs peuvent supprimer les publications des influenceurs qu'ils supervisent
        if ($user->role === 'supervisor') {
            // Vérifier si le superviseur supervise l'influenceur de cette publication via les assignments
            return \App\Models\Assignment::where('supervisor_id', $user->id)
                ->where('influencer_id', $publication->influencer_id)
                ->exists();
        }
        
        // L'influenceur ne peut supprimer que ses propres publications
        if ($user->role === 'influencer') {
            return $user->id === $publication->influencer_id;
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can verify the publication.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function verify(User $user, Publication $publication)
    {
        // Les admin et super admin peuvent vérifier toutes les publications
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Les superviseurs peuvent vérifier les publications des influenceurs qu'ils supervisent
        if ($user->role === 'supervisor') {
            // Vérifier si le superviseur supervise l'influenceur de cette publication via les assignments
            return \App\Models\Assignment::where('supervisor_id', $user->id)
                ->where('influencer_id', $publication->influencer_id)
                ->exists();
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can refresh the publication stats.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function refreshStats(User $user, Publication $publication)
    {
        // Les admin et super admin peuvent rafraîchir les statistiques de toutes les publications
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Les superviseurs peuvent rafraîchir les statistiques des publications des influenceurs qu'ils supervisent
        if ($user->role === 'supervisor') {
            // Vérifier si le superviseur supervise l'influenceur de cette publication via les assignments
            return \App\Models\Assignment::where('supervisor_id', $user->id)
                ->where('influencer_id', $publication->influencer_id)
                ->exists();
        }
        
        // Les influenceurs peuvent rafraîchir les statistiques de leurs propres publications
        if ($user->role === 'influencer') {
            return $user->id === $publication->influencer_id;
        }
        
        return false;
    }
}
