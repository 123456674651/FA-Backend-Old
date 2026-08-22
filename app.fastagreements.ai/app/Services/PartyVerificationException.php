<?php

namespace App\Services;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * A party/guarantor verification step could not be satisfied. Carries the HTTP
 * status and machine-readable code with it so callers can hand it straight to
 * the client without translating.
 */
class PartyVerificationException extends RuntimeException
{
    /**
     * `errorCode` rather than `code`: Exception already declares a non-readonly
     * $code, and redeclaring it readonly is a fatal error.
     */
    public function __construct(
        public readonly int $status,
        public readonly string $errorCode,
        string $message,
        public readonly ?array $extra = null,
    ) {
        parent::__construct($message);
    }

    public function toResponse(): JsonResponse
    {
        return ApiResponse::error($this->status, $this->errorCode, $this->getMessage(), $this->extra);
    }
}
