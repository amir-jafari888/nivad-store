<?php

declare(strict_types=1);

final class PasswordResetTokenService
{
    private PasswordResetTokenRepository $tokens;

    private const EXPIRATION = 3600;

    public function __construct(
        ?PasswordResetTokenRepository $tokens = null
    ) {
        $this->tokens =
            $tokens ??
            new PasswordResetTokenRepository();
    }

    public function generate(
        int $userId
    ): string {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $rawToken =
            Security::randomToken();

        $tokenHash =
            Security::hashToken(
                $rawToken
            );

        $this->tokens->create(
            $userId,
            $tokenHash,
            Request::ip(),
            Request::userAgent(),
            self::EXPIRATION
        );

        return $rawToken;
    }

    public function hash(
        string $rawToken
    ): string {
        if ($rawToken === '') {
            throw new InvalidArgumentException(
                'Reset token cannot be empty.'
            );
        }

        return Security::hashToken(
            $rawToken
        );
    }

    public function expirationSeconds(): int
    {
        return self::EXPIRATION;
    }
}
