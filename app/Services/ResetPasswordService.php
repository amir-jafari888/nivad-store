<?php

declare(strict_types=1);

final class ResetPasswordService
{
    private PDO $db;
    private UserRepository $users;
    private UserSessionRepository $sessions;
    private PasswordResetTokenRepository $tokens;
    private PasswordResetTokenService $tokenService;

    public function __construct(
        ?PDO $db = null,
        ?UserRepository $users = null,
        ?UserSessionRepository $sessions = null,
        ?PasswordResetTokenRepository $tokens = null,
        ?PasswordResetTokenService $tokenService = null
    ) {
        $this->db =
            $db ?? Database::connection();

        $this->users =
            $users ??
            new UserRepository($this->db);

        $this->sessions =
            $sessions ??
            new UserSessionRepository(
                $this->db
            );

        $this->tokens =
            $tokens ??
            new PasswordResetTokenRepository(
                $this->db
            );

        $this->tokenService =
            $tokenService ??
            new PasswordResetTokenService(
                $this->tokens
            );
    }

    public function reset(
        string $rawToken,
        string $newPassword
    ): int {
        $rawToken =
            trim($rawToken);

        if ($rawToken === '') {
            throw new AuthenticationException(
                'لینک بازیابی رمز عبور نامعتبر یا منقضی شده است.'
            );
        }

        $tokenHash =
            $this->tokenService->hash(
                $rawToken
            );

        $this->db->beginTransaction();

        try {

            $token =
                $this->tokens
                ->findValidByHash(
                    $tokenHash
                );

            if ($token === null) {
                throw new AuthenticationException(
                    'لینک بازیابی رمز عبور نامعتبر یا منقضی شده است.'
                );
            }

            $userId =
                (int)$token['user_id'];

            $user =
                $this->users->findById(
                    $userId
                );

            if (
                $user === null ||
                !$this->users->isActive($user)
            ) {
                throw new AuthenticationException(
                    'لینک بازیابی رمز عبور نامعتبر یا منقضی شده است.'
                );
            }

            $newHash =
                Password::hash(
                    $newPassword
                );

            $updated =
                $this->users->updatePassword(
                    $userId,
                    $newHash
                );

            if (!$updated) {
                throw new RuntimeException(
                    'Password reset failed.'
                );
            }

            $used =
                $this->tokens->markUsed(
                    (int)$token['id']
                );

            if (!$used) {
                throw new AuthenticationException(
                    'لینک بازیابی رمز عبور نامعتبر یا قبلاً استفاده شده است.'
                );
            }

            $this->sessions
                ->revokeAllForUser(
                    $userId
                );

            $this->tokens
                ->revokeActiveForUser(
                    $userId
                );

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
