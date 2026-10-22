<?php

declare(strict_types=1);

final class OrderStatusHistoryRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(
        int $id
    ): ?OrderStatusHistory {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM order_status_history
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $this->hydrate($row);
    }

    public function findAllByOrder(
        int $orderId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM order_status_history
             WHERE order_id = :order_id
             ORDER BY created_at ASC, id ASC'
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): OrderStatusHistory => $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $orderId,
        ?string $fromStatus,
        string $toStatus,
        ?int $changedByUserId = null,
        ?string $note = null
    ): OrderStatusHistory {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_status_history (
                order_id,
                from_status,
                to_status,
                changed_by_user_id,
                note
            ) VALUES (
                :order_id,
                :from_status,
                :to_status,
                :changed_by_user_id,
                :note
            )'
        );

        $stmt->execute([
            'order_id' => $orderId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $changedByUserId,
            'note' => $note,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        $history = $this->findById($id);

        if ($history === null) {
            throw new RuntimeException(
                'تاریخچه وضعیت سفارش ایجاد نشد.'
            );
        }

        return $history;
    }

    private function hydrate(
        array $row
    ): OrderStatusHistory {
        return new OrderStatusHistory(
            (int)$row['id'],
            (int)$row['order_id'],
            $row['from_status'] !== null
                ? (string)$row['from_status']
                : null,
            (string)$row['to_status'],
            $row['changed_by_user_id'] !== null
                ? (int)$row['changed_by_user_id']
                : null,
            $row['note'] !== null
                ? (string)$row['note']
                : null,
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null
        );
    }
}
