<?php

namespace App\Services\Auth;

/**
 * MSG91 could not be reached, or answered with a transport-level failure
 * (a non-2xx status, a connection error). This is our outage, not evidence
 * that the caller's token or code was wrong — it must not render the same
 * way as Msg91TokenException does, or an MSG91 outage looks to every
 * customer like their own code was rejected, with nothing in the logs to
 * say otherwise. Rendered as 503 VERIFICATION_UNAVAILABLE by bootstrap/app.php,
 * registered ahead of the general PhoneVerificationException handler.
 */
class Msg91UnavailableException extends PhoneVerificationException
{
}
