<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';

final class AccountVerificationService
{
    private UserRepository $users;

    public function __construct(
        ?UserRepository $users = null
    ) {
        $this->users =
            $users ??
            new UserRepository();
    }

    public function status(int $userId): array
    {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $user =
            $this->users->getVerificationStatus(
                $userId
            );

        if ($user === null) {
            throw new RuntimeException(
                'User not found.'
            );
        }

        return [
            'email_verified' =>
            !empty($user['email_verified_at']),

            'phone_verified' =>
            !empty($user['phone_verified_at']),

            'account_status' =>
            (string)$user['status'],

            'fully_verified' =>
            !empty($user['email_verified_at']) &&
                !empty($user['phone_verified_at']),
        ];
    }

    public function isEmailVerified(
        int $userId
    ): bool {
        $status =
            $this->status($userId);

        return $status['email_verified'];
    }
}
