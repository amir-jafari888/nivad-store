<?php

declare(strict_types=1);

final class Session
{
    private static bool $started = false;

    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/security.php';
        }

        return self::$config['session'];
    }

    /**
     * Secure cookies are required for HTTPS deployments. Local XAMPP
     * development over HTTP is the only exception because browsers do not
     * send Secure cookies over plain HTTP.
     */
    private static function usesSecureCookie(array $config): bool
    {
        $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
        $host = preg_replace('/:\\d+$/', '', $host) ?? $host;

        $isLocalHost = in_array(
            $host,
            ['localhost', '127.0.0.1', '::1', '[::1]'],
            true
        );

        $isLocalHttp = $isLocalHost && !Security::isHttps();

        return (bool) ($config['secure'] ?? true) && !$isLocalHttp;
    }

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = self::config();

        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException('PHP sessions are disabled.');
        }

        $secureCookie = self::usesSecureCookie($config);

        session_name($config['name']);

        ini_set(
            'session.use_strict_mode',
            $config['use_strict_mode'] ? '1' : '0'
        );

        ini_set(
            'session.use_only_cookies',
            $config['use_only_cookies'] ? '1' : '0'
        );

        ini_set(
            'session.cookie_httponly',
            $config['httponly'] ? '1' : '0'
        );

        ini_set(
            'session.cookie_secure',
            $secureCookie ? '1' : '0'
        );

        ini_set(
            'session.cookie_samesite',
            $config['samesite']
        );

        ini_set(
            'session.gc_maxlifetime',
            (string) $config['lifetime']
        );

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secureCookie,
            'httponly' => $config['httponly'],
            'samesite' => $config['samesite'],
        ]);

        if (!session_start()) {
            throw new RuntimeException('Failed to start session.');
        }

        self::$started = true;
    }

    public static function regenerate(bool $deleteOldSession = true): void
    {
        self::ensureStarted();

        if (!session_regenerate_id($deleteOldSession)) {
            throw new RuntimeException(
                'Failed to regenerate session ID.'
            );
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();

        $_SESSION[$key] = $value;
    }

    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        self::ensureStarted();

        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();

        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();

        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        self::ensureStarted();

        $_SESSION = [];
    }

    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'] ?? '',
                    'secure' => (bool) $params['secure'],
                    'httponly' => (bool) $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        self::$started = false;
    }

    public static function id(): string
    {
        self::ensureStarted();

        return session_id();
    }

    private static function ensureStarted(): void
    {
        if (!self::$started && session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
    }
}
