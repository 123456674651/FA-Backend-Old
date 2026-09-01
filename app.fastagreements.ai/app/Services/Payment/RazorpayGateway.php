<?php

namespace App\Services\Payment;

interface RazorpayGateway
{
    /**
     * Registers an order with Razorpay and returns its id.
     */
    public function createOrder(int $amountPaise, string $currency, string $receipt, array $notes = []): string;
}
