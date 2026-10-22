<?php

declare(strict_types=1);

final class WishlistItemRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(
        int $id
    ): ?WishlistItem {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                wishlist_id,
                variant_id,
                created_at
             FROM wishlist_items
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

    public function findByWishlist(
        int $wishlistId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                wishlist_id,
                variant_id,
                created_at
             FROM wishlist_items
             WHERE wishlist_id = :wishlist_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'wishlist_id' => $wishlistId,
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

    public function findByWishlistAndVariant(
        int $wishlistId,
        int $variantId
    ): ?WishlistItem {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                wishlist_id,
                variant_id,
                created_at
             FROM wishlist_items
             WHERE wishlist_id = :wishlist_id
               AND variant_id = :variant_id
             LIMIT 1'
        );

        $stmt->execute([
            'wishlist_id' => $wishlistId,
            'variant_id' => $variantId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM wishlist_items
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function existsByWishlistAndVariant(
        int $wishlistId,
        int $variantId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM wishlist_items
             WHERE wishlist_id = :wishlist_id
               AND variant_id = :variant_id
             LIMIT 1'
        );

        $stmt->execute([
            'wishlist_id' => $wishlistId,
            'variant_id' => $variantId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        int $wishlistId,
        int $variantId
    ): WishlistItem {
        if (
            $this->existsByWishlistAndVariant(
                $wishlistId,
                $variantId
            )
        ) {
            throw new RuntimeException(
                'این Variant قبلاً در Wishlist وجود دارد.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO wishlist_items (
                wishlist_id,
                variant_id
             ) VALUES (
                :wishlist_id,
                :variant_id
             )'
        );

        $stmt->execute([
            'wishlist_id' => $wishlistId,
            'variant_id' => $variantId,
        ]);

        $id = (int)$this->db->lastInsertId();

        $item = $this->findById($id);

        if ($item === null) {
            throw new RuntimeException(
                'WishlistItem ایجاد شد اما قابل بازیابی نیست.'
            );
        }

        return $item;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM wishlist_items
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteByWishlistAndVariant(
        int $wishlistId,
        int $variantId
    ): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM wishlist_items
             WHERE wishlist_id = :wishlist_id
               AND variant_id = :variant_id'
        );

        $stmt->execute([
            'wishlist_id' => $wishlistId,
            'variant_id' => $variantId,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(
        array $row
    ): WishlistItem {
        return new WishlistItem(
            (int)$row['id'],
            (int)$row['wishlist_id'],
            (int)$row['variant_id'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null
        );
    }
}
