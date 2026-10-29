<?php

declare(strict_types=1);

/**
 * Per-request correlation metadata without persistence.
 * Incoming request IDs are accepted only if they match a strict safe format.
 */
final class AdminRequestContext
{
    private static ?string $requestId = null;

    public static function id(): string
    {
        if (self::$requestId !== null) {
            return self::$requestId;
        }

        $incoming = Request::header('X-Request-ID', '');
        if (
            is_string($incoming) &&
            preg_match('/^[A-Za-z0-9._-]{8,64}$/', $incoming) === 1
        ) {
            self::$requestId = $incoming;
        } else {
            self::$requestId = bin2hex(random_bytes(16));
        }

        return self::$requestId;
    }

    public static function publishResponseHeader(): void
    {
        Response::header('X-Request-ID', self::id());
    }

    public static function resetForTests(): void
    {
        self::$requestId = null;
    }
}
