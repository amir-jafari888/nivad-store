<?php

declare(strict_types=1);

final class RateLimitMiddleware
{
    public static function check(
        string $key,
        int $maxAttempts,
        int $windowSeconds
    ): bool {
        if ($key === '') {
            throw new InvalidArgumentException('Rate limit key cannot be empty.');
        }

        if ($maxAttempts < 1 || $windowSeconds < 1) {
            throw new InvalidArgumentException('Invalid rate limit configuration.');
        }

        Session::start();

        $sessionKey = '_rate_limit_' . hash('sha256', $key);
        $now = time();

        $data = Session::get($sessionKey, [
            'count' => 0,
            'started_at' => $now,
        ]);

        if (
            !is_array($data) ||
            !isset($data['count'], $data['started_at'])
        ) {
            $data = [
                'count' => 0,
                'started_at' => $now,
            ];
        }

        if (($now - (int) $data['started_at']) >= $windowSeconds) {
            $data = [
                'count' => 0,
                'started_at' => $now,
            ];
        }

        if ((int) $data['count'] >= $maxAttempts) {
            Session::set($sessionKey, $data);
            return false;
        }

        $data['count']++;

        Session::set($sessionKey, $data);

        return true;
    }

    public static function reset(string $key): void
    {
        if ($key === '') {
            return;
        }

        Session::start();

        $sessionKey = '_rate_limit_' . hash('sha256', $key);

        Session::remove($sessionKey);
    }
}
