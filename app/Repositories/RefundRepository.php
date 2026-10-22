<?php

declare(strict_types=1);

final class RefundRepository
{
    private PDO $pdo;

    public function __construct(
        ?PDO $pdo = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();
    }

    public function findById(
        int $id
    ): ?Refund {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM refunds
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Refund::fromArray($row);
    }

    public function findByRefundNumber(
        string $refundNumber
    ): ?Refund {
        $refundNumber =
            trim($refundNumber);

        if ($refundNumber === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM refunds
             WHERE refund_number = :refund_number
             LIMIT 1'
        );

        $stmt->execute([
            'refund_number' =>
            $refundNumber,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Refund::fromArray($row);
    }

    public function findByReturnId(
        int $returnId
    ): array {
        if ($returnId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM refunds
             WHERE return_id = :return_id
             ORDER BY id DESC'
        );

        $stmt->execute([
            'return_id' => $returnId,
        ]);

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                Refund::fromArray($row);
        }

        return $items;
    }

    public function findByOrderId(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM refunds
             WHERE order_id = :order_id
             ORDER BY id DESC'
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                Refund::fromArray($row);
        }

        return $items;
    }

    public function findByPaymentId(
        int $paymentId
    ): array {
        if ($paymentId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM refunds
             WHERE payment_id = :payment_id
             ORDER BY id DESC'
        );

        $stmt->execute([
            'payment_id' => $paymentId,
        ]);

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                Refund::fromArray($row);
        }

        return $items;
    }

    public function create(
        int $returnId,
        int $orderId,
        ?int $paymentId,
        string $refundNumber,
        string $amount,
        string $currency,
        string $status = Refund::STATUS_PENDING,
        ?string $gateway = null,
        ?string $gatewayRefundId = null,
        ?string $reason = null
    ): Refund {
        $stmt = $this->pdo->prepare(
            'INSERT INTO refunds (
                return_id,
                order_id,
                payment_id,
                refund_number,
                amount,
                currency,
                status,
                gateway,
                gateway_refund_id,
                reason
            ) VALUES (
                :return_id,
                :order_id,
                :payment_id,
                :refund_number,
                :amount,
                :currency,
                :status,
                :gateway,
                :gateway_refund_id,
                :reason
            )'
        );

        $stmt->execute([
            'return_id' =>
            $returnId,

            'order_id' =>
            $orderId,

            'payment_id' =>
            $paymentId,

            'refund_number' =>
            trim($refundNumber),

            'amount' =>
            $amount,

            'currency' =>
            $currency,

            'status' =>
            $status,

            'gateway' =>
            $gateway !== null
                ? trim($gateway)
                : null,

            'gateway_refund_id' =>
            $gatewayRefundId !== null
                ? trim($gatewayRefundId)
                : null,

            'reason' =>
            $reason,
        ]);

        $id =
            (int)$this->pdo->lastInsertId();

        $refund =
            $this->findById($id);

        if ($refund === null) {
            throw new RuntimeException(
                'Refund could not be loaded after creation.'
            );
        }

        return $refund;
    }

    public function updateStatus(
        int $id,
        string $status
    ): Refund {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Invalid refund id.'
            );
        }

        $stmt = $this->pdo->prepare(
            'UPDATE refunds
             SET status = :status,
                 processed_at =
                     CASE
                         WHEN :is_processing = 1
                         THEN COALESCE(
                             processed_at,
                             CURRENT_TIMESTAMP
                         )
                         WHEN :is_successful = 1
                         THEN COALESCE(
                             processed_at,
                             CURRENT_TIMESTAMP
                         )
                         ELSE processed_at
                     END,
                 completed_at =
                     CASE
                         WHEN :is_successful = 1
                         THEN COALESCE(
                             completed_at,
                             CURRENT_TIMESTAMP
                         )
                         ELSE completed_at
                     END
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'status' =>
            $status,

            'is_processing' =>
            $status === Refund::STATUS_PROCESSING
                ? 1
                : 0,

            'is_successful' =>
            $status === Refund::STATUS_SUCCESSFUL
                ? 1
                : 0,

            'id' =>
            $id,
        ]);

        $refund =
            $this->findById($id);

        if ($refund === null) {
            throw new RuntimeException(
                'Refund could not be loaded after status update.'
            );
        }

        return $refund;
    }
}
