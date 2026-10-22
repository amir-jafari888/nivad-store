<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Core/Request.php';
require_once __DIR__ . '/../Repositories/EmailVerificationTokenRepository.php';
require_once __DIR__ . '/../Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../Core/Exceptions/AuthenticationException.php';

final class EmailVerificationTokenService
{
    private EmailVerificationTokenRepository $tokens;

    private const EXPIRATION = 86400;

    public function __construct(
        ?EmailVerificationTokenRepository $tokens = null
    ) {
        $this->tokens =
            $tokens ??
            new EmailVerificationTokenRepository();
    }



    public function generate(
        int $userId
    ): string {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $this->tokens->revokeActiveForUser(
            $userId
        );

        $rawToken =
            Security::randomToken();

        $tokenHash =
            Security::hashToken(
                $rawToken
            );
        $rateKey =
            'email-verification-token|' .
            $userId .
            '|' .
            Request::ip();

        if (
            !RateLimitMiddleware::check(
                $rateKey,
                5,
                900
            )
        ) {
            throw new AuthenticationException(
                'تعداد درخواست‌های تأیید ایمیل بیش از حد مجاز است. لطفاً بعداً دوباره تلاش کنید.'
            );
        }

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
                'Verification token cannot be empty.'
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
