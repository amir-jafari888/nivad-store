<?php

declare(strict_types=1);

final class OrderRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM orders
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $this->hydrate($row);
    }

    public function findByOrderNumber(
        string $orderNumber
    ): ?Order {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM orders
             WHERE order_number = :order_number
             LIMIT 1'
        );

        $stmt->execute([
            'order_number' => trim($orderNumber),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $this->hydrate($row);
    }

    private function hydrate(array $row): Order
    {
        return new Order(
            (int)$row['id'],
            (int)$row['user_id'],
            (string)$row['order_number'],
            (string)$row['status'],
            (string)$row['currency'],
            (string)$row['subtotal'],
            (string)$row['discount_amount'],
            (string)$row['shipping_amount'],
            (string)$row['tax_amount'],
            (string)$row['total_amount'],
            (string)$row['recipient_name'],
            (string)$row['recipient_phone'],
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
            $row['customer_note'] !== null
                ? (string)$row['customer_note']
                : null,
            $row['placed_at'] !== null
                ? (string)$row['placed_at']
                : null,
            $row['confirmed_at'] !== null
                ? (string)$row['confirmed_at']
                : null,
            $row['shipped_at'] !== null
                ? (string)$row['shipped_at']
                : null,
            $row['delivered_at'] !== null
                ? (string)$row['delivered_at']
                : null,
            $row['cancelled_at'] !== null
                ? (string)$row['cancelled_at']
                : null,
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,
            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null
        );
    }

    public function findOrderItemSnapshotByVariant(
        int $variantId
    ): ?array {
        if ($variantId <= 0) {
            return null;
        }

        $stmt =
            $this->pdo->prepare(
                'SELECT
                p.id AS product_id,
                p.name AS product_name,
                p.sku AS product_sku,
                v.id AS variant_id,
                v.sku AS variant_sku
             FROM product_variants v
             INNER JOIN products p
                ON p.id = v.product_id
             WHERE v.id = :variant_id
               AND v.deleted_at IS NULL
               AND p.deleted_at IS NULL
             LIMIT 1'
            );

        $stmt->execute([
            'variant_id' => $variantId,
        ]);

        $row =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        if ($row === false) {
            return null;
        }

        return [
            'productId' =>
            (int)$row['product_id'],

            'productName' =>
            (string)$row['product_name'],

            'productSku' =>
            (string)$row['product_sku'],

            'variantId' =>
            (int)$row['variant_id'],

            'variantSku' =>
            (string)$row['variant_sku'],

            /*
         * SQL snapshot currently has no variant_title column.
         * order_items.variant_title is nullable.
         */
            'variantTitle' =>
            null,
        ];
    }

    public function create(
        int $userId,
        string $orderNumber,
        string $status,
        string $currency,
        string $subtotal,
        string $discountAmount,
        string $shippingAmount,
        string $taxAmount,
        string $totalAmount,
        string $recipientName,
        string $recipientPhone,
        string $province,
        string $city,
        string $address,
        string $postalCode,
        ?string $buildingNumber = null,
        ?string $unit = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $customerNote = null,
        ?string $placedAt = null
    ): Order {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders (
            user_id,
            order_number,
            status,
            currency,
            subtotal,
            discount_amount,
            shipping_amount,
            tax_amount,
            total_amount,
            recipient_name,
            recipient_phone,
            province,
            city,
            address,
            postal_code,
            building_number,
            unit,
            latitude,
            longitude,
            customer_note,
            placed_at
        ) VALUES (
            :user_id,
            :order_number,
            :status,
            :currency,
            :subtotal,
            :discount_amount,
            :shipping_amount,
            :tax_amount,
            :total_amount,
            :recipient_name,
            :recipient_phone,
            :province,
            :city,
            :address,
            :postal_code,
            :building_number,
            :unit,
            :latitude,
            :longitude,
            :customer_note,
            :placed_at
        )'
        );

        $stmt->execute([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'status' => $status,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_amount' => $shippingAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'province' => $province,
            'city' => $city,
            'address' => $address,
            'postal_code' => $postalCode,
            'building_number' => $buildingNumber,
            'unit' => $unit,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'customer_note' => $customerNote,
            'placed_at' => $placedAt,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        $order = $this->findById($id);

        if ($order === null) {
            throw new RuntimeException(
                'سفارش ایجاد نشد.'
            );
        }

        return $order;
    }

    public function updateStatus(
        int $id,
        string $status,
        ?string $expectedCurrentStatus = null
    ): ?Order {
        if ($id <= 0) {
            return null;
        }

        $timestampColumn = match ($status) {
            'confirmed' => 'confirmed_at',
            'shipped' => 'shipped_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
            default => null,
        };

        $whereStatus =
            $expectedCurrentStatus !== null
            ? ' AND status = :expected_current_status'
            : '';

        if ($timestampColumn !== null) {
            $sql = sprintf(
                'UPDATE orders
             SET status = :status,
                 %s = COALESCE(%s, CURRENT_TIMESTAMP)
             WHERE id = :id%s
             LIMIT 1',
                $timestampColumn,
                $timestampColumn,
                $whereStatus
            );
        } else {
            $sql = sprintf(
                'UPDATE orders
             SET status = :status
             WHERE id = :id%s
             LIMIT 1',
                $whereStatus
            );
        }

        $stmt = $this->pdo->prepare($sql);

        $params = [
            'status' => $status,
            'id' => $id,
        ];

        if ($expectedCurrentStatus !== null) {
            $params['expected_current_status'] =
                $expectedCurrentStatus;
        }

        $stmt->execute($params);

        if (
            $expectedCurrentStatus !== null
            && $stmt->rowCount() !== 1
        ) {
            return null;
        }

        return $this->findById($id);
    }

    public function findAllByUser(
        int $userId
    ): array {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM orders
         WHERE user_id = :user_id
         ORDER BY id DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $orders = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = Order::fromArray($row);
        }

        return $orders;
    }

    public function findAllByStatus(
        string $status
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM orders
         WHERE status = :status
         ORDER BY id DESC'
        );

        $stmt->execute([
            'status' => $status,
        ]);

        $orders = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = Order::fromArray($row);
        }

        return $orders;
    }

    public function findByIdForUpdate(
        int $id
    ): ?Order {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
         FROM orders
         WHERE id = :id
         LIMIT 1
         FOR UPDATE'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $this->hydrate($row);
    }
}
