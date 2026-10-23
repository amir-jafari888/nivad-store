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

        $metrics = [
            'orders' =>
                $this->reportService->orderSummary($query),

            'payments' =>
                $this->reportService->paymentSummary($query),

            'order_items' =>
                $this->reportService->orderItemSummary($query),

            'inventory' =>
                $this->reportService->inventorySummary($query),
        ];

        /*
         * The trend uses the existing ReportQuery and
         * salesRevenueByPeriod contracts. It is deliberately
         * isolated so a trend-query failure does not hide the
         * four established dashboard summaries.
         */
        $today = new DateTimeImmutable('today');
        $trendFrom = $today->modify('-13 days');

        try {
            $trendQuery = $this->reportService->createQuery([
                'from_date' => $trendFrom->format(ReportContract::PERIOD_DATE_FORMAT),
                'to_date' => $today->format(ReportContract::PERIOD_DATE_FORMAT),
                'group_by' => ReportContract::GROUP_DAY,
            ]);

            $metrics['sales_by_period'] =
                $this->reportService->salesRevenueByPeriod(
                    $trendQuery
                );

            $metrics['sales_by_period_status'] = 'ready';
        } catch (Throwable $exception) {
            $metrics['sales_by_period'] = [];
            $metrics['sales_by_period_status'] = 'error';
        }

        return $metrics;
    }
}
