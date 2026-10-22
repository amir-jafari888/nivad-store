<?php

declare(strict_types=1);

final class Logger
{
    public static function info(
        string $message,
        array $context = []
    ): void {
        self::write('INFO', $message, $context);
    }

    public static function warning(
        string $message,
        array $context = []
    ): void {
        self::write('WARNING', $message, $context);
    }

    public static function error(
        string $message,
        array $context = []
    ): void {
        self::write('ERROR', $message, $context);
    }

    private static function write(
        string $level,
        string $message,
        array $context
    ): void {
        $safeContext = self::sanitizeContext($context);

        $entry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $safeContext,
        ];

        error_log(
            '[NIVAD] ' .
                json_encode(
                    $entry,
                    JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                )
        );
    }

    private static function sanitizeContext(array $context): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'csrf_token',
            'session_id',
            'authorization',
            'cookie',
            'secret',
            'api_key',
            'card_number',
            'cvv',
        ];

        foreach ($context as $key => $value) {
            if (
                in_array(
                    strtolower((string) $key),
                    $sensitiveKeys,
                    true
                )
            ) {
                $context[$key] = '[REDACTED]';
            }
        }

        return $context;
    }
}
