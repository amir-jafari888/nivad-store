<?php

declare(strict_types=1);

final class PaymentAttemptRepository
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
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM payment_attempts
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
            : PaymentAttempt::fromArray($row);
    }

    public function findByIdForUpdate(
        int $id
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM payment_attempts
         WHERE id = :id
         LIMIT 1
         FOR UPDATE'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : PaymentAttempt::fromArray($row);
    }

    public function findAllByOrder(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM payment_attempts
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
                PaymentAttempt::fromArray($row);
        }

        return $items;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *
         FROM payment_attempts
         ORDER BY id DESC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] =
                PaymentAttempt::fromArray($row);
        }

        return $items;
    }

    public function create(
        int $orderId,
        ?int $userId,
        string $gateway,
        ?string $gatewayTransactionId,
        ?string $authority,
        string $amount,
        string $currency = 'IRR',
        string $status = 'initiated',
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?string $ipAddress = null
    ): PaymentAttempt {
        $stmt = $this->pdo->prepare(
            'INSERT INTO payment_attempts (
                order_id,
                user_id,
                gateway,
                gateway_transaction_id,
                authority,
                amount,
                currency,
                status,
                error_code,
                error_message,
                ip_address
            ) VALUES (
                :order_id,
                :user_id,
                :gateway,
                :gateway_transaction_id,
                :authority,
                :amount,
                :currency,
                :status,
                :error_code,
                :error_message,
                :ip_address
            )'
        );

        $stmt->execute([
            'order_id' =>
            $orderId,
            'user_id' =>
            $userId,
            'gateway' =>
            trim($gateway),
            'gateway_transaction_id' =>
            $gatewayTransactionId,
            'authority' =>
            $authority,
            'amount' =>
            $amount,
            'currency' =>
            $currency,
            'status' =>
            $status,
            'error_code' =>
            $errorCode,
            'error_message' =>
            $errorMessage,
            'ip_address' =>
            $ipAddress,
        ]);

        $id =
            (int)$this->pdo->lastInsertId();

        $attempt =
            $this->findById($id);

        if ($attempt === null) {
            throw new RuntimeException(
                'Payment attempt could not be loaded after creation.'
            );
        }

        return $attempt;
    }

    public function markSuccess(
        int $id,
        string $expectedStatus = 'pending'
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE payment_attempts
     SET
        status = \'success\',
        completed_at =
            COALESCE(
                completed_at,
                CURRENT_TIMESTAMP
            )
     WHERE id = :id
       AND status = :expected_status
     LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'expected_status' => $expectedStatus,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById($id);
    }

    public function markFailed(
        int $id,
        string $errorCode,
        string $errorMessage,
        string $expectedStatus = 'pending'
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE payment_attempts
     SET
        status = \'failed\',
        error_code = :error_code,
        error_message = :error_message,
        completed_at =
            COALESCE(
                completed_at,
                CURRENT_TIMESTAMP
            )
     WHERE id = :id
       AND status = :expected_status
     LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'error_code' =>
            $errorCode,
            'error_message' =>
            $errorMessage,
            'expected_status' =>
            $expectedStatus,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById($id);
    }

    public function markCancelled(
        int $id,
        string $expectedStatus = 'pending'
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE payment_attempts
     SET
        status = 'cancelled',
        completed_at =
            COALESCE(
                completed_at,
                CURRENT_TIMESTAMP
            )
     WHERE id = :id
       AND status = :expected_status
     LIMIT 1"
        );

        $stmt->execute([
            'id' => $id,
            'expected_status' =>
            $expectedStatus,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById($id);
    }

    public function markExpired(
        int $id,
        string $expectedStatus = 'pending'
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE payment_attempts
     SET
        status = 'expired',
        completed_at =
            COALESCE(
                completed_at,
                CURRENT_TIMESTAMP
            )
     WHERE id = :id
       AND status = :expected_status
     LIMIT 1"
        );

        $stmt->execute([
            'id' => $id,
            'expected_status' =>
            $expectedStatus,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById($id);
    }

    public function markPending(
        int $id
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE payment_attempts
         SET status = 'pending'
         WHERE id = :id
           AND status = 'initiated'
         LIMIT 1"
        );

        $stmt->execute([
            'id' => $id,
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById($id);
    }

    public function findPendingByOrder(
        int $orderId
    ): array {
        $stmt = $this->pdo->prepare(
            '
        SELECT *
        FROM payment_attempts
        WHERE order_id = :order_id
          AND status = :status
        ORDER BY id DESC
        '
        );

        $stmt->execute([
            'order_id' => $orderId,
            'status' => 'pending',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $attempts = [];

        foreach ($rows as $row) {
            $attempts[] = PaymentAttempt::fromArray($row);
        }

        return $attempts;
    }

    public function findLatestByOrder(
        int $orderId
    ): ?PaymentAttempt {
        $stmt = $this->pdo->prepare(
            '
        SELECT *
        FROM payment_attempts
        WHERE order_id = :order_id
        ORDER BY id DESC
        LIMIT 1
        '
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return PaymentAttempt::fromArray($row);
    }

    public function updateGatewayData(
        int $id,
        ?string $gatewayTransactionId,
        ?string $authority,
        string $expectedStatus = 'pending'
    ): ?PaymentAttempt {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE payment_attempts
     SET
        gateway_transaction_id = :gateway_transaction_id,
        authority = :authority
     WHERE id = :id
       AND status = :expected_status
     LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'gateway_transaction_id' =>
            $gatewayTransactionId,
            'authority' =>
            $authority,
            'expected_status' =>
            $expectedStatus,
        ]);

        return $this->findById($id);
    }
}
