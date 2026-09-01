<?php

namespace App\Services\Payment;

/**
 * Proves a payment really happened, without trusting the client that reports it.
 */
class PaymentVerificationService
{
    public function __construct(
        private readonly string $keySecret,
        private readonly string $webhookSecret,
    ) {
    }

    public function checkoutSignatureValid(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $this->keySecret);

        return $this->matches($expected, $signature);
    }

    public function webhookSignatureValid(string $rawBody, string $signature): bool
    {
        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        return $this->matches($expected, $signature);
    }

    private function matches(string $expected, string $provided): bool
    {
        if ($provided === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }
}
