<?php

declare(strict_types=1);

final class AuthorizationException extends AppException
{
    public function __construct(
        string $message = 'دسترسی مجاز نیست.'
    ) {
        parent::__construct(
            $message,
            403
        );
    }
}