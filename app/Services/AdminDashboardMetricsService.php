<?php

declare(strict_types=1);

final class AdminDashboardMetricsService
{
    private ReportService $reportService;

    public function __construct(?ReportService $reportService = null)
    {
        $this->reportService = $reportService ?? new ReportService();
    }

    public function metrics(array $queryData = []): array
    {
        // KPI cards intentionally remain all-time. Periodic reporting uses separate queries.
        $allTimeData = $queryData;
        unset($allTimeData['from_date'], $allTimeData['to_date']);
        $allTimeQuery = $this->reportService->createQuery($allTimeData);

        $today = new DateTimeImmutable('today');
        $currentFrom = $today->modify('-13 days');
        $previousFrom = $today->modify('-27 days');
        $previousTo = $today->modify('-14 days');

        $periodBase = $queryData;
        $periodBase['group_by'] = ReportContract::GROUP_DAY;

        $currentData = $periodBase;
        $currentData['from_date'] = $currentFrom->format(ReportContract::PERIOD_DATE_FORMAT);
        $currentData['to_date'] = $today->format(ReportContract::PERIOD_DATE_FORMAT);

        $previousData = $periodBase;
        $previousData['from_date'] = $previousFrom->format(ReportContract::PERIOD_DATE_FORMAT);
        $previousData['to_date'] = $previousTo->format(ReportContract::PERIOD_DATE_FORMAT);

        $currentQuery = $this->reportService->createQuery($currentData);
        $previousQuery = $this->reportService->createQuery($previousData);

        $salesByPeriod = [];
        $salesByPeriodStatus = 'error';
        try {
            $rows = $this->reportService->salesRevenueByPeriod($currentQuery);
            $byDate = [];
            foreach ($rows as $row) {
                $date = (string)($row['period'] ?? '');
                if ($date !== '') {
                    $byDate[$date] = $row;
                }
            }

            // Fill dates without orders with zero values so the chart always represents 14 days.
            for ($date = $currentFrom; $date <= $today; $date = $date->modify('+1 day')) {
                $key = $date->format(ReportContract::PERIOD_DATE_FORMAT);
                $salesByPeriod[] = $byDate[$key] ?? [
                    'period' => $key,
                    'total_orders' => 0,
                    'gross_sales' => 0,
                    'total_discount' => 0,
                    'total_shipping' => 0,
                    'total_tax' => 0,
                    'net_sales' => 0,
                ];
            }
            $salesByPeriodStatus = 'ready';
        } catch (Throwable $exception) {
            // Optional chart data must not take down the dashboard.
            $salesByPeriodStatus = 'error';
        }

        $currentOrders = $this->reportService->orderSummary($currentQuery);
        $previousOrders = $this->reportService->orderSummary($previousQuery);
        $currentPayments = $this->reportService->paymentSummary($currentQuery);
        $previousPayments = $this->reportService->paymentSummary($previousQuery);
        $currentItems = $this->reportService->orderItemSummary($currentQuery);
        $previousItems = $this->reportService->orderItemSummary($previousQuery);

        return [
            'orders' => $this->reportService->orderSummary($allTimeQuery),
            'payments' => $this->reportService->paymentSummary($allTimeQuery),
            'order_items' => $this->reportService->orderItemSummary($allTimeQuery),
            'inventory' => $this->reportService->inventorySummary($allTimeQuery),
            'sales_by_period' => $salesByPeriod,
            'sales_by_period_status' => $salesByPeriodStatus,
            'period' => [
                'from' => $currentFrom->format(ReportContract::PERIOD_DATE_FORMAT),
                'to' => $today->format(ReportContract::PERIOD_DATE_FORMAT),
                'previous_from' => $previousFrom->format(ReportContract::PERIOD_DATE_FORMAT),
                'previous_to' => $previousTo->format(ReportContract::PERIOD_DATE_FORMAT),
            ],
            'period_comparison' => [
                'orders' => [
                    'current' => (int)($currentOrders['total_orders'] ?? 0),
                    'previous' => (int)($previousOrders['total_orders'] ?? 0),
                ],
                'payments' => [
                    'current' => (int)($currentPayments['total_payments'] ?? 0),
                    'previous' => (int)($previousPayments['total_payments'] ?? 0),
                ],
                'items' => [
                    'current' => (int)($currentItems['total_quantity'] ?? 0),
                    'previous' => (int)($previousItems['total_quantity'] ?? 0),
                ],
                'sales' => [
                    'current' => (float)($currentOrders['total_amount'] ?? 0),
                    'previous' => (float)($previousOrders['total_amount'] ?? 0),
                ],
            ],
        ];
    }
}
