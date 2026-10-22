<?php

declare(strict_types=1);

final class SessionValidator
{
    public static function sessionId(
        mixed $id
    ): array {
        $errors = [];

        if (
            filter_var(
                $id,
                FILTER_VALIDATE_INT
            ) === false
        ) {
            $errors['session_id'] =
                'شناسه نشست نامعتبر است.';

            return $errors;
        }

        $id = (int)$id;

        if ($id < 1) {
            $errors['session_id'] =
                'شناسه نشست نامعتبر است.';
        }

        return $errors;
    }
}
