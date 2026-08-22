<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Services\Auth\JwtService;
use App\Services\Auth\JwtVerificationException;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires a valid customer session token.
 *
 * Mirrors the Node service's `requireAuth`. The important consequence is not
 * the 401 — it is that every handler behind this middleware reads the caller
 * from `$request->user()` instead of a `customer_id` in the request body.
 * Identity is derived from a signed token, so changing one integer no longer
 * moves you to somebody else's data.
 */
class AuthenticateJwt
{
    public function __construct(private readonly JwtService $jwt)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->jwt->bearerFrom($request->header('Authorization'));

        if ($token === null) {
            return ApiResponse::error(401, 'UNAUTHENTICATED', 'Missing or malformed Authorization header.');
        }

        try {
            $claims = $this->jwt->verify($token);
        } catch (JwtVerificationException $e) {
            $code = $e->reason === JwtService::FAILURE_EXPIRED ? 'TOKEN_EXPIRED' : 'TOKEN_INVALID';

            return ApiResponse::error(401, $code, $e->getMessage());
        }

        $customer = Customer::find((int) $claims['sub']);

        if ($customer === null) {
            // The account was deleted after the token was issued.
            return ApiResponse::error(401, 'UNAUTHENTICATED', 'This account no longer exists.');
        }

        if (!$customer->is_active) {
            return ApiResponse::error(403, 'ACCOUNT_DISABLED', 'This account has been disabled.');
        }

        // Both are set so downstream code can use whichever reads better:
        // `$request->user()` in controllers, `auth('customer')->id()` in services.
        $request->setUserResolver(fn () => $customer);
        Auth::guard('customer')->setUser($customer);

        return $next($request);
    }
}
