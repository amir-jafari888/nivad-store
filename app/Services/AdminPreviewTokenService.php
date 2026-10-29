<?php

declare(strict_types=1);

/**
 * Short-lived, scope-bound content preview tokens. No DB or network access.
 * Configure NIVAD_ADMIN_PREVIEW_SECRET outside source control (32+ bytes).
 * Tokens are time-limited and admin-bound, but are not single-use without a
 * DB-backed nonce/replay store.
 */
final class AdminPreviewTokenService
{
    public function issue(
        string $entityType,
        string $entityId,
        int $adminUserId,
        int $ttlSeconds = 900,
        ?int $now = null
    ): string {
        $entityType = $this->requiredIdentifier($entityType, 100, 'Entity type');
        $entityId = $this->requiredIdentifier($entityId, 100, 'Entity ID');

        if ($adminUserId < 1) {
            throw new InvalidArgumentException('A valid admin ID is required.');
        }

        if ($ttlSeconds < 60 || $ttlSeconds > 3600) {
            throw new InvalidArgumentException('Preview token lifetime must be between 60 and 3600 seconds.');
        }

        $secret = $this->secret();
        $issuedAt = $now ?? time();

        $payload = [
            'v' => 1,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'admin_id' => $adminUserId,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $ttlSeconds,
            'nonce' => bin2hex(random_bytes(16)),
        ];

        $encodedPayload = $this->base64UrlEncode(json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $encodedPayload, $secret, true));

        return $encodedPayload . '.' . $signature;
    }

    public function verify(
        string $token,
        int $expectedAdminId,
        ?string $expectedEntityType = null,
        ?string $expectedEntityId = null,
        ?int $now = null
    ): ?array {
        if ($expectedAdminId < 1 || strlen($token) > 4096) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }

        try {
            $secret = $this->secret();
        } catch (Throwable $exception) {
            return null;
        }

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $parts[0], $secret, true)
        );
        if (!hash_equals($expectedSignature, $parts[1])) {
            return null;
        }

        $decoded = $this->base64UrlDecode($parts[0]);
        if ($decoded === null) {
            return null;
        }

        try {
            $payload = json_decode($decoded, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return null;
        }

        if (
            !is_array($payload) ||
            ($payload['v'] ?? null) !== 1 ||
            !is_string($payload['entity_type'] ?? null) ||
            !is_string($payload['entity_id'] ?? null) ||
            !is_int($payload['admin_id'] ?? null) ||
            !is_int($payload['iat'] ?? null) ||
            !is_int($payload['exp'] ?? null) ||
            !is_string($payload['nonce'] ?? null)
        ) {
            return null;
        }

        $clock = $now ?? time();
        if (
            $payload['admin_id'] !== $expectedAdminId ||
            $payload['exp'] <= $clock ||
            $payload['iat'] > $clock + 60 ||
            $payload['exp'] - $payload['iat'] > 3600 ||
            $payload['exp'] <= $payload['iat']
        ) {
            return null;
        }

        if (
            $expectedEntityType !== null &&
            !hash_equals($expectedEntityType, $payload['entity_type'])
        ) {
            return null;
        }

        if (
            $expectedEntityId !== null &&
            !hash_equals($expectedEntityId, $payload['entity_id'])
        ) {
            return null;
        }

        return $payload;
    }

    private function secret(): string
    {
        $secret = getenv('NIVAD_ADMIN_PREVIEW_SECRET');

        if (!is_string($secret) || strlen($secret) < 32) {
            throw new RuntimeException('NIVAD_ADMIN_PREVIEW_SECRET must contain at least 32 bytes.');
        }

        return $secret;
    }

    private function requiredIdentifier(string $value, int $maxLength, string $label): string
    {
        $value = trim($value);

        if (
            $value === '' ||
            mb_strlen($value) > $maxLength ||
            preg_match('/^[A-Za-z0-9_.:-]+$/', $value) !== 1
        ) {
            throw new InvalidArgumentException($label . ' is invalid.');
        }

        return $value;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            return null;
        }

        $padding = (4 - (strlen($value) % 4)) % 4;
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', $padding), true);

        return is_string($decoded) ? $decoded : null;
    }
}
