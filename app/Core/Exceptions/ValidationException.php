<?php

declare(strict_types=1);

final class ValidationException extends AppException
{
    private ValidationResult $result;

    public function __construct(
        array $errors = [],
        string $message =
        'اطلاعات واردشده معتبر نیست.'
    ) {
        $this->result =
            ValidationResult::failure(
                $errors
            );

        parent::__construct(
            $message,
            422,
            $this->result->errors()
        );
    }

    public function result(): ValidationResult
    {
        return $this->result;
    }

    public function has(
        string $field
    ): bool {
        return $this->result->has(
            $field
        );
    }

    public function first(
        ?string $field = null
    ): ?string {
        return $this->result->first(
            $field
        );
    }

    public function count(): int
    {
        return $this->result->count();
    }
}
