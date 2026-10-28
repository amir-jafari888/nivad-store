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

        $salesByPeriod = [];
        $salesByPeriodStatus = 'error';

        try {
            $salesByPeriod =
                $this->reportService->salesRevenueByPeriod($query);

            $salesByPeriodStatus =
                $salesByPeriod === [] ? 'empty' : 'ready';
        } catch (Throwable $exception) {
            // The dashboard remains available when this optional report source is unavailable.
            $salesByPeriodStatus = 'error';
        }

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

            'sales_by_period' => $salesByPeriod,
            'sales_by_period_status' => $salesByPeriodStatus,
        ];
    }
}
