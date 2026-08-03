<?php

namespace App\Exceptions\Api;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * The uniform error envelope for the /api/v1 surface:
 *
 *   {"error": {"code": "...", "message": "...", "details": {...}}}
 */
class ApiException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 400,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public static function quotaExceeded(string $feature, int $limit, int $used, ?string $message = null): self
    {
        return new self(
            'quota_exceeded',
            $message ?? __('You have reached your daily limit for this feature.'),
            403,
            [
                'feature' => $feature,
                'limit' => $limit,
                'used' => $used,
                'reset_at' => now()->addDay()->startOfDay()->toIso8601String(),
                'upgrade_url' => url('/checkout'),
            ],
        );
    }

    public static function premiumRequired(string $message, array $details = []): self
    {
        return new self('premium_required', $message, 403, $details + ['upgrade_url' => url('/checkout')]);
    }

    public static function notFound(string $message): self
    {
        return new self('not_found', $message, 404);
    }

    public static function conflict(string $message, array $details = []): self
    {
        return new self('session_conflict', $message, 409, $details);
    }

    public static function generationFailed(string $message): self
    {
        return new self('generation_failed', $message, 422);
    }

    public function toResponse(): JsonResponse
    {
        $payload = [
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
        ];

        if ($this->details !== []) {
            $payload['details'] = $this->details;
        }

        return response()->json(['error' => $payload], $this->status);
    }
}
