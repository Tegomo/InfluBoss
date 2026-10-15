<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/**
 * Paiements (données financières) : réservés aux admins et super_admins.
 */
class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
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
