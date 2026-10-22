<?php

declare(strict_types=1);

final class ProductVariantAttributeRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function exists(
        int $variantId,
        int $attributeValueId
    ): bool {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM variant_attribute_values
             WHERE variant_id = :variant_id
               AND attribute_value_id = :attribute_value_id
             LIMIT 1'
        );

        $stmt->execute([
            'variant_id' => $variantId,
            'attribute_value_id' => $attributeValueId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function existsByVariantAndAttribute(
        int $variantId,
        int $attributeId
    ): bool {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM variant_attribute_values
             WHERE variant_id = :variant_id
               AND attribute_id = :attribute_id
             LIMIT 1'
        );

        $stmt->execute([
            'variant_id' => $variantId,
            'attribute_id' => $attributeId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        int $variantId,
        int $attributeValueId
    ): ?ProductVariantAttribute {
        if ($this->exists($variantId, $attributeValueId)) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT attribute_id
             FROM attribute_values
             WHERE id = :attribute_value_id
               AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'attribute_value_id' => $attributeValueId,
        ]);

        $attributeId =
            $stmt->fetchColumn();

        if ($attributeId === false) {
            return null;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO variant_attribute_values
            (
                variant_id,
                attribute_id,
                attribute_value_id,
                created_at
            )
            VALUES
            (
                :variant_id,
                :attribute_id,
                :attribute_value_id,
                CURRENT_TIMESTAMP
            )'
        );

        $insert->execute([
            'variant_id' => $variantId,
            'attribute_id' => (int)$attributeId,
            'attribute_value_id' => $attributeValueId,
        ]);

        return $this->findByVariantAndAttributeValue(
            $variantId,
            $attributeValueId
        );
    }

    public function delete(
        int $variantId,
        int $attributeValueId
    ): bool {
        $stmt = $this->pdo->prepare(
            'DELETE FROM variant_attribute_values
             WHERE variant_id = :variant_id
               AND attribute_value_id = :attribute_value_id'
        );

        $stmt->execute([
            'variant_id' => $variantId,
            'attribute_value_id' => $attributeValueId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return ProductVariantAttribute[]
     */
    public function findAllByVariant(
        int $variantId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT
                variant_id,
                attribute_id,
                attribute_value_id,
                created_at
             FROM variant_attribute_values
             WHERE variant_id = :variant_id
             ORDER BY attribute_id ASC, attribute_value_id ASC'
        );

        $stmt->execute([
            'variant_id' => $variantId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductVariantAttribute =>
            $this->hydrate($row),
            $rows
        );
    }

    private function findByVariantAndAttributeValue(
        int $variantId,
        int $attributeValueId
    ): ?ProductVariantAttribute {
        $stmt = $this->pdo->prepare(
            'SELECT
                variant_id,
                attribute_id,
                attribute_value_id,
                created_at
             FROM variant_attribute_values
             WHERE variant_id = :variant_id
               AND attribute_value_id = :attribute_value_id
             LIMIT 1'
        );

        $stmt->execute([
            'variant_id' => $variantId,
            'attribute_value_id' => $attributeValueId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $this->hydrate($row);
    }

    private function hydrate(
        array $row
    ): ProductVariantAttribute {
        return new ProductVariantAttribute(
            (int)$row['variant_id'],
            (int)$row['attribute_id'],
            (int)$row['attribute_value_id'],
            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null
        );
    }
}
