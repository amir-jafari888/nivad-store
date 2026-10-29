<?php

declare(strict_types=1);

/**
 * Shared audit writer for sensitive admin operations.
 * Call from the mutation service and use the same transaction/connection
 * where possible. Snapshots are centrally redacted before persistence.
 */
final class AdminActivityAuditService
{
    private AuditLogRepository $repository;
    private AdminActorContext $actors;
    private AdminAuditSnapshotService $snapshots;

    public function __construct(
        ?AuditLogRepository $repository = null,
        ?AdminActorContext $actors = null,
        ?AdminAuditSnapshotService $snapshots = null
    ) {
        $this->repository = $repository ?? new AuditLogRepository();
        $this->actors = $actors ?? new AdminActorContext();
        $this->snapshots = $snapshots ?? new AdminAuditSnapshotService();
    }

    public function record(
        string $action,
        string $module,
        string $description,
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?array $before = null,
        ?array $after = null,
        ?int $actorAdminId = null
    ): int {
        $action = trim($action);
        $module = trim($module);
        $description = trim($description);

        if ($action === '' || $module === '' || $description === '') {
            throw new InvalidArgumentException('Audit action, module and description are required.');
        }

        $actorAdminId = $actorAdminId === null
            ? $this->actors->currentAdminId()
            : $this->actors->requireCurrentAdmin($actorAdminId);

        $snapshot = $this->snapshots->compare($before, $after);

        return $this->repository->create([
            'admin_user_id' => $actorAdminId,
            'action' => mb_substr($action, 0, 100),
            'module' => mb_substr($module, 0, 100),
            'entity_type' => $entityType !== null ? mb_substr(trim($entityType), 0, 100) : null,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'description' => mb_substr($description, 0, 1000),
            'old_values' => $snapshot['before'] === null ? null : json_encode($snapshot['before'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'new_values' => $snapshot['after'] === null ? null : json_encode($snapshot['after'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    private function redact(array $data): array
    {
        return SensitiveDataRedactor::redact($data);
    }
}
