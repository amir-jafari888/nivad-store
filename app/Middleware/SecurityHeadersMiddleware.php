<?php

declare(strict_types=1);

final class SecurityHeadersMiddleware
{
    public static function send(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        header(
            'Content-Security-Policy: ' .
                "default-src 'self'; " .
                "base-uri 'self'; " .
                "form-action 'self'; " .
                "frame-ancestors 'self';"
        );

        if (
            isset($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        ) {
            header(
                'Strict-Transport-Security: ' .
                    'max-age=31536000; includeSubDomains'
            );
        }
    }
}
