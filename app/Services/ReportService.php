<?php

declare(strict_types=1);

final class ReportService
{
    private ReportValidator $validator;

    private ReportRepository $repository;

    public function __construct(
        ?ReportValidator $validator = null,
        ?ReportRepository $repository = null
    ) {
        $this->validator =
            $validator ?? new ReportValidator();

        $this->repository =
            $repository ?? new ReportRepository();
    }

    public function validateQuery(array $data): array
    {
        return $this->validator->validate($data);
    }

    public function createQuery(array $data): ReportQuery
    {
        $errors =
            $this->validateQuery($data);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode('; ', $errors)
            );
        }

        $currency =
            $this->validator->normalizeCurrency(
                isset($data['currency'])
                    ? (string)$data['currency']
                    : null
            );

        $status =
            isset($data['status'])
            ? trim((string)$data['status'])
            : null;

        $statuses =
            isset($data['statuses'])
            && is_array($data['statuses'])
            ? array_values(
                array_map(
                    static fn($value) =>
                    trim((string)$value),
                    $data['statuses']
                )
            )
            : [];

        return new ReportQuery(
            isset($data['from_date'])
                ? trim((string)$data['from_date'])
                : null,
            isset($data['to_date'])
                ? trim((string)$data['to_date'])
                : null,
            $currency,
            $status !== ''
                ? $status
                : null,
            $statuses,
            isset($data['group_by'])
                ? trim((string)$data['group_by'])
                : ReportContract::GROUP_DAY
        );
    }

    public function dataSources(): array
    {
        return ReportContract::DATA_SOURCES;
    }

    public function metrics(): array
    {
        return ReportContract::METRICS;
    }

    public function orderSummary(ReportQuery $query): array
    {
        return $this->repository
            ->orderSummary($query);
    }

    public function paymentSummary(ReportQuery $query): array
    {
        return $this->repository
            ->paymentSummary($query);
    }

    public function orderItemSummary(ReportQuery $query): array
    {
        return $this->repository
            ->orderItemSummary($query);
    }

    public function ordersByPeriod(ReportQuery $query): array
    {
        return $this->repository
            ->ordersByPeriod($query);
    }

    public function paymentsByPeriod(ReportQuery $query): array
    {
        return $this->repository
            ->paymentsByPeriod($query);
    }

    public function orderItemsByPeriod(ReportQuery $query): array
    {
        return $this->repository
            ->orderItemsByPeriod($query);
    }

    public function inventorySummary(ReportQuery $query): array
    {
        return $this->repository
            ->inventorySummary($query);
    }

    public function inventoryMovementSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->inventoryMovementSummary($query);
    }

    public function inventoryReservationSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->inventoryReservationSummary($query);
    }

    public function shipmentSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->shipmentSummary($query);
    }

    public function returnSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->returnSummary($query);
    }

    public function refundSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->refundSummary($query);
    }

    public function orderStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->orderStatusSummary($query);
    }

    public function paymentStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentStatusSummary($query);
    }

    public function shipmentStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->shipmentStatusSummary($query);
    }

    public function returnStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->returnStatusSummary($query);
    }

    public function refundStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->refundStatusSummary($query);
    }

    public function productPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->productPerformanceSummary($query);
    }

    public function productVariantPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->productVariantPerformanceSummary($query);
    }

    public function productPerformanceByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->productPerformanceByPeriod($query);
    }

    public function categoryPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->categoryPerformanceSummary($query);
    }

    public function brandPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->brandPerformanceSummary($query);
    }

    public function categoryPerformanceByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->categoryPerformanceByPeriod($query);
    }

    public function brandPerformanceByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->brandPerformanceByPeriod($query);
    }

    public function paymentAttemptSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentAttemptSummary($query);
    }

    public function paymentAttemptStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentAttemptStatusSummary($query);
    }

    public function paymentGatewaySummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentGatewaySummary($query);
    }

    public function paymentAttemptGatewaySummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentAttemptGatewaySummary($query);
    }

    public function paymentCurrencySummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentCurrencySummary($query);
    }

    public function paymentAttemptsByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentAttemptsByPeriod($query);
    }

    public function paymentGatewayByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->paymentGatewayByPeriod($query);
    }

    public function customerPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->customerPerformanceSummary($query);
    }

    public function customerOrderSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->customerOrderSummary($query);
    }

    public function customerPaymentSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->customerPaymentSummary($query);
    }

    public function customerReturnRefundSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->customerReturnRefundSummary($query);
    }

    public function customerPerformanceByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->customerPerformanceByPeriod($query);
    }

    public function customerStatusSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->customerStatusSummary($query);
    }

    public function salesRevenueSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->salesRevenueSummary($query);
    }

    public function salesRevenueByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->salesRevenueByPeriod($query);
    }

    public function salesRevenueByCurrency(
        ReportQuery $query
    ): array {
        return $this->repository
            ->salesRevenueByCurrency($query);
    }

    public function discountSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->discountSummary($query);
    }

    public function taxShippingSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->taxShippingSummary($query);
    }

    public function orderLifecycleSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->orderLifecycleSummary($query);
    }

    public function orderStatusPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->orderStatusPerformanceSummary($query);
    }

    public function orderStatusPerformanceByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->orderStatusPerformanceByPeriod($query);
    }

    public function shipmentFulfillmentSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->shipmentFulfillmentSummary($query);
    }

    public function shipmentStatusPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->shipmentStatusPerformanceSummary($query);
    }

    public function shipmentDeliveryByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->shipmentDeliveryByPeriod($query);
    }

    public function returnPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->returnPerformanceSummary($query);
    }

    public function refundPerformanceSummary(
        ReportQuery $query
    ): array {
        return $this->repository
            ->refundPerformanceSummary($query);
    }

    public function refundPerformanceByPeriod(
        ReportQuery $query
    ): array {
        return $this->repository
            ->refundPerformanceByPeriod($query);
    }

    public function inventoryStockSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryStockSummary($query);
    }

    public function inventoryLowStockSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryLowStockSummary($query);
    }

    public function inventoryOutOfStockSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryOutOfStockSummary($query);
    }

    public function inventoryReservedSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryReservedSummary($query);
    }

    public function inventoryAvailableSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryAvailableSummary($query);
    }

    public function inventoryByVariant(ReportQuery $query): array
    {
        return $this->repository->inventoryByVariant($query);
    }

    public function inventoryMovementTypeSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryMovementTypeSummary($query);
    }

    public function inventoryMovementByPeriod(ReportQuery $query): array
    {
        return $this->repository->inventoryMovementByPeriod($query);
    }

    public function inventoryReservationStatusSummary(ReportQuery $query): array
    {
        return $this->repository->inventoryReservationStatusSummary($query);
    }
}
