<?php

declare(strict_types=1);

final class AccountSecurityService
{
    private UserRepository $users;
    private UserSessionManagementService $sessions;

    public function __construct(
        ?UserRepository $users = null,
        ?UserSessionManagementService $sessions = null
    ) {
        $this->users =
            $users ??
            new UserRepository();

        $this->sessions =
            $sessions ??
            new UserSessionManagementService();
    }

    public function summary(
        int $userId
    ): array {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $user =
            $this->users->findById(
                $userId
            );

        if ($user === null) {
            throw new RuntimeException(
                'User not found.'
            );
        }

        $sessions =
            $this->sessions
            ->listActiveExceptCurrent(
                $userId,
                Session::id()
            );

        return [
            'email_verified' =>
            !empty($user['email_verified_at']),

            'phone_verified' =>
            !empty($user['phone_verified_at']),

            'account_status' =>
            (string)$user['status'],

            'last_login_at' =>
            $user['last_login_at'],

            'active_other_sessions' =>
            count($sessions),
        ];
    }
}
