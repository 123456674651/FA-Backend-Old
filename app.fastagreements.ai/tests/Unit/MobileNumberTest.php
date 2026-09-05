<?php

namespace Tests\Unit;

use App\Support\MobileNumber;
use PHPUnit\Framework\TestCase;

class MobileNumberTest extends TestCase
{
    public function test_strips_country_code_from_e164(): void
    {
        $this->assertSame('9876543210', MobileNumber::toStored('+919876543210'));
    }

    public function test_strips_non_digits(): void
    {
        $this->assertSame('9876543210', MobileNumber::toStored('+91 98765-43210'));
    }

    public function test_leaves_a_bare_ten_digit_number_alone(): void
    {
        $this->assertSame('9876543210', MobileNumber::toStored('9876543210'));
    }

    public function test_returns_short_input_unpadded_so_callers_can_reject_it(): void
    {
        $this->assertSame('12345', MobileNumber::toStored('12345'));
    }

    public function test_empty_input_yields_empty_string(): void
    {
        $this->assertSame('', MobileNumber::toStored(''));
    }
}
