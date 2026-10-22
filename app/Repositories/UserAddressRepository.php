<?php

declare(strict_types=1);

final class UserAddressRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findAllByUser(
        int $userId
    ): array {
        if ($userId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
            id,
            user_id,
            title,
            recipient_name,
            phone,
            province,
            city,
            address,
            postal_code,
            building_number,
            unit,
            latitude,
            longitude,
            is_default,
            created_at,
            updated_at,
            deleted_at
         FROM user_addresses
         WHERE user_id = :user_id
           AND deleted_at IS NULL
         ORDER BY is_default DESC, id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $rows = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

        return array_map(
            fn(array $row): UserAddress =>
            $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $userId,
        array $data
    ): UserAddress {
        if ($userId < 1) {
            throw new InvalidArgumentException(
                'Invalid user ID.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO user_addresses (
            user_id,
            title,
            recipient_name,
            phone,
            province,
            city,
            address,
            postal_code,
            building_number,
            unit,
            latitude,
            longitude,
            is_default
        ) VALUES (
            :user_id,
            :title,
            :recipient_name,
            :phone,
            :province,
            :city,
            :address,
            :postal_code,
            :building_number,
            :unit,
            :latitude,
            :longitude,
            :is_default
        )'
        );

        $isDefault =
            !empty($data['is_default']) ? 1 : 0;

        if ($isDefault === 1) {
            $clear = $this->db->prepare(
                'UPDATE user_addresses
             SET is_default = 0
             WHERE user_id = :user_id
               AND deleted_at IS NULL'
            );

            $clear->execute([
                'user_id' => $userId,
            ]);
        }

        $stmt->execute([
            'user_id' => $userId,
            'title' =>
            trim((string)$data['title']),
            'recipient_name' =>
            trim((string)$data['recipient_name']),
            'phone' =>
            trim((string)$data['phone']),
            'province' =>
            trim((string)$data['province']),
            'city' =>
            trim((string)$data['city']),
            'address' =>
            trim((string)$data['address']),
            'postal_code' =>
            trim((string)$data['postal_code']),
            'building_number' => ($data['building_number'] ?? null) !== null
                ? trim((string)$data['building_number'])
                : null,
            'unit' => ($data['unit'] ?? null) !== null
                ? trim((string)$data['unit'])
                : null,
            'latitude' => ($data['latitude'] ?? null) !== null
                ? trim((string)$data['latitude'])
                : null,
            'longitude' => ($data['longitude'] ?? null) !== null
                ? trim((string)$data['longitude'])
                : null,
            'is_default' => $isDefault,
        ]);

        $id = (int)$this->db->lastInsertId();

        $address =
            $this->findByIdAndUser(
                $id,
                $userId
            );

        if ($address === null) {
            throw new RuntimeException(
                'آدرس ایجاد نشد.'
            );
        }

        return $address;
    }

    public function update(
        int $id,
        int $userId,
        array $data
    ): ?UserAddress {
        $existing =
            $this->findByIdAndUser(
                $id,
                $userId
            );

        if ($existing === null) {
            return null;
        }

        $isDefault =
            !empty($data['is_default']) ? 1 : 0;

        if ($isDefault === 1) {
            $clear = $this->db->prepare(
                'UPDATE user_addresses
             SET is_default = 0
             WHERE user_id = :user_id
               AND deleted_at IS NULL'
            );

            $clear->execute([
                'user_id' => $userId,
            ]);
        }

        $stmt = $this->db->prepare(
            'UPDATE user_addresses
         SET
            title = :title,
            recipient_name = :recipient_name,
            phone = :phone,
            province = :province,
            city = :city,
            address = :address,
            postal_code = :postal_code,
            building_number = :building_number,
            unit = :unit,
            latitude = :latitude,
            longitude = :longitude,
            is_default = :is_default,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND user_id = :user_id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'title' =>
            trim((string)$data['title']),
            'recipient_name' =>
            trim((string)$data['recipient_name']),
            'phone' =>
            trim((string)$data['phone']),
            'province' =>
            trim((string)$data['province']),
            'city' =>
            trim((string)$data['city']),
            'address' =>
            trim((string)$data['address']),
            'postal_code' =>
            trim((string)$data['postal_code']),
            'building_number' => ($data['building_number'] ?? null) !== null
                ? trim((string)$data['building_number'])
                : null,
            'unit' => ($data['unit'] ?? null) !== null
                ? trim((string)$data['unit'])
                : null,
            'latitude' => ($data['latitude'] ?? null) !== null
                ? trim((string)$data['latitude'])
                : null,
            'longitude' => ($data['longitude'] ?? null) !== null
                ? trim((string)$data['longitude'])
                : null,
            'is_default' => $isDefault,
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $this->findByIdAndUser(
            $id,
            $userId
        );
    }

    public function delete(
        int $id,
        int $userId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE user_addresses
         SET
            deleted_at = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP,
            is_default = 0
         WHERE id = :id
           AND user_id = :user_id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function setDefault(
        int $id,
        int $userId
    ): ?UserAddress {
        $address =
            $this->findByIdAndUser(
                $id,
                $userId
            );

        if ($address === null) {
            return null;
        }

        $this->db->beginTransaction();

        try {
            $clear = $this->db->prepare(
                'UPDATE user_addresses
             SET is_default = 0
             WHERE user_id = :user_id
               AND deleted_at IS NULL'
            );

            $clear->execute([
                'user_id' => $userId,
            ]);

            $set = $this->db->prepare(
                'UPDATE user_addresses
             SET
                is_default = 1,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND user_id = :user_id
               AND deleted_at IS NULL
             LIMIT 1'
            );

            $set->execute([
                'id' => $id,
                'user_id' => $userId,
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }

        return $this->findByIdAndUser(
            $id,
            $userId
        );
    }

    public function findByIdAndUser(
        int $id,
        int $userId
    ): ?UserAddress {
        if (
            $id < 1 ||
            $userId < 1
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                title,
                recipient_name,
                phone,
                province,
                city,
                address,
                postal_code,
                building_number,
                unit,
                latitude,
                longitude,
                is_default,
                created_at,
                updated_at,
                deleted_at
             FROM user_addresses
             WHERE id = :id
               AND user_id = :user_id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findDefaultByUser(
        int $userId
    ): ?UserAddress {
        if ($userId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                user_id,
                title,
                recipient_name,
                phone,
                province,
                city,
                address,
                postal_code,
                building_number,
                unit,
                latitude,
                longitude,
                is_default,
                created_at,
                updated_at,
                deleted_at
             FROM user_addresses
             WHERE user_id = :user_id
               AND is_default = 1
               AND deleted_at IS NULL
             ORDER BY id DESC
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    private function hydrate(
        array $row
    ): UserAddress {
        return new UserAddress(
            (int)$row['id'],
            (int)$row['user_id'],
            (string)$row['title'],
            (string)$row['recipient_name'],
            (string)$row['phone'],
            (string)$row['province'],
            (string)$row['city'],
            (string)$row['address'],
            (string)$row['postal_code'],
            $row['building_number'] !== null
                ? (string)$row['building_number']
                : null,
            $row['unit'] !== null
                ? (string)$row['unit']
                : null,
            $row['latitude'] !== null
                ? (string)$row['latitude']
                : null,
            $row['longitude'] !== null
                ? (string)$row['longitude']
                : null,
            (bool)$row['is_default'],
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
}
