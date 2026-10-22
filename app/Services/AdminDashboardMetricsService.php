<?php

declare(strict_types=1);

final class AdminDashboardMetricsService
{
    private ReportService $reportService;

    public function __construct(
        ?ReportService $reportService = null
    ) {
        $this->reportService =
            $reportService ?? new ReportService();
    }

    public function metrics(
        array $queryData = []
    ): array {
        $query =
            $this->reportService->createQuery(
                $queryData
            );

        return [
            'orders' =>
            $this->reportService
                ->orderSummary($query),

            'payments' =>
            $this->reportService
                ->paymentSummary($query),

            'order_items' =>
            $this->reportService
                ->orderItemSummary($query),

            'inventory' =>
            $this->reportService
                ->inventorySummary($query),
        ];
    }
}
