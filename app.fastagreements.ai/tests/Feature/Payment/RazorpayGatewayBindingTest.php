<?php

namespace Tests\Feature\Payment;

use App\Services\Payment\RazorpayApiGateway;
use App\Services\Payment\RazorpayGateway;
use Tests\TestCase;

class RazorpayGatewayBindingTest extends TestCase
{
    public function test_the_interface_resolves_to_the_api_gateway(): void
    {
        $this->assertInstanceOf(RazorpayApiGateway::class, app(RazorpayGateway::class));
    }

    public function test_the_fake_can_replace_it_in_tests(): void
    {
        $fake = new \Tests\Support\FakeRazorpayGateway();
        $this->app->instance(RazorpayGateway::class, $fake);

        $fake->nextOrderId = 'order_FAKE123';
        $id = app(RazorpayGateway::class)->createOrder(39900, 'INR', 'rcpt_1');

        $this->assertSame('order_FAKE123', $id);
        $this->assertSame(39900, $fake->createdOrders[0]['amount_paise']);
        $this->assertSame('rcpt_1', $fake->createdOrders[0]['receipt']);
    }
}
