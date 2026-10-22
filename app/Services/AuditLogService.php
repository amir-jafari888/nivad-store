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

    public function count(): int
    {
        return $this->repository->count();
    }
}
