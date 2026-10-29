<?php

declare(strict_types=1);

/**
 * Shared audit writer for sensitive admin operations.
 *
 * Call this from the service that performs the mutation, using the same
 * transaction/connection where possible. Never pass passwords, session
 * tokens, payment secrets, or raw credentials into snapshots.
 */
final class AdminActivityAuditService
{
    private AuditLogRepository $repository;

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
    ];

    public function __construct(
        ?AuditLogRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new AuditLogRepository();
    }

    public function record(
        string $action,
        string $module,
        string $description,
        ?string $entityType = null,
        int|string|null $entityId = null,
        array|null $before = null,
        array|null $after = null,
        ?int $actorAdminId = null
    ): int {
        $action = trim($action);
        $module = trim($module);
        $description = trim($description);

        if ($action === '' || $module === '' || $description === '') {
            throw new InvalidArgumentException(
                'Audit action, module and description are required.'
            );
        }

        if ($actorAdminId === null) {
            $currentId = AdminAuth::id();
            $actorAdminId = is_int($currentId) && $currentId > 0
                ? $currentId
                : null;
        }

        return $this->repository->create([
            'admin_user_id' => $actorAdminId,
            'action' => mb_substr($action, 0, 100),
            'module' => mb_substr($module, 0, 100),
            'entity_type' => $entityType !== null
                ? mb_substr(trim($entityType), 0, 100)
                : null,
            'entity_id' => $entityId !== null
                ? (string) $entityId
                : null,
            'description' => mb_substr($description, 0, 1000),
            'old_values' => $this->encodeSnapshot($before),
            'new_values' => $this->encodeSnapshot($after),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    private function encodeSnapshot(?array $snapshot): ?string
    {
        if ($snapshot === null) {
            return null;
        }

        $safeSnapshot = $this->redact($snapshot);

        try {
            return json_encode(
                $safeSnapshot,
                JSON_THROW_ON_ERROR |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                'Audit snapshot could not be encoded.',
                0,
                $exception
            );
        }
    }

    private function redact(array $data): array
    {
        $safe = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $sensitive = false;

            foreach (self::SENSITIVE_KEY_PARTS as $part) {
                if (str_contains($normalizedKey, $part)) {
                    $sensitive = true;
                    break;
                }
            }

            if ($sensitive) {
                $safe[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $safe[$key] = $this->redact($value);
                continue;
            }

            if (
                is_string($value) &&
                strlen($value) > 20000
            ) {
                $safe[$key] = '[OMITTED: VALUE TOO LARGE]';
                continue;
            }

            if (
                is_scalar($value) ||
                $value === null
            ) {
                $safe[$key] = $value;
                continue;
            }

            $safe[$key] = '[OMITTED: UNSUPPORTED VALUE]';
        }

        return $safe;
    }
}
