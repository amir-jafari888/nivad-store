<?php

declare(strict_types=1);

final class SetupRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function findDeviceById(
        int $deviceId
    ): ?SetupDevice {
        if ($deviceId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                device_type,
                description,
                image,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM setup_devices
             WHERE id = :id
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $deviceId,
            'status' => SetupDevice::STATUS_ACTIVE,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrateDevice($row)
            : null;
    }

    public function findDeviceBySlug(
        string $slug
    ): ?SetupDevice {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                name,
                slug,
                device_type,
                description,
                image,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM setup_devices
             WHERE slug = :slug
               AND status = :status
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'slug' => $slug,
            'status' => SetupDevice::STATUS_ACTIVE,
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
                name,
                slug,
                device_type,
                description,
                image,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM setup_devices
             WHERE status = :status
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            'status' => SetupDevice::STATUS_ACTIVE,
        ]);

        $result = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $this->hydrateDevice($row);
        }

        return $result;
    }

    public function findCompatibleAccessories(
        int $deviceId,
        ?string $relationType = null
    ): array {
        if ($deviceId < 1) {
            return [];
        }

        $sql =
            'SELECT
                sa.id,
                sa.name,
                sa.slug,
                sa.accessory_type,
                sa.description,
                sa.image,
                sa.status,
                sa.sort_order,
                sa.created_at,
                sa.updated_at,
                sa.deleted_at
             FROM setup_device_accessories sda
             INNER JOIN setup_accessories sa
                ON sa.id = sda.setup_accessory_id
               AND sa.status = :accessory_status
               AND sa.deleted_at IS NULL
             WHERE sda.setup_device_id = :device_id';

        $parameters = [
            'accessory_status' =>
            SetupAccessory::STATUS_ACTIVE,
            'device_id' => $deviceId,
        ];

        if ($relationType !== null) {
            if (
                !in_array(
                    $relationType,
                    [
                        'recommended',
                        'compatible',
                        'optional',
                    ],
                    true
                )
            ) {
                return [];
            }

            $sql .=
                ' AND sda.relation_type = :relation_type';

            $parameters['relation_type'] =
                $relationType;
        }

        $sql .=
            ' ORDER BY
                sda.sort_order ASC,
                sa.sort_order ASC,
                sa.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parameters);

        $result = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $this->hydrateAccessory($row);
        }

        return $result;
    }

    public function findCompatibleProducts(
        int $deviceId,
        ?string $relationType = null
    ): array {
        if ($deviceId < 1) {
            return [];
        }

        $sql =
            'SELECT
            sdp.product_id,
            sdp.setup_device_id,
            sdp.relation_type,
            sdp.sort_order,
            sdp.created_at,

            p.name,
            p.slug,
            p.short_description,

            pv.id AS variant_id,
            pv.name AS variant_name,
            pv.price,
            pv.compare_at_price,
            pv.currency

         FROM setup_device_products sdp

         INNER JOIN products p
            ON p.id = sdp.product_id
           AND p.status = :product_status
           AND p.deleted_at IS NULL

         LEFT JOIN product_variants pv
            ON pv.id = (
                SELECT pv2.id
                FROM product_variants pv2
                WHERE pv2.product_id = p.id
                  AND pv2.status = :variant_status
                  AND pv2.deleted_at IS NULL
                ORDER BY
                    pv2.is_default DESC,
                    pv2.sort_order ASC,
                    pv2.id ASC
                LIMIT 1
            )

         WHERE sdp.setup_device_id = :device_id';

        $parameters = [
            'product_status' => 'active',
            'variant_status' => 'active',
            'device_id' => $deviceId,
        ];

        if ($relationType !== null) {
            if (
                !in_array(
                    $relationType,
                    [
                        'recommended',
                        'compatible',
                        'optional',
                    ],
                    true
                )
            ) {
                return [];
            }

            $sql .=
                ' AND sdp.relation_type = :relation_type';

            $parameters['relation_type'] =
                $relationType;
        }

        $sql .=
            ' ORDER BY
            CASE sdp.relation_type
                WHEN "recommended" THEN 1
                WHEN "compatible" THEN 2
                WHEN "optional" THEN 3
                ELSE 4
            END,
            sdp.sort_order ASC,
            sdp.product_id ASC';

        $stmt = $this->db->prepare($sql);

        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hydrateDevice(
        array $data
    ): SetupDevice {
        return new SetupDevice(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            (string)$data['device_type'],
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

    private function hydrateAccessory(
        array $data
    ): SetupAccessory {
        return new SetupAccessory(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            (string)$data['accessory_type'],
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

    public function findComplementCandidatesByDevices(
        array $deviceIds,
        int $excludeProductId
    ): array {
        $deviceIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $deviceIds
                    ),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if ($deviceIds === []) {
            return [];
        }

        if ($excludeProductId < 1) {
            return [];
        }

        $placeholders = [];

        $parameters = [
            'product_status' => 'active',
            'variant_status' => 'active',
            'exclude_product_id' => $excludeProductId,
        ];

        foreach ($deviceIds as $index => $deviceId) {
            $key = 'device_' . $index;

            $placeholders[] = ':' . $key;

            $parameters[$key] = $deviceId;
        }

        $sql =
            'SELECT
            sdp.product_id,
            sdp.setup_device_id,
            sdp.relation_type,
            sdp.sort_order,

            p.name,
            p.slug,
            p.short_description,

            b.id AS brand_id,
            b.name AS brand_name,

            pv.id AS variant_id,
            pv.name AS variant_name,
            pv.price,
            pv.compare_at_price,
            pv.currency,

            inv.quantity,
            inv.reserved_quantity,
            inv.low_stock_threshold

         FROM setup_device_products sdp

         INNER JOIN products p
            ON p.id = sdp.product_id
           AND p.status = :product_status
           AND p.deleted_at IS NULL

         LEFT JOIN brands b
            ON b.id = p.brand_id
           AND b.status = :brand_status
           AND b.deleted_at IS NULL

         LEFT JOIN product_variants pv
            ON pv.id = (
                SELECT pv2.id
                FROM product_variants pv2
                WHERE pv2.product_id = p.id
                  AND pv2.status = :variant_status
                  AND pv2.deleted_at IS NULL
                ORDER BY
                    pv2.is_default DESC,
                    pv2.sort_order ASC,
                    pv2.id ASC
                LIMIT 1
            )

         LEFT JOIN inventory inv
            ON inv.variant_id = pv.id

         WHERE sdp.setup_device_id IN (
            ' . implode(', ', $placeholders) . '
         )

           AND sdp.product_id != :exclude_product_id

         ORDER BY
            CASE sdp.relation_type
                WHEN "recommended" THEN 0
                WHEN "compatible" THEN 1
                WHEN "optional" THEN 2
                ELSE 3
            END,
            sdp.sort_order ASC,
            sdp.product_id ASC';

        $parameters['brand_status'] = 'active';

        $stmt = $this->db->prepare($sql);

        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
