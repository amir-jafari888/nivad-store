<?php

declare(strict_types=1);

final class DiscoveryProductRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findByTagId(int $tagId): array
    {
        if ($tagId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                product_id,
                discovery_tag_id,
                sort_order,
                created_at
             FROM product_discovery
             WHERE discovery_tag_id = :tag_id
             ORDER BY sort_order ASC, product_id ASC'
        );

        $stmt->execute([
            'tag_id' => $tagId,
        ]);

        $rows = $stmt->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function findByProductId(int $productId): array
    {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                product_id,
                discovery_tag_id,
                sort_order,
                created_at
             FROM product_discovery
             WHERE product_id = :product_id
             ORDER BY sort_order ASC, discovery_tag_id ASC'
        );

        $stmt->execute([
            'product_id' => $productId,
        ]);

        $rows = $stmt->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function findMapping(
        int $productId,
        int $tagId
    ): ?DiscoveryProduct {
        if ($productId < 1 || $tagId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                product_id,
                discovery_tag_id,
                sort_order,
                created_at
             FROM product_discovery
             WHERE product_id = :product_id
               AND discovery_tag_id = :tag_id
             LIMIT 1'
        );

        $stmt->execute([
            'product_id' => $productId,
            'tag_id' => $tagId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function create(
        int $productId,
        int $tagId,
        int $sortOrder = 0
    ): DiscoveryProduct {
        if ($productId < 1) {
            throw new InvalidArgumentException(
                'Invalid product id.'
            );
        }

        if ($tagId < 1) {
            throw new InvalidArgumentException(
                'Invalid discovery tag id.'
            );
        }

        if ($sortOrder < 0) {
            throw new InvalidArgumentException(
                'Invalid discovery product sort order.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO product_discovery (
                product_id,
                discovery_tag_id,
                sort_order
             ) VALUES (
                :product_id,
                :tag_id,
                :sort_order
             )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'tag_id' => $tagId,
            'sort_order' => $sortOrder,
        ]);

        $mapping = $this->findMapping(
            $productId,
            $tagId
        );

        if (!$mapping instanceof DiscoveryProduct) {
            throw new RuntimeException(
                'Discovery product mapping could not be loaded after creation.'
            );
        }

        return $mapping;
    }

    public function updateSortOrder(
        int $productId,
        int $tagId,
        int $sortOrder
    ): ?DiscoveryProduct {
        if (
            $productId < 1 ||
            $tagId < 1 ||
            $sortOrder < 0
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE product_discovery
             SET sort_order = :sort_order
             WHERE product_id = :product_id
               AND discovery_tag_id = :tag_id'
        );

        $stmt->execute([
            'product_id' => $productId,
            'tag_id' => $tagId,
            'sort_order' => $sortOrder,
        ]);

        return $this->findMapping(
            $productId,
            $tagId
        );
    }

    public function delete(
        int $productId,
        int $tagId
    ): bool {
        if ($productId < 1 || $tagId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM product_discovery
             WHERE product_id = :product_id
               AND discovery_tag_id = :tag_id'
        );

        $stmt->execute([
            'product_id' => $productId,
            'tag_id' => $tagId,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $data): DiscoveryProduct
    {
        return new DiscoveryProduct(
            (int)$data['product_id'],
            (int)$data['discovery_tag_id'],
            (int)$data['sort_order'],
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null
        );
    }

    public function findAvailableProductsByTagId(
        int $tagId,
        int $limit = 20,
        int $offset = 0
    ): array {
        if ($tagId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            'SELECT DISTINCT
        p.id,
        (
            SELECT pc.category_id
            FROM product_categories pc
            WHERE pc.product_id = p.id
            ORDER BY
                pc.is_primary DESC,
                pc.sort_order ASC,
                pc.category_id ASC
            LIMIT 1
        ) AS category_id,
        p.brand_id,
        p.name,
        p.slug,
        p.short_description,
        p.description,
        p.status,
        p.is_featured,
        p.created_at,
        p.updated_at,
        p.deleted_at
     FROM product_discovery pd
     INNER JOIN products p
        ON p.id = pd.product_id
       AND p.deleted_at IS NULL
       AND p.status = :product_status
     INNER JOIN product_variants pv
        ON pv.product_id = p.id
       AND pv.deleted_at IS NULL
       AND pv.status = :variant_status
     INNER JOIN inventory i
        ON i.variant_id = pv.id
       AND i.quantity > i.reserved_quantity
     WHERE pd.discovery_tag_id = :tag_id
     ORDER BY
        pd.sort_order ASC,
        pd.product_id ASC
     LIMIT :limit
     OFFSET :offset'
        );

        $stmt->bindValue(
            ':product_status',
            'active',
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':variant_status',
            'active',
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':tag_id',
            $tagId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $products = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = new Product(
                (int)$row['id'],
                (int)$row['category_id'],
                $row['brand_id'] !== null
                    ? (int)$row['brand_id']
                    : null,
                (string)$row['name'],
                (string)$row['slug'],
                $row['short_description'] !== null
                    ? (string)$row['short_description']
                    : null,
                $row['description'] !== null
                    ? (string)$row['description']
                    : null,
                (string)$row['status'],
                (bool)$row['is_featured'],
                $row['created_at'] !== null
                    ? (string)$row['created_at']
                    : null,
                $row['updated_at'] !== null
                    ? (string)$row['updated_at']
                    : null,
                $row['deleted_at'] !== null
                    ? (string)$row['deleted_at']
                    : null
            );
        }

        return $products;
    }
}
