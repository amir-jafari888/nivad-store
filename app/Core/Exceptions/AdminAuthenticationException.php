<?php

declare(strict_types=1);

final class AdminAuthenticationException extends AppException
{
    public function __construct(
        string $message =
        'اطلاعات ورود مدیر صحیح نیست.'
    ) {
        parent::__construct(
            $message,
            401
        );
    }
}
