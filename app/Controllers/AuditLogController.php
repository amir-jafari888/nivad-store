<?php

declare(strict_types=1);

final class AuditLogController extends Controller
{
    private AuditLogService $service;

    public function __construct(
        ?AuditLogService $service = null
    ) {
        $this->service =
            $service ??
            new AuditLogService();
    }

    public function index(): never
    {
        $limit =
            isset($_GET['limit'])
            ? (int)$_GET['limit']
            : 100;

        $this->success(
            [
                'items' =>
                $this->service
                    ->findAll($limit),
                'count' =>
                $this->service->count(),
            ],
            'لاگ‌های حسابرسی دریافت شدند.'
        );
    }
}
