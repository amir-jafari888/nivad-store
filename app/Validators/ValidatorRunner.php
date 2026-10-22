<?php

declare(strict_types=1);

final class ValidatorRunner
{
    public static function run(
        ValidatorInterface $validator,
        array $data
    ): ValidationResult {
        return Validation::validateWith(
            $validator,
            $data
        );
    }

    public static function requireValid(
        ValidatorInterface $validator,
        array $data
    ): void {
        Validation::requireValid(
            $validator,
            $data
        );
    }
}
