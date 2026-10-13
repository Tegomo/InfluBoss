<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

/**
 * Accès aux missions. Même règle que la liste des missions : un superviseur voit les missions dont il est le
 * superviseur nommé ou celles des influenceurs qui lui sont liés (pivot supervisor_influencer).
 */
class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            'super_admin', 'admin' => true,
            'supervisor' => $assignment->supervisor_id === $user->id
                || $user->influencers()->whereKey($assignment->influencer_id)->exists(),
            'influencer' => $assignment->influencer_id === $user->id,
            default => false,
        };
    }

    /**
     * Reprend la règle existante de AssignmentController::edit/update : admins et superviseur nommé.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        return in_array($user->role, ['super_admin', 'admin'])
            || ($user->role === 'supervisor' && $assignment->supervisor_id === $user->id);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }
}
