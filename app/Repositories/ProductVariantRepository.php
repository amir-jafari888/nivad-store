<?php

declare(strict_types=1);

final class ProductVariantRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?ProductVariant
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function findBySku(string $sku): ?ProductVariant
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE sku = :sku
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'sku' => trim($sku),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function findByBarcode(string $barcode): ?ProductVariant
    {
        $barcode = trim($barcode);

        if ($barcode === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE barcode = :barcode
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'barcode' => $barcode,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM product_variants
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(int $id): ?ProductVariant
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'status' => 'active',
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    public function create(
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status = 'active',
        bool $isDefault = false,
        int $sortOrder = 0
    ): ?ProductVariant {
        if ($isDefault) {
            $this->clearDefaultForProduct($productId);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO product_variants
        (
            product_id,
            sku,
            barcode,
            price,
            compare_at_price,
            currency,
            status,
            is_default,
            sort_order,
            created_at
        )
        VALUES
        (
            :product_id,
            :sku,
            :barcode,
            :price,
            :compare_at_price,
            :currency,
            :status,
            :is_default,
            :sort_order,
            CURRENT_TIMESTAMP
        )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'sku' => trim($sku),
            'barcode' => $barcode !== null && trim($barcode) !== ''
                ? trim($barcode)
                : null,
            'price' => $price,
            'compare_at_price' => $compareAtPrice !== null
                ? $compareAtPrice
                : null,
            'currency' => strtoupper(trim($currency)),
            'status' => trim($status),
            'is_default' => $isDefault ? 1 : 0,
            'sort_order' => $sortOrder,
        ]);

        return $this->findById(
            (int)$this->pdo->lastInsertId()
        );
    }

    public function update(
        int $id,
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        bool $isDefault,
        int $sortOrder
    ): ?ProductVariant {
        if ($isDefault) {
            $this->clearDefaultForProduct(
                $productId,
                $id
            );
        }

        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET product_id = :product_id,
             sku = :sku,
             barcode = :barcode,
             price = :price,
             compare_at_price = :compare_at_price,
             currency = :currency,
             status = :status,
             is_default = :is_default,
             sort_order = :sort_order,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'product_id' => $productId,
            'sku' => trim($sku),
            'barcode' => $barcode !== null && trim($barcode) !== ''
                ? trim($barcode)
                : null,
            'price' => $price,
            'compare_at_price' => $compareAtPrice !== null
                ? $compareAtPrice
                : null,
            'currency' => strtoupper(trim($currency)),
            'status' => trim($status),
            'is_default' => $isDefault ? 1 : 0,
            'sort_order' => $sortOrder,
        ]);

        return $this->findById($id);
    }

    private function hydrate(array $row): ProductVariant
    {
        return new ProductVariant(
            (int)$row['id'],
            (int)$row['product_id'],
            (string)$row['sku'],
            $row['barcode'] !== null
                ? (string)$row['barcode']
                : null,
            (string)$row['price'],
            $row['compare_at_price'] !== null
                ? (string)$row['compare_at_price']
                : null,
            (string)$row['currency'],
            (string)$row['status'],
            (bool)$row['is_default'],
            (int)$row['sort_order'],
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

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
             SET deleted_at = CURRENT_TIMESTAMP,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
             SET deleted_at = NULL,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(
        int $id,
        string $status
    ): ?ProductVariant {
        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
             SET status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $this->findById($id);
    }

    public function setDefault(int $id): ?ProductVariant
    {
        $variant = $this->findById($id);

        if ($variant === null) {
            return null;
        }

        $this->clearDefaultForProduct(
            $variant->productId,
            $id
        );

        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
             SET is_default = 1,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $this->findById($id);
    }

    public function clearDefault(int $id): ?ProductVariant
    {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
             SET is_default = 0,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $this->findById($id);
    }

    /**
     * @return ProductVariant[]
     */
    public function findAllByProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE product_id = :product_id
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        $stmt->execute([
            'product_id' => $productId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductVariant =>
            $this->hydrate($row),
            $rows
        );
    }

    /**
     * @return ProductVariant[]
     */
    public function findActiveByProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE product_id = :product_id
               AND status = :status
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        $stmt->execute([
            'product_id' => $productId,
            'status' => 'active',
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductVariant =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findDefaultByProduct(
        int $productId
    ): ?ProductVariant {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM product_variants
             WHERE product_id = :product_id
               AND is_default = 1
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'product_id' => $productId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->hydrate($row);
    }

    private function clearDefaultForProduct(
        int $productId,
        ?int $exceptId = null
    ): void {
        if ($exceptId !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE product_variants
                 SET is_default = 0,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE product_id = :product_id
                   AND id <> :id
                   AND is_default = 1
                   AND deleted_at IS NULL'
            );

            $stmt->execute([
                'product_id' => $productId,
                'id' => $exceptId,
            ]);

            return;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
             SET is_default = 0,
                 updated_at = CURRENT_TIMESTAMP
             WHERE product_id = :product_id
               AND is_default = 1
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'product_id' => $productId,
        ]);
    }

    public function adminFindAllByProductIncludingDeleted(
        int $productId
    ): array {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT
            id,
            product_id,
            sku,
            barcode,
            price,
            compare_at_price,
            currency,
            status,
            is_default,
            sort_order,
            created_at,
            updated_at,
            deleted_at
         FROM product_variants
         WHERE product_id = :product_id
         ORDER BY deleted_at IS NULL DESC,
                  sort_order ASC,
                  id DESC'
        );

        $stmt->execute([
            'product_id' => $productId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adminFindById(
        int $id
    ): ?array {
        if ($id < 1) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
            id,
            product_id,
            sku,
            barcode,
            price,
            compare_at_price,
            currency,
            status,
            is_default,
            sort_order,
            created_at,
            updated_at,
            deleted_at
         FROM product_variants
         WHERE id = :id
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function adminExistsBySku(
        string $sku,
        ?int $exceptId = null
    ): bool {
        $sql = '
        SELECT id
        FROM product_variants
        WHERE sku = :sku
          AND deleted_at IS NULL
    ';

        $params = [
            'sku' => trim($sku),
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    public function adminExistsByBarcode(
        string $barcode,
        ?int $exceptId = null
    ): bool {
        $barcode = trim($barcode);

        if ($barcode === '') {
            return false;
        }

        $sql = '
        SELECT id
        FROM product_variants
        WHERE barcode = :barcode
          AND deleted_at IS NULL
    ';

        $params = [
            'barcode' => $barcode,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    public function adminCreate(
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        bool $isDefault,
        int $sortOrder
    ): array {
        if ($isDefault) {
            $clear = $this->pdo->prepare(
                'UPDATE product_variants
             SET is_default = 0,
                 updated_at = CURRENT_TIMESTAMP
             WHERE product_id = :product_id
               AND deleted_at IS NULL'
            );

            $clear->execute([
                'product_id' => $productId,
            ]);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO product_variants (
            product_id,
            sku,
            barcode,
            price,
            compare_at_price,
            currency,
            status,
            is_default,
            sort_order
        ) VALUES (
            :product_id,
            :sku,
            :barcode,
            :price,
            :compare_at_price,
            :currency,
            :status,
            :is_default,
            :sort_order
        )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'sku' => trim($sku),
            'barcode' => $barcode !== null && trim($barcode) !== ''
                ? trim($barcode)
                : null,
            'price' => $price,
            'compare_at_price' => $compareAtPrice,
            'currency' => strtoupper(trim($currency)),
            'status' => $status,
            'is_default' => $isDefault ? 1 : 0,
            'sort_order' => $sortOrder,
        ]);

        $variant = $this->adminFindById(
            (int)$this->pdo->lastInsertId()
        );

        if ($variant === null) {
            throw new RuntimeException(
                'Variant could not be loaded after creation.'
            );
        }

        return $variant;
    }

    public function adminUpdate(
        int $id,
        int $productId,
        string $sku,
        ?string $barcode,
        string $price,
        ?string $compareAtPrice,
        string $currency,
        string $status,
        bool $isDefault,
        int $sortOrder
    ): ?array {
        $variant = $this->adminFindById($id);

        if ($variant === null) {
            return null;
        }

        if ($isDefault) {
            $clear = $this->pdo->prepare(
                'UPDATE product_variants
             SET is_default = 0,
                 updated_at = CURRENT_TIMESTAMP
             WHERE product_id = :product_id
               AND id <> :id
               AND deleted_at IS NULL'
            );

            $clear->execute([
                'product_id' => $productId,
                'id' => $id,
            ]);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET
            product_id = :product_id,
            sku = :sku,
            barcode = :barcode,
            price = :price,
            compare_at_price = :compare_at_price,
            currency = :currency,
            status = :status,
            is_default = :is_default,
            sort_order = :sort_order,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'product_id' => $productId,
            'sku' => trim($sku),
            'barcode' => $barcode !== null && trim($barcode) !== ''
                ? trim($barcode)
                : null,
            'price' => $price,
            'compare_at_price' => $compareAtPrice,
            'currency' => strtoupper(trim($currency)),
            'status' => $status,
            'is_default' => $isDefault ? 1 : 0,
            'sort_order' => $sortOrder,
        ]);

        return $this->adminFindById($id);
    }

    public function adminDelete(
        int $id
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET deleted_at = CURRENT_TIMESTAMP,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function adminRestore(
        int $id
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET deleted_at = NULL,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function adminSetDefault(
        int $id
    ): ?array {
        $variant = $this->adminFindById($id);

        if (
            $variant === null ||
            $variant['deleted_at'] !== null
        ) {
            return null;
        }

        $clear = $this->pdo->prepare(
            'UPDATE product_variants
         SET is_default = 0,
             updated_at = CURRENT_TIMESTAMP
         WHERE product_id = :product_id
           AND id <> :id
           AND deleted_at IS NULL'
        );

        $clear->execute([
            'product_id' => (int)$variant['product_id'],
            'id' => $id,
        ]);

        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET is_default = 1,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $this->adminFindById($id);
    }

    public function adminClearDefault(
        int $id
    ): ?array {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET is_default = 0,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $this->adminFindById($id);
    }

    public function adminSetStatus(
        int $id,
        string $status
    ): ?array {
        $stmt = $this->pdo->prepare(
            'UPDATE product_variants
         SET status = :status,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $this->adminFindById($id);
    }
}
