<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Security.php';
require_once __DIR__ . '/../Core/Request.php';
require_once __DIR__ . '/../Core/Exceptions/AppException.php';
require_once __DIR__ . '/../Core/Exceptions/AuthenticationException.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Repositories/EmailVerificationTokenRepository.php';
require_once __DIR__ . '/EmailVerificationTokenService.php';

final class EmailVerificationService
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
        $this->users = $users ?? new UserRepository($this->db);
        $this->tokens = $tokens ?? new EmailVerificationTokenRepository($this->db);
        $this->tokenService = $tokenService ?? new EmailVerificationTokenService($this->tokens);
    }

    public function verify(string $rawToken): int
    {
        $rawToken = trim($rawToken);

        if ($rawToken === '') {
            throw new AuthenticationException(
                'لینک تأیید ایمیل نامعتبر یا منقضی شده است.'
            );
        }

        $tokenHash = $this->tokenService->hash($rawToken);

        $this->db->beginTransaction();

        try {
            $token = $this->tokens->findValidByHashForUpdate($tokenHash);

            if ($token === null) {
                throw new AuthenticationException(
                    'لینک تأیید ایمیل نامعتبر یا منقضی شده است.'
                );
            }

            $userId = (int)$token['user_id'];
            $user = $this->users->findById($userId);

            if ($user === null) {
                throw new AuthenticationException(
                    'لینک تأیید ایمیل نامعتبر یا منقضی شده است.'
                );
            }

            if (!empty($user['email_verified_at'])) {
                throw new AuthenticationException(
                    'ایمیل این حساب قبلاً تأیید شده است.'
                );
            }

            $updated = $this->users->markEmailVerified($userId);

            if (!$updated) {
                throw new AuthenticationException(
                    'تأیید ایمیل انجام نشد.'
                );
            }

            Logger::info(
                'User email verified.',
                [
                    'user_id' => $userId,
                ]
            );

            $used = $this->tokens->markUsed(
                (int)$token['id']
            );

            if (!$used) {
                throw new AuthenticationException(
                    'لینک تأیید ایمیل نامعتبر یا قبلاً استفاده شده است.'
                );
            }

            $this->tokens->revokeActiveForUser($userId);

            $this->db->commit();

            return $userId;
        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }
}
