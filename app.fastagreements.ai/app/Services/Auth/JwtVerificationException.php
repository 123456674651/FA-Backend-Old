<?php

namespace App\Services\Auth;

use RuntimeException;

/**
 * A session token was rejected. `reason` is one of JwtService::FAILURE_*,
 * so the middleware can tell an expired session (re-authenticate) apart from
 * a bad one (something is wrong with the client).
 */
class JwtVerificationException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
