<?php

declare(strict_types=1);

final class ReportController extends Controller
{
    private ReportService $service;

    /**
     * Only ReportService report methods are allowed here.
     * No arbitrary method dispatch is permitted.
     */
    private const REPORT_METHODS = [
        'order-summary' =>
        'orderSummary',

        'payment-summary' =>
        'paymentSummary',

        'order-item-summary' =>
        'orderItemSummary',

        'orders-by-period' =>
        'ordersByPeriod',

        'payments-by-period' =>
        'paymentsByPeriod',

        'order-items-by-period' =>
        'orderItemsByPeriod',

        'inventory-summary' =>
        'inventorySummary',

        'inventory-movement-summary' =>
        'inventoryMovementSummary',

        'inventory-reservation-summary' =>
        'inventoryReservationSummary',

        'shipment-summary' =>
        'shipmentSummary',

        'return-summary' =>
        'returnSummary',

        'refund-summary' =>
        'refundSummary',

        'order-status-summary' =>
        'orderStatusSummary',

        'payment-status-summary' =>
        'paymentStatusSummary',

        'shipment-status-summary' =>
        'shipmentStatusSummary',

        'return-status-summary' =>
        'returnStatusSummary',

        'refund-status-summary' =>
        'refundStatusSummary',

        'product-performance-summary' =>
        'productPerformanceSummary',

        'product-variant-performance-summary' =>
        'productVariantPerformanceSummary',

        'product-performance-by-period' =>
        'productPerformanceByPeriod',

        'category-performance-summary' =>
        'categoryPerformanceSummary',

        'brand-performance-summary' =>
        'brandPerformanceSummary',

        'category-performance-by-period' =>
        'categoryPerformanceByPeriod',

        'brand-performance-by-period' =>
        'brandPerformanceByPeriod',

        'payment-attempt-summary' =>
        'paymentAttemptSummary',

        'payment-attempt-status-summary' =>
        'paymentAttemptStatusSummary',

        'payment-gateway-summary' =>
        'paymentGatewaySummary',

        'payment-attempt-gateway-summary' =>
        'paymentAttemptGatewaySummary',

        'payment-currency-summary' =>
        'paymentCurrencySummary',

        'payment-attempts-by-period' =>
        'paymentAttemptsByPeriod',

        'payment-gateway-by-period' =>
        'paymentGatewayByPeriod',

        'customer-performance-summary' =>
        'customerPerformanceSummary',

        'customer-order-summary' =>
        'customerOrderSummary',

        'customer-payment-summary' =>
        'customerPaymentSummary',

        'customer-return-refund-summary' =>
        'customerReturnRefundSummary',

        'customer-performance-by-period' =>
        'customerPerformanceByPeriod',

        'customer-status-summary' =>
        'customerStatusSummary',

        'sales-revenue-summary' =>
        'salesRevenueSummary',

        'sales-revenue-by-period' =>
        'salesRevenueByPeriod',

        'sales-revenue-by-currency' =>
        'salesRevenueByCurrency',

        'discount-summary' =>
        'discountSummary',

        'tax-shipping-summary' =>
        'taxShippingSummary',

        'order-lifecycle-summary' =>
        'orderLifecycleSummary',

        'order-status-performance-summary' =>
        'orderStatusPerformanceSummary',

        'order-status-performance-by-period' =>
        'orderStatusPerformanceByPeriod',

        'shipment-fulfillment-summary' =>
        'shipmentFulfillmentSummary',

        'shipment-status-performance-summary' =>
        'shipmentStatusPerformanceSummary',

        'shipment-delivery-by-period' =>
        'shipmentDeliveryByPeriod',

        'return-performance-summary' =>
        'returnPerformanceSummary',

        'refund-performance-summary' =>
        'refundPerformanceSummary',

        'refund-performance-by-period' =>
        'refundPerformanceByPeriod',

        'inventory-stock-summary' =>
        'inventoryStockSummary',

        'inventory-low-stock-summary' =>
        'inventoryLowStockSummary',

        'inventory-out-of-stock-summary' =>
        'inventoryOutOfStockSummary',

        'inventory-reserved-summary' =>
        'inventoryReservedSummary',

        'inventory-available-summary' =>
        'inventoryAvailableSummary',

        'inventory-by-variant' =>
        'inventoryByVariant',

        'inventory-movement-type-summary' =>
        'inventoryMovementTypeSummary',

        'inventory-movement-by-period' =>
        'inventoryMovementByPeriod',

        'inventory-reservation-status-summary' =>
        'inventoryReservationStatusSummary',
    ];

    public function __construct(
        ?ReportService $service = null
    ) {
        $this->service =
            $service ?? new ReportService();
    }

    public function show(
        array $parameters = []
    ): never {
        $report =
            trim(
                (string)($parameters['report'] ?? '')
            );

        if (
            $report === ''
            || !isset(self::REPORT_METHODS[$report])
        ) {
            $this->error(
                'گزارش موردنظر معتبر نیست.',
                404
            );
        }

        $data = [
            'from_date' =>
            Request::query('from_date'),

            'to_date' =>
            Request::query('to_date'),

            'currency' =>
            Request::query('currency'),

            'status' =>
            Request::query('status'),

            'statuses' =>
            Request::query('statuses', []),

            'group_by' =>
            Request::query('group_by'),
        ];

        $data = array_filter(
            $data,
            static fn(mixed $value): bool =>
            $value !== null
        );

        try {
            $query =
                $this->service->createQuery($data);
        } catch (
            InvalidArgumentException $exception
        ) {
            $this->error(
                $exception->getMessage(),
                422
            );
        }

        $method =
            self::REPORT_METHODS[$report];

        $result =
            $this->service->{$method}($query);

        $this->success(
            [
                'report' => $report,
                'data' => $result,
            ],
            'گزارش با موفقیت دریافت شد.'
        );
    }
}
