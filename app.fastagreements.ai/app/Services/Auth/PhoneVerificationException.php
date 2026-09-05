<?php

namespace App\Services\Auth;

use RuntimeException;

/**
 * A phone-verification token was rejected. Always surfaces as a 401 — never a
 * 500 — because it describes what the caller sent, not a fault on this server.
 */
class PhoneVerificationException extends RuntimeException
{
}
