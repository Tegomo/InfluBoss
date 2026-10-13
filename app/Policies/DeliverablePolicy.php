<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Deliverable;
use App\Models\User;

/**
 * Livrables : gérés par les admins, le superviseur de la mission et les superviseurs liés à l'influenceur.
 */
class DeliverablePolicy
{
    public function create(User $user, Assignment $assignment): bool
    {
        return $this->canManage($user, $assignment);
    }

    public function update(User $user, Deliverable $deliverable): bool
    {
        return $this->canManage($user, $deliverable->assignment);
    }

    public function delete(User $user, Deliverable $deliverable): bool
    {
        return $this->canManage($user, $deliverable->assignment);
    }

    private function canManage(User $user, ?Assignment $assignment): bool
    {
        if ($assignment === null) {
            return false;
        }

        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return $user->role === 'supervisor' && (
            $assignment->supervisor_id === $user->id
            || $user->influencers()->whereKey($assignment->influencer_id)->exists()
        );
    }
}
