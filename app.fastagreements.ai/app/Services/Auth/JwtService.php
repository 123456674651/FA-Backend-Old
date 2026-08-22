<?php

namespace App\Services\Auth;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use Throwable;

/**
 * Issues and verifies the customer session token.
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

    public function issueForCustomer(int $customerId): string
    {
        $now = time();

        return JWT::encode(
            [
                'iss' => $this->issuer(),
                'sub' => (string) $customerId,
                'type' => self::TYPE_CUSTOMER,
                'iat' => $now,
                'exp' => $now + $this->ttl(),
            ],
            $this->secret(),
            $this->algo(),
        );
    }

    /**
     * Returns the decoded claims, or throws JwtVerificationException.
     *
     * The algorithm is pinned to the one we sign with. Passing the configured
     * algorithm rather than accepting the token's own `alg` header is what
     * stops an algorithm-confusion attack, where a caller re-signs a token
     * with `alg: none` or swaps HS256 for RS256 to have their own key checked.
     *
     * @return array{sub: string, type: string, iat: int, exp: int}
     */
    public function verify(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret(), $this->algo()));
        } catch (ExpiredException) {
            throw new JwtVerificationException(self::FAILURE_EXPIRED, 'Session has expired.');
        } catch (Throwable) {
            // Intentionally opaque: distinguishing "bad signature" from
            // "malformed" tells an attacker which half of a forgery worked.
            throw new JwtVerificationException(self::FAILURE_INVALID, 'Session token is not valid.');
        }

        $claims = (array) $decoded;

        // php-jwt validates the signature and the time claims, but not these.
        if (($claims['iss'] ?? null) !== $this->issuer()) {
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

    private function secret(): string
    {
        $secret = config('apiauth.jwt.secret');

        // Failing loudly beats signing with an empty string, which would make
        // every token forgeable by anyone who noticed.
        if (!is_string($secret) || strlen($secret) < 32) {
            throw new RuntimeException(
                'JWT_SECRET is missing or shorter than 32 characters. Set it in .env before using the mobile API.'
            );
        }

        return $secret;
    }

    private function algo(): string
    {
        return (string) config('apiauth.jwt.algo', 'HS256');
    }

    private function issuer(): string
    {
        return (string) config('apiauth.jwt.issuer', 'fastagreements');
    }

    private function ttl(): int
    {
        return (int) config('apiauth.jwt.ttl', 60 * 60 * 24 * 30);
    }
}
