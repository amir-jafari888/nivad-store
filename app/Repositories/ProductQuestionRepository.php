<?php

declare(strict_types=1);

final class ProductQuestionRepository
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
        int $questionId
    ): ?ProductQuestion {
        if ($questionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                product_id,
                user_id,
                question,
                status,
                admin_note,
                created_at,
                updated_at
             FROM product_questions
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $questionId,
        ]);

        $row = $stmt->fetch();

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
            question,
            status,
            admin_note,
            created_at,
            updated_at
         FROM product_questions
         WHERE product_id = :product_id
           AND status = :status
         ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'product_id' => $productId,
            'status' => ProductQuestion::STATUS_APPROVED,
        ]);

        return array_map(
            fn(array $row): ProductQuestion =>
            $this->hydrate($row),
            $stmt->fetchAll()
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
                question,
                status,
                admin_note,
                created_at,
                updated_at
             FROM product_questions
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return array_map(
            fn(array $row): ProductQuestion =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
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
                question,
                status,
                admin_note,
                created_at,
                updated_at
             FROM product_questions
             WHERE status = :status
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'status' => $status,
        ]);

        return array_map(
            fn(array $row): ProductQuestion =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                product_id,
                user_id,
                question,
                status,
                admin_note,
                created_at,
                updated_at
             FROM product_questions
             ORDER BY created_at DESC, id DESC'
        );

        return array_map(
            fn(array $row): ProductQuestion =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function create(
        int $productId,
        int $userId,
        string $question,
        string $status = ProductQuestion::STATUS_PENDING,
        ?string $adminNote = null
    ): ProductQuestion {
        if ($productId < 1) {
            throw new InvalidArgumentException(
                'Invalid product ID.'
            );
        }

        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $question = trim($question);

        if ($question === '') {
            throw new InvalidArgumentException(
                'Question cannot be empty.'
            );
        }

        $allowedStatuses = [
            ProductQuestion::STATUS_PENDING,
            ProductQuestion::STATUS_APPROVED,
            ProductQuestion::STATUS_REJECTED,
            ProductQuestion::STATUS_HIDDEN,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid question status.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO product_questions (
                product_id,
                user_id,
                question,
                status,
                admin_note
             ) VALUES (
                :product_id,
                :user_id,
                :question,
                :status,
                :admin_note
             )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'question' => $question,
            'status' => $status,
            'admin_note' => $adminNote,
        ]);

        $questionId = (int) $this->db->lastInsertId();

        $result = $this->findById($questionId);

        if (!$result instanceof ProductQuestion) {
            throw new RuntimeException(
                'Product question could not be loaded after creation.'
            );
        }

        return $result;
    }

    public function updateStatus(
        int $questionId,
        string $status
    ): ?ProductQuestion {
        if ($questionId < 1) {
            return null;
        }

        $allowedStatuses = [
            ProductQuestion::STATUS_PENDING,
            ProductQuestion::STATUS_APPROVED,
            ProductQuestion::STATUS_REJECTED,
            ProductQuestion::STATUS_HIDDEN,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid question status.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE product_questions
             SET
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $questionId,
            'status' => $status,
        ]);

        return $this->findById($questionId);
    }

    public function updateAdminNote(
        int $questionId,
        ?string $adminNote
    ): ?ProductQuestion {
        if ($questionId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE product_questions
             SET
                admin_note = :admin_note,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $questionId,
            'admin_note' => $adminNote,
        ]);

        return $this->findById($questionId);
    }

    private function hydrate(
        array $row
    ): ProductQuestion {
        return new ProductQuestion(
            (int) $row['id'],
            (int) $row['product_id'],
            (int) $row['user_id'],
            (string) $row['question'],
            (string) $row['status'],
            $row['admin_note'] !== null
                ? (string) $row['admin_note']
                : null,
            $row['created_at'] !== null
                ? (string) $row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string) $row['updated_at']
                : null
        );
    }
}
