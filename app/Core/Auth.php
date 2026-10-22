<?php

declare(strict_types=1);

final class Auth
{
    private const USER_KEY = '_auth_user_id';

    public static function login(int $userId): void
    {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        Session::start();
        Session::regenerate(true);
        Session::set(self::USER_KEY, $userId);

        $repository =
            new UserSessionRepository();

        $repository->create(
            $userId,
            Session::id(),
            Security::clientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            7200
        );
    }

    public static function logout(): void
    {
        Session::start();

        $sessionId = Session::id();

        if ($sessionId !== '') {
            $repository =
                new UserSessionRepository();

            $repository->revokeBySessionId(
                $sessionId
            );
        }

        Session::remove(
            self::USER_KEY
        );

        Session::regenerate(true);
    }

    public static function logoutOtherSessions(): int
    {
        $userId = self::id();

        if ($userId === null) {
            return 0;
        }

        $sessionService =
            new UserSessionService();

        return $sessionService
            ->revokeAllExceptCurrent(
                $userId,
                Session::id()
            );
    }

    public static function check(): bool
    {
        Session::start();

        $userId =
            Session::get(self::USER_KEY);

        return is_int($userId)
            && $userId > 0;
    }

    public static function id(): ?int
    {
        Session::start();

        $userId =
            Session::get(self::USER_KEY);

        if (
            !is_int($userId) ||
            $userId < 1
        ) {
            return null;
        }

        return $userId;
    }
}
