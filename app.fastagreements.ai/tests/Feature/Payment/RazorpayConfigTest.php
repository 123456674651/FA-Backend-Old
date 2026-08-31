<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;

class RazorpayConfigTest extends TestCase
{
    public function test_razorpay_config_reads_from_env(): void
    {
        config([
            'services.razorpay.key_id' => 'rzp_test_abc',
            'services.razorpay.key_secret' => 'secret_abc',
            'services.razorpay.webhook_secret' => 'hook_abc',
        ]);

        $this->assertSame('rzp_test_abc', config('services.razorpay.key_id'));
        $this->assertSame('secret_abc', config('services.razorpay.key_secret'));
        $this->assertSame('hook_abc', config('services.razorpay.webhook_secret'));
    }

    public function test_razorpay_config_block_exists(): void
    {
        $this->assertIsArray(config('services.razorpay'));
        $this->assertArrayHasKey('key_id', config('services.razorpay'));
        $this->assertArrayHasKey('key_secret', config('services.razorpay'));
        $this->assertArrayHasKey('webhook_secret', config('services.razorpay'));
    }

    public function test_sdk_is_installed(): void
    {
        $this->assertTrue(class_exists(\Razorpay\Api\Api::class));
    }
}
