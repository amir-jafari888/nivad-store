<?php

declare(strict_types=1);

final class InventoryMovementRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findById(
        int $id
    ): ?InventoryMovement {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                variant_id,
                movement_type,
                quantity,
                reference_type,
                reference_id,
                note,
                created_by,
                created_at
             FROM inventory_movements
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
    ): InventoryMovement {
        return new InventoryMovement(
            (int) $row['id'],
            (int) $row['variant_id'],
            (string) $row['movement_type'],
            (int) $row['quantity'],
            $row['reference_type'] !== null
                ? (string) $row['reference_type']
                : null,
            $row['reference_id'] !== null
                ? (int) $row['reference_id']
                : null,
            $row['note'] !== null
                ? (string) $row['note']
                : null,
            $row['created_by'] !== null
                ? (int) $row['created_by']
                : null,
            $row['created_at'] !== null
                ? (string) $row['created_at']
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
            movement_type,
            quantity,
            reference_type,
            reference_id,
            note,
            created_by,
            created_at
         FROM inventory_movements
         WHERE variant_id = ?
         ORDER BY id ASC'
        );

        $stmt->execute([$variantId]);

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        return array_map(
            fn(array $row): InventoryMovement =>
            $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $variantId,
        string $movementType,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $createdBy = null
    ): InventoryMovement {
        $stmt = $this->db->prepare(
            'INSERT INTO inventory_movements (
            variant_id,
            movement_type,
            quantity,
            reference_type,
            reference_id,
            note,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $variantId,
            $movementType,
            $quantity,
            $referenceType,
            $referenceId,
            $note,
            $createdBy,
        ]);

        $id = (int) $this->db->lastInsertId();

        $movement = $this->findById($id);

        if ($movement === null) {
            throw new RuntimeException(
                'Inventory movement could not be loaded after creation.'
            );
        }

        return $movement;
    }
}
