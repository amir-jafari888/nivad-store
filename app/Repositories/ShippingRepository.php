<?php

declare(strict_types=1);

final class ShippingRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findShipmentById(
        int $id
    ): ?Shipment {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipments
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : Shipment::fromArray($row);
    }

    public function findShipmentsByOrderId(
        int $orderId
    ): array {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipments
             WHERE order_id = :order_id
             ORDER BY id DESC'
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $items = [];

        while (
            $row = $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] = Shipment::fromArray($row);
        }

        return $items;
    }

    public function findShipmentItems(
        int $shipmentId
    ): array {
        if ($shipmentId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipment_items
             WHERE shipment_id = :shipment_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'shipment_id' => $shipmentId,
        ]);

        $items = [];

        while (
            $row = $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] = ShipmentItem::fromArray($row);
        }

        return $items;
    }

    public function findShippingMethodById(
        int $id
    ): ?ShippingMethod {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipping_methods
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : ShippingMethod::fromArray($row);
    }

    public function findActiveShippingMethods(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *
             FROM shipping_methods
             WHERE status = \'active\'
               AND deleted_at IS NULL
             ORDER BY sort_order ASC, id ASC'
        );

        $items = [];

        while (
            $row = $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] = ShippingMethod::fromArray($row);
        }

        return $items;
    }

    public function findShippingZoneById(
        int $id
    ): ?ShippingZone {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipping_zones
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : ShippingZone::fromArray($row);
    }

    public function findZoneLocations(
        int $shippingZoneId
    ): array {
        if ($shippingZoneId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipping_zone_locations
             WHERE shipping_zone_id = :shipping_zone_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'shipping_zone_id' => $shippingZoneId,
        ]);

        $items = [];

        while (
            $row = $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                ShippingZoneLocation::fromArray($row);
        }

        return $items;
    }

    public function findAllShippingZones(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *
         FROM shipping_zones
         WHERE deleted_at IS NULL
         ORDER BY sort_order ASC, id ASC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = ShippingZone::fromArray($row);
        }

        return $items;
    }

    public function createShippingZone(
        string $name,
        string $slug,
        ?string $description,
        string $status = ShippingZone::STATUS_ACTIVE,
        int $sortOrder = 0
    ): ShippingZone {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shipping_zones (
            name,
            slug,
            description,
            status,
            sort_order
        ) VALUES (
            :name,
            :slug,
            :description,
            :status,
            :sort_order
        )'
        );

        $stmt->execute([
            'name' => trim($name),
            'slug' => trim($slug),
            'description' =>
            $description !== null
                ? trim($description)
                : null,
            'status' => trim($status),
            'sort_order' => $sortOrder,
        ]);

        $zone = $this->findShippingZoneById(
            (int)$this->pdo->lastInsertId()
        );

        if ($zone === null) {
            throw new RuntimeException(
                'Created shipping zone could not be loaded.'
            );
        }

        return $zone;
    }

    public function updateShippingZoneStatus(
        int $id,
        string $status
    ): ?ShippingZone {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE shipping_zones
         SET status = :status
         WHERE id = :id
           AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $id,
            'status' => trim($status),
        ]);

        return $this->findShippingZoneById($id);
    }

    public function findAllShipments(): array
    {
        $stmt = $this->pdo->query(
            'SELECT *
         FROM shipments
         ORDER BY id DESC'
        );

        $items = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = Shipment::fromArray($row);
        }

        return $items;
    }

    public function updateShipmentStatus(
        int $id,
        string $status
    ): ?Shipment {
        if ($id <= 0) {
            return null;
        }

        $status = trim($status);

        $shippedAt = null;
        $deliveredAt = null;

        if (
            $status === Shipment::STATUS_SHIPPED ||
            $status === Shipment::STATUS_IN_TRANSIT
        ) {
            $shippedAt = date('Y-m-d H:i:s');
        }

        if ($status === Shipment::STATUS_DELIVERED) {
            $shippedAt = date('Y-m-d H:i:s');
            $deliveredAt = date('Y-m-d H:i:s');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE shipments
         SET status = :status,
             shipped_at =
                 COALESCE(:shipped_at, shipped_at),
             delivered_at =
                 COALESCE(:delivered_at, delivered_at)
         WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
        ]);

        return $this->findShipmentById($id);
    }

    public function findShippingMethodZone(
        int $shippingMethodId,
        int $shippingZoneId
    ): ?ShippingMethodZone {
        if (
            $shippingMethodId <= 0 ||
            $shippingZoneId <= 0
        ) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM shipping_method_zones
             WHERE shipping_method_id = :shipping_method_id
               AND shipping_zone_id = :shipping_zone_id
             LIMIT 1'
        );

        $stmt->execute([
            'shipping_method_id' =>
            $shippingMethodId,

            'shipping_zone_id' =>
            $shippingZoneId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : ShippingMethodZone::fromArray($row);
    }

    public function findZoneByLocation(
        ?string $province,
        ?string $city,
        ?string $postalPrefix = null
    ): ?ShippingZone {
        $province = $province !== null
            ? trim($province)
            : null;

        $city = $city !== null
            ? trim($city)
            : null;

        $postalPrefix = $postalPrefix !== null
            ? trim($postalPrefix)
            : null;

        if (
            ($province === null || $province === '') &&
            ($city === null || $city === '') &&
            ($postalPrefix === null || $postalPrefix === '')
        ) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT z.*
             FROM shipping_zones z
             INNER JOIN shipping_zone_locations l
                ON l.shipping_zone_id = z.id
             WHERE z.status = \'active\'
               AND z.deleted_at IS NULL
               AND l.status = \'active\'
               AND (
                    (
                        :province IS NOT NULL
                        AND :province <> \'\'
                        AND l.province = :province
                    )
                    OR
                    (
                        :city IS NOT NULL
                        AND :city <> \'\'
                        AND l.city = :city
                    )
                    OR
                    (
                        :postal_prefix IS NOT NULL
                        AND :postal_prefix <> \'\'
                        AND l.postal_prefix = :postal_prefix
                    )
               )
             ORDER BY z.sort_order ASC, z.id ASC
             LIMIT 1'
        );

        $stmt->execute([
            'province' =>
            $province,

            'city' =>
            $city,

            'postal_prefix' =>
            $postalPrefix,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : ShippingZone::fromArray($row);
    }

    public function createShipment(
        int $orderId,
        int $shippingMethodId,
        ?string $trackingNumber,
        string $status,
        string $shippingCost,
        string $currency
    ): Shipment {
        if ($orderId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order id.'
            );
        }

        if ($shippingMethodId <= 0) {
            throw new InvalidArgumentException(
                'Invalid shipping method id.'
            );
        }

        $status = trim($status);
        $shippingCost = trim($shippingCost);
        $currency = strtoupper(trim($currency));

        if ($status === '') {
            throw new InvalidArgumentException(
                'Invalid shipment status.'
            );
        }

        if ($shippingCost === '') {
            throw new InvalidArgumentException(
                'Invalid shipping cost.'
            );
        }

        if ($currency === '') {
            throw new InvalidArgumentException(
                'Invalid currency.'
            );
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO shipments (
            order_id,
            shipping_method_id,
            tracking_number,
            status,
            shipping_cost,
            currency
        ) VALUES (
            :order_id,
            :shipping_method_id,
            :tracking_number,
            :status,
            :shipping_cost,
            :currency
        )'
        );

        $stmt->execute([
            'order_id' => $orderId,
            'shipping_method_id' => $shippingMethodId,
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'shipping_cost' => $shippingCost,
            'currency' => $currency,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        $shipment = $this->findShipmentById($id);

        if ($shipment === null) {
            throw new RuntimeException(
                'Created shipment could not be loaded.'
            );
        }

        return $shipment;
    }

    public function createShipmentItem(
        int $shipmentId,
        int $orderItemId,
        int $quantity
    ): ShipmentItem {
        if ($shipmentId <= 0) {
            throw new InvalidArgumentException(
                'Invalid shipment id.'
            );
        }

        if ($orderItemId <= 0) {
            throw new InvalidArgumentException(
                'Invalid order item id.'
            );
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Invalid shipment item quantity.'
            );
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO shipment_items (
            shipment_id,
            order_item_id,
            quantity
        ) VALUES (
            :shipment_id,
            :order_item_id,
            :quantity
        )'
        );

        $stmt->execute([
            'shipment_id' => $shipmentId,
            'order_item_id' => $orderItemId,
            'quantity' => $quantity,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM shipment_items
         WHERE id = :id
         LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                'Created shipment item could not be loaded.'
            );
        }

        return ShipmentItem::fromArray($row);
    }

    public function findAllShippingMethods(): array
    {
        $stmt =
            $this->pdo->query(
                'SELECT
                id,
                name,
                slug,
                description,
                base_cost,
                currency,
                estimated_min_days,
                estimated_max_days,
                status,
                sort_order,
                created_at,
                updated_at,
                deleted_at
             FROM shipping_methods
             ORDER BY
                sort_order ASC,
                id ASC'
            );

        $items = [];

        while (
            $row =
            $stmt->fetch(PDO::FETCH_ASSOC)
        ) {
            $items[] =
                ShippingMethod::fromArray($row);
        }

        return $items;
    }

    public function createShippingMethod(
        string $name,
        string $slug,
        ?string $description,
        string $baseCost,
        string $currency,
        ?int $minDays,
        ?int $maxDays,
        string $status,
        int $sortOrder
    ): ?ShippingMethod {
        $stmt =
            $this->pdo->prepare(
                'INSERT INTO shipping_methods (
                name,
                slug,
                description,
                base_cost,
                currency,
                estimated_min_days,
                estimated_max_days,
                status,
                sort_order
             ) VALUES (
                :name,
                :slug,
                :description,
                :base_cost,
                :currency,
                :min_days,
                :max_days,
                :status,
                :sort_order
             )'
            );

        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'base_cost' => $baseCost,
            'currency' => $currency,
            'min_days' => $minDays,
            'max_days' => $maxDays,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findShippingMethodById(
            (int)$this->pdo->lastInsertId()
        );
    }

    public function updateShippingMethod(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        string $baseCost,
        string $currency,
        ?int $minDays,
        ?int $maxDays,
        string $status,
        int $sortOrder
    ): ?ShippingMethod {
        if ($id < 1) {
            return null;
        }

        $stmt =
            $this->pdo->prepare(
                'UPDATE shipping_methods
             SET
                name = :name,
                slug = :slug,
                description = :description,
                base_cost = :base_cost,
                currency = :currency,
                estimated_min_days = :min_days,
                estimated_max_days = :max_days,
                status = :status,
                sort_order = :sort_order,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
            );

        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'base_cost' => $baseCost,
            'currency' => $currency,
            'min_days' => $minDays,
            'max_days' => $maxDays,
            'status' => $status,
            'sort_order' => $sortOrder,
        ]);

        return $this->findShippingMethodById($id);
    }

    public function deleteShippingMethod(
        int $id
    ): bool {
        if ($id < 1) {
            return false;
        }

        $stmt =
            $this->pdo->prepare(
                'UPDATE shipping_methods
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
}
