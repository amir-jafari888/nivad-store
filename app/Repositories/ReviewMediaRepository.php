<?php

declare(strict_types=1);

final class ReviewMediaRepository
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
    ): ?ReviewMedia {
        if ($id < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                review_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                status,
                created_at,
                updated_at
             FROM review_media
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByReviewId(
        int $reviewId
    ): array {
        if ($reviewId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                review_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                status,
                created_at,
                updated_at
             FROM review_media
             WHERE review_id = :review_id
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'review_id' => $reviewId,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): ReviewMedia =>
            $this->hydrate($row),
            $rows
        );
    }

    public function findByStatus(
        string $status
    ): array {
        if (
            !in_array(
                $status,
                [
                    ReviewMedia::STATUS_PENDING,
                    ReviewMedia::STATUS_APPROVED,
                    ReviewMedia::STATUS_REJECTED,
                    ReviewMedia::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                review_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                status,
                created_at,
                updated_at
             FROM review_media
             WHERE status = :status
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'status' => $status,
        ]);

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row): ReviewMedia =>
            $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $reviewId,
        string $mediaType,
        string $filePath,
        ?string $thumbnailPath,
        ?string $altText,
        int $sortOrder,
        string $status
    ): ?ReviewMedia {
        if ($reviewId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO review_media
            (
                review_id,
                media_type,
                file_path,
                thumbnail_path,
                alt_text,
                sort_order,
                status,
                created_at
            )
            VALUES
            (
                :review_id,
                :media_type,
                :file_path,
                :thumbnail_path,
                :alt_text,
                :sort_order,
                :status,
                CURRENT_TIMESTAMP
            )'
        );

        $stmt->execute([
            'review_id' => $reviewId,
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
            'status' => trim($status),
        ]);

        return $this->findById(
            (int)$this->db->lastInsertId()
        );
    }

    public function updateStatus(
        int $id,
        string $status
    ): ?ReviewMedia {
        if ($id < 1) {
            return null;
        }

        if (
            !in_array(
                $status,
                [
                    ReviewMedia::STATUS_PENDING,
                    ReviewMedia::STATUS_APPROVED,
                    ReviewMedia::STATUS_REJECTED,
                    ReviewMedia::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE review_media
             SET status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
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
            'DELETE FROM review_media
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(
        array $row
    ): ReviewMedia {
        return new ReviewMedia(
            (int)$row['id'],
            (int)$row['review_id'],
            (string)$row['media_type'],
            (string)$row['file_path'],
            $row['thumbnail_path'] !== null
                ? (string)$row['thumbnail_path']
                : null,
            $row['alt_text'] !== null
                ? (string)$row['alt_text']
                : null,
            (int)$row['sort_order'],
            (string)$row['status'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null
        );
    }
}
