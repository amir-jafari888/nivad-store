<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Core/Request.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Repositories/EmailVerificationTokenRepository.php';
require_once __DIR__ . '/EmailVerificationTokenService.php';
require_once __DIR__ . '/../Middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../Core/Exceptions/AuthenticationException.php';

final class ResendEmailVerificationService
{
    private PDO $db;
    private UserRepository $users;
    private EmailVerificationTokenRepository $tokens;
    private EmailVerificationTokenService $tokenService;

    public function __construct(
        ?PDO $db = null,
        ?UserRepository $users = null,
        ?EmailVerificationTokenRepository $tokens = null,
        ?EmailVerificationTokenService $tokenService = null
    ) {
        $this->db = $db ?? Database::connection();

        $this->users =
            $users ??
            new UserRepository($this->db);

        $this->tokens =
            $tokens ??
            new EmailVerificationTokenRepository($this->db);

        $this->tokenService =
            $tokenService ??
            new EmailVerificationTokenService($this->tokens);
    }

    public function resend(string $email): ?string
    {
        $email = mb_strtolower(trim($email));

        $rateKey =
            'email-verification-resend|' .
            $email .
            '|' .
            Request::ip();

        if (
            !RateLimitMiddleware::check(
                $rateKey,
                3,
                900
            )
        ) {
            throw new AuthenticationException(
                'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً بعداً دوباره تلاش کنید.'
            );
        }

        if ($email === '') {
            return null;
        }

        $user = $this->users->findByIdentifier($email);

        if ($user === null) {
            return null;
        }

        if (!empty($user['email_verified_at'])) {
            return null;
        }

        $userId = (int)$user['id'];

        $this->db->beginTransaction();

        try {
            $this->tokens->revokeActiveForUser($userId);

            $token =
                $this->tokenService->generate($userId);

            $this->db->commit();

            return $token;
        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }
}
