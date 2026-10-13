<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Notifications en base de l'utilisateur connecté (cloche de la barre de navigation).
 */
class NotificationController extends Controller
{
    /**
     * Marque la notification comme lue et ouvre sa cible.
     */
    public function read(string $id)
    {
        $notification = Auth::user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
