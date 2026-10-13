<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Notes de mission : les admins gèrent toutes les notes ; l'auteur gère les siennes tant qu'il a encore
 * accès à la mission (AssignmentPolicy::view).
 */
class NotePolicy
{
    public function update(User $user, Note $note): bool
    {
        return $this->canManage($user, $note);
    }

    public function delete(User $user, Note $note): bool
    {
        return $this->canManage($user, $note);
    }

    private function canManage(User $user, Note $note): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return $note->user_id === $user->id
            && $note->assignment !== null
            && Gate::forUser($user)->allows('view', $note->assignment);
    }
}
