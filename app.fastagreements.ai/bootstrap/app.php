<?php

use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\EnsureMinimumAppVersion;
use App\Services\Auth\Msg91UnavailableException;
use App\Services\Auth\PhoneVerificationException;
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
        // Registered ahead of the general PhoneVerificationException handler
        // below — Laravel matches the first registered handler whose type
        // fits, and Msg91UnavailableException extends PhoneVerificationException,
        // so the order here is load-bearing. An MSG91 outage is our fault, not
        // evidence the caller's code was wrong: it must not render as the same
        // 401 a genuinely bad token gets, or every customer during an outage is
        // told their code is invalid.
        $exceptions->render(function (Msg91UnavailableException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(503, 'VERIFICATION_UNAVAILABLE', $e->getMessage());
            }

            return null;
        });

        // A rejected phone-verification token describes what the caller sent, so
        // it is a 401 — not the 500 an uncaught RuntimeException would otherwise
        // produce. Widened from FirebaseTokenException to the interface's base
        // exception so a future provider is covered without a second handler.
        $exceptions->render(function (PhoneVerificationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(401, 'PHONE_TOKEN_INVALID', $e->getMessage());
            }

            return null;
        });
    })->create();
