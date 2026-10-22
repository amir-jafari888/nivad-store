<?php

declare(strict_types=1);

final class MethodNotAllowedException extends AppException
{
    public function __construct(
        string $message = 'متد درخواست مجاز نیست.'
    ) {
        parent::__construct(
            $message,
            405
        );
    }
}
