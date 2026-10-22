<?php

declare(strict_types=1);

final class InventoryRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findById(int $id): ?Inventory
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                variant_id,
                quantity,
                reserved_quantity,
                low_stock_threshold,
                updated_at
             FROM inventory
             WHERE id = ?
             LIMIT 1'
        );

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? $this->hydrate($row)
            : null;
    }

    public function findByVariant(int $variantId): ?Inventory
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                variant_id,
                quantity,
                reserved_quantity,
                low_stock_threshold,
                updated_at
             FROM inventory
             WHERE variant_id = ?
             LIMIT 1'
        );

        $stmt->execute([$variantId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? $this->hydrate($row)
            : null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM inventory
             WHERE id = ?
             LIMIT 1'
        );

        $stmt->execute([$id]);

        return (bool) $stmt->fetchColumn();
    }

    public function existsByVariant(int $variantId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM inventory
             WHERE variant_id = ?
             LIMIT 1'
        );

        $stmt->execute([$variantId]);

        return (bool) $stmt->fetchColumn();
    }

    private function hydrate(array $row): Inventory
    {
        return new Inventory(
            (int) $row['id'],
            (int) $row['variant_id'],
            (int) $row['quantity'],
            (int) $row['reserved_quantity'],
            (int) $row['low_stock_threshold'],
            $row['updated_at'] !== null
                ? (string) $row['updated_at']
                : null
        );
    }

    public function create(
        int $variantId,
        int $quantity = 0,
        int $reservedQuantity = 0,
        int $lowStockThreshold = 5
    ): Inventory {
        $stmt = $this->db->prepare(
            'INSERT INTO inventory (
            variant_id,
            quantity,
            reserved_quantity,
            low_stock_threshold
        ) VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $variantId,
            $quantity,
            $reservedQuantity,
            $lowStockThreshold,
        ]);

        $id = (int) $this->db->lastInsertId();

        $inventory = $this->findById($id);

        if ($inventory === null) {
            throw new RuntimeException(
                'Inventory could not be loaded after creation.'
            );
        }

        return $inventory;
    }

    public function reserveQuantity(
        int $variantId,
        int $quantity
    ): bool {
        if (
            $variantId <= 0 ||
            $quantity <= 0
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE inventory
         SET reserved_quantity =
             reserved_quantity + :quantity
         WHERE variant_id = :variant_id
           AND quantity - reserved_quantity >= :quantity'
        );

        $stmt->execute([
            'variant_id' => $variantId,
            'quantity' => $quantity,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function releaseQuantity(
        int $variantId,
        int $quantity
    ): bool {
        if (
            $variantId <= 0 ||
            $quantity <= 0
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE inventory
         SET reserved_quantity =
             reserved_quantity - :quantity
         WHERE variant_id = :variant_id
           AND reserved_quantity >= :quantity'
        );

        $stmt->execute([
            'variant_id' => $variantId,
            'quantity' => $quantity,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function update(
        int $inventoryId,
        int $quantity,
        int $reservedQuantity,
        int $lowStockThreshold
    ): ?Inventory {
        $stmt = $this->db->prepare(
            'UPDATE inventory
         SET
            quantity = ?,
            reserved_quantity = ?,
            low_stock_threshold = ?
         WHERE id = ?'
        );

        $stmt->execute([
            $quantity,
            $reservedQuantity,
            $lowStockThreshold,
            $inventoryId,
        ]);

        return $this->findById($inventoryId);
    }
}
