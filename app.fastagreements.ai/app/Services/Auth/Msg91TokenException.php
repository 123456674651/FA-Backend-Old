<?php

namespace App\Services\Auth;

/**
 * An MSG91 widget access token was rejected. Renders as 401 via the
 * PhoneVerificationException handler in bootstrap/app.php.
 */
class Msg91TokenException extends PhoneVerificationException
{
}
