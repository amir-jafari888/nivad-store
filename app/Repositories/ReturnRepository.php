<?php

declare(strict_types=1);

final class ReturnRepository
{
    private PDO $pdo;

    public function __construct(
        ?PDO $pdo = null
    ) {
        $this->pdo =
            $pdo ?? Database::connection();
    }

    public function findAll(): array
    {
        $stmt =
            $this->pdo->query(
                'SELECT *
             FROM returns
             ORDER BY requested_at DESC, id DESC'
            );

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                ReturnRequest::fromArray($row);
        }

        return $items;
    }

    public function findById(
        int $id
    ): ?ReturnRequest {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM returns
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
            : ReturnRequest::fromArray($row);
    }

    public function findByReturnNumber(
        string $returnNumber
    ): ?ReturnRequest {
        $returnNumber =
            trim($returnNumber);

        if ($returnNumber === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM returns
             WHERE return_number = :return_number
             LIMIT 1'
        );

        $stmt->execute([
            'return_number' => $returnNumber,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : ReturnRequest::fromArray($row);
    }

    public function findByUserId(
        int $userId
    ): array {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM returns
             WHERE user_id = :user_id
             ORDER BY requested_at DESC, id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                ReturnRequest::fromArray($row);
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
             FROM returns
             WHERE order_id = :order_id
             ORDER BY requested_at DESC, id DESC'
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
                ReturnRequest::fromArray($row);
        }

        return $items;
    }

    public function findItems(
        int $returnId
    ): array {
        if ($returnId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM return_items
             WHERE return_id = :return_id
             ORDER BY id ASC'
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
                ReturnItem::fromArray($row);
        }

        return $items;
    }

    public function findStatusHistory(
        int $returnId
    ): array {
        if ($returnId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM return_status_history
             WHERE return_id = :return_id
             ORDER BY id ASC'
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
                ReturnStatusHistory::fromArray($row);
        }

        return $items;
    }

    public function create(
        int $orderId,
        int $userId,
        string $returnNumber,
        string $status,
        ?string $reason,
        ?string $customerNote,
        ?string $adminNote
    ): ReturnRequest {
        $stmt = $this->pdo->prepare(
            'INSERT INTO returns (
                order_id,
                user_id,
                return_number,
                status,
                reason,
                customer_note,
                admin_note
            ) VALUES (
                :order_id,
                :user_id,
                :return_number,
                :status,
                :reason,
                :customer_note,
                :admin_note
            )'
        );

        $stmt->execute([
            'order_id' =>
            $orderId,

            'user_id' =>
            $userId,

            'return_number' =>
            trim($returnNumber),

            'status' =>
            $status,

            'reason' =>
            $reason,

            'customer_note' =>
            $customerNote,

            'admin_note' =>
            $adminNote,
        ]);

        $id =
            (int)$this->pdo->lastInsertId();

        $return =
            $this->findById($id);

        if ($return === null) {
            throw new RuntimeException(
                'Return request could not be loaded after creation.'
            );
        }

        return $return;
    }

    public function createItem(
        int $returnId,
        int $orderItemId,
        int $quantity,
        ?string $reason,
        ?string $conditionNote
    ): ReturnItem {
        $stmt = $this->pdo->prepare(
            'INSERT INTO return_items (
                return_id,
                order_item_id,
                quantity,
                reason,
                condition_note
            ) VALUES (
                :return_id,
                :order_item_id,
                :quantity,
                :reason,
                :condition_note
            )'
        );

        $stmt->execute([
            'return_id' =>
            $returnId,

            'order_item_id' =>
            $orderItemId,

            'quantity' =>
            $quantity,

            'reason' =>
            $reason,

            'condition_note' =>
            $conditionNote,
        ]);

        $id =
            (int)$this->pdo->lastInsertId();

        $itemStmt = $this->pdo->prepare(
            'SELECT *
             FROM return_items
             WHERE id = :id
             LIMIT 1'
        );

        $itemStmt->execute([
            'id' => $id,
        ]);

        $row =
            $itemStmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                'Return item could not be loaded after creation.'
            );
        }

        return ReturnItem::fromArray($row);
    }

    public function createStatusHistory(
        int $returnId,
        ?string $fromStatus,
        string $toStatus,
        ?int $changedByAdminId,
        ?string $note
    ): ReturnStatusHistory {
        $stmt = $this->pdo->prepare(
            'INSERT INTO return_status_history (
                return_id,
                from_status,
                to_status,
                changed_by_admin_id,
                note
            ) VALUES (
                :return_id,
                :from_status,
                :to_status,
                :changed_by_admin_id,
                :note
            )'
        );

        $stmt->execute([
            'return_id' =>
            $returnId,

            'from_status' =>
            $fromStatus,

            'to_status' =>
            $toStatus,

            'changed_by_admin_id' =>
            $changedByAdminId,

            'note' =>
            $note,
        ]);

        $id =
            (int)$this->pdo->lastInsertId();

        $historyStmt = $this->pdo->prepare(
            'SELECT *
             FROM return_status_history
             WHERE id = :id
             LIMIT 1'
        );

        $historyStmt->execute([
            'id' => $id,
        ]);

        $row =
            $historyStmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                'Return status history could not be loaded after creation.'
            );
        }

        return ReturnStatusHistory::fromArray($row);
    }
}
