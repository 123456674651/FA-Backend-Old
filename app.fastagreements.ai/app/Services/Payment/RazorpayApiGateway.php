<?php

namespace App\Services\Payment;

use Razorpay\Api\Api;
use Throwable;

class RazorpayApiGateway implements RazorpayGateway
{
    public function __construct(
        private readonly string $keyId,
        private readonly string $keySecret,
    ) {
    }

    public function createOrder(int $amountPaise, string $currency, string $receipt, array $notes = []): string
    {
        try {
            $api = new Api($this->keyId, $this->keySecret);

            $order = $api->order->create([
                'amount' => $amountPaise,
                'currency' => $currency,
                'receipt' => $receipt,
                'notes' => $notes,
            ]);

            return (string) $order['id'];
        } catch (Throwable $e) {
            throw new PaymentGatewayException('Razorpay order creation failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
