<?php

namespace App\Services\Payment;

/**
 * A payment request that is wrong on its face — unknown plan, inactive plan,
 * or a per-agreement request that never named an OTP mode.
 */
class PaymentException extends \RuntimeException
{
}
