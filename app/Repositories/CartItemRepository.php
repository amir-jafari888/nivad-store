<?php

declare(strict_types=1);

final class CartItemRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(
        int $id
    ): ?CartItem {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                cart_id,
                variant_id,
                quantity,
                unit_price,
                created_at,
                updated_at
             FROM cart_items
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findByCart(
        int $cartId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                cart_id,
                variant_id,
                quantity,
                unit_price,
                created_at,
                updated_at
             FROM cart_items
             WHERE cart_id = :cart_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'cart_id' => $cartId,
        ]);

        $items = [];

        foreach (
            $stmt->fetchAll(PDO::FETCH_ASSOC)
            as $row
        ) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function findByCartAndVariant(
        int $cartId,
        int $variantId
    ): ?CartItem {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                cart_id,
                variant_id,
                quantity,
                unit_price,
                created_at,
                updated_at
             FROM cart_items
             WHERE cart_id = :cart_id
               AND variant_id = :variant_id
             LIMIT 1'
        );

        $stmt->execute([
            'cart_id' => $cartId,
            'variant_id' => $variantId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function exists(
        int $id
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM cart_items
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function existsByCartAndVariant(
        int $cartId,
        int $variantId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM cart_items
             WHERE cart_id = :cart_id
               AND variant_id = :variant_id
             LIMIT 1'
        );

        $stmt->execute([
            'cart_id' => $cartId,
            'variant_id' => $variantId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        int $cartId,
        int $variantId,
        int $quantity,
        string $unitPrice
    ): CartItem {
        if (
            $this->existsByCartAndVariant(
                $cartId,
                $variantId
            )
        ) {
            throw new RuntimeException(
                'این Variant قبلاً در Cart وجود دارد.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO cart_items (
                cart_id,
                variant_id,
                quantity,
                unit_price
             ) VALUES (
                :cart_id,
                :variant_id,
                :quantity,
                :unit_price
             )'
        );

        $stmt->execute([
            'cart_id' => $cartId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        $id = (int)$this->db->lastInsertId();

        $item = $this->findById($id);

        if ($item === null) {
            throw new RuntimeException(
                'CartItem ایجاد شد اما قابل بازیابی نیست.'
            );
        }

        return $item;
    }

    public function update(
        int $id,
        int $quantity,
        string $unitPrice
    ): ?CartItem {
        $stmt = $this->db->prepare(
            'UPDATE cart_items
             SET
                quantity = :quantity,
                unit_price = :unit_price
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        return $this->findById($id);
    }

    public function delete(
        int $id
    ): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM cart_items
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(
        array $row
    ): CartItem {
        return new CartItem(
            (int)$row['id'],
            (int)$row['cart_id'],
            (int)$row['variant_id'],
            (int)$row['quantity'],
            (string)$row['unit_price'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null
        );
    }
}
