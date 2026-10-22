<?php

declare(strict_types=1);

final class NotFoundException extends AppException
{
    public function __construct(
        string $message = 'مورد موردنظر پیدا نشد.'
    ) {
        parent::__construct(
            $message,
            404
        );
    }
}
