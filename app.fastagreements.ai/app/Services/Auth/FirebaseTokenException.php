<?php

namespace App\Services\Auth;

use RuntimeException;

/**
 * A Firebase ID token was rejected. Always surfaces as a 401/422 to the
 * client — never a 500 — because it describes what the caller sent, not a
 * fault on this server.
 */
class FirebaseTokenException extends RuntimeException
{
}
