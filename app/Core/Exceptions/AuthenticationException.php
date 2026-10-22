<?php

declare(strict_types=1);

final class AuthenticationException extends AppException
{
    public function __construct(
        string $message = 'اطلاعات احراز هویت صحیح نیست.'
    ) {
        parent::__construct(
            $message,
            401
        );
    }
}
