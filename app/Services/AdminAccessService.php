<?php

declare(strict_types=1);

final class AdminAccessService
{
    private AdminAccessRepository $repository;

    public function __construct(?AdminAccessRepository $repository = null)
    {
        $this->repository = $repository ?? new AdminAccessRepository();
    }

    public function admins(): array
    {
        return $this->repository->admins();
    }

    public function roles(): array
    {
        return $this->repository->roles();
    }

    public function permissions(): array
    {
        return $this->repository->permissions();
    }

    public function rolePermissions(int $roleId): array
    {
        $role = $this->repository->roleById($roleId);
        if ($role === null || $role['deleted_at'] !== null) {
            throw new InvalidArgumentException('نقش پیدا نشد.');
        }
        return $this->repository->permissionsForRole($roleId);
    }

    public function createAdmin(array $input): int
    {
        $username = trim((string)($input['username'] ?? ''));
        $email = mb_strtolower(trim((string)($input['email'] ?? '')));
        $password = (string)($input['password'] ?? '');
        $firstName = trim((string)($input['first_name'] ?? ''));
        $lastName = trim((string)($input['last_name'] ?? ''));
        $roleIds = $this->ids($input['role_ids'] ?? []);

        if (!preg_match('/^[a-zA-Z0-9_.-]{3,100}$/', $username)) {
            throw new InvalidArgumentException('نام کاربری باید ۳ تا ۱۰۰ نویسه و شامل حروف لاتین، عدد و . _ - باشد.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            throw new InvalidArgumentException('ایمیل مدیر معتبر نیست.');
        }
        if (strlen($password) < 12) {
            throw new InvalidArgumentException('رمز عبور مدیر باید حداقل ۱۲ نویسه داشته باشد.');
        }
        if ($roleIds === []) {
            throw new InvalidArgumentException('حداقل یک نقش برای مدیر انتخاب کنید.');
        }
        $roles = $this->repository->validRoleIds($roleIds);
        if (count($roles) !== count($roleIds)) {
            throw new InvalidArgumentException('یک یا چند نقش انتخاب‌شده فعال یا معتبر نیستند.');
        }
        foreach ($roles as $role) {
            if ($role['slug'] === 'super-admin') {
                throw new InvalidArgumentException('ساخت مدیر Super Admin از این فرم مجاز نیست.');
            }
        }
        return $this->repository->createAdmin([
            'username' => $username,
            'email' => $email,
            'password_hash' => Password::hash($password),
            'first_name' => $firstName !== '' ? mb_substr($firstName, 0, 100) : null,
            'last_name' => $lastName !== '' ? mb_substr($lastName, 0, 100) : null,
        ], $roleIds);
    }

    public function updateAdmin(int $targetId, int $actorId, array $input): bool
    {
        if ($targetId < 1) {
            throw new InvalidArgumentException('شناسه مدیر نامعتبر است.');
        }
        $current = $this->repository->adminById($targetId);
        if ($current === null || $current['deleted_at'] !== null) {
            return false;
        }
        $status = (string)($input['status'] ?? $current['status']);
        $allowed = ['active', 'inactive', 'blocked', 'pending'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('وضعیت مدیر نامعتبر است.');
        }
        if ($targetId === $actorId && $status !== 'active') {
            throw new InvalidArgumentException('امکان غیرفعال‌کردن حساب مدیر فعلی وجود ندارد.');
        }
        if ($status !== 'active' && $this->isSuperAdmin($targetId) && !$this->repository->hasOtherActiveSuperAdmin($targetId)) {
            throw new InvalidArgumentException('آخرین Super Admin را نمی‌توان غیرفعال کرد.');
        }
        $email = mb_strtolower(trim((string)($input['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            throw new InvalidArgumentException('ایمیل مدیر معتبر نیست.');
        }
        $data = [
            'email' => $email,
            'first_name' => mb_substr(trim((string)($input['first_name'] ?? '')), 0, 100),
            'last_name' => mb_substr(trim((string)($input['last_name'] ?? '')), 0, 100),
            'status' => $status,
        ];
        $password = (string)($input['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 12) {
                throw new InvalidArgumentException('رمز عبور جدید باید حداقل ۱۲ نویسه داشته باشد.');
            }
            $data['password_hash'] = Password::hash($password);
        }
        return $this->repository->updateAdmin($targetId, $data);
    }

    public function deleteAdmin(int $targetId, int $actorId): bool
    {
        if ($targetId < 1 || $targetId === $actorId) {
            throw new InvalidArgumentException('حذف حساب مدیر فعلی مجاز نیست.');
        }
        $target = $this->repository->adminById($targetId);
        if ($target === null || $target['deleted_at'] !== null) {
            return false;
        }
        if ($this->isSuperAdmin($targetId) && !$this->repository->hasOtherActiveSuperAdmin($targetId)) {
            throw new InvalidArgumentException('آخرین Super Admin را نمی‌توان حذف کرد.');
        }
        return $this->repository->softDeleteAdmin($targetId);
    }

    public function assignAdminRoles(int $targetId, int $actorId, array $input): bool
    {
        if ($targetId === $actorId) {
            throw new InvalidArgumentException('تغییر نقش حساب مدیر فعلی از این رابط مجاز نیست.');
        }
        $target = $this->repository->adminById($targetId);
        if ($target === null || $target['deleted_at'] !== null) return false;
        $roleIds = $this->ids($input['role_ids'] ?? []);
        if ($roleIds === []) {
            throw new InvalidArgumentException('حداقل یک نقش برای مدیر لازم است.');
        }
        $roles = $this->repository->validRoleIds($roleIds);
        if (count($roles) !== count($roleIds)) {
            throw new InvalidArgumentException('یک یا چند نقش انتخاب‌شده فعال یا معتبر نیستند.');
        }
        foreach ($roles as $role) {
            if ($role['slug'] === 'super-admin') {
                throw new InvalidArgumentException('تخصیص نقش Super Admin از این رابط مجاز نیست.');
            }
        }
        if ($this->isSuperAdmin($targetId) && !$this->repository->hasOtherActiveSuperAdmin($targetId)) {
            throw new InvalidArgumentException('نمی‌توان دسترسی Super Admin آخرین مدیر را حذف کرد.');
        }
        $this->repository->replaceAdminRoles($targetId, $roleIds);
        return true;
    }

    public function createRole(array $input): int
    {
        $name = trim((string)($input['name'] ?? ''));
        $slug = strtolower(trim((string)($input['slug'] ?? '')));
        $description = trim((string)($input['description'] ?? ''));
        if ($name === '' || mb_strlen($name) > 100) {
            throw new InvalidArgumentException('نام نقش الزامی و حداکثر ۱۰۰ نویسه است.');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || mb_strlen($slug) > 120 || $slug === 'super-admin') {
            throw new InvalidArgumentException('شناسه نقش معتبر نیست یا رزرو شده است.');
        }
        return $this->repository->createRole([
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? mb_substr($description, 0, 500) : null,
            'status' => 'active',
        ]);
    }

    public function updateRole(int $id, array $input): bool
    {
        $role = $this->repository->roleById($id);
        if ($role === null || $role['deleted_at'] !== null) return false;
        if ($role['slug'] === 'super-admin') {
            throw new InvalidArgumentException('ویرایش نقش اصلی Super Admin از این رابط مجاز نیست.');
        }
        $name = trim((string)($input['name'] ?? ''));
        $status = (string)($input['status'] ?? $role['status']);
        if ($name === '' || mb_strlen($name) > 100 || !in_array($status, ['active', 'inactive'], true)) {
            throw new InvalidArgumentException('نام یا وضعیت نقش معتبر نیست.');
        }
        if ($status === 'inactive' && $role['status'] === 'active' && $this->repository->roleAssignedToActiveAdmin($id)) {
            throw new InvalidArgumentException('ابتدا نقش را از مدیران فعال جدا کنید؛ سپس می‌توانید آن را غیرفعال کنید.');
        }
        return $this->repository->updateRole($id, [
            'name' => $name,
            'description' => mb_substr(trim((string)($input['description'] ?? '')), 0, 500),
            'status' => $status,
        ]);
    }

    public function assignRolePermissions(int $roleId, array $input): bool
    {
        $role = $this->repository->roleById($roleId);
        if ($role === null || $role['deleted_at'] !== null) return false;
        if ($role['slug'] === 'super-admin') {
            throw new InvalidArgumentException('مجوزهای نقش اصلی Super Admin از این رابط قابل بازنویسی نیستند.');
        }
        $ids = $this->ids($input['permission_ids'] ?? []);
        $valid = $this->repository->validPermissionIds($ids);
        if (count($valid) !== count($ids)) {
            throw new InvalidArgumentException('یک یا چند مجوز انتخاب‌شده معتبر یا فعال نیستند.');
        }
        $this->repository->replaceRolePermissions($roleId, $ids);
        return true;
    }

    private function isSuperAdmin(int $adminId): bool
    {
        return $this->repository->adminHasRoleSlug($adminId, 'super-admin');
    }

    private function ids(mixed $values): array
    {
        if (!is_array($values) || count($values) > 500) {
            throw new InvalidArgumentException('فهرست شناسه‌ها معتبر نیست.');
        }
        $ids = [];
        foreach ($values as $value) {
            if (!filter_var($value, FILTER_VALIDATE_INT) || (int)$value < 1) {
                throw new InvalidArgumentException('یک یا چند شناسه انتخاب‌شده نامعتبر است.');
            }
            $ids[] = (int)$value;
        }
        return array_values(array_unique($ids));
    }
}
