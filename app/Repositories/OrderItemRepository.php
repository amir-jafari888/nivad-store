<?php

declare(strict_types=1);

final class OrderItemRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?OrderItem
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM order_items
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

    public function findAllByOrder(
        int $orderId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): OrderItem => $this->hydrate($row),
            $rows
        );
    }

    private function hydrate(array $row): OrderItem
    {
        return new OrderItem(
            (int)$row['id'],
            (int)$row['order_id'],
            $row['product_id'] !== null
                ? (int)$row['product_id']
                : null,
            $row['variant_id'] !== null
                ? (int)$row['variant_id']
                : null,
            (string)$row['product_name'],
            (string)$row['product_sku'],
            $row['variant_sku'] !== null
                ? (string)$row['variant_sku']
                : null,
            $row['variant_title'] !== null
                ? (string)$row['variant_title']
                : null,
            (int)$row['quantity'],
            (string)$row['unit_price'],
            (string)$row['discount_amount'],
            (string)$row['tax_amount'],
            (string)$row['total_amount'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null
        );
    }

    public function create(
        int $orderId,
        ?int $productId,
        ?int $variantId,
        string $productName,
        string $productSku,
        ?string $variantSku,
        ?string $variantTitle,
        int $quantity,
        string $unitPrice,
        string $discountAmount,
        string $taxAmount,
        string $totalAmount
    ): OrderItem {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_items (
            order_id,
            product_id,
            variant_id,
            product_name,
            product_sku,
            variant_sku,
            variant_title,
            quantity,
            unit_price,
            discount_amount,
            tax_amount,
            total_amount
        ) VALUES (
            :order_id,
            :product_id,
            :variant_id,
            :product_name,
            :product_sku,
            :variant_sku,
            :variant_title,
            :quantity,
            :unit_price,
            :discount_amount,
            :tax_amount,
            :total_amount
        )'
        );

        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'variant_sku' => $variantSku,
            'variant_title' => $variantTitle,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        $item = $this->findById($id);

        if ($item === null) {
            throw new RuntimeException(
                'آیتم سفارش ایجاد نشد.'
            );
        }

        return $item;
    }
}
