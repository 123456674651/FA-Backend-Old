<?php

namespace Tests\Support;

use App\Services\Auth\PhoneIdentityVerifier;
use App\Services\Auth\PhoneVerificationException;

/**
 * A provider that answers however the test needs, and records what it was asked.
 *
 * `tokensSeen` is how a test proves the token actually reached the verifier
 * rather than the controller trusting something in the request body.
 */
class FakePhoneIdentityVerifier implements PhoneIdentityVerifier
{
    /** @var array<int, string> */
    public array $tokensSeen = [];

    private ?string $failure = null;
    private string $uid = 'fake-uid';
    private string $phoneNumber = '+919876543210';

    public function willReturn(string $uid, string $phoneNumber): self
    {
        $this->uid = $uid;
        $this->phoneNumber = $phoneNumber;
        $this->failure = null;

        return $this;
    }

    public function willThrow(string $message): self
    {
        $this->failure = $message;

        return $this;
    }

    public function verify(string $token): array
    {
        $this->tokensSeen[] = $token;

        if ($this->failure !== null) {
            throw new PhoneVerificationException($this->failure);
        }

        return ['uid' => $this->uid, 'phone_number' => $this->phoneNumber];
    }
}
