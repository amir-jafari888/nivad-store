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

    public function __construct(
        ?AuditLogRepository $repository = null,
        ?AdminActorContext $actors = null
    ) {
        $this->repository = $repository ?? new AuditLogRepository();
        $this->actors = $actors ?? new AdminActorContext();
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

        return $this->repository->create([
            'admin_user_id' => $actorAdminId,
            'action' => mb_substr($action, 0, 100),
            'module' => mb_substr($module, 0, 100),
            'entity_type' => $entityType !== null ? mb_substr(trim($entityType), 0, 100) : null,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'description' => mb_substr($description, 0, 1000),
            'old_values' => $before === null ? null : SensitiveDataRedactor::encode($before),
            'new_values' => $after === null ? null : SensitiveDataRedactor::encode($after),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    private function redact(array $data): array
    {
        return SensitiveDataRedactor::redact($data);
    }
}
