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
        try {
            $updated = $this->service->updateAdmin(
                (int)($parameters['id'] ?? 0),
                $this->actorId(),
                $this->payload()
            );
            if (!$updated) $this->error('مدیر پیدا نشد.', 404);
            AdminAuditTrail::record($this->actorId(), 'update', 'admin_users', 'admin_user', (int)($parameters['id'] ?? 0), 'Admin account updated.');
            $this->success(['updated' => true], 'اطلاعات مدیر به‌روزرسانی شد.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        } catch (PDOException $exception) {
            $this->error('ایمیل واردشده قبلاً ثبت شده است.', 409);
        }
    }

    public function assignAdminRoles(array $parameters = []): never
    {
        try {
            $updated = $this->service->assignAdminRoles(
                (int)($parameters['id'] ?? 0),
                $this->actorId(),
                $this->payload()
            );
            if (!$updated) $this->error('مدیر پیدا نشد.', 404);
            AdminAuditTrail::record($this->actorId(), 'assign_roles', 'admin_users', 'admin_user', (int)($parameters['id'] ?? 0), 'Admin roles reassigned.');
            $this->success(['updated' => true], 'نقش‌های مدیر به‌روزرسانی شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function sessions(): never
    {
        $adminId = $this->actorId();
        Session::start();
        $tokenHash = hash('sha256', Session::id());
        $items = (new AdminSessionService())->activeForAdmin($adminId, $tokenHash);
        $this->success(['items' => $items], 'نشست‌های فعال دیگر دریافت شدند.');
    }

    public function revokeSession(array $parameters = []): never
    {
        $adminId = $this->actorId();
        Session::start();
        $tokenHash = hash('sha256', Session::id());
        $revoked = (new AdminSessionService())->revokeByIdForAdmin(
            (int)($parameters['id'] ?? 0),
            $adminId,
            $tokenHash
        );
        if (!$revoked) $this->error('نشست پیدا نشد، منقضی شده یا نشست فعلی است.', 404);
        AdminAuditTrail::record($adminId, 'revoke_session', 'admin_sessions', 'admin_session', (int)($parameters['id'] ?? 0), 'Another Admin session was revoked.');
        $this->success(['revoked' => true], 'نشست انتخاب‌شده لغو شد.');
    }

    public function roles(): never
    {
        $this->success(['items' => $this->service->roles()], 'نقش‌ها دریافت شدند.');
    }

    public function createRole(): never
    {
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
        try {
            $updated = $this->service->updateRole((int)($parameters['id'] ?? 0), $this->payload());
            if (!$updated) $this->error('نقش پیدا نشد.', 404);
            AdminAuditTrail::record($this->actorId(), 'update', 'admin_roles', 'admin_role', (int)($parameters['id'] ?? 0), 'Admin role updated.');
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
        try {
            $updated = $this->service->assignRolePermissions(
                (int)($parameters['id'] ?? 0),
                $this->payload()
            );
            if (!$updated) $this->error('نقش پیدا نشد.', 404);
            AdminAuditTrail::record($this->actorId(), 'assign_permissions', 'admin_roles', 'admin_role', (int)($parameters['id'] ?? 0), 'Admin role permissions reassigned.');
            $this->success(['updated' => true], 'مجوزهای نقش به‌روزرسانی شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }
}
