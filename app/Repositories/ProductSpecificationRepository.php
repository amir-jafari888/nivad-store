<?php

declare(strict_types=1);

final class ProductSpecificationRepository
{
    private PDO $pdo;

    public function __construct(
        ?PDO $pdo = null
    ) {
        $this->pdo =
            $pdo ??
            Database::connection();
    }

    public function find(
        int $productId,
        int $specificationId
    ): ?ProductSpecification {
        if (
            $productId < 1
            || $specificationId < 1
        ) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                product_id,
                specification_id,
                value_text,
                value_number,
                value_boolean,
                value_date,
                created_at,
                updated_at
             FROM product_specifications
             WHERE product_id = :product_id
               AND specification_id = :specification_id
             LIMIT 1'
        );

        $stmt->execute([
            'product_id' =>
            $productId,
            'specification_id' =>
            $specificationId,
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $this->hydrate($row);
    }

    public function exists(
        int $productId,
        int $specificationId
    ): bool {
        if (
            $productId < 1
            || $specificationId < 1
        ) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM product_specifications
             WHERE product_id = :product_id
               AND specification_id = :specification_id
             LIMIT 1'
        );

        $stmt->execute([
            'product_id' =>
            $productId,
            'specification_id' =>
            $specificationId,
        ]);

        return
            $stmt->fetchColumn() !== false;
    }

    /**
     * @return ProductSpecification[]
     */
    public function findAllByProduct(
        int $productId
    ): array {
        if ($productId < 1) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                product_id,
                specification_id,
                value_text,
                value_number,
                value_boolean,
                value_date,
                created_at,
                updated_at
             FROM product_specifications
             WHERE product_id = :product_id
             ORDER BY specification_id ASC'
        );

        $stmt->execute([
            'product_id' =>
            $productId,
        ]);

        $rows =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductSpecification =>
            $this->hydrate($row),
            $rows
        );
    }

    /**
     * @return ProductSpecification[]
     */
    public function findAllBySpecification(
        int $specificationId
    ): array {
        if ($specificationId < 1) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                product_id,
                specification_id,
                value_text,
                value_number,
                value_boolean,
                value_date,
                created_at,
                updated_at
             FROM product_specifications
             WHERE specification_id = :specification_id
             ORDER BY product_id ASC'
        );

        $stmt->execute([
            'specification_id' =>
            $specificationId,
        ]);

        $rows =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): ProductSpecification =>
            $this->hydrate($row),
            $rows
        );
    }

    public function create(
        int $productId,
        int $specificationId,
        ?string $valueText = null,
        ?string $valueNumber = null,
        ?bool $valueBoolean = null,
        ?string $valueDate = null
    ): ?ProductSpecification {
        if (
            $productId < 1
            || $specificationId < 1
        ) {
            return null;
        }

        if (
            $this->exists(
                $productId,
                $specificationId
            )
        ) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO product_specifications
            (
                product_id,
                specification_id,
                value_text,
                value_number,
                value_boolean,
                value_date,
                created_at
            )
            VALUES
            (
                :product_id,
                :specification_id,
                :value_text,
                :value_number,
                :value_boolean,
                :value_date,
                CURRENT_TIMESTAMP
            )'
        );

        $stmt->execute([
            'product_id' =>
            $productId,

            'specification_id' =>
            $specificationId,

            'value_text' =>
            $valueText,

            'value_number' =>
            $valueNumber,

            'value_boolean' =>
            $valueBoolean,

            'value_date' =>
            $valueDate,
        ]);

        return $this->find(
            $productId,
            $specificationId
        );
    }

    public function update(
        int $productId,
        int $specificationId,
        ?string $valueText = null,
        ?string $valueNumber = null,
        ?bool $valueBoolean = null,
        ?string $valueDate = null
    ): ?ProductSpecification {
        if (
            $productId < 1
            || $specificationId < 1
        ) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE product_specifications
             SET
                value_text = :value_text,
                value_number = :value_number,
                value_boolean = :value_boolean,
                value_date = :value_date,
                updated_at = CURRENT_TIMESTAMP
             WHERE product_id = :product_id
               AND specification_id = :specification_id'
        );

        $stmt->execute([
            'product_id' =>
            $productId,

            'specification_id' =>
            $specificationId,

            'value_text' =>
            $valueText,

            'value_number' =>
            $valueNumber,

            'value_boolean' =>
            $valueBoolean,

            'value_date' =>
            $valueDate,
        ]);

        return $this->find(
            $productId,
            $specificationId
        );
    }

    public function delete(
        int $productId,
        int $specificationId
    ): bool {
        if (
            $productId < 1
            || $specificationId < 1
        ) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'DELETE FROM product_specifications
             WHERE product_id = :product_id
               AND specification_id = :specification_id'
        );

        $stmt->execute([
            'product_id' =>
            $productId,

            'specification_id' =>
            $specificationId,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(
        array $row
    ): ProductSpecification {
        return new ProductSpecification(
            (int)$row['product_id'],
            (int)$row['specification_id'],

            $row['value_text'] !== null
                ? (string)$row['value_text']
                : null,

            $row['value_number'] !== null
                ? (string)$row['value_number']
                : null,

            $row['value_boolean'] !== null
                ? (bool)$row['value_boolean']
                : null,

            $row['value_date'] !== null
                ? (string)$row['value_date']
                : null,

            $row['created_at'] !== null
                ? (string)$row['created_at']
                : null,

            $row['updated_at'] !== null
                ? (string)$row['updated_at']
                : null
        );
    }
}
