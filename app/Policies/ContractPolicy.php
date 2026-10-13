<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

/**
 * Admins : gestion complète. Superviseurs liés et influenceur concerné : consultation et téléchargement.
 */
class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'supervisor', 'influencer']);
    }

    public function view(User $user, Contract $contract): bool
    {
        return match ($user->role) {
            'super_admin', 'admin' => true,
            'supervisor' => $user->influencers()->whereKey($contract->influencer_id)->exists(),
            'influencer' => $user->id === $contract->influencer_id,
            default => false,
        };
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
