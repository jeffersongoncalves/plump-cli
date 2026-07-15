<?php

namespace App\Exceptions;

use RuntimeException;

class PlumbApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>|null  $response
     */
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly ?array $response = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromResponse(int $statusCode, array $body, ?string $retryAfterHeader = null): self
    {
        $message = $body['message']
            ?? $body['error']
            ?? self::defaultMessage($statusCode);

        $message = self::stringify($message);

        if (isset($body['stale_since'])) {
            $message .= ' Stale since '.self::stringify($body['stale_since']).'.';
        }

        $retryAfter = ctype_digit((string) $retryAfterHeader) ? (int) $retryAfterHeader : null;

        if ($retryAfter !== null) {
            $message .= " Retry after {$retryAfter}s.";
        }

        return new self($message, $statusCode, $body, $retryAfter);
    }

    private static function defaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            404 => 'Package not found.',
            422 => 'Scan results are stale.',
            429 => 'Rate limit exceeded (120 GET requests/minute).',
            503 => 'Daily scan budget exhausted.',
            default => 'Plumb API request failed.',
        };
    }

    /**
     * Flatten an API payload value (string or array) into a readable message.
     *
     * @param  mixed  $value
     */
    private static function stringify($value): string
    {
        if (is_array($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
