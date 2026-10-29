<?php

declare(strict_types=1);

/**
 * Shared recursive redaction for audit snapshots, notification payloads and
 * operational metadata. Keep this policy centralized to avoid secret leaks
 * when new event producers are added.
 */
final class SensitiveDataRedactor
{
    private const SENSITIVE_KEY_PARTS = [
        'password',
        'token',
        'secret',
        'authorization',
        'cookie',
        'session_id',
        'api_key',
        'private_key',
        'card_number',
        'cvv',
        'credential',
        'otp',
        'recovery_code',
        'bank_account',
        'refresh_token',
        'access_token',
    ];

    private const MAX_STRING_BYTES = 20000;
    private const MAX_DEPTH = 12;

    public static function redact(array $data, int $depth = 0): array
    {
        if ($depth >= self::MAX_DEPTH) {
            return ['_redaction' => '[OMITTED: MAX DEPTH]'];
        }

        $safe = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            foreach (self::SENSITIVE_KEY_PARTS as $part) {
                if (str_contains($normalizedKey, $part)) {
                    $safe[$key] = '[REDACTED]';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $safe[$key] = self::redact($value, $depth + 1);
                continue;
            }

            if (is_string($value) && strlen($value) > self::MAX_STRING_BYTES) {
                $safe[$key] = '[OMITTED: VALUE TOO LARGE]';
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $safe[$key] = $value;
                continue;
            }

            $safe[$key] = '[OMITTED: UNSUPPORTED VALUE]';
        }

        return $safe;
    }

    public static function encode(array $data): string
    {
        try {
            return json_encode(
                self::redact($data),
                JSON_THROW_ON_ERROR |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                'Sensitive payload could not be encoded.',
                0,
                $exception
            );
        }
    }
}
