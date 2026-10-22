<?php

declare(strict_types=1);

final class PaymentRepository
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
    ): ?Payment {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM payments
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
            : Payment::fromArray($row);
    }

    public function findAllByOrder(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM payments
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
                Payment::fromArray($row);
        }

        return $items;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *
         FROM payments
         ORDER BY id DESC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] =
                Payment::fromArray($row);
        }

        return $items;
    }

    public function create(
        int $orderId,
        ?int $paymentAttemptId,
        ?int $userId,
        string $gateway,
        string $transactionId,
        string $amount,
        string $currency,
        string $paidAt,
        string $status = 'successful'
    ): Payment {
        $stmt = $this->pdo->prepare(
            'INSERT INTO payments (
                order_id,
                payment_attempt_id,
                user_id,
                gateway,
                transaction_id,
                amount,
                currency,
                status,
                paid_at
            ) VALUES (
                :order_id,
                :payment_attempt_id,
                :user_id,
                :gateway,
                :transaction_id,
                :amount,
                :currency,
                :status,
                :paid_at
            )'
        );

        $stmt->execute([
            'order_id' =>
            $orderId,
            'payment_attempt_id' =>
            $paymentAttemptId,
            'user_id' =>
            $userId,
            'gateway' =>
            trim($gateway),
            'transaction_id' =>
            trim($transactionId),
            'amount' =>
            $amount,
            'currency' =>
            $currency,
            'status' =>
            $status,
            'paid_at' =>
            $paidAt,
        ]);

        $id =
            (int)$this->pdo->lastInsertId();

        $payment =
            $this->findById($id);

        if ($payment === null) {
            throw new RuntimeException(
                'Payment could not be loaded after creation.'
            );
        }

        return $payment;
    }

    public function findSuccessfulByOrder(
        int $orderId
    ): ?Payment {
        if ($orderId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE order_id = :order_id
           AND status = \'successful\'
         ORDER BY id DESC
         LIMIT 1'
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Payment::fromArray($row);
    }

    public function findByTransactionId(
        string $transactionId
    ): ?Payment {
        $transactionId =
            trim($transactionId);

        if ($transactionId === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE transaction_id = :transaction_id
         LIMIT 1'
        );

        $stmt->execute([
            'transaction_id' =>
            $transactionId,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Payment::fromArray($row);
    }

    public function findByAttemptId(
        int $paymentAttemptId
    ): ?Payment {
        if ($paymentAttemptId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE payment_attempt_id = :payment_attempt_id
         ORDER BY id DESC
         LIMIT 1'
        );

        $stmt->execute([
            'payment_attempt_id' => $paymentAttemptId,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Payment::fromArray($row);
    }



    public function createFromSuccessfulAttempt(
        PaymentAttempt $attempt
    ): Payment {
        if ($attempt->id <= 0) {
            throw new InvalidArgumentException(
                'Payment attempt id is invalid.'
            );
        }

        if ($attempt->orderId <= 0) {
            throw new InvalidArgumentException(
                'Payment attempt has no valid order.'
            );
        }

        if ($attempt->status !== 'success') {
            throw new InvalidArgumentException(
                'Only successful payment attempts can be finalized.'
            );
        }

        $transactionId =
            trim(
                (string)$attempt->gatewayTransactionId
            );

        if ($transactionId === '') {
            throw new InvalidArgumentException(
                'Payment attempt transaction id is required.'
            );
        }

        $completedAt =
            trim((string)$attempt->completedAt);

        if ($completedAt === '') {
            throw new InvalidArgumentException(
                'Payment attempt completed at is required.'
            );
        }

        $date = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $completedAt
        );

        $errors = DateTime::getLastErrors();

        if (
            $date === false ||
            (
                $errors !== false &&
                (
                    $errors['warning_count'] > 0 ||
                    $errors['error_count'] > 0
                )
            ) ||
            $date->format('Y-m-d H:i:s') !== $completedAt
        ) {
            throw new InvalidArgumentException(
                'Invalid payment attempt completed at.'
            );
        }

        $transactionStarted = false;

        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $transactionStarted = true;
            }

            $stmt = $this->pdo->prepare(
                'SELECT *
             FROM payment_attempts
             WHERE id = :id
             LIMIT 1
             FOR UPDATE'
            );

            $stmt->execute([
                'id' => $attempt->id,
            ]);

            $row =
                $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                throw new RuntimeException(
                    'Payment attempt not found.'
                );
            }

            $lockedAttempt =
                PaymentAttempt::fromArray($row);

            if ($lockedAttempt->status !== 'success') {
                throw new RuntimeException(
                    'Payment attempt is not successful.'
                );
            }

            if (
                $lockedAttempt->orderId !==
                $attempt->orderId
            ) {
                throw new RuntimeException(
                    'Payment attempt order mismatch.'
                );
            }

            if (
                trim(
                    (string)$lockedAttempt->gatewayTransactionId
                ) !== $transactionId
            ) {
                throw new RuntimeException(
                    'Payment attempt transaction mismatch.'
                );
            }

            $existing = $this->findByAttemptId(
                $attempt->id
            );

            if ($existing !== null) {
                if ($existing->status !== 'successful') {
                    throw new RuntimeException(
                        'Payment has an invalid finalization status.'
                    );
                }

                if (
                    $existing->paymentAttemptId !==
                    $attempt->id
                ) {
                    throw new RuntimeException(
                        'Payment attempt relation mismatch.'
                    );
                }

                if (
                    $existing->orderId !==
                    $attempt->orderId
                ) {
                    throw new RuntimeException(
                        'Payment order mismatch.'
                    );
                }

                if (
                    $existing->userId !==
                    $attempt->userId
                ) {
                    throw new RuntimeException(
                        'Payment user mismatch.'
                    );
                }

                if (
                    $existing->gateway !==
                    $attempt->gateway
                ) {
                    throw new RuntimeException(
                        'Payment gateway mismatch.'
                    );
                }

                if (
                    $existing->amount !==
                    $attempt->amount
                ) {
                    throw new RuntimeException(
                        'Payment amount mismatch.'
                    );
                }

                if (
                    $existing->currency !==
                    $attempt->currency
                ) {
                    throw new RuntimeException(
                        'Payment currency mismatch.'
                    );
                }

                $existingTransactionId =
                    trim($existing->transactionId);

                $attemptTransactionId =
                    trim(
                        (string)$attempt->gatewayTransactionId
                    );

                if (
                    $existingTransactionId !==
                    $attemptTransactionId
                ) {
                    throw new RuntimeException(
                        'Payment transaction mismatch.'
                    );
                }

                return $existing;
            }

            if ($existing !== null) {
                if ($transactionStarted) {
                    $this->pdo->commit();
                }

                return $existing;
            }

            $existingTransaction =
                $this->findByTransactionId(
                    $transactionId
                );

            if ($existingTransaction !== null) {
                throw new RuntimeException(
                    'Payment transaction already exists.'
                );
            }

            $transactionId =
                trim(
                    (string)$attempt->gatewayTransactionId
                );

            if ($transactionId === '') {
                throw new InvalidArgumentException(
                    'Payment attempt transaction id is required.'
                );
            }

            $paymentStatus = 'successful';

            if ($paymentStatus !== 'successful') {
                throw new RuntimeException(
                    'Invalid payment finalization status.'
                );
            }

            $payment =
                $this->create(
                    $lockedAttempt->orderId,
                    $lockedAttempt->id,
                    $lockedAttempt->userId,
                    $lockedAttempt->gateway,
                    $transactionId,
                    $lockedAttempt->amount,
                    $lockedAttempt->currency,
                    $completedAt,
                    'successful'
                );

            if ($transactionStarted) {
                $this->pdo->commit();
            }

            return $payment;
        } catch (Throwable $exception) {
            if (
                $transactionStarted &&
                $this->pdo->inTransaction()
            ) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function markRefunded(
        int $paymentId,
        ?string $refundedAt = null,
        string $expectedCurrentStatus = 'successful'
    ): ?Payment {
        $refundedAt =
            $refundedAt ??
            date('Y-m-d H:i:s');

        $sql = "
        UPDATE payments
        SET
            status = 'refunded',
            refunded_at = :refunded_at
        WHERE id = :id
          AND status = :expected_current_status
    ";

        if ($paymentId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':refunded_at' =>
            $refundedAt,
            ':id' =>
            $paymentId,
            ':expected_current_status' =>
            $expectedCurrentStatus,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById(
            $paymentId
        );
    }

    public function markPartiallyRefunded(
        int $paymentId,
        ?string $refundedAt = null,
        string $expectedCurrentStatus = 'successful'
    ): ?Payment {
        $refundedAt =
            $refundedAt ??
            date('Y-m-d H:i:s');

        $sql = "
        UPDATE payments
        SET
            status = 'partially_refunded',
            refunded_at = :refunded_at
        WHERE id = :id
          AND status = :expected_current_status
    ";

        if ($paymentId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':refunded_at' =>
            $refundedAt,
            ':id' =>
            $paymentId,
            ':expected_current_status' =>
            $expectedCurrentStatus,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById(
            $paymentId
        );
    }

    public function findRefundedByOrder(
        int $orderId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE order_id = :order_id
           AND status = \'refunded\'
         ORDER BY id DESC'
        );

        if ($orderId <= 0) {
            return [];
        }

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $payments = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $payments[] = Payment::fromArray($row);
        }

        return $payments;
    }

    public function findPartiallyRefundedByOrder(
        int $orderId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE order_id = :order_id
           AND status = \'partially_refunded\'
         ORDER BY id DESC'
        );

        if ($orderId <= 0) {
            return [];
        }

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $payments = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $payments[] = Payment::fromArray($row);
        }

        return $payments;
    }

    public function findLatestRefundedByOrder(
        int $orderId
    ): ?Payment {
        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE order_id = :order_id
           AND status = \'refunded\'
         ORDER BY id DESC
         LIMIT 1'
        );

        if ($orderId <= 0) {
            return null;
        }

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Payment::fromArray($row);
    }

    public function findLatestPartiallyRefundedByOrder(
        int $orderId
    ): ?Payment {
        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payments
         WHERE order_id = :order_id
           AND status = \'partially_refunded\'
         ORDER BY id DESC
         LIMIT 1'
        );

        if ($orderId <= 0) {
            return null;
        }

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Payment::fromArray($row);
    }
}
