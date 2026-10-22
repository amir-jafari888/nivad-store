<?php

declare(strict_types=1);

function security_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function security_hash(string $value): string
{
    return hash('sha256', $value);
}

function security_verify(
    string $value,
    string $hash
): bool {
    return hash_equals(
        $hash,
        security_hash($value)
    );
}
