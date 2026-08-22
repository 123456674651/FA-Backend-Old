<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * One response shape for everything added by the JWT/Firebase work.
 *
 * The older endpoints in this app answer in several different shapes — some
 * with `'status' => true`, some with the string `'true'`, some with neither.
 * Rewriting all of them is a separate job; what this guarantees is that the
 * new auth surface is consistent, and that a client can always branch on
 * `status` and switch on `code`.
 */
class ApiResponse
{
    public static function ok(mixed $data = null, string $message = ''): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * @param  string  $code  A stable, machine-readable identifier. Clients branch
     *                        on this; `message` is for humans and may be reworded.
     */
    public static function error(int $httpStatus, string $code, string $message, mixed $extra = null): JsonResponse
    {
        $body = [
            'status' => false,
            'code' => $code,
            'message' => $message,
        ];

        if ($extra !== null) {
            $body['data'] = $extra;
        }

        return response()->json($body, $httpStatus);
    }
}
