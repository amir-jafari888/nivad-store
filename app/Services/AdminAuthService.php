<?php

declare(strict_types=1);

final class AdminAuthService
{
    private AdminRepository $admins;
    private AdminAuthenticationAuditService $audit;
    private AdminLoginAttemptService $loginAttempts;

    public function __construct(
        ?AdminRepository $admins = null,
        ?AdminLoginAttemptService $loginAttempts = null
    ) {
        $this->admins =
            $admins ??
            new AdminRepository();

        $this->loginAttempts =
            $loginAttempts ??
            new AdminLoginAttemptService();

        $this->audit =
            new AdminAuthenticationAuditService();
    }

    private function authenticationFailure(
        string $identifier,
        string $reason = 'authentication_failed'
    ): never {
        $this->audit->loginFailure(
            $identifier,
            $reason
        );

        throw new AdminAuthenticationException();
    }

    public function authenticate(
        string $identifier,
        string $password
    ): array {
        $identifier =
            trim($identifier);

        if (
            $identifier === '' ||
            $password === ''
        ) {
            $this->authenticationFailure(
                $identifier
            );
        }

        $ipAddress =
            Request::ip();

        $userAgent =
            Request::userAgent();

        if (
            $this->loginAttempts
            ->isRateLimited(
                $identifier,
                $ipAddress
            )
        ) {
            $this->loginAttempts
                ->recordRateLimited(
                    null,
                    $identifier,
                    $ipAddress,
                    $userAgent
                );

            $this->authenticationFailure(
                $identifier,
                'rate_limit_exceeded'
            );
        }

        $admin =
            $this->admins
            ->findByIdentifier(
                $identifier
            );

        if (
            $admin === null ||
            !$this->admins->isActive(
                $admin
            )
        ) {
            $this->loginAttempts
                ->recordFailure(
                    $admin !== null
                        ? (int)$admin['id']
                        : null,
                    $identifier,
                    $ipAddress,
                    $userAgent,
                    'inactive_or_unknown_admin'
                );

            $this->authenticationFailure(
                $identifier
            );
        }

        $hash =
            (string)(
                $admin['password_hash'] ?? ''
            );

        if (
            !Password::verify(
                $password,
                $hash
            )
        ) {
            $this->loginAttempts
                ->recordFailure(
                    (int)$admin['id'],
                    $identifier,
                    $ipAddress,
                    $userAgent,
                    'invalid_password'
                );

            $this->authenticationFailure(
                $identifier,
                'invalid_password'
            );
        }

        if (
            Password::needsRehash(
                $hash
            )
        ) {
            $newHash =
                Password::hash(
                    $password
                );

            $stmt =
                Database::connection()
                ->prepare(
                    'UPDATE admin_users
                 SET password_hash =
                     :password_hash
                 WHERE id = :id
                   AND deleted_at IS NULL
                 LIMIT 1'
                );

            $stmt->execute([
                'password_hash' =>
                $newHash,

                'id' =>
                (int)$admin['id'],
            ]);
        }

        AdminAuth::login(
            (int)$admin['id']
        );

        $this->admins
            ->updateLastLogin(
                (int)$admin['id']
            );

        $this->loginAttempts
            ->recordSuccess(
                (int)$admin['id'],
                $identifier,
                $ipAddress,
                $userAgent
            );

        $this->audit->login(
            (int)$admin['id'],
            $identifier
        );

        return $admin;
    }

    public function current(): ?array
    {
        $adminUserId =
            AdminAuth::id();

        if ($adminUserId === null) {
            return null;
        }

        return $this->admins
            ->findActiveById(
                $adminUserId
            );
    }

    public function authenticated(): bool
    {
        return $this->current() !== null;
    }

    public function logout(): never
    {
        $this->clearAuthenticatedSession();

        Response::success(
            null,
            'از پنل مدیریت خارج شدید.'
        );
    }

    private function clearAuthenticatedSession(): void
    {
        AdminAuth::logout();
    }

    public function validateCurrentSession(): bool
    {
        $adminUserId =
            AdminAuth::id();

        if ($adminUserId === null) {
            return false;
        }

        if (AdminAuth::expired()) {
            $this->audit->sessionInvalid(
                $adminUserId,
                'session_expired'
            );

            AdminAuth::logout();

            return false;
        }

        if (
            !$this->admins->existsActive(
                $adminUserId
            )
        ) {
            $this->audit->sessionInvalid(
                $adminUserId,
                'inactive_or_deleted_admin'
            );

            AdminAuth::logout();

            return false;
        }

        Session::start();

        $sessionTokenHash =
            hash(
                'sha256',
                Session::id()
            );

        $sessions =
            new AdminSessionService();

        if (
            !$sessions->isValid(
                $sessionTokenHash,
                $adminUserId
            )
        ) {
            $this->audit->sessionInvalid(
                $adminUserId,
                'admin_session_invalid'
            );

            AdminAuth::logout();

            return false;
        }

        $sessions->touch(
            $sessionTokenHash
        );

        return true;
    }

    public function currentSafe(): ?array
    {
        if (
            !$this->validateCurrentSession()
        ) {
            return null;
        }

        $admin =
            $this->current();

        if ($admin === null) {
            return null;
        }

        unset(
            $admin['password_hash']
        );

        return $admin;
    }

    public function requireAdminId(): int
    {
        if (!$this->validateCurrentSession()) {
            throw new AdminAuthenticationException(
                'نشست مدیر معتبر نیست.'
            );
        }

        $adminUserId = AdminAuth::id();

        if ($adminUserId === null) {
            throw new AdminAuthenticationException(
                'هویت مدیر پیدا نشد.'
            );
        }

        return $adminUserId;
    }

    public function can(
        string $permission
    ): bool {
        $adminUserId =
            $this->requireAdminId();

        return Authorization::can(
            $adminUserId,
            $permission
        );
    }

    public function authorize(
        string $permission
    ): void {
        $adminUserId =
            $this->requireAdminId();

        Authorization::authorize(
            $adminUserId,
            $permission
        );
    }

    public function canAny(
        array $permissions
    ): bool {
        $adminUserId =
            $this->requireAdminId();

        $authorization =
            new AuthorizationService();

        return $authorization
            ->hasAnyPermission(
                $adminUserId,
                $permissions
            );
    }

    public function canAll(
        array $permissions
    ): bool {
        $adminUserId =
            $this->requireAdminId();

        $authorization =
            new AuthorizationService();

        return $authorization
            ->hasAllPermissions(
                $adminUserId,
                $permissions
            );
    }
}
