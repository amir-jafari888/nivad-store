<?php

declare(strict_types=1);

final class ReviewRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findById(int $id): ?Review
    {
        if ($id < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                user_id,
                order_item_id,
                rating,
                title,
                body,
                status,
                is_verified_purchase,
                admin_note,
                created_at,
                updated_at
             FROM reviews
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

    public function findByProductId(
        int $productId
    ): array {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
            id,
            product_id,
            user_id,
            order_item_id,
            rating,
            title,
            body,
            status,
            is_verified_purchase,
            admin_note,
            created_at,
            updated_at
         FROM reviews
         WHERE product_id = :product_id
           AND status = :status
         ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'product_id' => $productId,
            'status' => Review::STATUS_APPROVED,
        ]);

        return array_map(
            fn(array $row): Review => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findByUserId(
        int $userId
    ): array {
        if ($userId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                user_id,
                order_item_id,
                rating,
                title,
                body,
                status,
                is_verified_purchase,
                admin_note,
                created_at,
                updated_at
             FROM reviews
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return array_map(
            fn(array $row): Review => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findByOrderItemId(
        int $orderItemId
    ): ?Review {
        if ($orderItemId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                user_id,
                order_item_id,
                rating,
                title,
                body,
                status,
                is_verified_purchase,
                admin_note,
                created_at,
                updated_at
             FROM reviews
             WHERE order_item_id = :order_item_id
             LIMIT 1'
        );

        $stmt->execute([
            'order_item_id' => $orderItemId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findByStatus(
        string $status
    ): array {
        $status = trim($status);

        if ($status === '') {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                user_id,
                order_item_id,
                rating,
                title,
                body,
                status,
                is_verified_purchase,
                admin_note,
                created_at,
                updated_at
             FROM reviews
             WHERE status = :status
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'status' => $status,
        ]);

        return array_map(
            fn(array $row): Review => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                product_id,
                user_id,
                order_item_id,
                rating,
                title,
                body,
                status,
                is_verified_purchase,
                admin_note,
                created_at,
                updated_at
             FROM reviews
             ORDER BY created_at DESC, id DESC'
        );

        return array_map(
            fn(array $row): Review => $this->hydrate($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function create(
        int $productId,
        int $userId,
        ?int $orderItemId,
        int $rating,
        ?string $title,
        string $body,
        string $status = Review::STATUS_PENDING,
        bool $isVerifiedPurchase = false,
        ?string $adminNote = null
    ): Review {
        if ($productId < 1) {
            throw new InvalidArgumentException(
                'شناسه محصول نامعتبر است.'
            );
        }

        if ($userId < 1) {
            throw new InvalidArgumentException(
                'شناسه کاربر نامعتبر است.'
            );
        }

        if ($orderItemId !== null && $orderItemId < 1) {
            throw new InvalidArgumentException(
                'شناسه آیتم سفارش نامعتبر است.'
            );
        }

        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException(
                'امتیاز باید بین ۱ تا ۵ باشد.'
            );
        }

        $body = trim($body);

        if ($body === '') {
            throw new InvalidArgumentException(
                'متن نقد و بررسی الزامی است.'
            );
        }

        $title = $title !== null
            ? trim($title)
            : null;

        $adminNote = $adminNote !== null
            ? trim($adminNote)
            : null;

        if (
            !in_array(
                $status,
                [
                    Review::STATUS_PENDING,
                    Review::STATUS_APPROVED,
                    Review::STATUS_REJECTED,
                    Review::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'وضعیت نقد و بررسی نامعتبر است.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO reviews (
                product_id,
                user_id,
                order_item_id,
                rating,
                title,
                body,
                status,
                is_verified_purchase,
                admin_note
             ) VALUES (
                :product_id,
                :user_id,
                :order_item_id,
                :rating,
                :title,
                :body,
                :status,
                :is_verified_purchase,
                :admin_note
             )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'order_item_id' => $orderItemId,
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'status' => $status,
            'is_verified_purchase' => $isVerifiedPurchase ? 1 : 0,
            'admin_note' => $adminNote,
        ]);

        $id = (int)$this->db->lastInsertId();

        $review = $this->findById($id);

        if (!$review instanceof Review) {
            throw new RuntimeException(
                'نقد و بررسی پس از ایجاد قابل بارگذاری نیست.'
            );
        }

        return $review;
    }

    public function updateStatus(
        int $id,
        string $status
    ): ?Review {
        if ($id < 1) {
            return null;
        }

        $status = trim($status);

        if (
            !in_array(
                $status,
                [
                    Review::STATUS_PENDING,
                    Review::STATUS_APPROVED,
                    Review::STATUS_REJECTED,
                    Review::STATUS_HIDDEN,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'وضعیت نقد و بررسی نامعتبر است.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE reviews
             SET status = :status
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $this->findById($id);
    }

    public function updateAdminNote(
        int $id,
        ?string $adminNote
    ): ?Review {
        if ($id < 1) {
            return null;
        }

        $adminNote = $adminNote !== null
            ? trim($adminNote)
            : null;

        $stmt = $this->db->prepare(
            'UPDATE reviews
             SET admin_note = :admin_note
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'admin_note' => $adminNote,
        ]);

        return $this->findById($id);
    }

    private function hydrate(array $row): Review
    {
        return new Review(
            (int)$row['id'],
            (int)$row['product_id'],
            (int)$row['user_id'],
            $row['order_item_id'] !== null
                ? (int)$row['order_item_id']
                : null,
            (int)$row['rating'],
            $row['title'] !== null
                ? (string)$row['title']
                : null,
            (string)$row['body'],
            (string)$row['status'],
            (bool)$row['is_verified_purchase'],
            $row['admin_note'] !== null
                ? (string)$row['admin_note']
                : null,
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null
        );
    }
}
