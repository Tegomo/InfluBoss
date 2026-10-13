<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des utilisateurs
     */
    public function viewAny(User $user, ?string $role = null): bool
    {
        // Le client (annonceur) consulte uniquement la liste des ambassadeurs.
        if ($user->role === 'client') {
            return $role === null || $role === 'influencer';
        }

        return in_array($user->role, ['super_admin', 'admin', 'supervisor']);
    }

    /**
     * Détermine si l'utilisateur peut voir un utilisateur spécifique
     */
    public function view(User $user, User $model): bool
    {
        if ($user->role === 'client') {
            return $model->role === 'influencer' || $user->id === $model->id;
        }

        return in_array($user->role, ['super_admin', 'admin', 'supervisor']) || $user->id === $model->id;
    }

    /**
     * Détermine si l'utilisateur peut créer un nouveau utilisateur
     *
     * @param  string|null  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, $role = null)
    {
        // Si aucun rôle n'est spécifié, autoriser les admin et super_admin à créer des utilisateurs
        if ($role === null) {
            return in_array($user->role, ['super_admin', 'admin']);
        }

        // Seuls les super admin peuvent créer des admin
        if ($role === 'admin' && $user->role === 'super_admin') {
            return true;
        }

        // Les admin et super admin peuvent créer des superviseurs et des influenceurs
        if (in_array($role, ['supervisor', 'influencer']) && in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour le profil d'un autre utilisateur.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        // L'utilisateur peut mettre à jour son propre profil
        if ($user->id === $model->id) {
            return true;
        }

        // Les super admin peuvent tout mettre à jour
        if ($user->role === 'super_admin') {
            return true;
        }

        // Les admin peuvent mettre à jour tous les profils sauf les super admin
        if ($user->role === 'admin' && $model->role !== 'super_admin') {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un autre utilisateur.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        // On ne peut pas se supprimer soi-même par cette méthode
        if ($user->id === $model->id) {
            return false;
        }

        // Les super admin peuvent tout supprimer
        if ($user->role === 'super_admin') {
            return true;
        }

        // Les admin peuvent supprimer les superviseurs et les influenceurs
        if ($user->role === 'admin' && in_array($model->role, ['supervisor', 'influencer'])) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut saisir le nombre d'abonnés d'un influenceur :
     * les admins, et les superviseurs liés à cet influenceur (pivot supervisor_influencer).
     */
    public function updateFollowers(User $user, User $influencer): bool
    {
        if ($influencer->role !== 'influencer') {
            return false;
        }

        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return $user->role === 'supervisor'
            && $user->influencers()->whereKey($influencer->id)->exists();
    }
}
