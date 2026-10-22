<?php

declare(strict_types=1);

final class CouponRepository
{
    private PDO $db;

    public function __construct(
        ?PDO $db = null
    ) {
        $this->db =
            $db ??
            Database::connection();
    }

    public function findById(
        int $couponId
    ): ?Coupon {
        if ($couponId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                code,
                title,
                description,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount_amount,
                usage_limit,
                usage_limit_per_user,
                starts_at,
                expires_at,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM coupons
             WHERE id = :id
             AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $couponId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findByCode(
        string $code
    ): ?Coupon {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                code,
                title,
                description,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount_amount,
                usage_limit,
                usage_limit_per_user,
                starts_at,
                expires_at,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM coupons
             WHERE code = :code
             AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'code' => $code,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findActiveByCode(
        string $code
    ): ?Coupon {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                code,
                title,
                description,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount_amount,
                usage_limit,
                usage_limit_per_user,
                starts_at,
                expires_at,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM coupons
             WHERE code = :code
             AND status = :status
             AND deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            'code' => $code,
            'status' => Coupon::STATUS_ACTIVE,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                code,
                title,
                description,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount_amount,
                usage_limit,
                usage_limit_per_user,
                starts_at,
                expires_at,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM coupons
             ORDER BY id DESC'
        );

        return $this->hydrateAll(
            $stmt->fetchAll()
        );
    }

    public function findDeletedById(
        int $couponId
    ): ?Coupon {
        if ($couponId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                code,
                title,
                description,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount_amount,
                usage_limit,
                usage_limit_per_user,
                starts_at,
                expires_at,
                status,
                created_at,
                updated_at,
                deleted_at
             FROM coupons
             WHERE id = :id
             AND deleted_at IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $couponId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function create(
        string $code,
        ?string $title,
        ?string $description,
        string $discountType,
        string $discountValue,
        ?string $minimumOrderAmount,
        ?string $maximumDiscountAmount,
        ?int $usageLimit,
        ?int $usageLimitPerUser,
        ?string $startsAt,
        ?string $expiresAt,
        string $status
    ): ?Coupon {
        $stmt = $this->db->prepare(
            'INSERT INTO coupons (
                code,
                title,
                description,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount_amount,
                usage_limit,
                usage_limit_per_user,
                starts_at,
                expires_at,
                status
             ) VALUES (
                :code,
                :title,
                :description,
                :discount_type,
                :discount_value,
                :minimum_order_amount,
                :maximum_discount_amount,
                :usage_limit,
                :usage_limit_per_user,
                :starts_at,
                :expires_at,
                :status
             )'
        );

        $stmt->execute([
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'minimum_order_amount' => $minimumOrderAmount,
            'maximum_discount_amount' => $maximumDiscountAmount,
            'usage_limit' => $usageLimit,
            'usage_limit_per_user' => $usageLimitPerUser,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'status' => $status,
        ]);

        return $this->findById(
            (int)$this->db->lastInsertId()
        );
    }

    public function update(
        int $couponId,
        string $code,
        ?string $title,
        ?string $description,
        string $discountType,
        string $discountValue,
        ?string $minimumOrderAmount,
        ?string $maximumDiscountAmount,
        ?int $usageLimit,
        ?int $usageLimitPerUser,
        ?string $startsAt,
        ?string $expiresAt,
        string $status
    ): ?Coupon {
        if ($couponId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE coupons
             SET
                code = :code,
                title = :title,
                description = :description,
                discount_type = :discount_type,
                discount_value = :discount_value,
                minimum_order_amount = :minimum_order_amount,
                maximum_discount_amount = :maximum_discount_amount,
                usage_limit = :usage_limit,
                usage_limit_per_user = :usage_limit_per_user,
                starts_at = :starts_at,
                expires_at = :expires_at,
                status = :status
             WHERE id = :id
             AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $couponId,
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'minimum_order_amount' => $minimumOrderAmount,
            'maximum_discount_amount' => $maximumDiscountAmount,
            'usage_limit' => $usageLimit,
            'usage_limit_per_user' => $usageLimitPerUser,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'status' => $status,
        ]);

        return $this->findById(
            $couponId
        );
    }

    public function delete(
        int $couponId
    ): bool {
        if ($couponId < 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE coupons
             SET deleted_at = NOW()
             WHERE id = :id
             AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $couponId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function restore(
        int $couponId
    ): ?Coupon {
        if ($couponId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE coupons
             SET deleted_at = NULL
             WHERE id = :id
             AND deleted_at IS NOT NULL'
        );

        $stmt->execute([
            'id' => $couponId,
        ]);

        return $this->findById(
            $couponId
        );
    }

    public function updateStatus(
        int $couponId,
        string $status
    ): ?Coupon {
        if ($couponId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE coupons
             SET status = :status
             WHERE id = :id
             AND deleted_at IS NULL'
        );

        $stmt->execute([
            'id' => $couponId,
            'status' => $status,
        ]);

        return $this->findById(
            $couponId
        );
    }

    private function hydrate(
        array $data
    ): Coupon {
        return new Coupon(
            (int)$data['id'],
            (string)$data['code'],
            $data['title'] !== null
                ? (string)$data['title']
                : null,
            $data['description'] !== null
                ? (string)$data['description']
                : null,
            (string)$data['discount_type'],
            (string)$data['discount_value'],
            $data['minimum_order_amount'] !== null
                ? (string)$data['minimum_order_amount']
                : null,
            $data['maximum_discount_amount'] !== null
                ? (string)$data['maximum_discount_amount']
                : null,
            $data['usage_limit'] !== null
                ? (int)$data['usage_limit']
                : null,
            $data['usage_limit_per_user'] !== null
                ? (int)$data['usage_limit_per_user']
                : null,
            $data['starts_at'] !== null
                ? (string)$data['starts_at']
                : null,
            $data['expires_at'] !== null
                ? (string)$data['expires_at']
                : null,
            (string)$data['status'],
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

    private function hydrateAll(
        array $rows
    ): array {
        $items = [];

        foreach ($rows as $row) {
            $items[] =
                $this->hydrate($row);
        }

        return $items;
    }
}
