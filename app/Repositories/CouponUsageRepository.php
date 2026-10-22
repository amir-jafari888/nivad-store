<?php

declare(strict_types=1);

final class CouponUsageRepository
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
        int $usageId
    ): ?CouponUsage {
        if ($usageId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                coupon_id,
                user_id,
                order_id,
                discount_amount,
                currency,
                created_at
             FROM coupon_usages
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $usageId,
        ]);

        $row = $stmt->fetch();

        return $row !== false
            ? $this->hydrate($row)
            : null;
    }

    public function findByCouponId(
        int $couponId
    ): array {
        if ($couponId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                coupon_id,
                user_id,
                order_id,
                discount_amount,
                currency,
                created_at
             FROM coupon_usages
             WHERE coupon_id = :coupon_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'coupon_id' => $couponId,
        ]);

        return $this->hydrateAll(
            $stmt->fetchAll()
        );
    }

    public function findByUserId(
        int $userId
    ): array {
        if ($userId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                coupon_id,
                user_id,
                order_id,
                discount_amount,
                currency,
                created_at
             FROM coupon_usages
             WHERE user_id = :user_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $this->hydrateAll(
            $stmt->fetchAll()
        );
    }

    public function findByOrderId(
        int $orderId
    ): array {
        if ($orderId < 1) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                coupon_id,
                user_id,
                order_id,
                discount_amount,
                currency,
                created_at
             FROM coupon_usages
             WHERE order_id = :order_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            'order_id' => $orderId,
        ]);

        return $this->hydrateAll(
            $stmt->fetchAll()
        );
    }

    public function create(
        int $couponId,
        int $userId,
        int $orderId,
        string $discountAmount,
        string $currency = 'IRR'
    ): ?CouponUsage {
        if (
            $couponId < 1 ||
            $userId < 1 ||
            $orderId < 1
        ) {
            return null;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO coupon_usages (
                coupon_id,
                user_id,
                order_id,
                discount_amount,
                currency
             ) VALUES (
                :coupon_id,
                :user_id,
                :order_id,
                :discount_amount,
                :currency
             )'
        );

        $stmt->execute([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'currency' => $currency,
        ]);

        $usageId = (int)$this->db->lastInsertId();

        return $usageId > 0
            ? $this->findById($usageId)
            : null;
    }

    private function hydrate(
        array $row
    ): CouponUsage {
        return new CouponUsage(
            (int)$row['id'],
            (int)$row['coupon_id'],
            (int)$row['user_id'],
            (int)$row['order_id'],
            (string)$row['discount_amount'],
            (string)$row['currency'],
            isset($row['created_at'])
                ? (string)$row['created_at']
                : null
        );
    }

    private function hydrateAll(
        array $rows
    ): array {
        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    public function countByCouponId(
        int $couponId
    ): int {
        if ($couponId < 1) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
         FROM coupon_usages
         WHERE coupon_id = :coupon_id'
        );

        $stmt->execute([
            'coupon_id' => $couponId,
        ]);

        return (int)$stmt->fetchColumn();
    }

    public function countByUserAndCoupon(
        int $userId,
        int $couponId
    ): int {
        if (
            $userId < 1 ||
            $couponId < 1
        ) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
         FROM coupon_usages
         WHERE user_id = :user_id
           AND coupon_id = :coupon_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'coupon_id' => $couponId,
        ]);

        return (int)$stmt->fetchColumn();
    }
}
