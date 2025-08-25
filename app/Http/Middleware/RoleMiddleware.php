<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Vérifier si l'utilisateur a l'un des rôles autorisés
        if(in_array($user->role, $roles)) {
            return $next($request);
        }
        
        // Si le super_admin est dans les rôles autorisés, vérifier si l'utilisateur est super_admin
        if(in_array('admin', $roles) && $user->role === 'super_admin') {
            return $next($request);
        }
        
        return abort(403, 'Accès non autorisé.');
    }
}
