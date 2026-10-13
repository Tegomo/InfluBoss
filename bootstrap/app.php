<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Compte client (annonceur) en lecture seule sur toute l'application.
        $middleware->web(append: \App\Http\Middleware\ReadOnlyClient::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
