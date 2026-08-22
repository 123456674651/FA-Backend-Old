<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses builds older than MIN_APP_VERSION with a clear "update required".
 *
 * This is what makes the cutover to token auth survivable. Without it an old
 * build — which sends `customer_id` in the body and no Authorization header —
 * fails with a 401 on every screen and looks broken. With it, the app gets one
 * unambiguous signal it can turn into an update prompt.
 *
 * Disabled entirely while MIN_APP_VERSION is unset, so it stays out of the way
 * during development and manual testing.
 */
class EnsureMinimumAppVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        $minimum = config('apiauth.min_app_version');

        if (!is_string($minimum) || $minimum === '') {
            return $next($request);
        }

        $sent = $request->header('X-App-Version');

        // A build old enough not to know about this header is, by definition,
        // older than the first build that does.
        if (!is_string($sent) || $sent === '') {
            return $this->updateRequired($minimum);
        }

        if (version_compare($sent, $minimum, '<')) {
            return $this->updateRequired($minimum);
        }

        return $next($request);
    }

    private function updateRequired(string $minimum): Response
    {
        return ApiResponse::error(
            426,
            'UPDATE_REQUIRED',
            'Please update the app to continue.',
            ['minimum_version' => $minimum],
        );
    }
}
