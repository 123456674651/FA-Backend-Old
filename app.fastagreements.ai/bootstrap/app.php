<?php

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\EnsureMinimumAppVersion;
use App\Services\Auth\FirebaseTokenException;
use App\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.jwt' => AuthenticateJwt::class,
            'app.version' => EnsureMinimumAppVersion::class,
        ]);

        // Every mobile request passes the version gate. It is a no-op until
        // MIN_APP_VERSION is set, and it must run before auth so an old build
        // is told to update rather than told it is unauthenticated.
        $middleware->appendToGroup('api', EnsureMinimumAppVersion::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A rejected Firebase token describes what the caller sent, so it is a
        // 401 — not the 500 an uncaught RuntimeException would otherwise produce.
        $exceptions->render(function (FirebaseTokenException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(401, 'FIREBASE_TOKEN_INVALID', $e->getMessage());
            }

            return null;
        });
    })->create();
