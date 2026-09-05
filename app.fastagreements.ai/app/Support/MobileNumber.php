<?php

namespace App\Support;

/**
 * Phone numbers as this app stores them: the ten digits the `customers.mobile`
 * column holds, so one person resolves to one row however their number was
 * written — E.164 from a provider, spaced and hyphenated from a form.
 *
 * Returns short input unchanged rather than padding or throwing: callers decide
 * what an unusable number means, and they all want to answer with their own
 * error rather than catch one.
 */
class MobileNumber
{
    public static function toStored(string $phoneNumber): string
    {
        $digits = preg_replace('/\D/', '', $phoneNumber) ?? '';

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }
}
