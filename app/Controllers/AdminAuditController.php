<?php

declare(strict_types=1);

final class AdminAuditController extends Controller
{
    private AuditLogService $service;

    public function __construct(?AuditLogService $service = null)
    {
        $this->service = $service ?? new AuditLogService();
    }

    public function entityTimeline(array $parameters = []): never
    {
        try {
            $items = $this->service->entityTimeline(
                (string)($parameters['entityType'] ?? ''),
                (string)($parameters['entityId'] ?? ''),
                (int)Request::query('limit', 50),
                (int)Request::query('before_id', 0)
            );
            $this->success([
                'items' => $items,
                'next_before_id' => count($items) > 0 ? (int)$items[count($items) - 1]['id'] : null,
            ], 'تاریخچه فعالیت موجودیت دریافت شد.');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage(), 422);
        }
    }
}
