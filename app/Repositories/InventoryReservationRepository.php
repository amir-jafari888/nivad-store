<?php

declare(strict_types=1);

final class InventoryReservationRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findById(
        int $id
    ): ?InventoryReservation {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                variant_id,
                cart_id,
                order_id,
                quantity,
                status,
                expires_at,
                released_at,
                converted_at,
                created_at,
                updated_at
             FROM inventory_reservations
             WHERE id = ?
             LIMIT 1'
        );

        $stmt->execute([$id]);

        $row = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $row
            ? $this->hydrate($row)
            : null;
    }

    private function hydrate(
        array $row
    ): InventoryReservation {
        return new InventoryReservation(
            (int) $row['id'],
            (int) $row['variant_id'],
            $row['cart_id'] !== null
                ? (int) $row['cart_id']
                : null,
            $row['order_id'] !== null
                ? (int) $row['order_id']
                : null,
            (int) $row['quantity'],
            (string) $row['status'],
            $row['expires_at'] !== null
                ? (string) $row['expires_at']
                : null,
            $row['released_at'] !== null
                ? (string) $row['released_at']
                : null,
            $row['converted_at'] !== null
                ? (string) $row['converted_at']
                : null,
            $row['created_at'] !== null
                ? (string) $row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string) $row['updated_at']
                : null
        );
    }

    public function findAllByVariant(
        int $variantId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
            id,
            variant_id,
            cart_id,
            order_id,
            quantity,
            status,
            expires_at,
            released_at,
            converted_at,
            created_at,
            updated_at
         FROM inventory_reservations
         WHERE variant_id = ?
         ORDER BY id ASC'
        );

        $stmt->execute([$variantId]);

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        return array_map(
            fn(array $row): InventoryReservation =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findAllByCart(
        int $cartId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
            id,
            variant_id,
            cart_id,
            order_id,
            quantity,
            status,
            expires_at,
            released_at,
            converted_at,
            created_at,
            updated_at
         FROM inventory_reservations
         WHERE cart_id = ?
         ORDER BY id ASC'
        );

        $stmt->execute([$cartId]);

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        return array_map(
            fn(array $row): InventoryReservation =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findAllByOrder(
        int $orderId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
            id,
            variant_id,
            cart_id,
            order_id,
            quantity,
            status,
            expires_at,
            released_at,
            converted_at,
            created_at,
            updated_at
         FROM inventory_reservations
         WHERE order_id = ?
         ORDER BY id ASC'
        );

        $stmt->execute([$orderId]);

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        return array_map(
            fn(array $row): InventoryReservation =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findAllActiveByCart(
        int $cartId
    ): array {
        $stmt =
            $this->db->prepare(
                'SELECT
            id,
            variant_id,
            cart_id,
            order_id,
            quantity,
            status,
            expires_at,
            released_at,
            converted_at,
            created_at,
            updated_at
         FROM inventory_reservations
         WHERE cart_id = ?
           AND status = ?
           AND (
                expires_at IS NULL
                OR expires_at > CURRENT_TIMESTAMP
           )
         ORDER BY id ASC'
            );

        $stmt->execute([
            $cartId,
            'active',
        ]);

        $rows =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        return array_map(
            fn(array $row): InventoryReservation =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findAllActiveByVariant(
        int $variantId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
            id,
            variant_id,
            cart_id,
            order_id,
            quantity,
            status,
            expires_at,
            released_at,
            converted_at,
            created_at,
            updated_at
         FROM inventory_reservations
         WHERE variant_id = ?
           AND status = ?
         ORDER BY id ASC'
        );

        $stmt->execute([
            $variantId,
            'active',
        ]);

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        return array_map(
            fn(array $row): InventoryReservation =>
            $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $variantId,
        ?int $cartId,
        ?int $orderId,
        int $quantity,
        string $status = 'active',
        ?string $expiresAt = null
    ): InventoryReservation {
        $stmt = $this->db->prepare(
            'INSERT INTO inventory_reservations (
            variant_id,
            cart_id,
            order_id,
            quantity,
            status,
            expires_at
        ) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $variantId,
            $cartId,
            $orderId,
            $quantity,
            $status,
            $expiresAt,
        ]);

        $id = (int) $this->db->lastInsertId();

        $reservation = $this->findById($id);

        if ($reservation === null) {
            throw new RuntimeException(
                'Inventory reservation could not be loaded after creation.'
            );
        }

        return $reservation;
    }

    public function attachToOrder(
        int $id,
        int $orderId
    ): ?InventoryReservation {
        if (
            $id <= 0 ||
            $orderId <= 0
        ) {
            return null;
        }

        $stmt =
            $this->db->prepare(
                'UPDATE inventory_reservations
             SET order_id = :order_id
             WHERE id = :id
               AND status = :status'
            );

        $stmt->execute([
            'order_id' => $orderId,
            'id' => $id,
            'status' => 'active',
        ]);

        return $this->findById($id);
    }

    public function releaseActive(
        int $id
    ): ?InventoryReservation {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE inventory_reservations
         SET status = :status,
             released_at = COALESCE(
                 released_at,
                 CURRENT_TIMESTAMP
             )
         WHERE id = :id
           AND status = :expected_status
         LIMIT 1'
        );

        $stmt->execute([
            'status' => 'released',
            'id' => $id,
            'expected_status' => 'active',
        ]);

        if ($stmt->rowCount() !== 1) {
            return null;
        }

        return $this->findById($id);
    }

    

    public function updateStatus(
        int $id,
        string $status
    ): ?InventoryReservation {
        $stmt = $this->db->prepare(
            'UPDATE inventory_reservations
         SET status = ?
         WHERE id = ?'
        );

        $stmt->execute([
            $status,
            $id,
        ]);

        return $this->findById($id);
    }
}
