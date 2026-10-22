<?php

declare(strict_types=1);

final class AdminAuthController
extends Controller
{
    public function login(): never
    {
        $identifier =
            Request::post(
                'identifier'
            );

        $password =
            Request::post(
                'password'
            );

        $errors =
            AdminAuthValidator::login(
                $identifier,
                $password
            );

        if ($errors !== []) {
            $this->error(
                'اطلاعات ورود مدیر نامعتبر است.',
                422,
                $errors
            );
        }

        try {
            $admin =
                (new AdminAuthService())
                ->authenticate(
                    trim(
                        (string)$identifier
                    ),
                    (string)$password
                );
        } catch (
            AdminAuthenticationException
            $exception
        ) {
            $this->error(
                $exception->getMessage(),
                401
            );
        }

        $this->success(
            [
                'admin_user_id' =>
                (int)$admin['id'],
            ],
            'ورود مدیر با موفقیت انجام شد.'
        );
    }

    public function logout(): never
    {
        AdminAuth::logout();

        $this->success(
            null,
            'از پنل مدیریت خارج شدید.'
        );
    }

    public function me(): never
    {
        $service =
            new AdminAuthService();

        if (
            !$service->validateCurrentSession()
        ) {
            $this->error(
                'احراز هویت مدیر معتبر نیست.',
                401
            );
        }

        $admin =
            $service->currentSafe();

        if ($admin === null) {
            $this->error(
                'مدیر فعال پیدا نشد.',
                401
            );
        }

        $this->success(
            $admin,
            'اطلاعات مدیر دریافت شد.'
        );
    }

    public function status(): never
    {
        $service =
            new AdminAuthService();

        if (
            !$service->validateCurrentSession()
        ) {
            $this->success(
                [
                    'authenticated' => false,
                    'admin_user_id' => null,
                ],
                'مدیر احراز هویت نشده است.'
            );
        }

        $admin =
            $service->currentSafe();

        $this->success(
            [
                'authenticated' => true,
                'admin_user_id' =>
                AdminAuth::id(),
                'admin' => $admin,
            ],
            'وضعیت احراز هویت مدیر دریافت شد.'
        );
    }
}
