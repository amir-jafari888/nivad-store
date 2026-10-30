<?php

declare(strict_types=1);

final class AdminAccessController extends Controller
{
    private AdminAccessService $service;

    public function __construct(?AdminAccessService $service = null)
    {
        $this->service = $service ?? new AdminAccessService();
    }

    private function actorId(): int
    {
        $id = AdminAuth::id();
        if ($id === null || $id < 1) $this->error('نشست مدیر معتبر نیست.', 401);
        return $id;
    }

    private function payload(): array
    {
        return Request::isJson() ? Request::json() : $_POST;
    }

    private function requireReauthentication(string $action): void
    {
        $payload = $this->payload();
        (new AdminReauthenticationService())->requireCurrentPassword(
            (string)($payload['reauth_password'] ?? ''),
            $action
        );
    }

    private function invalid(InvalidArgumentException $exception): never
    {
        $this->error($exception->getMessage(), 422);
    }

    public function admins(): never
    {
        $this->success(['items' => $this->service->admins()], 'مدیران دریافت شدند.');
    }

    public function createAdmin(): never
    {
        $this->requireReauthentication('admin.create');
        try {
            $id = $this->service->createAdmin($this->payload());
            AdminAuditTrail::record($this->actorId(), 'create', 'admin_users', 'admin_user', $id, 'Admin account created.', null, ['status' => 'active']);
            $this->success(['id' => $id], 'مدیر جدید ایجاد شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        } catch (PDOException $exception) {
            $this->error('نام کاربری یا ایمیل تکراری است یا ثبت مدیر با محدودیت دیتابیس مواجه شد.', 409);
        }
    }

    public function updateAdmin(array $parameters = []): never
    {
        $this->requireReauthentication('admin.update');
        try {
            $targetId = (int)($parameters['id'] ?? 0);
            $before = $this->service->adminSecuritySnapshot($targetId);
            $updated = $this->service->updateAdmin(
                $targetId,
                $this->actorId(),
                $this->payload()
            );
            if (!$updated) $this->error('مدیر پیدا نشد.', 404);
            $after = $this->service->adminSecuritySnapshot($targetId);
            AdminAuditTrail::record($this->actorId(), 'update', 'admin_users', 'admin_user', $targetId, 'Admin account security state updated.', $before, $after);
            $this->success(['updated' => true], 'اطلاعات مدیر به‌روزرسانی شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        } catch (PDOException $exception) {
            $this->error('ایمیل واردشده قبلاً ثبت شده است.', 409);
        }
    }

    public function deleteAdmin(array $parameters = []): never
    {
        $this->requireReauthentication('admin.delete');
        try {
            $adminId = $this->actorId();
            $targetId = (int)($parameters['id'] ?? 0);
            $before = $this->service->adminSecuritySnapshot($targetId);
            $deleted = $this->service->deleteAdmin($targetId, $adminId);
            if (!$deleted) $this->error('مدیر پیدا نشد.', 404);
            $after = $this->service->adminSecuritySnapshot($targetId);
            AdminAuditTrail::record($adminId, 'soft_delete', 'admin_users', 'admin_user', $targetId, 'Admin account soft-deleted and sessions revoked.', $before, $after);
            $this->success(['deleted' => true], 'حساب مدیر غیرفعال و نشست‌های او لغو شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function assignAdminRoles(array $parameters = []): never
    {
        $this->requireReauthentication('admin.roles.assign');
        try {
            $targetId = (int)($parameters['id'] ?? 0);
            $before = $this->service->adminSecuritySnapshot($targetId);
            $updated = $this->service->assignAdminRoles(
                $targetId,
                $this->actorId(),
                $this->payload()
            );
            if (!$updated) $this->error('مدیر پیدا نشد.', 404);
            $after = $this->service->adminSecuritySnapshot($targetId);
            AdminAuditTrail::record($this->actorId(), 'assign_roles', 'admin_users', 'admin_user', $targetId, 'Admin roles reassigned.', $before, $after);
            $this->success(['updated' => true], 'نقش‌های مدیر به‌روزرسانی شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function sessions(): never
    {
        $this->actorId();
        Session::start();
        $tokenHash = hash('sha256', Session::id());
        $items = (new AdminSessionService())->activeForAllAdmins($tokenHash);
        $this->success(['items' => $items], 'نشست‌های فعال مدیران دریافت شدند.');
    }

    public function sessionsHistory(): never
    {
        $this->actorId();
        $items = (new AdminSessionService())->findForMonitoring(200);
        $this->success(['items' => $items], 'تاریخچه نشست‌های نگهداری‌شده دریافت شد.');
    }

    public function revokeSession(array $parameters = []): never
    {
        $this->requireReauthentication('admin.session.revoke');
        $adminId = $this->actorId();
        Session::start();
        $tokenHash = hash('sha256', Session::id());
        $sessionId = (int)($parameters['id'] ?? 0);
        $sessions = new AdminSessionService();
        $owner = $sessions->activeSessionOwner($sessionId, $tokenHash);
        if ($owner === null) {
            $this->error('نشست پیدا نشد، منقضی شده یا نشست فعلی است.', 404);
        }
        $revoked = $sessions->revokeByIdForAnyAdmin($sessionId, $tokenHash);
        if (!$revoked) {
            $this->error('لغو نشست انجام نشد؛ وضعیت نشست تغییر کرده است.', 409);
        }
        AdminAuditTrail::record(
            $adminId,
            'revoke_session',
            'admin_sessions',
            'admin_session',
            $sessionId,
            'An Admin session was revoked by an authorized Admin.',
            null,
            ['target_admin_id' => (int)$owner['admin_user_id']]
        );
        $this->success(['revoked' => true, 'admin_user_id' => (int)$owner['admin_user_id']], 'نشست انتخاب‌شده لغو شد.');
    }

    public function roles(): never
    {
        $this->success(['items' => $this->service->roles()], 'نقش‌ها دریافت شدند.');
    }

    public function createRole(): never
    {
        $this->requireReauthentication('admin.role.create');
        try {
            $id = $this->service->createRole($this->payload());
            AdminAuditTrail::record($this->actorId(), 'create', 'admin_roles', 'admin_role', $id, 'Admin role created.');
            $this->success(['id' => $id], 'نقش جدید ایجاد شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        } catch (PDOException $exception) {
            $this->error('شناسه این نقش قبلاً استفاده شده است.', 409);
        }
    }

    public function updateRole(array $parameters = []): never
    {
        $this->requireReauthentication('admin.role.update');
        try {
            $roleId = (int)($parameters['id'] ?? 0);
            $before = $this->service->roleSecuritySnapshot($roleId);
            $updated = $this->service->updateRole($roleId, $this->payload());
            if (!$updated) $this->error('نقش پیدا نشد.', 404);
            $after = $this->service->roleSecuritySnapshot($roleId);
            AdminAuditTrail::record($this->actorId(), 'update', 'admin_roles', 'admin_role', $roleId, 'Admin role security state updated.', $before, $after);
            $this->success(['updated' => true], 'نقش به‌روزرسانی شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        } catch (PDOException $exception) {
            $this->error('تغییر نقش به دلیل محدودیت دیتابیس انجام نشد.', 409);
        }
    }

    public function permissions(): never
    {
        $this->success(['items' => $this->service->permissions()], 'مجوزها دریافت شدند.');
    }

    public function effectivePermissions(array $parameters = []): never
    {
        try {
            $adminId = (int)($parameters['id'] ?? 0);
            $items = $this->service->effectivePermissions($adminId);
            $this->success(['items' => $items], 'دسترسی مؤثر مدیر دریافت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function rolePermissions(array $parameters = []): never
    {
        try {
            $items = $this->service->rolePermissions((int)($parameters['id'] ?? 0));
            $this->success(['items' => $items], 'مجوزهای نقش دریافت شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function assignRolePermissions(array $parameters = []): never
    {
        $this->requireReauthentication('admin.permissions.assign');
        try {
            $roleId = (int)($parameters['id'] ?? 0);
            $before = $this->service->roleSecuritySnapshot($roleId);
            $updated = $this->service->assignRolePermissions(
                $roleId,
                $this->payload()
            );
            if (!$updated) $this->error('نقش پیدا نشد.', 404);
            $after = $this->service->roleSecuritySnapshot($roleId);
            AdminAuditTrail::record($this->actorId(), 'assign_permissions', 'admin_roles', 'admin_role', $roleId, 'Admin role permissions reassigned.', $before, $after);
            $this->success(['updated' => true], 'مجوزهای نقش به‌روزرسانی شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }
}
