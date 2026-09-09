<?php

namespace App\Services\Auth;

use App\Models\Customer;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Issues and verifies the customer session token, via
 * php-open-source-saver/jwt-auth.
 *
 * Deliberately narrow: a token says which customer is calling and nothing
 * else. The admin panel has its own session guard and its own permissions,
 * so there is no role or permission claim here to keep in sync.
 */
class JwtService
{
    public const TYPE_CUSTOMER = 'customer';

    /** Thrown when a token is structurally fine but past its expiry. */
    public const FAILURE_EXPIRED = 'expired';

    /** Thrown for anything else: bad signature, wrong issuer, malformed. */
    public const FAILURE_INVALID = 'invalid';

    public function issueForCustomer(Customer $customer): string
    {
        return JWTAuth::fromUser($customer);
    }

    /**
     * Returns the decoded claims, or throws JwtVerificationException.
     *
     * @return array{sub: string, type: string, iat: int, exp: int}
     */
    public function verify(string $token): array
    {
        try {
            $claims = JWTAuth::setToken($token)->getPayload()->toArray();
        } catch (TokenExpiredException) {
            throw new JwtVerificationException(self::FAILURE_EXPIRED, 'Session has expired.');
        } catch (JWTException) {
            // Intentionally opaque: distinguishing "bad signature" from
            // "malformed" tells an attacker which half of a forgery worked.
            throw new JwtVerificationException(self::FAILURE_INVALID, 'Session token is not valid.');
        }

        if (($claims['type'] ?? null) !== self::TYPE_CUSTOMER) {
            throw new JwtVerificationException(self::FAILURE_INVALID, 'Session token is not valid.');
        }

        if (!isset($claims['sub']) || !ctype_digit((string) $claims['sub'])) {
            throw new JwtVerificationException(self::FAILURE_INVALID, 'Session token is not valid.');
        }

        return $claims;
    }

    /** Pulls the bearer token out of an Authorization header, or null. */
    public function bearerFrom(?string $authorizationHeader): ?string
    {
        if ($authorizationHeader === null) {
            return null;
        }

        if (!preg_match('/^Bearer\s+(\S+)$/i', trim($authorizationHeader), $matches)) {
            return null;
        }

        return $matches[1];
    }
}
