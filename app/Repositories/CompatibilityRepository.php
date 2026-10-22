<?php

declare(strict_types=1);

final class CompatibilityRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findDeviceById(
        int $deviceId
    ): ?CompatibilityDevice {
        if ($deviceId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                brand_name,
                name,
                model,
                device_type,
                slug,
                description,
                image,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM compatibility_devices
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $deviceId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateDevice($row)
            : null;
    }

    public function findDeviceBySlug(
        string $slug
    ): ?CompatibilityDevice {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                brand_name,
                name,
                model,
                device_type,
                slug,
                description,
                image,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM compatibility_devices
             WHERE slug = :slug
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
            'status' => CompatibilityDevice::STATUS_ACTIVE,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateDevice($row)
            : null;
    }

    public function findActiveDevices(): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                brand_name,
                name,
                model,
                device_type,
                slug,
                description,
                image,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM compatibility_devices
             WHERE status = :status
               AND deleted_at IS NULL
             ORDER BY
                sort_order ASC,
                id ASC'
        );

        $stmt->execute([
            'status' => CompatibilityDevice::STATUS_ACTIVE,
        ]);

        $result = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] =
                $this->hydrateDevice($row);
        }

        return $result;
    }

    public function findCompatibleProducts(
        int $deviceId,
        ?string $compatibilityType = null
    ): array {
        if ($deviceId < 1) {
            return [];
        }

        $sql =
            'SELECT
            pc.product_id,
            pc.compatibility_device_id,
            pc.compatibility_type,
            pc.note,
            pc.sort_order,
            pc.created_at,
            p.name AS product_name,
            p.slug AS product_slug,
            b.name AS brand_name,
            MIN(pv.price) AS price
         FROM product_compatibility pc
         INNER JOIN compatibility_devices cd
            ON cd.id = pc.compatibility_device_id
           AND cd.status = :device_status
           AND cd.deleted_at IS NULL
         INNER JOIN products p
            ON p.id = pc.product_id
           AND p.status = :product_status
           AND p.deleted_at IS NULL
         LEFT JOIN brands b
            ON b.id = p.brand_id
           AND b.status = :brand_status
           AND b.deleted_at IS NULL
         LEFT JOIN product_variants pv
            ON pv.product_id = p.id
           AND pv.status = :variant_status
           AND pv.deleted_at IS NULL
         WHERE pc.compatibility_device_id = :device_id';

        $parameters = [
            'device_status' =>
            CompatibilityDevice::STATUS_ACTIVE,

            'product_status' =>
            'active',

            'brand_status' =>
            'active',

            'variant_status' =>
            'active',

            'device_id' =>
            $deviceId,
        ];

        if ($compatibilityType !== null) {
            $compatibilityType =
                trim($compatibilityType);

            if (
                !in_array(
                    $compatibilityType,
                    [
                        ProductCompatibility::TYPE_COMPATIBLE,
                        ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
                        ProductCompatibility::TYPE_NOT_COMPATIBLE,
                    ],
                    true
                )
            ) {
                return [];
            }

            $sql .=
                ' AND pc.compatibility_type =
                :compatibility_type';

            $parameters['compatibility_type'] =
                $compatibilityType;
        }

        $sql .=
            ' GROUP BY
            pc.product_id,
            pc.compatibility_device_id,
            pc.compatibility_type,
            pc.note,
            pc.sort_order,
            pc.created_at,
            p.name,
            p.slug,
            b.name
          ORDER BY
            pc.sort_order ASC,
            pc.product_id ASC';

        $stmt =
            $this->db->prepare($sql);

        $stmt->execute($parameters);

        $result = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $result[] = [
                'product_id' =>
                (int)$row['product_id'],

                'compatibility_device_id' =>
                (int)$row['compatibility_device_id'],

                'compatibility_type' =>
                (string)$row['compatibility_type'],

                'note' =>
                $row['note'] !== null
                    ? (string)$row['note']
                    : null,

                'sort_order' =>
                (int)$row['sort_order'],

                'created_at' =>
                $row['created_at'] !== null
                    ? (string)$row['created_at']
                    : null,

                'name' =>
                (string)$row['product_name'],

                'slug' =>
                (string)$row['product_slug'],

                'brandName' =>
                $row['brand_name'] !== null
                    ? (string)$row['brand_name']
                    : null,

                'price' =>
                $row['price'] !== null
                    ? (string)$row['price']
                    : null,
            ];
        }

        return $result;
    }

    public function findMapping(
        int $productId,
        int $deviceId
    ): ?ProductCompatibility {
        if (
            $productId < 1 ||
            $deviceId < 1
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                product_id,
                compatibility_device_id,
                compatibility_type,
                note,
                sort_order,
                created_at
             FROM product_compatibility
             WHERE product_id = :product_id
               AND compatibility_device_id = :device_id
             LIMIT 1'
        );

        $stmt->execute([
            'product_id' => $productId,
            'device_id' => $deviceId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateCompatibility($row)
            : null;
    }

    public function createMapping(
        int $productId,
        int $deviceId,
        string $compatibilityType,
        ?string $note,
        int $sortOrder = 0
    ): ProductCompatibility {
        if (
            $productId < 1 ||
            $deviceId < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid compatibility mapping identifiers.'
            );
        }

        if (
            !in_array(
                $compatibilityType,
                [
                    ProductCompatibility::TYPE_COMPATIBLE,
                    ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
                    ProductCompatibility::TYPE_NOT_COMPATIBLE,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid compatibility type.'
            );
        }

        if ($sortOrder < 0) {
            throw new InvalidArgumentException(
                'Invalid compatibility sort order.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO product_compatibility (
                product_id,
                compatibility_device_id,
                compatibility_type,
                note,
                sort_order
             ) VALUES (
                :product_id,
                :device_id,
                :compatibility_type,
                :note,
                :sort_order
             )'
        );

        $stmt->execute([
            'product_id' => $productId,
            'device_id' => $deviceId,
            'compatibility_type' => $compatibilityType,
            'note' => $note,
            'sort_order' => $sortOrder,
        ]);

        $mapping =
            $this->findMapping(
                $productId,
                $deviceId
            );

        if (!$mapping instanceof ProductCompatibility) {
            throw new RuntimeException(
                'Compatibility mapping could not be loaded after creation.'
            );
        }

        return $mapping;
    }

    public function updateMapping(
        int $productId,
        int $deviceId,
        string $compatibilityType,
        ?string $note,
        int $sortOrder
    ): ?ProductCompatibility {
        if (
            $productId < 1 ||
            $deviceId < 1 ||
            $sortOrder < 0
        ) {
            return null;
        }

        if (
            !in_array(
                $compatibilityType,
                [
                    ProductCompatibility::TYPE_COMPATIBLE,
                    ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
                    ProductCompatibility::TYPE_NOT_COMPATIBLE,
                ],
                true
            )
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE product_compatibility
             SET
                compatibility_type = :compatibility_type,
                note = :note,
                sort_order = :sort_order
             WHERE product_id = :product_id
               AND compatibility_device_id = :device_id'
        );

        $stmt->execute([
            'product_id' => $productId,
            'device_id' => $deviceId,
            'compatibility_type' => $compatibilityType,
            'note' => $note,
            'sort_order' => $sortOrder,
        ]);

        return $this->findMapping(
            $productId,
            $deviceId
        );
    }

    public function deleteMapping(
        int $productId,
        int $deviceId
    ): bool {
        if (
            $productId < 1 ||
            $deviceId < 1
        ) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM product_compatibility
             WHERE product_id = :product_id
               AND compatibility_device_id = :device_id'
        );

        $stmt->execute([
            'product_id' => $productId,
            'device_id' => $deviceId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function findAllMappings(): array
    {
        $stmt =
            $this->db->query(
                'SELECT
                pc.product_id,
                pc.compatibility_device_id,
                pc.compatibility_type,
                pc.note,
                pc.sort_order,
                pc.created_at,
                p.name AS product_name,
                p.slug AS product_slug,
                cd.brand_name,
                cd.name AS device_name,
                cd.model AS device_model
             FROM product_compatibility pc
             INNER JOIN products p
                ON p.id = pc.product_id
               AND p.deleted_at IS NULL
             INNER JOIN compatibility_devices cd
                ON cd.id =
                    pc.compatibility_device_id
             ORDER BY
                pc.sort_order ASC,
                pc.product_id ASC,
                pc.compatibility_device_id ASC'
            );

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] = [
                'product_id' =>
                (int)$row['product_id'],

                'compatibility_device_id' =>
                (int)$row['compatibility_device_id'],

                'compatibility_type' =>
                (string)$row['compatibility_type'],

                'note' =>
                $row['note'] !== null
                    ? (string)$row['note']
                    : null,

                'sort_order' =>
                (int)$row['sort_order'],

                'created_at' =>
                $row['created_at'] !== null
                    ? (string)$row['created_at']
                    : null,

                'product_name' =>
                (string)$row['product_name'],

                'product_slug' =>
                (string)$row['product_slug'],

                'brand_name' =>
                (string)$row['brand_name'],

                'device_name' =>
                (string)$row['device_name'],

                'device_model' =>
                $row['device_model'] !== null
                    ? (string)$row['device_model']
                    : null,
            ];
        }

        return $items;
    }

    public function findAllDevices(): array
    {
        $stmt = $this->db->query(
            'SELECT
            id,
            brand_name,
            name,
            model,
            device_type,
            slug,
            description,
            image,
            status,
            sort_order,
            created_at,
            updated_at,
            deleted_at
         FROM compatibility_devices
         ORDER BY sort_order ASC, id ASC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->hydrateDevice($row);
        }

        return $items;
    }

    public function createDevice(
        string $brandName,
        string $name,
        ?string $model,
        string $deviceType,
        string $slug,
        ?string $description,
        ?string $image,
        string $status,
        int $sortOrder
    ): ?CompatibilityDevice {
        $stmt = $this->db->prepare(
            'INSERT INTO compatibility_devices (
            brand_name,
            name,
            model,
            device_type,
            slug,
            description,
            image,
            status,
            sort_order
         ) VALUES (
            :brand_name,
            :name,
            :model,
            :device_type,
            :slug,
            :description,
            :image,
            :status,
            :sort_order
         )'
        );

        $stmt->execute([
            'brand_name' => $brandName,
            'name' => $name,
            'model' => $model,
            'device_type' => $deviceType,
            'slug' => $slug,
            'description' => $description,
            'image' => $image,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findDeviceById(
            (int)$this->db->lastInsertId()
        );
    }

    public function updateDevice(
        int $id,
        string $brandName,
        string $name,
        ?string $model,
        string $deviceType,
        string $slug,
        ?string $description,
        ?string $image,
        string $status,
        int $sortOrder
    ): ?CompatibilityDevice {
        if ($id < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE compatibility_devices
         SET
            brand_name = :brand_name,
            name = :name,
            model = :model,
            device_type = :device_type,
            slug = :slug,
            description = :description,
            image = :image,
            status = :status,
            sort_order = :sort_order,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'brand_name' => $brandName,
            'name' => $name,
            'model' => $model,
            'device_type' => $deviceType,
            'slug' => $slug,
            'description' => $description,
            'image' => $image,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findDeviceById($id);
    }

    public function deleteDevice(int $id): bool
    {
        if ($id < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE compatibility_devices
         SET
            deleted_at = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function updateDeviceStatus(
        int $id,
        string $status
    ): ?CompatibilityDevice {
        if (
            !in_array(
                $status,
                [
                    CompatibilityDevice::STATUS_ACTIVE,
                    CompatibilityDevice::STATUS_INACTIVE,
                ],
                true
            )
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE compatibility_devices
         SET
            status = :status,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        return $this->findDeviceById($id);
    }

    public function restoreDevice(
        int $id
    ): ?CompatibilityDevice {
        $stmt = $this->db->prepare(
            'UPDATE compatibility_devices
         SET
            deleted_at = NULL,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id
           AND deleted_at IS NOT NULL
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        return $this->findDeviceById($id);
    }

    private function hydrateDevice(
        array $data
    ): CompatibilityDevice {
        return new CompatibilityDevice(
            (int)$data['id'],
            (string)$data['brand_name'],
            (string)$data['name'],
            $data['model'] !== null
                ? (string)$data['model']
                : null,
            (string)$data['device_type'],
            (string)$data['slug'],
            $data['description'] !== null
                ? (string)$data['description']
                : null,
            $data['image'] !== null
                ? (string)$data['image']
                : null,
            (string)$data['status'],
            (int)$data['sort_order'],
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

    private function hydrateCompatibility(
        array $data
    ): ProductCompatibility {
        return new ProductCompatibility(
            (int)$data['product_id'],
            (int)$data['compatibility_device_id'],
            (string)$data['compatibility_type'],
            $data['note'] !== null
                ? (string)$data['note']
                : null,
            (int)$data['sort_order'],
            $data['created_at'] !== null
                ? (string)$data['created_at']
                : null
        );
    }

    public function countActiveDevices(): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
         FROM compatibility_devices
         WHERE status = :status
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'status' => CompatibilityDevice::STATUS_ACTIVE,
        ]);

        return (int)$stmt->fetchColumn();
    }

    public function countMappings(): int
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*)
         FROM product_compatibility'
        );

        return (int)$stmt->fetchColumn();
    }

    public function findDevicesByProduct(
        int $productId
    ): array {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
            cd.id,
            cd.brand_name,
            cd.name,
            cd.model,
            cd.device_type,
            cd.slug,
            cd.description,
            cd.image,
            cd.status,
            cd.sort_order,
            cd.created_at,
            cd.updated_at,
            cd.deleted_at,
            pc.compatibility_type,
            pc.note AS compatibility_note,
            pc.sort_order AS compatibility_sort_order
         FROM product_compatibility pc
         INNER JOIN compatibility_devices cd
            ON cd.id = pc.compatibility_device_id
           AND cd.status = :device_status
           AND cd.deleted_at IS NULL
         INNER JOIN products p
            ON p.id = pc.product_id
           AND p.status = :product_status
           AND p.deleted_at IS NULL
         WHERE pc.product_id = :product_id
           AND pc.compatibility_type IN (
               :compatible,
               :partial
           )
         ORDER BY
            CASE
                WHEN pc.compatibility_type = :compatible_order
                    THEN 0
                WHEN pc.compatibility_type = :partial_order
                    THEN 1
                ELSE 2
            END,
            pc.sort_order ASC,
            cd.sort_order ASC,
            cd.id ASC'
        );

        $stmt->execute([
            'device_status' => CompatibilityDevice::STATUS_ACTIVE,
            'product_status' => 'active',
            'product_id' => $productId,
            'compatible' => ProductCompatibility::TYPE_COMPATIBLE,
            'partial' => ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
            'compatible_order' => ProductCompatibility::TYPE_COMPATIBLE,
            'partial_order' => ProductCompatibility::TYPE_PARTIALLY_COMPATIBLE,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllMappingsByProduct(
        int $productId
    ): array {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
            cd.id,
            cd.brand_name,
            cd.name,
            cd.model,
            cd.device_type,
            cd.slug,
            cd.description,
            cd.image,
            cd.status,
            cd.sort_order,
            cd.created_at,
            cd.updated_at,
            cd.deleted_at,
            pc.compatibility_type,
            pc.note AS compatibility_note,
            pc.sort_order AS compatibility_sort_order
         FROM product_compatibility pc
         INNER JOIN compatibility_devices cd
            ON cd.id = pc.compatibility_device_id
           AND cd.status = :device_status
           AND cd.deleted_at IS NULL
         INNER JOIN products p
            ON p.id = pc.product_id
           AND p.status = :product_status
           AND p.deleted_at IS NULL
         WHERE pc.product_id = :product_id
         ORDER BY
            pc.sort_order ASC,
            cd.sort_order ASC,
            cd.id ASC'
        );

        $stmt->execute([
            'device_status' =>
            CompatibilityDevice::STATUS_ACTIVE,

            'product_status' =>
            'active',

            'product_id' =>
            $productId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
