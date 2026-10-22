<?php

declare(strict_types=1);

final class ProductMediaRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findById(
        int $id
    ): ?ProductMedia {
        if ($id < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                variant_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                is_primary,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM product_media
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function exists(
        int $id
    ): bool {
        if ($id < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM product_media
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function findAllByProduct(
        int $productId
    ): array {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                variant_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                is_primary,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM product_media
             WHERE product_id = :product_id
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'product_id' => $productId,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): ProductMedia =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findAllByVariant(
        int $variantId
    ): array {
        if ($variantId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                variant_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                is_primary,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM product_media
             WHERE variant_id = :variant_id
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'variant_id' => $variantId,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): ProductMedia =>
            $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $productId,
        ?int $variantId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath,
        ?string $altText,
        int $sortOrder,
        bool $isPrimary,
        string $status
    ): ?ProductMedia {
        $stmt = $this->db->prepare(
            'INSERT INTO product_media
            (
                product_id,
                variant_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                is_primary,
                status,
                created_at
            )
            VALUES
            (
                :product_id,
                :variant_id,
                :media_type,
                :file_path,
                :thumbnail_path,
                :alt_text,
                :sort_order,
                :is_primary,
                :status,
                CURRENT_TIMESTAMP
            )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'media_type' => trim($mediaType),
            'file_path' => trim($filePath),
            'thumbnail_path' =>
            $thumbnailPath !== null
                ? trim($thumbnailPath)
                : null,
            'alt_text' =>
            $altText !== null
                ? trim($altText)
                : null,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary ? 1 : 0,
            'status' => trim($status),
        ]);

        return $this->findById(
            (int)$this->db->lastInsertId()
        );
    }

    public function update(
        int $id,
        int $productId,
        ?int $variantId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath,
        ?string $altText,
        int $sortOrder,
        bool $isPrimary,
        string $status
    ): ?ProductMedia {
        if ($id < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE product_media
             SET product_id = :product_id,
                 variant_id = :variant_id,
                 media_type = :media_type,
                 file_path = :file_path,
                 thumbnail_path = :thumbnail_path,
                 alt_text = :alt_text,
                 sort_order = :sort_order,
                 is_primary = :is_primary,
                 status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'media_type' => trim($mediaType),
            'file_path' => trim($filePath),
            'thumbnail_path' =>
            $thumbnailPath !== null
                ? trim($thumbnailPath)
                : null,
            'alt_text' =>
            $altText !== null
                ? trim($altText)
                : null,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary ? 1 : 0,
            'status' => trim($status),
        ]);

        return $this->findById($id);
    }

    public function delete(
        int $id
    ): bool {
        if ($id < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE product_media
             SET deleted_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(
        array $data
    ): ProductMedia {
        return new ProductMedia(
            (int)$data['id'],
            (int)$data['product_id'],
            $data['variant_id'] !== null
                ? (int)$data['variant_id']
                : null,
            (string)$data['media_type'],
            (string)$data['file_path'],
            $data['thumbnail_path'] !== null
                ? (string)$data['thumbnail_path']
                : null,
            $data['alt_text'] !== null
                ? (string)$data['alt_text']
                : null,
            (int)$data['sort_order'],
            (bool)$data['is_primary'],
            (string)$data['status'],
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null,
            $data['updated_at'] !== null
                ? (string)$data['updated_at']
                : null,
            $data['deleted_at'] !== null
                ? (string)$data['deleted_at']
                : null
        );
    }
}
