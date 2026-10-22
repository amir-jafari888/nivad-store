<?php

declare(strict_types=1);

final class LoginAttemptService
{
    private CustomerLoginAttemptRepository $attempts;

    private const IDENTIFIER_WINDOW = 900;
    private const IP_WINDOW = 900;

    private const IDENTIFIER_LIMIT = 5;
    private const IP_LIMIT = 20;

    public function __construct(
        ?CustomerLoginAttemptRepository $attempts = null
    ) {
        $this->attempts =
            $attempts ??
            new CustomerLoginAttemptRepository();
    }

    public function identifierHash(
        string $identifier
    ): string {
        $identifier = trim(
            mb_strtolower($identifier)
        );

        if ($identifier === '') {
            throw new InvalidArgumentException(
                'Identifier cannot be empty.'
            );
        }

        return hash(
            'sha256',
            $identifier
        );
    }

    public function isRateLimited(
        string $identifier,
        string $ipAddress
    ): bool {
        $identifierHash =
            $this->identifierHash($identifier);

        $identifierFailures =
            $this->attempts
            ->countRecentFailuresByIdentifier(
                $identifierHash,
                self::IDENTIFIER_WINDOW
            );

        if (
            $identifierFailures >=
            self::IDENTIFIER_LIMIT
        ) {
            return true;
        }

        $ipFailures =
            $this->attempts
            ->countRecentFailuresByIp(
                $ipAddress,
                self::IP_WINDOW
            );

        return $ipFailures >= self::IP_LIMIT;
    }

    public function recordFailure(
        ?int $userId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent,
        string $reason = 'invalid_credentials'
    ): int {
        return $this->attempts->record(
            $userId,
            $this->identifierHash($identifier),
            $ipAddress,
            $userAgent,
            'failed',
            $reason
        );
    }

    public function recordRateLimited(
        ?int $userId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent
    ): int {
        return $this->attempts->record(
            $userId,
            $this->identifierHash($identifier),
            $ipAddress,
            $userAgent,
            'rate_limited',
            'rate_limit_exceeded'
        );
    }

    public function recordSuccess(
        int $userId,
        string $identifier,
        string $ipAddress,
        ?string $userAgent
    ): int {
        return $this->attempts->record(
            $userId,
            $this->identifierHash($identifier),
            $ipAddress,
            $userAgent,
            'success'
        );
    }

    public function recentFailureCount(
        string $identifier,
        string $ipAddress
    ): array {
        $identifierHash =
            $this->identifierHash($identifier);

        return [
            'identifier' =>
            $this->attempts
                ->countRecentFailuresByIdentifier(
                    $identifierHash,
                    self::IDENTIFIER_WINDOW
                ),

            'ip' =>
            $this->attempts
                ->countRecentFailuresByIp(
                    $ipAddress,
                    self::IP_WINDOW
                ),
        ];
    }

    public function limits(): array
    {
        return [
            'identifier' => self::IDENTIFIER_LIMIT,
            'ip' => self::IP_LIMIT,
            'identifier_window' =>
            self::IDENTIFIER_WINDOW,
            'ip_window' =>
            self::IP_WINDOW,
        ];
    }
}
