<?php

declare(strict_types=1);

require_once __DIR__ . '/../Services/ResendEmailVerificationService.php';
require_once __DIR__ . '/../Core/Exceptions/AuthenticationException.php';
require_once __DIR__ . '/../Services/AccountVerificationService.php';

final class AuthController extends Controller
{
    public function login(): never
    {
        $identifier =
            Request::post('identifier');

        $password =
            Request::post('password');

        $errors =
            AuthValidator::login(
                $identifier,
                $password
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات ورود نامعتبر است.',
                422,
                $errors
            );
        }

        $identifier =
            trim((string)$identifier);

        $password =
            (string)$password;

        try {
            $user =
                (new AuthService())
                ->authenticate(
                    $identifier,
                    $password
                );
        } catch (
            AuthenticationException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                401
            );
        }

        $this->success(
            [
                'user_id' => (int)$user['id'],
            ],
            'ورود با موفقیت انجام شد.'
        );
    }

    public function csrf(): never
    {
        $this->success(
            [
                'token' => Csrf::token(),
            ],
            'توکن امنیتی دریافت شد.'
        );
    }

    public function status(): never
    {
        $userId = Auth::id();

        $this->success(
            [
                'authenticated' =>
                $userId !== null,

                'user_id' =>
                $userId,
            ],
            'وضعیت احراز هویت دریافت شد.'
        );
    }

    public function profile(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        $service =
            new UserProfileService();

        $user =
            $service->findById($userId);

        if ($user === null) {
            $this->error(
                'کاربر پیدا نشد.',
                404
            );
        }

        $this->success([
            'user' => [
                'id' => (int)$user['id'],
                'first_name' => (string)$user['first_name'],
                'last_name' => (string)$user['last_name'],
                'email' => (string)$user['email'],
                'phone' => (string)$user['phone'],
                'status' => (string)$user['status'],
                'email_verified_at' =>
                $user['email_verified_at'],
                'phone_verified_at' =>
                $user['phone_verified_at'],
            ],
        ]);
    }

    public function updateProfile(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        $data =
            Request::isJson()
            ? Request::json()
            : $_POST;

        $firstName =
            trim(
                (string)($data['first_name'] ?? '')
            );

        $lastName =
            trim(
                (string)($data['last_name'] ?? '')
            );

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'] =
                'نام الزامی است.';
        }

        if ($lastName === '') {
            $errors['last_name'] =
                'نام خانوادگی الزامی است.';
        }

        if ($errors !== []) {
            $this->error(
                'اطلاعات پروفایل نامعتبر است.',
                422,
                $errors
            );
        }

        try {
            $user =
                (new UserProfileService())
                ->update(
                    $userId,
                    $firstName,
                    $lastName
                );
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        }

        if ($user === null) {
            $this->error(
                'کاربر پیدا نشد.',
                404
            );
        }

        $this->success([
            'user' => [
                'id' => (int)$user['id'],
                'first_name' =>
                (string)$user['first_name'],
                'last_name' =>
                (string)$user['last_name'],
                'email' =>
                (string)$user['email'],
                'phone' =>
                (string)$user['phone'],
            ],
        ], 'پروفایل با موفقیت بروزرسانی شد.');
    }

    public function logout(): never
    {
        Auth::logout();

        $this->success(
            null,
            'با موفقیت از حساب خارج شدید.'
        );
    }

    public function register(): never
    {
        $firstName =
            Request::post('first_name');

        $lastName =
            Request::post('last_name');

        $email =
            Request::post('email');

        $phone =
            Request::post('phone');

        $password =
            Request::post('password');

        $passwordConfirmation =
            Request::post('password_confirmation');

        $errors =
            AuthValidator::register(
                $firstName,
                $lastName,
                $email,
                $phone,
                $password,
                $passwordConfirmation
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات ثبت‌نام نامعتبر است.',
                422,
                $errors
            );
        }

        try {
            $user =
                (new AuthService())
                ->register(
                    trim((string)$firstName),
                    trim((string)$lastName),
                    trim((string)$email),
                    trim((string)$phone),
                    (string)$password
                );
        } catch (
            ValidationException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422,
                $exception->errors()
            );
        }

        $this->success(
            [
                'user_id' => (int)$user['id'],
            ],
            'ثبت‌نام با موفقیت انجام شد.'
        );
    }

    public function changePassword(): never
    {
        $currentPassword =
            Request::post('current_password');

        $newPassword =
            Request::post('new_password');

        $newPasswordConfirmation =
            Request::post(
                'new_password_confirmation'
            );

        $errors =
            AuthValidator::changePassword(
                $currentPassword,
                $newPassword,
                $newPasswordConfirmation
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات تغییر رمز عبور نامعتبر است.',
                422,
                $errors
            );
        }

        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        try {

            (new PasswordChangeService())
                ->change(
                    $userId,
                    (string)$currentPassword,
                    (string)$newPassword
                );
        } catch (
            AuthenticationException $exception
        ) {

            $this->error(
                $exception->getMessage(),
                401
            );
        } catch (
            ValidationException $exception
        ) {

            $this->error(
                $exception->getMessage(),
                422,
                $exception->errors()
            );
        }

        $this->success(
            null,
            'رمز عبور با موفقیت تغییر کرد.'
        );
    }

    public function forgotPassword(): never
    {
        $email =
            Request::post('email');

        $errors =
            AuthValidator::forgotPassword(
                $email
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات بازیابی رمز عبور نامعتبر است.',
                422,
                $errors
            );
        }

        try {

            $token =
                (new ForgotPasswordService())
                ->request(
                    trim((string)$email)
                );
        } catch (Throwable $exception) {

            Logger::error(
                'Forgot password request failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'درخواست بازیابی رمز عبور ثبت نشد.',
                500
            );
        }

        /*
     * Development response:
     * The raw token is returned only so the
     * password reset flow can be tested.
     *
     * This must be removed/replaced by email
     * delivery before production.
     */
        $data = [
            'reset_requested' => true,
        ];

        $this->success(
            $data,
            'اگر حسابی با این ایمیل وجود داشته باشد، لینک بازیابی ارسال خواهد شد.'
        );
    }

    public function resetPassword(): never
    {
        $token =
            Request::post('token');

        $newPassword =
            Request::post('new_password');

        $confirmation =
            Request::post(
                'new_password_confirmation'
            );

        $errors =
            AuthValidator::resetPassword(
                $token,
                $newPassword,
                $confirmation
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات بازیابی رمز عبور نامعتبر است.',
                422,
                $errors
            );
        }

        try {

            $userId =
                (new ResetPasswordService())
                ->reset(
                    trim((string)$token),
                    (string)$newPassword
                );
        } catch (
            AuthenticationException $exception
        ) {

            $this->error(
                $exception->getMessage(),
                401
            );
        } catch (Throwable $exception) {

            Logger::error(
                'Password reset failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'بازیابی رمز عبور انجام نشد.',
                500
            );
        }

        $this->success(
            [
                'user_id' => $userId,
            ],
            'رمز عبور با موفقیت تغییر کرد.'
        );
    }

    public function verifyEmail(): never
    {
        $token =
            Request::post('token');

        $errors =
            AuthValidator::verifyEmail(
                $token
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات تأیید ایمیل نامعتبر است.',
                422,
                $errors
            );
        }

        try {

            $userId =
                (new EmailVerificationService())
                ->verify(
                    trim((string)$token)
                );
        } catch (
            AuthenticationException $exception
        ) {

            $this->error(
                $exception->getMessage(),
                401
            );
        } catch (Throwable $exception) {

            Logger::error(
                'Email verification failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'تأیید ایمیل انجام نشد.',
                500
            );
        }

        $this->success(
            [
                'user_id' => $userId,
            ],
            'ایمیل با موفقیت تأیید شد.'
        );
    }

    public function resendEmailVerification(): never
    {
        $email = Request::post('email');

        $errors = AuthValidator::resendEmailVerification($email);

        if ($errors !== []) {
            $this->error(
                'اطلاعات ارسال مجدد تأیید ایمیل نامعتبر است.',
                422,
                $errors
            );
        }

        try {
            $token = (new ResendEmailVerificationService())
                ->resend(
                    trim((string)$email)
                );
        } catch (Throwable $exception) {
            Logger::error(
                'Resend email verification failed.',
                [
                    'exception' => get_class($exception),
                ]
            );

            $this->error(
                'ارسال مجدد تأیید ایمیل انجام نشد.',
                500
            );
        }

        $data = [
            'verification_requested' => true,
        ];

        /*
     * Development only.
     * Remove before production and replace with
     * real email delivery.
     */

        $this->success(
            $data,
            'اگر حسابی با این ایمیل وجود داشته باشد، لینک تأیید ارسال خواهد شد.'
        );
    }

    public function verificationStatus(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای مشاهده وضعیت تأیید باید وارد حساب شوید.',
                401
            );
        }

        try {
            $status =
                (new AccountVerificationService())
                ->status($userId);
        } catch (Throwable $exception) {
            Logger::error(
                'Account verification status failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'دریافت وضعیت تأیید حساب انجام نشد.',
                500
            );
        }

        $this->success(
            $status,
            'وضعیت تأیید حساب دریافت شد.'
        );
    }

    public function logoutOtherSessions(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        try {
            $count =
                Auth::logoutOtherSessions();
        } catch (Throwable $exception) {
            Logger::error(
                'Logout other sessions failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'خروج از نشست‌های دیگر انجام نشد.',
                500
            );
        }

        $this->success(
            [
                'revoked_sessions' => $count,
            ],
            'نشست‌های دیگر با موفقیت بسته شدند.'
        );
    }

    public function sessions(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای مشاهده نشست‌ها باید وارد حساب شوید.',
                401
            );
        }

        try {
            $sessions =
                (new UserSessionManagementService())
                ->listActiveExceptCurrent(
                    $userId,
                    Session::id()
                );
        } catch (Throwable $exception) {
            Logger::error(
                'User sessions listing failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'دریافت نشست‌های فعال انجام نشد.',
                500
            );
        }

        $result = [];

        foreach ($sessions as $session) {
            $result[] = [
                'id' => (int)$session['id'],
                'ip_address' =>
                (string)$session['ip_address'],
                'user_agent' =>
                (string)$session['user_agent'],
                'expires_at' =>
                (string)$session['expires_at'],
                'created_at' =>
                (string)$session['created_at'],
                'updated_at' =>
                (string)$session['updated_at'],
            ];
        }

        $this->success(
            $result,
            'نشست‌های فعال دریافت شدند.'
        );
    }

    public function revokeSession(): never
    {
        $userId =
            Auth::id();

        if ($userId === null) {
            $this->error(
                'برای ادامه باید وارد حساب شوید.',
                401
            );
        }

        $sessionId =
            Request::post('session_id');

        $errors =
            SessionValidator::sessionId(
                $sessionId
            );

        if ($errors !== []) {
            $this->error(
                'شناسه نشست نامعتبر است.',
                422,
                $errors
            );
        }

        $sessionId =
            (int)$sessionId;

        try {
            $service =
                new UserSessionManagementService();

            $session =
                $service->findByIdForUser(
                    $sessionId,
                    $userId
                );

            if ($session === null) {
                $this->error(
                    'نشست موردنظر پیدا نشد.',
                    404
                );
            }

            if (
                hash_equals(
                    (string)$session['session_id'],
                    Session::id()
                )
            ) {
                $this->error(
                    'برای خروج از نشست فعلی از گزینه خروج استفاده کنید.',
                    422
                );
            }

            $revoked =
                $service->revoke(
                    $sessionId,
                    $userId
                );

            if (!$revoked) {
                $this->error(
                    'بستن نشست انجام نشد.',
                    500
                );
            }
        } catch (Throwable $exception) {
            Logger::error(
                'User session revoke failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'بستن نشست انجام نشد.',
                500
            );
        }

        $this->success(
            [
                'revoked' => true,
            ],
            'نشست با موفقیت بسته شد.'
        );
    }

    public function securitySummary(): never
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->error(
                'برای مشاهده امنیت حساب باید وارد شوید.',
                401
            );
        }

        try {
            $summary =
                (new AccountSecurityService())
                ->summary(
                    $userId
                );
        } catch (Throwable $exception) {
            Logger::error(
                'Account security summary failed.',
                [
                    'exception' =>
                    get_class($exception),
                ]
            );

            $this->error(
                'دریافت وضعیت امنیت حساب انجام نشد.',
                500
            );
        }

        $this->success(
            $summary,
            'وضعیت امنیت حساب دریافت شد.'
        );
    }
}
