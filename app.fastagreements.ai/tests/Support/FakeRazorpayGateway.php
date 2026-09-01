<?php

namespace Tests\Support;

use App\Services\Payment\PaymentGatewayException;
use App\Services\Payment\RazorpayGateway;

class FakeRazorpayGateway implements RazorpayGateway
{
    /** @var array<int, array{amount_paise: int, currency: string, receipt: string, notes: array}> */
    public array $createdOrders = [];

    public string $nextOrderId = 'order_TEST0000000001';

    public bool $throwOnCreate = false;

    public function createOrder(int $amountPaise, string $currency, string $receipt, array $notes = []): string
    {
        if ($this->throwOnCreate) {
            throw new PaymentGatewayException('Simulated gateway failure.');
        }

        $this->createdOrders[] = [
            'amount_paise' => $amountPaise,
            'currency' => $currency,
            'receipt' => $receipt,
            'notes' => $notes,
        ];

        return $this->nextOrderId;
    }
}
