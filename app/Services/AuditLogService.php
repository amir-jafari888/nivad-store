<?php

declare(strict_types=1);

final class AuditLogService
{
    private AuditLogRepository $repository;

    public function __construct(
        ?AuditLogRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new AuditLogRepository();
    }

    public function findAll(
        int $limit = 100
    ): array {
        return $this->repository
            ->findAll($limit);
    }

    public function record(
        ?int $adminUserId,
        string $action,
        string $module,
        ?string $entityType = null,
        ?int $entityId = null,
        string $description = '',
        ?array $oldValues = null,
        ?array $newValues = null
    ): int {
        if ($adminUserId !== null && $adminUserId < 1) {
            throw new InvalidArgumentException('Invalid audit actor ID.');
        }
        if ($action === '' || $module === '') {
            throw new InvalidArgumentException('Audit action and module are required.');
        }
        $oldJson = $oldValues !== null
            ? json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            : null;
        $newJson = $newValues !== null
            ? json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            : null;
        if (($oldJson !== null && strlen($oldJson) > 50000) || ($newJson !== null && strlen($newJson) > 50000)) {
            throw new InvalidArgumentException('Audit payload is too large.');
        }
        return $this->repository->create([
            'admin_user_id' => $adminUserId,
            'action' => mb_substr($action, 0, 100),
            'module' => mb_substr($module, 0, 100),
            'entity_type' => $entityType !== null ? mb_substr($entityType, 0, 100) : null,
            'entity_id' => $entityId,
            'description' => $description !== '' ? mb_substr($description, 0, 1000) : null,
            'old_values' => $oldJson,
            'new_values' => $newJson,
            'ip_address' => Security::clientIp(),
            'user_agent' => mb_substr(Request::userAgent(), 0, 500),
        ]);
    }

    public function count(): int
    {
        return $this->repository->count();
    }
}
