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
            $this->success(['updated' => true], 'نقش‌های مدیر به‌روزرسانی شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }

    public function roles(): never
    {
        $this->success(['items' => $this->service->roles()], 'نقش‌ها دریافت شدند.');
    }

    public function createRole(): never
    {
        try {
            $id = $this->service->createRole($this->payload());
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
            $this->success(['updated' => true], 'مجوزهای نقش به‌روزرسانی شدند.');
        } catch (InvalidArgumentException $exception) {
            $this->invalid($exception);
        }
    }
}
