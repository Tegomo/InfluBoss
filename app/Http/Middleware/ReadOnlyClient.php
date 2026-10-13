<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le rôle « client » (annonceur) est en lecture seule : toute écriture et tout formulaire de création
 * ou de modification lui sont refusés, quel que soit le contrôleur. Garde-fou central qui complète les policies.
 */
class ReadOnlyClient
{
    /** Écritures autorisées malgré tout : se déconnecter, marquer ses notifications comme lues. */
    private const ALLOWED_WRITE_ROUTES = ['logout', 'notifications.read-all'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'client') {
            $routeName = (string) $request->route()?->getName();

            $isWrite = ! $request->isMethodSafe();
            $isForm = str_ends_with($routeName, '.create') || str_ends_with($routeName, '.edit');

            if (($isWrite && ! in_array($routeName, self::ALLOWED_WRITE_ROUTES, true)) || $isForm) {
                abort(403, 'Compte en lecture seule.');
            }
        }

        return $next($request);
    }
}
