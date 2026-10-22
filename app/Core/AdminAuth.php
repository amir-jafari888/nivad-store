<?php

declare(strict_types=1);

final class AdminAuth
{
    private const USER_KEY =
    '_admin_auth_user_id';

    private const LOGIN_AT_KEY =
    '_admin_auth_login_at';

    private static function sessionTokenHash(): string
    {
        Session::start();

        return hash(
            'sha256',
            Session::id()
        );
    }

    public static function login(
        int $adminUserId
    ): void {
        if ($adminUserId < 1) {
            throw new InvalidArgumentException(
                'Invalid admin user ID.'
            );
        }

        Session::start();

        self::regenerateSession();

        self::initializeLoginSession(
            $adminUserId
        );

        $config =
            require __DIR__ .
            '/../../config/security.php';

        $lifetime = (int)(
            $config['session']['lifetime']
            ?? 7200
        );

        $sessions =
            new AdminSessionRepository();

        $sessions->create(
            $adminUserId,
            self::sessionTokenHash(),
            Security::clientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $lifetime
        );
    }

    public static function regenerateSession(): void
    {
        Session::start();

        Session::regenerate(true);
    }

    private static function initializeLoginSession(
        int $adminUserId
    ): void {
        Session::set(
            self::USER_KEY,
            $adminUserId
        );

        Session::set(
            self::LOGIN_AT_KEY,
            time()
        );
    }

    public static function logout(): void
    {
        Session::start();

        $sessionTokenHash =
            self::sessionTokenHash();

        if ($sessionTokenHash !== '') {
            $sessions =
                new AdminSessionRepository();

            $sessions->revokeByTokenHash(
                $sessionTokenHash
            );
        }

        self::clearLoginSession();

        Session::regenerate(true);
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function id(): ?int
    {
        Session::start();

        $adminUserId =
            Session::get(
                self::USER_KEY
            );

        if (
            !is_int($adminUserId) ||
            $adminUserId < 1
        ) {
            return null;
        }

        return $adminUserId;
    }

    public static function loginAt(): ?int
    {
        Session::start();

        $loginAt =
            Session::get(
                self::LOGIN_AT_KEY
            );

        if (
            !is_int($loginAt) ||
            $loginAt < 1
        ) {
            return null;
        }

        return $loginAt;
    }

    public static function session(): array
    {
        $adminUserId =
            self::id();

        return [
            'authenticated' =>
            $adminUserId !== null
                && self::hasLoginTimestamp()
                && !self::expired(),

            'admin_user_id' =>
            $adminUserId,

            'login_at' =>
            self::loginAt(),

            'expired' =>
            $adminUserId !== null
                && self::expired(),
        ];
    }

    public static function refresh(): void
    {
        if (!self::check()) {
            return;
        }

        Session::regenerate(true);
    }

    public static function expired(): bool
    {
        $loginAt =
            self::loginAt();

        if ($loginAt === null) {
            return true;
        }

        $config =
            require __DIR__ .
            '/../../config/security.php';

        $lifetime = (int)(
            $config['session']['lifetime']
            ?? 7200
        );

        if ($lifetime < 1) {
            return false;
        }

        return (
            time() - $loginAt
        ) >= $lifetime;
    }

    public static function valid(): bool
    {
        if (!self::check()) {
            return false;
        }

        if (self::expired()) {
            self::logout();

            return false;
        }

        return true;
    }

    public static function hasLoginTimestamp(): bool
    {
        return self::loginAt() !== null;
    }

    private static function clearLoginSession(): void
    {
        Session::remove(
            self::USER_KEY
        );

        Session::remove(
            self::LOGIN_AT_KEY
        );
    }

    public static function authenticated(): bool
    {
        return self::valid();
    }
}
