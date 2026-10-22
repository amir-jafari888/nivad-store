<?php

declare(strict_types=1);

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        Session::start();

        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = Security::randomToken();
            Session::set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function verify(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $sessionToken = Session::get(self::SESSION_KEY);

        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function regenerate(): string
    {
        Session::start();

        $token = Security::randomToken();

        Session::set(self::SESSION_KEY, $token);

        return $token;
    }

    public static function forget(): void
    {
        Session::start();

        Session::remove(self::SESSION_KEY);
    }
}
