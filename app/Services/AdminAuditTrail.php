<?php

declare(strict_types=1);

final class AdminAuditTrail
{
    public static function record(
        ?int $adminId,
        string $action,
        string $module,
        ?string $entityType = null,
        ?int $entityId = null,
        string $description = '',
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            (new AuditLogService())->record(
                $adminId,
                $action,
                $module,
                $entityType,
                $entityId,
                $description,
                $oldValues,
                $newValues
            );
        } catch (Throwable $exception) {
            Logger::error('Admin audit event could not be persisted.', [
                'action' => $action,
                'module' => $module,
                'exception_type' => get_class($exception),
            ]);
        }
    }
}
