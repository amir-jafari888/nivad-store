<?php

declare(strict_types=1);

final class ProductRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    private function productCategorySelect(): string
    {
        return "
            (
                SELECT pc.category_id
                FROM product_categories pc
                WHERE pc.product_id = p.id
                ORDER BY
                    pc.is_primary DESC,
                    pc.sort_order ASC,
                    pc.category_id ASC
                LIMIT 1
            ) AS category_id
        ";
    }

    public function findById(
        int $productId
    ): ?Product {
        if ($productId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                p.id,
                ' . $this->productCategorySelect() . ',
                p.brand_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.status,
                p.is_featured,
                p.meta_title,
                p.meta_description,
                p.created_at,
                p.updated_at,
                p.deleted_at
             FROM products p
             WHERE p.id = :id
               AND p.deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $productId,
        ]);

        $product = $stmt->fetch();

        return $product !== false
            ? $this->hydrate($product)
            : null;
    }

    public function findBySlug(
        string $slug
    ): ?Product {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                p.id,
                ' . $this->productCategorySelect() . ',
                p.brand_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.status,
                p.is_featured,
                p.meta_title,
                p.meta_description,
                p.created_at,
                p.updated_at,
                p.deleted_at
             FROM products p
             WHERE p.slug = :slug
               AND p.deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        $product = $stmt->fetch();

        return $product !== false
            ? $this->hydrate($product)
            : null;
    }

    public function exists(
        int $productId
    ): bool {
        if ($productId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
             FROM products
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $productId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findActiveById(
        int $productId
    ): ?Product {
        if ($productId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                p.id,
                ' . $this->productCategorySelect() . ',
                p.brand_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.status,
                p.is_featured,
                p.meta_title,
                p.meta_description,
                p.created_at,
                p.updated_at,
                p.deleted_at
             FROM products p
             WHERE p.id = :id
               AND p.status = :status
               AND p.deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $productId,
            'status' => 'active',
        ]);

        $product = $stmt->fetch();

        return $product !== false
            ? $this->hydrate($product)
            : null;
    }

    private function hydrate(array $data): Product
    {
        if (
            !isset($data['category_id'])
            || $data['category_id'] === null
        ) {
            throw new RuntimeException(
                'Product category relation could not be resolved.'
            );
        }

        return new Product(
            (int) $data['id'],
            (int) $data['category_id'],
            $data['brand_id'] !== null
                ? (int) $data['brand_id']
                : null,
            (string) $data['name'],
            (string) $data['slug'],
            $data['short_description'] !== null
                ? (string) $data['short_description']
                : null,
            $data['description'] !== null
                ? (string) $data['description']
                : null,
            (string) $data['status'],
            (bool) $data['is_featured'],
            $data['created_at'] !== null
                ? (string) $data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string) $data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string) $data['deleted_at']
                : null,
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null
        );
    }

    public function create(
        int $categoryId,
        ?int $brandId,
        string $name,
        string $slug,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured
    ): Product {
        if ($categoryId < 1) {
            throw new InvalidArgumentException(
                'Product category is required.'
            );
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO products (
                    brand_id,
                    name,
                    slug,
                    short_description,
                    description,
                    status,
                    is_featured
                ) VALUES (
                    :brand_id,
                    :name,
                    :slug,
                    :short_description,
                    :description,
                    :status,
                    :is_featured
                )'
            );

            $stmt->execute([
                'brand_id' => $brandId,
                'name' => $name,
                'slug' => $slug,
                'short_description' => $shortDescription,
                'description' => $description,
                'status' => $status,
                'is_featured' => $isFeatured ? 1 : 0,
            ]);

            $productId = (int) $this->db->lastInsertId();

            $categoryStmt = $this->db->prepare(
                'INSERT INTO product_categories (
                    product_id,
                    category_id,
                    is_primary,
                    sort_order
                ) VALUES (
                    :product_id,
                    :category_id,
                    1,
                    0
                )'
            );

            $categoryStmt->execute([
                'product_id' => $productId,
                'category_id' => $categoryId,
            ]);

            $product = $this->findById($productId);

            if (!$product instanceof Product) {
                throw new RuntimeException(
                    'Product could not be loaded after creation.'
                );
            }

            $this->db->commit();

            return $product;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function update(
        int $productId,
        int $categoryId,
        ?int $brandId,
        string $name,
        string $slug,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured
    ): ?Product {
        if ($productId < 1) {
            return null;
        }

        if ($categoryId < 1) {
            throw new InvalidArgumentException(
                'Product category is required.'
            );
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'UPDATE products
                 SET
                    brand_id = :brand_id,
                    name = :name,
                    slug = :slug,
                    short_description = :short_description,
                    description = :description,
                    status = :status,
                    is_featured = :is_featured,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                   AND deleted_at IS NULL'
            );

            $stmt->execute([
                'id' => $productId,
                'brand_id' => $brandId,
                'name' => $name,
                'slug' => $slug,
                'short_description' => $shortDescription,
                'description' => $description,
                'status' => $status,
                'is_featured' => $isFeatured ? 1 : 0,
            ]);

            if ($stmt->rowCount() === 0) {
                $existing = $this->findById($productId);

                $this->db->rollBack();

                return $existing;
            }

            $clearPrimaryStmt = $this->db->prepare(
                'UPDATE product_categories
                 SET is_primary = 0
                 WHERE product_id = :product_id'
            );

            $clearPrimaryStmt->execute([
                'product_id' => $productId,
            ]);

            $relationStmt = $this->db->prepare(
                'SELECT category_id
                 FROM product_categories
                 WHERE product_id = :product_id
                   AND category_id = :category_id
                 LIMIT 1'
            );

            $relationStmt->execute([
                'product_id' => $productId,
                'category_id' => $categoryId,
            ]);

            if ($relationStmt->fetchColumn() !== false) {
                $setPrimaryStmt = $this->db->prepare(
                    'UPDATE product_categories
                     SET
                        is_primary = 1,
                        sort_order = 0
                     WHERE product_id = :product_id
                       AND category_id = :category_id'
                );

                $setPrimaryStmt->execute([
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                ]);
            } else {
                $insertRelationStmt = $this->db->prepare(
                    'INSERT INTO product_categories (
                        product_id,
                        category_id,
                        is_primary,
                        sort_order
                    ) VALUES (
                        :product_id,
                        :category_id,
                        1,
                        0
                    )'
                );

                $insertRelationStmt->execute([
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                ]);
            }

            $product = $this->findById($productId);

            if ($product === null) {
                throw new RuntimeException(
                    'Product could not be loaded after update.'
                );
            }

            $this->db->commit();

            return $product;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function existsBySlug(
        string $slug
    ): bool {
        $slug = trim($slug);

        if ($slug === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id
             FROM products
             WHERE slug = :slug
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                p.id,
                ' . $this->productCategorySelect() . ',
                p.brand_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.status,
                p.is_featured,
                p.meta_title,
                p.meta_description,
                p.created_at,
                p.updated_at,
                p.deleted_at
             FROM products p
             WHERE p.deleted_at IS NULL
             ORDER BY p.id DESC'
        );

        $products = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT
                p.id,
                ' . $this->productCategorySelect() . ',
                p.brand_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.status,
                p.is_featured,
                p.meta_title,
                p.meta_description,
                p.created_at,
                p.updated_at,
                p.deleted_at
             FROM products p
             WHERE p.deleted_at IS NULL
               AND p.status = \'active\'
             ORDER BY p.id DESC'
        );

        $products = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    public function adminFindAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                brand_id,
                product_type_id,
                name,
                slug,
                sku,
                short_description,
                description,
                status,
                is_featured,
                sort_order,
                meta_title,
                meta_description,
                created_at,
                updated_at,
                deleted_at
             FROM products
             WHERE deleted_at IS NULL
             ORDER BY sort_order ASC, id DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adminFindById(
        int $productId
    ): ?array {
        if ($productId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                brand_id,
                product_type_id,
                name,
                slug,
                sku,
                short_description,
                description,
                status,
                is_featured,
                sort_order,
                meta_title,
                meta_description,
                created_at,
                updated_at,
                deleted_at
             FROM products
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $productId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false
            ? $row
            : null;
    }

    public function adminExistsBySlug(
        string $slug,
        ?int $exceptId = null
    ): bool {
        $slug = trim($slug);

        if ($slug === '') {
            return false;
        }

        $sql =
            'SELECT id
             FROM products
             WHERE slug = :slug
               AND deleted_at IS NULL';

        $params = [
            'slug' => $slug,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    public function adminExistsBySku(
        string $sku,
        ?int $exceptId = null
    ): bool {
        $sku = trim($sku);

        if ($sku === '') {
            return false;
        }

        $sql =
            'SELECT id
             FROM products
             WHERE sku = :sku
               AND deleted_at IS NULL';

        $params = [
            'sku' => $sku,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(
        int $productId
    ): bool {
        if ($productId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE products
             SET deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $productId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $productId
    ): bool {
        if ($productId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE products
             SET deleted_at = NULL
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $productId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setStatus(
        int $productId,
        string $status
    ): ?Product {
        if ($productId < 1) {
            return null;
        }

        $status = trim($status);

        if ($status === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE products
             SET
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $productId,
            'status' => $status,
        ]);

        return $this->findById($productId);
    }

    public function adminCreate(
        int $brandId,
        int $productTypeId,
        string $name,
        string $slug,
        string $sku,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured,
        int $sortOrder,
        ?string $metaTitle,
        ?string $metaDescription
    ): array {
        $stmt = $this->db->prepare(
            'INSERT INTO products (
                brand_id,
                product_type_id,
                name,
                slug,
                sku,
                short_description,
                description,
                status,
                is_featured,
                sort_order,
                meta_title,
                meta_description
            ) VALUES (
                :brand_id,
                :product_type_id,
                :name,
                :slug,
                :sku,
                :short_description,
                :description,
                :status,
                :is_featured,
                :sort_order,
                :meta_title,
                :meta_description
            )'
        );

        $stmt->execute([
            'brand_id' => $brandId,
            'product_type_id' => $productTypeId,
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'short_description' => $shortDescription,
            'description' => $description,
            'status' => $status,
            'is_featured' => $isFeatured ? 1 : 0,
            'sort_order' => $sortOrder,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ]);

        $productId = (int) $this->db->lastInsertId();

        $product = $this->adminFindById($productId);

        if ($product === null) {
            throw new RuntimeException(
                'Product could not be loaded after creation.'
            );
        }

        return $product;
    }

    public function adminUpdate(
        int $productId,
        int $brandId,
        int $productTypeId,
        string $name,
        string $slug,
        string $sku,
        ?string $shortDescription,
        ?string $description,
        string $status,
        bool $isFeatured,
        int $sortOrder,
        ?string $metaTitle,
        ?string $metaDescription
    ): ?array {
        if ($productId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE products
             SET
                brand_id = :brand_id,
                product_type_id = :product_type_id,
                name = :name,
                slug = :slug,
                sku = :sku,
                short_description = :short_description,
                description = :description,
                status = :status,
                is_featured = :is_featured,
                sort_order = :sort_order,
                meta_title = :meta_title,
                meta_description = :meta_description,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $productId,
            'brand_id' => $brandId,
            'product_type_id' => $productTypeId,
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'short_description' => $shortDescription,
            'description' => $description,
            'status' => $status,
            'is_featured' => $isFeatured ? 1 : 0,
            'sort_order' => $sortOrder,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ]);

        return $this->adminFindById($productId);
    }
}
