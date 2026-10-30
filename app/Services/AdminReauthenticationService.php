<?php

declare(strict_types=1);

/**
 * Verifies the currently authenticated administrator's password immediately
 * before a privileged access-management mutation. No reusable step-up token
 * is issued or stored in the session.
 */
final class AdminReauthenticationService
{
    private AdminRepository $admins;

    public function __construct(?AdminRepository $admins = null)
    {
        $this->admins = $admins ?? new AdminRepository();
    }

    public function requireCurrentPassword(string $password, string $action): void
    {
        $adminId = AdminAuth::id();
        if (!is_int($adminId) || $adminId < 1) {
            throw new AdminAuthenticationException('نشست مدیر معتبر نیست.');
        }

        $action = trim($action);
        if ($action === '' || strlen($action) > 120) {
            throw new InvalidArgumentException('شناسه عملیات امنیتی معتبر نیست.');
        }

        if ($password === '' || strlen($password) > 1024) {
            AdminAuditTrail::record(
                $adminId,
                'reauth_failed',
                'admin_security',
                'admin_user',
                $adminId,
                'Admin re-authentication failed because no valid password input was supplied.',
                null,
                ['action' => $action]
            );
            throw new AuthorizationException('برای این عملیات، تأیید مجدد هویت لازم است.');
        }

        $admin = $this->admins->findActiveById($adminId);
        $hash = (string)($admin['password_hash'] ?? '');

        if ($admin === null || $hash === '' || !Password::verify($password, $hash)) {
            AdminAuditTrail::record(
                $adminId,
                'reauth_failed',
                'admin_security',
                'admin_user',
                $adminId,
                'Admin re-authentication failed.',
                null,
                ['action' => $action]
            );
            throw new AuthorizationException('رمز عبور فعلی صحیح نیست؛ عملیات انجام نشد.');
        }

        AdminAuditTrail::record(
            $adminId,
            'reauth_succeeded',
            'admin_security',
            'admin_user',
            $adminId,
            'Admin re-authenticated for a privileged access-management operation.',
            null,
            ['action' => $action]
        );
    }
}
