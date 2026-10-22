<?php

declare(strict_types=1);

final class PasswordChangeService
{
    private UserRepository $users;
    private UserSessionRepository $sessions;
    private PDO $db;

    public function __construct(
        ?UserRepository $users = null,
        ?UserSessionRepository $sessions = null,
        ?PDO $db = null
    ) {
        $this->db = $db ?? Database::connection();

        $this->users =
            $users ?? new UserRepository($this->db);

        $this->sessions =
            $sessions ?? new UserSessionRepository($this->db);
    }

    public function change(
        int $userId,
        string $currentPassword,
        string $newPassword
    ): void {
        if ($userId < 1) {
            throw new AuthenticationException(
                'برای ادامه باید وارد حساب شوید.'
            );
        }

        $user =
            $this->users->findById($userId);

        if (
            $user === null ||
            !$this->users->isActive($user)
        ) {
            throw new AuthenticationException(
                'برای ادامه باید وارد حساب شوید.'
            );
        }

        if (
            !Password::verify(
                $currentPassword,
                (string)$user['password_hash']
            )
        ) {
            throw new AuthenticationException(
                'رمز عبور فعلی صحیح نیست.'
            );
        }

        if ($currentPassword === $newPassword) {
            throw new ValidationException([
                'new_password' =>
                'رمز عبور جدید باید با رمز فعلی متفاوت باشد.',
            ]);
        }

        $newHash =
            Password::hash($newPassword);

        $this->db->beginTransaction();

        try {
            $updated =
                $this->users->updatePassword(
                    $userId,
                    $newHash
                );

            if (!$updated) {
                throw new RuntimeException(
                    'Password update failed.'
                );
            }

            $this->sessions
                ->revokeAllForUser($userId);

            $this->db->commit();
        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }

        /*
         * The current PHP authentication session is
         * regenerated after a successful password change.
         * The current authenticated user remains logged in.
         */
        Auth::login($userId);
    }
}
