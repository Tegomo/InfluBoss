<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/**
 * Paiements (données financières) : les admins gèrent tout ; l'influenceur consulte les siens.
 * Les superviseurs n'y ont pas accès.
 */
class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'influencer']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user) || ($user->role === 'influencer' && $user->id === $payment->influencer_id);
    }

    public function downloadReceipt(User $user, Payment $payment): bool
    {
        return $this->view($user, $payment);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
    }

    public function export(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }
}
