<?php

namespace Tests\Unit;

use App\Services\Payment\PaymentVerificationService;
use PHPUnit\Framework\TestCase;

class PaymentVerificationServiceTest extends TestCase
{
    private const KEY_SECRET = 'key_secret_value';
    private const WEBHOOK_SECRET = 'webhook_secret_value';

    private function service(): PaymentVerificationService
    {
        return new PaymentVerificationService(self::KEY_SECRET, self::WEBHOOK_SECRET);
    }

    private function validCheckoutSignature(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId . '|' . $paymentId, self::KEY_SECRET);
    }

    public function test_a_genuine_checkout_signature_is_accepted(): void
    {
        $sig = $this->validCheckoutSignature('order_1', 'pay_1');

        $this->assertTrue($this->service()->checkoutSignatureValid('order_1', 'pay_1', $sig));
    }

    public function test_a_tampered_signature_is_rejected(): void
    {
        $sig = $this->validCheckoutSignature('order_1', 'pay_1');

        $this->assertFalse($this->service()->checkoutSignatureValid('order_1', 'pay_1', $sig . 'x'));
    }

    /** The signature binds the payment to one specific order. */
    public function test_a_signature_from_another_order_is_rejected(): void
    {
        $sig = $this->validCheckoutSignature('order_OTHER', 'pay_1');

        $this->assertFalse($this->service()->checkoutSignatureValid('order_1', 'pay_1', $sig));
    }

    public function test_a_signature_for_another_payment_is_rejected(): void
    {
        $sig = $this->validCheckoutSignature('order_1', 'pay_OTHER');

        $this->assertFalse($this->service()->checkoutSignatureValid('order_1', 'pay_1', $sig));
    }

    public function test_an_empty_signature_is_rejected(): void
    {
        $this->assertFalse($this->service()->checkoutSignatureValid('order_1', 'pay_1', ''));
    }

    /** A signature made with a different secret must never validate. */
    public function test_a_signature_from_a_foreign_secret_is_rejected(): void
    {
        $forged = hash_hmac('sha256', 'order_1|pay_1', 'attacker_secret');

        $this->assertFalse($this->service()->checkoutSignatureValid('order_1', 'pay_1', $forged));
    }

    public function test_a_genuine_webhook_signature_is_accepted(): void
    {
        $body = '{"event":"payment.captured"}';
        $sig = hash_hmac('sha256', $body, self::WEBHOOK_SECRET);

        $this->assertTrue($this->service()->webhookSignatureValid($body, $sig));
    }

    /**
     * The webhook is signed over the exact bytes sent. Re-encoding the parsed
     * array changes key order and whitespace and would fail to verify, so the
     * controller must pass the raw body.
     */
    public function test_a_webhook_signature_over_reencoded_json_is_rejected(): void
    {
        $body = '{"event":"payment.captured","id":"pay_1"}';
        $sig = hash_hmac('sha256', $body, self::WEBHOOK_SECRET);
        $reencoded = json_encode(json_decode($body, true));

        $this->assertFalse($this->service()->webhookSignatureValid($reencoded . ' ', $sig));
    }

    public function test_a_webhook_signed_with_the_checkout_secret_is_rejected(): void
    {
        $body = '{"event":"payment.captured"}';
        $wrong = hash_hmac('sha256', $body, self::KEY_SECRET);

        $this->assertFalse($this->service()->webhookSignatureValid($body, $wrong));
    }
}
