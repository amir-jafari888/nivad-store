<?php

declare(strict_types=1);

final class ForgotPasswordService
{
    private UserRepository $users;
    private PasswordResetTokenRepository $tokens;
    private PasswordResetTokenService $tokenService;

    public function __construct(
        ?UserRepository $users = null,
        ?PasswordResetTokenRepository $tokens = null,
        ?PasswordResetTokenService $tokenService = null
    ) {
        $this->users =
            $users ??
            new UserRepository();

        $this->tokens =
            $tokens ??
            new PasswordResetTokenRepository();

        $this->tokenService =
            $tokenService ??
            new PasswordResetTokenService(
                $this->tokens
            );
    }

    public function request(
        string $email
    ): ?string {
        $email =
            mb_strtolower(
                trim($email)
            );

        $user =
            $this->users->findByIdentifier(
                $email
            );

        /*
         * Never reveal whether the email
         * belongs to an existing account.
         */
        if (
            $user === null ||
            !$this->users->isActive($user)
        ) {
            return null;
        }

        $this->tokens
            ->revokeActiveForUser(
                (int)$user['id']
            );

        return $this->tokenService->generate(
            (int)$user['id']
        );
    }
}
