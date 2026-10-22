<?php

declare(strict_types=1);

final class Security
{
    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/security.php';
        }

        return self::$config;
    }

    public static function randomToken(?int $bytes = null): string
    {
        $config = self::config();

        $bytes ??= $config['token']['bytes'];

        if ($bytes < 16) {
            throw new InvalidArgumentException(
                'Token size must be at least 16 bytes.'
            );
        }

        return bin2hex(random_bytes($bytes));
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function verifyToken(
        string $token,
        string $hash
    ): bool {
        return hash_equals(
            $hash,
            self::hashToken($token)
        );
    }

    public static function isHttps(): bool
    {
        return isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    }

    public static function enforceHttps(): void
    {
        $securityConfig = self::config();

        if (!(bool)($securityConfig['https_only'] ?? false)) {
            return;
        }

        $appConfig = require __DIR__ . '/../../config/app.php';

        if (($appConfig['env'] ?? 'production') === 'development') {
            return;
        }

        if (self::isHttps() || PHP_SAPI === 'cli') {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (preg_match('/[\r\n]/', $uri)) {
            http_response_code(400);
            exit('Invalid request URI.');
        }

        $host = trim(
            (string)($_SERVER['HTTP_HOST'] ?? '')
        );

        if (
            $host === '' ||
            !preg_match(
                '/^(?:[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?|\[[0-9a-f:]+\])(?::\d{1,5})?$/i',
                $host
            )
        ) {
            http_response_code(400);
            exit('Invalid application host.');
        }

        header(
            'Location: https://' . $host . $uri,
            true,
            308
        );

        exit;
    }

    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
