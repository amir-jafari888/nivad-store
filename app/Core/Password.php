<?php

declare(strict_types=1);

final class Password
{
    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/security.php';
        }

        return self::$config['password'];
    }

    public static function hash(string $password): string
    {
        if ($password === '') {
            throw new InvalidArgumentException(
                'Password cannot be empty.'
            );
        }

        $config = self::config();

        $hash = password_hash(
            $password,
            $config['algorithm'],
            $config['options']
        );

        if ($hash === false) {
            throw new RuntimeException(
                'Failed to hash password.'
            );
        }

        return $hash;
    }

    public static function verify(
        string $password,
        string $hash
    ): bool {
        if ($password === '' || $hash === '') {
            return false;
        }

        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        $config = self::config();

        return password_needs_rehash(
            $hash,
            $config['algorithm'],
            $config['options']
        );
    }
}
