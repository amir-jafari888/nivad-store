<?php

declare(strict_types=1);

final class WishlistRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(int $id): ?Wishlist
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                name,
                is_default,
                created_at,
                updated_at
             FROM wishlists
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

    public function findAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                name,
                is_default,
                created_at,
                updated_at
             FROM wishlists
             WHERE user_id = :user_id
             ORDER BY is_default DESC, id ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $wishlists = [];

        foreach (
            $stmt->fetchAll(PDO::FETCH_ASSOC)
            as $row
        ) {
            $wishlists[] = $this->hydrate($row);
        }

        return $wishlists;
    }

    public function findDefaultByUser(int $userId): ?Wishlist
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                name,
                is_default,
                created_at,
                updated_at
             FROM wishlists
             WHERE user_id = :user_id
               AND is_default = 1
             ORDER BY id ASC
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
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
             FROM wishlists
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function existsByUserAndName(
        int $userId,
        string $name
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM wishlists
             WHERE user_id = :user_id
               AND name = :name
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($name),
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        int $userId,
        string $name = 'علاقه‌مندی‌ها',
        bool $isDefault = true
    ): Wishlist {
        if ($isDefault) {
            $this->clearDefaultForUser($userId);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO wishlists (
                user_id,
                name,
                is_default
             ) VALUES (
                :user_id,
                :name,
                :is_default
             )'
        );

        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($name),
            'is_default' => $isDefault ? 1 : 0,
        ]);

        $id = (int)$this->db->lastInsertId();

        $wishlist = $this->findById($id);

        if ($wishlist === null) {
            throw new RuntimeException(
                'Wishlist ایجاد شد اما قابل بازیابی نیست.'
            );
        }

        return $wishlist;
    }

    public function update(
        int $id,
        string $name,
        bool $isDefault
    ): ?Wishlist {
        $wishlist = $this->findById($id);

        if ($wishlist === null) {
            return null;
        }

        if ($isDefault) {
            $this->clearDefaultForUser(
                $wishlist->userId,
                $id
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE wishlists
             SET
                name = :name,
                is_default = :is_default,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => trim($name),
            'is_default' => $isDefault ? 1 : 0,
        ]);

        return $this->findById($id);
    }

    public function setDefault(int $id): ?Wishlist
    {
        $wishlist = $this->findById($id);

        if ($wishlist === null) {
            return null;
        }

        $this->clearDefaultForUser(
            $wishlist->userId,
            $id
        );

        $stmt = $this->db->prepare(
            'UPDATE wishlists
             SET
                is_default = 1,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $this->findById($id);
    }

    private function clearDefaultForUser(
        int $userId,
        ?int $exceptId = null
    ): void {
        $sql = 'UPDATE wishlists
                SET
                    is_default = 0,
                    updated_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id
                  AND is_default = 1';

        $params = [
            'user_id' => $userId,
        ];

        if ($exceptId !== null) {
            $sql .= ' AND id != :except_id';

            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    private function hydrate(array $row): Wishlist
    {
        return new Wishlist(
            (int)$row['id'],
            (int)$row['user_id'],
            (string)$row['name'],
            (bool)$row['is_default'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null
        );
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM wishlists
         WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}
