<?php

declare(strict_types=1);

final class AdminLoginAttemptService
{
    private AdminLoginAttemptRepository $attempts;

    private const IDENTIFIER_WINDOW = 900;
    private const IP_WINDOW = 900;

    private const IDENTIFIER_LIMIT = 5;
    private const IP_LIMIT = 20;

    public function __construct(
        ?AdminLoginAttemptRepository $attempts = null
    ) {
        $this->attempts =
            $attempts ??
            new AdminLoginAttemptRepository();
    }

    public function isRateLimited(
        string $identifier,
        string $ipAddress
    ): bool {
        $identifier =
            trim(
                mb_strtolower($identifier)
            );

        if (
            $identifier === '' ||
            $ipAddress === ''
        ) {
            return false;
        }

        if (
            $this->attempts
            ->countRecentFailuresByIdentifier(
                $identifier,
                self::IDENTIFIER_WINDOW
            ) >= self::IDENTIFIER_LIMIT
        ) {
            return true;
        }

        return
            $this->attempts
            ->countRecentFailuresByIp(
                $ipAddress,
                self::IP_WINDOW
            ) >= self::IP_LIMIT;
    }

    public function recordFailure(
        ?int $adminUserId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent,
        string $reason = 'invalid_credentials'
    ): int {
        return
            $this->attempts
            ->record(
                $adminUserId,
                trim(
                    mb_strtolower($identifier)
                ),
                $ipAddress,
                $userAgent,
                'failed',
                $reason
            );
    }

    public function recordRateLimited(
        ?int $adminUserId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent
    ): int {
        return
            $this->attempts
            ->record(
                $adminUserId,
                trim(
                    mb_strtolower($identifier)
                ),
                $ipAddress,
                $userAgent,
                'rate_limited',
                'rate_limit_exceeded'
            );
    }

    public function recordSuccess(
        int $adminUserId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent
    ): int {
        return
            $this->attempts
            ->record(
                $adminUserId,
                trim(
                    mb_strtolower($identifier)
                ),
                $ipAddress,
                $userAgent,
                'success'
            );
    }

    public function limits(): array
    {
        return [
            'identifier' =>
            self::IDENTIFIER_LIMIT,

            'ip' =>
            self::IP_LIMIT,

            'identifier_window' =>
            self::IDENTIFIER_WINDOW,

            'ip_window' =>
            self::IP_WINDOW,
        ];
    }
}
