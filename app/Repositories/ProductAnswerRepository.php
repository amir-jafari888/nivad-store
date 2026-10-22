<?php

declare(strict_types=1);

final class ProductAnswerRepository
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
        int $answerId
    ): ?ProductAnswer {
        if ($answerId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                question_id,
                admin_user_id,
                answer,
                status,
                created_at,
                updated_at
             FROM product_answers
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $answerId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findByQuestionId(
        int $questionId
    ): array {
        if ($questionId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
            id,
            question_id,
            admin_user_id,
            answer,
            status,
            created_at,
            updated_at
         FROM product_answers
         WHERE question_id = :question_id
           AND status = :status
         ORDER BY created_at ASC, id ASC'
        );

        $stmt->execute([
            'question_id' => $questionId,
            'status' => ProductAnswer::STATUS_APPROVED,
        ]);

        return array_map(
            fn(array $row): ProductAnswer =>
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
                question_id,
                admin_user_id,
                answer,
                status,
                created_at,
                updated_at
             FROM product_answers
             WHERE status = :status
             ORDER BY created_at DESC, id DESC'
        );

        $stmt->execute([
            'status' => $status,
        ]);

        return array_map(
            fn(array $row): ProductAnswer =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                question_id,
                admin_user_id,
                answer,
                status,
                created_at,
                updated_at
             FROM product_answers
             ORDER BY created_at DESC, id DESC'
        );

        return array_map(
            fn(array $row): ProductAnswer =>
            $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function create(
        int $questionId,
        ?int $adminUserId,
        string $answer,
        string $status = ProductAnswer::STATUS_PENDING
    ): ProductAnswer {
        if ($questionId < 1) {
            throw new InvalidArgumentException(
                'Invalid question ID.'
            );
        }

        if (
            $adminUserId !== null &&
            $adminUserId < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid admin user ID.'
            );
        }

        $answer = trim($answer);

        if ($answer === '') {
            throw new InvalidArgumentException(
                'Answer cannot be empty.'
            );
        }

        $allowedStatuses = [
            ProductAnswer::STATUS_PENDING,
            ProductAnswer::STATUS_APPROVED,
            ProductAnswer::STATUS_REJECTED,
            ProductAnswer::STATUS_HIDDEN,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid answer status.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO product_answers (
                question_id,
                admin_user_id,
                answer,
                status
             ) VALUES (
                :question_id,
                :admin_user_id,
                :answer,
                :status
             )'
        );

        $stmt->execute([
            'question_id' => $questionId,
            'admin_user_id' => $adminUserId,
            'answer' => $answer,
            'status' => $status,
        ]);

        $answerId = (int) $this->db->lastInsertId();

        $result = $this->findById($answerId);

        if (!$result instanceof ProductAnswer) {
            throw new RuntimeException(
                'Product answer could not be loaded after creation.'
            );
        }

        return $result;
    }

    public function updateStatus(
        int $answerId,
        string $status
    ): ?ProductAnswer {
        if ($answerId < 1) {
            return null;
        }

        $allowedStatuses = [
            ProductAnswer::STATUS_PENDING,
            ProductAnswer::STATUS_APPROVED,
            ProductAnswer::STATUS_REJECTED,
            ProductAnswer::STATUS_HIDDEN,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid answer status.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE product_answers
             SET
                status = :status,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $answerId,
            'status' => $status,
        ]);

        return $this->findById($answerId);
    }

    private function hydrate(
        array $row
    ): ProductAnswer {
        return new ProductAnswer(
            (int) $row['id'],
            (int) $row['question_id'],
            $row['admin_user_id'] !== null
                ? (int) $row['admin_user_id']
                : null,
            (string) $row['answer'],
            (string) $row['status'],
            $row['created_at'] !== null
                ? (string) $row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string) $row['updated_at']
                : null
        );
    }
}
