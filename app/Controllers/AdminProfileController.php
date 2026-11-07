<?php

declare(strict_types=1);

final class AdminProfileController extends Controller
{
    private AdminRepository $admins;

    public function __construct(?AdminRepository $admins = null)
    {
        $this->admins = $admins ?? new AdminRepository();
    }

    private function actorId(): int
    {
        $id = AdminAuth::id();
        if (!is_int($id) || $id < 1) {
            $this->error('نشست مدیر معتبر نیست.', 401);
        }
        return $id;
    }

    public function update(): never
    {
        $adminId = $this->actorId();

        try {
            $payload = Request::isJson() ? Request::json() : $_POST;
            (new AdminReauthenticationService())->requireCurrentPassword(
                (string)($payload['current_password'] ?? ''),
                'profile.update'
            );

            $username = trim((string)($payload['username'] ?? ''));
            $email = mb_strtolower(trim((string)($payload['email'] ?? '')));
            $firstName = trim((string)($payload['first_name'] ?? ''));
            $lastName = trim((string)($payload['last_name'] ?? ''));
            $newPassword = (string)($payload['new_password'] ?? '');

            if (!preg_match('/^[a-zA-Z0-9_.-]{3,100}$/', $username)) {
                $this->error('نام کاربری باید ۳ تا ۱۰۰ نویسه و شامل حروف لاتین، عدد و . _ - باشد.', 422);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
                $this->error('ایمیل واردشده معتبر نیست.', 422);
            }
            if (mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100) {
                $this->error('نام و نام خانوادگی حداکثر می‌توانند ۱۰۰ نویسه باشند.', 422);
            }
            if ($newPassword !== '' && (strlen($newPassword) < 12 || strlen($newPassword) > 1024)) {
                $this->error('رمز عبور جدید باید حداقل ۱۲ و حداکثر ۱۰۲۴ بایت باشد.', 422);
            }
            if ($newPassword !== '' && $newPassword !== (string)($payload['confirm_password'] ?? '')) {
                $this->error('تکرار رمز عبور جدید با آن مطابقت ندارد.', 422);
            }

            $current = $this->admins->findActiveById($adminId);
            if ($current === null) {
                $this->error('حساب مدیر فعال پیدا نشد.', 401);
            }

            $data = [
                'username' => $username,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ];
            if ($newPassword !== '') {
                $data['password_hash'] = Password::hash($newPassword);
            }

            $updated = $this->admins->updateOwnProfile($adminId, $data);
            if (!$updated) {
                $this->error('ذخیره اطلاعات پروفایل انجام نشد.', 409);
            }

            AdminAuditTrail::record(
                $adminId,
                $newPassword !== '' ? 'profile_and_password_update' : 'profile_update',
                'admin_users',
                'admin_user',
                $adminId,
                'Administrator updated their own profile.',
                null,
                [
                    'username_changed' => $username !== (string)$current['username'],
                    'email_changed' => $email !== (string)$current['email'],
                    'name_changed' => $firstName !== (string)($current['first_name'] ?? '')
                        || $lastName !== (string)($current['last_name'] ?? ''),
                    'password_changed' => $newPassword !== '',
                ]
            );

            $this->success(['updated' => true], 'اطلاعات پروفایل با موفقیت ذخیره شد.');
        } catch (PDOException $exception) {
            $this->error('این ایمیل یا نام کاربری قبلاً برای حساب دیگری ثبت شده است.', 409);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }
}
