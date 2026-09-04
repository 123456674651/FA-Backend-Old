<?php

namespace App\Console\Commands;

use App\Models\PaymentOrder;
use Illuminate\Console\Command;

/**
 * Closes out orders where the customer opened checkout and never came back.
 *
 * Only `created` orders are touched. A paid order past its TTL is a payment
 * that arrived late, not an abandoned one, and expiring it would revoke
 * something the customer paid for.
 */
class ExpireStalePaymentOrders extends Command
{
    protected $signature = 'payments:expire-stale';

    protected $description = 'Mark abandoned payment orders as expired.';

    public function handle(): int
    {
        $expired = PaymentOrder::query()
            ->where('status', PaymentOrder::STATUS_CREATED)
            ->where('expires_at', '<', now())
            ->update(['status' => PaymentOrder::STATUS_EXPIRED]);

        $this->info("Expired {$expired} stale payment order(s).");

        return self::SUCCESS;
    }
}
