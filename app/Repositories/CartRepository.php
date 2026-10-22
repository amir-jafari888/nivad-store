<?php

declare(strict_types=1);

final class CartRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(
        int $id
    ): ?Cart {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                session_token_hash,
                status,
                currency,
                created_at,
                updated_at,
                expires_at
             FROM carts
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

    public function findByUser(
        int $userId
    ): ?Cart {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                session_token_hash,
                status,
                currency,
                created_at,
                updated_at,
                expires_at
             FROM carts
             WHERE user_id = :user_id
               AND status = :status
             ORDER BY id DESC
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'status' => 'active',
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findBySessionTokenHash(
        string $sessionTokenHash
    ): ?Cart {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                session_token_hash,
                status,
                currency,
                created_at,
                updated_at,
                expires_at
             FROM carts
             WHERE session_token_hash = :session_token_hash
             LIMIT 1'
        );

        $stmt->execute([
            'session_token_hash' => $sessionTokenHash,
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
             FROM carts
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function existsBySessionTokenHash(
        string $sessionTokenHash
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM carts
             WHERE session_token_hash = :session_token_hash
             LIMIT 1'
        );

        $stmt->execute([
            'session_token_hash' => $sessionTokenHash,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        ?int $userId,
        ?string $sessionTokenHash,
        string $status = 'active',
        string $currency = 'IRR',
        ?string $expiresAt = null
    ): Cart {
        $stmt = $this->db->prepare(
            'INSERT INTO carts (
                user_id,
                session_token_hash,
                status,
                currency,
                expires_at
             ) VALUES (
                :user_id,
                :session_token_hash,
                :status,
                :currency,
                :expires_at
             )'
        );

        $stmt->execute([
            'user_id' => $userId,
            'session_token_hash' => $sessionTokenHash,
            'status' => $status,
            'currency' => $currency,
            'expires_at' => $expiresAt,
        ]);

        $id = (int)$this->db->lastInsertId();

        $cart = $this->findById($id);

        if ($cart === null) {
            throw new RuntimeException(
                'Cart ایجاد شد اما قابل بازیابی نیست.'
            );
        }

        return $cart;
    }

    public function update(
        int $id,
        ?int $userId,
        ?string $sessionTokenHash,
        string $status,
        string $currency,
        ?string $expiresAt
    ): ?Cart {
        $stmt = $this->db->prepare(
            'UPDATE carts
             SET
                user_id = :user_id,
                session_token_hash = :session_token_hash,
                status = :status,
                currency = :currency,
                expires_at = :expires_at
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'session_token_hash' => $sessionTokenHash,
            'status' => $status,
            'currency' => $currency,
            'expires_at' => $expiresAt,
        ]);

        return $this->findById($id);
    }

    public function updateStatus(
        int $id,
        string $status
    ): ?Cart {
        $stmt = $this->db->prepare(
            'UPDATE carts
             SET status = :status
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $this->findById($id);
    }

    private function hydrate(
        array $row
    ): Cart {
        return new Cart(
            (int)$row['id'],
            $row['user_id'] !== null
                ? (int)$row['user_id']
                : null,
            $row['session_token_hash'] !== null
                ? (string)$row['session_token_hash']
                : null,
            (string)$row['status'],
            (string)$row['currency'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null,
            $row['expires_at'] !== null
                ? (string)$row['expires_at']
                : null
        );
    }
}
