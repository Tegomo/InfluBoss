<?php

namespace App\Policies;

use App\Models\LegalDocument;
use App\Models\User;

/**
 * Documents légaux (pièce d'identité, RIB…) : données sensibles. Seuls les admins les gèrent ;
 * l'influenceur peut consulter et télécharger les siens. Les superviseurs n'y ont pas accès.
 */
class LegalDocumentPolicy
{
    public function viewAny(User $user, User $influencer): bool
    {
        return $this->isAdmin($user) || $user->id === $influencer->id;
    }

    public function download(User $user, LegalDocument $document): bool
    {
        return $this->isAdmin($user) || $user->id === $document->influencer_id;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, LegalDocument $document): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }
}
