<?php

declare(strict_types=1);

class AppException extends RuntimeException
{
    private int $statusCode;
    private array $errors;

    public function __construct(
        string $message = 'خطایی در برنامه رخ داده است.',
        int $statusCode = 500,
        array $errors = []
    ) {
        parent::__construct(
            $message,
            $statusCode
        );

        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
