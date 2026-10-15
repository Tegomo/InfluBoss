<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

/**
 * Contrats : réservés aux admins et super_admins (consultation, téléchargement et gestion).
 * Le client ne voit que la période du contrat en cours, sur la fiche ambassadeur (sans passer par cette policy).
 */
class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Contract $contract): bool
    {
        return $this->isAdmin($user);
    }

    public function download(User $user, Contract $contract): bool
    {
        return $this->view($user, $contract);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Contract $contract): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }
}
