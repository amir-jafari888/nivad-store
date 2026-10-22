<?php

declare(strict_types=1);

final class ValidationResult
{
    private array $errors;

    public function __construct(
        array $errors = []
    ) {
        $this->errors = self::normalizeErrors(
            $errors
        );
    }

    public static function success(): self
    {
        return new self();
    }

    public static function failure(
        array $errors
    ): self {
        return new self($errors);
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function hasErrors(): bool
    {
        return !$this->isValid();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function has(
        string $field
    ): bool {
        return array_key_exists(
            $field,
            $this->errors
        );
    }

    public function first(
        ?string $field = null
    ): ?string {
        if ($field !== null) {
            $value = $this->errors[$field] ?? null;

            if (is_string($value)) {
                return $value;
            }

            if (
                is_array($value) &&
                isset($value[0]) &&
                is_string($value[0])
            ) {
                return $value[0];
            }

            return null;
        }

        foreach ($this->errors as $error) {
            if (is_string($error)) {
                return $error;
            }

            if (
                is_array($error) &&
                isset($error[0]) &&
                is_string($error[0])
            ) {
                return $error[0];
            }
        }

        return null;
    }

    public function count(): int
    {
        return count($this->errors);
    }

    private static function normalizeErrors(
        array $errors
    ): array {
        $normalized = [];

        foreach ($errors as $field => $messages) {
            $field = trim((string)$field);

            if ($field === '') {
                continue;
            }

            if (is_string($messages)) {
                $messages = trim($messages);

                if ($messages === '') {
                    continue;
                }

                $normalized[$field] = $messages;

                continue;
            }

            if (!is_array($messages)) {
                continue;
            }

            $fieldMessages = [];

            foreach ($messages as $message) {
                if (!is_string($message)) {
                    continue;
                }

                $message = trim($message);

                if ($message !== '') {
                    $fieldMessages[] = $message;
                }
            }

            if ($fieldMessages === []) {
                continue;
            }

            $normalized[$field] =
                count($fieldMessages) === 1
                ? $fieldMessages[0]
                : array_values(
                    array_unique(
                        $fieldMessages
                    )
                );
        }

        return $normalized;
    }
}
