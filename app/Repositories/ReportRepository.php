<?php

declare(strict_types=1);

final class ReportRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function orderSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $sql = "
            SELECT
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.subtotal), 0) AS subtotal,
                COALESCE(SUM(o.discount_amount), 0) AS discount_amount,
                COALESCE(SUM(o.shipping_amount), 0) AS shipping_amount,
                COALESCE(SUM(o.tax_amount), 0) AS tax_amount,
                COALESCE(SUM(o.total_amount), 0) AS total_amount
            FROM orders o
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function paymentSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'p.created_at', 'p.currency');

        $sql = "
            SELECT
                COUNT(p.id) AS total_payments,
                COALESCE(SUM(p.amount), 0) AS total_amount,
                COALESCE(AVG(p.amount), 0) AS average_amount
            FROM payments p
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function orderItemSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $sql = "
            SELECT
                COUNT(oi.id) AS total_lines,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.total_amount), 0) AS average_line_amount
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function ordersByPeriod(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'o.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.total_amount), 0) AS total_amount
            FROM orders o
            {$where}
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function paymentsByPeriod(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'p.created_at', 'p.currency');

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'p.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(p.id) AS total_payments,
                COALESCE(SUM(p.amount), 0) AS total_amount,
                COALESCE(AVG(p.amount), 0) AS average_amount
            FROM payments p
            {$where}
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function orderItemsByPeriod(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'o.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(oi.id) AS total_lines,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.total_amount), 0) AS average_line_amount
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            {$where}
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function inventorySummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'i.updated_at');

        $sql = "
            SELECT
                COUNT(i.id) AS total_inventory_rows,
                COALESCE(SUM(i.quantity), 0) AS total_quantity,
                COALESCE(SUM(i.reserved_quantity), 0) AS total_reserved_quantity,
                COALESCE(
                    SUM(i.quantity - i.reserved_quantity),
                    0
                ) AS total_available_quantity
            FROM inventory i
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryMovementSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'im.created_at');

        $sql = "
            SELECT
                COUNT(im.id) AS total_movements,
                COALESCE(SUM(im.quantity), 0) AS total_quantity
            FROM inventory_movements im
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryReservationSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'ir.created_at');

        $sql = "
            SELECT
                COUNT(ir.id) AS total_reservations,
                COALESCE(SUM(ir.quantity), 0) AS total_quantity
            FROM inventory_reservations ir
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function shipmentSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 's.created_at', 's.currency');

        $sql = "
            SELECT
                COUNT(s.id) AS total_shipments,
                COALESCE(SUM(s.shipping_cost), 0) AS total_shipping_cost
            FROM shipments s
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function returnSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'r.created_at');

        $sql = "
            SELECT
                COUNT(r.id) AS total_returns
            FROM returns r
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function refundSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'rf.created_at', 'rf.currency');

        $sql = "
            SELECT
                COUNT(rf.id) AS total_refunds,
                COALESCE(SUM(rf.amount), 0) AS total_amount,
                COALESCE(AVG(rf.amount), 0) AS average_amount
            FROM refunds rf
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function orderStatusSummary(ReportQuery $query): array
    {
        return $this->statusSummary(
            $query,
            'orders',
            'o.created_at',
            'o.currency',
            'o.status',
            'o.status'
        );
    }

    public function paymentStatusSummary(ReportQuery $query): array
    {
        return $this->statusSummary(
            $query,
            'payments',
            'p.created_at',
            'p.currency',
            'p.status',
            'p.status'
        );
    }

    public function shipmentStatusSummary(ReportQuery $query): array
    {
        return $this->statusSummary(
            $query,
            'shipments',
            's.created_at',
            's.currency',
            's.status',
            's.status'
        );
    }

    public function returnStatusSummary(ReportQuery $query): array
    {
        return $this->statusSummary(
            $query,
            'returns',
            'r.created_at',
            null,
            'r.status',
            'r.status'
        );
    }

    public function refundStatusSummary(ReportQuery $query): array
    {
        return $this->statusSummary(
            $query,
            'refunds',
            'rf.created_at',
            'rf.currency',
            'rf.status',
            'rf.status'
        );
    }

    public function productPerformanceSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $sql = "
            SELECT
                oi.product_id,
                MAX(oi.product_name) AS product_name,
                MAX(oi.product_sku) AS product_sku,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            {$where}
            AND oi.product_id IS NOT NULL
            GROUP BY oi.product_id
            ORDER BY total_amount DESC,
                total_quantity DESC,
                oi.product_id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function productVariantPerformanceSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $sql = "
            SELECT
                oi.product_id,
                oi.variant_id,
                MAX(oi.product_name) AS product_name,
                MAX(oi.product_sku) AS product_sku,
                MAX(oi.variant_sku) AS variant_sku,
                MAX(oi.variant_title) AS variant_title,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            {$where}
            AND oi.variant_id IS NOT NULL
            GROUP BY oi.product_id, oi.variant_id
            ORDER BY total_amount DESC,
                total_quantity DESC,
                oi.variant_id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function productPerformanceByPeriod(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'o.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COUNT(DISTINCT oi.product_id) AS total_products,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            {$where}
            AND oi.product_id IS NOT NULL
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function categoryPerformanceSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $sql = "
            SELECT
                c.id AS category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COUNT(DISTINCT oi.product_id) AS total_products,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            INNER JOIN products p
                ON p.id = oi.product_id
            INNER JOIN product_categories pc
                ON pc.product_id = p.id
            INNER JOIN categories c
                ON c.id = pc.category_id
            {$where}
            AND oi.product_id IS NOT NULL
            AND p.deleted_at IS NULL
            AND c.status = 'active'
            AND c.deleted_at IS NULL
            GROUP BY c.id, c.name, c.slug
            ORDER BY total_amount DESC,
                total_quantity DESC,
                c.id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function brandPerformanceSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $sql = "
            SELECT
                b.id AS brand_id,
                b.name AS brand_name,
                b.slug AS brand_slug,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COUNT(DISTINCT oi.product_id) AS total_products,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            INNER JOIN products p
                ON p.id = oi.product_id
            INNER JOIN brands b
                ON b.id = p.brand_id
            {$where}
            AND oi.product_id IS NOT NULL
            AND p.deleted_at IS NULL
            AND b.status = 'active'
            AND b.deleted_at IS NULL
            GROUP BY b.id, b.name, b.slug
            ORDER BY total_amount DESC,
                total_quantity DESC,
                b.id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function categoryPerformanceByPeriod(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'o.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COUNT(DISTINCT oi.product_id) AS total_products,
                COUNT(DISTINCT c.id) AS total_categories,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            INNER JOIN products p
                ON p.id = oi.product_id
            INNER JOIN product_categories pc
                ON pc.product_id = p.id
            INNER JOIN categories c
                ON c.id = pc.category_id
            {$where}
            AND oi.product_id IS NOT NULL
            AND p.deleted_at IS NULL
            AND c.status = 'active'
            AND c.deleted_at IS NULL
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function brandPerformanceByPeriod(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere($query, 'o.created_at', 'o.currency');

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'o.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(oi.id) AS total_lines,
                COUNT(DISTINCT oi.order_id) AS total_orders,
                COUNT(DISTINCT oi.product_id) AS total_products,
                COUNT(DISTINCT b.id) AS total_brands,
                COALESCE(SUM(oi.quantity), 0) AS total_quantity,
                COALESCE(SUM(oi.total_amount), 0) AS total_amount,
                COALESCE(AVG(oi.unit_price), 0) AS average_unit_price
            FROM order_items oi
            INNER JOIN orders o
                ON o.id = oi.order_id
            INNER JOIN products p
                ON p.id = oi.product_id
            INNER JOIN brands b
                ON b.id = p.brand_id
            {$where}
            AND oi.product_id IS NOT NULL
            AND p.deleted_at IS NULL
            AND b.status = 'active'
            AND b.deleted_at IS NULL
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function paymentAttemptSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere(
            $query,
            'pa.created_at',
            'pa.currency'
        );

        $sql = "
            SELECT
                COUNT(pa.id) AS total_attempts,
                COALESCE(SUM(pa.amount), 0) AS total_amount,
                COALESCE(AVG(pa.amount), 0) AS average_amount
            FROM payment_attempts pa
            {$where}
        ";

        return $this->fetchOne($sql, $params);
    }

    public function paymentAttemptStatusSummary(ReportQuery $query): array
    {
        return $this->statusSummary(
            $query,
            'payment_attempts',
            'pa.created_at',
            'pa.currency',
            'pa.status',
            'pa.status'
        );
    }

    public function paymentGatewaySummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere(
            $query,
            'p.created_at',
            'p.currency'
        );

        $sql = "
            SELECT
                p.gateway,
                COUNT(p.id) AS total_payments,
                COALESCE(SUM(p.amount), 0) AS total_amount,
                COALESCE(AVG(p.amount), 0) AS average_amount
            FROM payments p
            {$where}
            GROUP BY p.gateway
            ORDER BY total_amount DESC,
                p.gateway ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function paymentAttemptGatewaySummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'pa.created_at',
            'pa.currency'
        );

        $sql = "
            SELECT
                pa.gateway,
                COUNT(pa.id) AS total_attempts,
                COALESCE(SUM(pa.amount), 0) AS total_amount,
                COALESCE(AVG(pa.amount), 0) AS average_amount
            FROM payment_attempts pa
            {$where}
            GROUP BY pa.gateway
            ORDER BY total_amount DESC,
                pa.gateway ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function paymentCurrencySummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'p.created_at'
        );

        $sql = "
            SELECT
                p.currency,
                COUNT(p.id) AS total_payments,
                COALESCE(SUM(p.amount), 0) AS total_amount,
                COALESCE(AVG(p.amount), 0) AS average_amount
            FROM payments p
            {$where}
            GROUP BY p.currency
            ORDER BY total_amount DESC,
                p.currency ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function paymentAttemptsByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'pa.created_at',
            'pa.currency'
        );

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'pa.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(pa.id) AS total_attempts,
                COALESCE(SUM(pa.amount), 0) AS total_amount,
                COALESCE(AVG(pa.amount), 0) AS average_amount
            FROM payment_attempts pa
            {$where}
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function paymentGatewayByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'p.created_at',
            'p.currency'
        );

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'p.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                p.gateway,
                COUNT(p.id) AS total_payments,
                COALESCE(SUM(p.amount), 0) AS total_amount,
                COALESCE(AVG(p.amount), 0) AS average_amount
            FROM payments p
            {$where}
            GROUP BY {$groupExpression}, p.gateway
            ORDER BY period ASC,
                total_amount DESC,
                p.gateway ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function customerPerformanceSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
            SELECT
                u.id AS customer_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.total_amount), 0) AS total_spent,
                COALESCE(AVG(o.total_amount), 0) AS average_order_value,
                COUNT(DISTINCT p.id) AS total_payments,
                COALESCE(
                    SUM(
                        CASE
                            WHEN p.status = 'successful'
                            THEN p.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS successful_payment_amount,
                COUNT(DISTINCT r.id) AS total_returns
            FROM users u
            INNER JOIN orders o
                ON o.user_id = u.id
            LEFT JOIN payments p
                ON p.user_id = u.id
                AND p.order_id = o.id
            LEFT JOIN returns r
                ON r.user_id = u.id
                AND r.order_id = o.id
            {$where}
            AND u.deleted_at IS NULL
            GROUP BY
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone
            ORDER BY total_spent DESC,
                total_orders DESC,
                u.id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function customerOrderSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
            SELECT
                u.id AS customer_id,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.total_amount), 0) AS total_spent,
                COALESCE(AVG(o.total_amount), 0) AS average_order_value,
                MIN(o.created_at) AS first_order_at,
                MAX(o.created_at) AS last_order_at
            FROM users u
            INNER JOIN orders o
                ON o.user_id = u.id
            {$where}
            AND u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY total_spent DESC,
                total_orders DESC,
                u.id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function customerPaymentSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'p.created_at',
            'p.currency'
        );

        $sql = "
            SELECT
                u.id AS customer_id,
                COUNT(p.id) AS total_payments,
                COALESCE(SUM(p.amount), 0) AS total_payment_amount,
                COALESCE(
                    SUM(
                        CASE
                            WHEN p.status = 'successful'
                            THEN p.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS successful_payment_amount,
                COALESCE(AVG(p.amount), 0) AS average_payment_amount
            FROM users u
            INNER JOIN payments p
                ON p.user_id = u.id
            {$where}
            AND u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY total_payment_amount DESC,
                u.id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function customerReturnRefundSummary(
        ReportQuery $query
    ): array {
        [$returnWhere, $returnParams] = $this->buildWhere(
            $query,
            'r.created_at'
        );

        [$refundWhere, $refundParams] = $this->buildWhere(
            $query,
            'rf.created_at',
            'rf.currency'
        );

        $params = array_merge(
            $returnParams,
            $refundParams
        );

        $sql = "
            SELECT
                u.id AS customer_id,
                COALESCE(r.total_returns, 0) AS total_returns,
                COALESCE(r.completed_returns, 0) AS completed_returns,
                COALESCE(rf.total_refunds, 0) AS total_refunds,
                COALESCE(rf.total_refund_amount, 0) AS total_refund_amount
            FROM users u
            LEFT JOIN (
                SELECT
                    r.user_id,
                    COUNT(r.id) AS total_returns,
                    SUM(
                        CASE
                            WHEN r.status = 'completed'
                            THEN 1
                            ELSE 0
                        END
                    ) AS completed_returns
                FROM returns r
                {$returnWhere}
                GROUP BY r.user_id
            ) r
                ON r.user_id = u.id
            LEFT JOIN (
                SELECT
                    rf.user_id,
                    COUNT(rf.id) AS total_refunds,
                    COALESCE(SUM(rf.amount), 0) AS total_refund_amount
                FROM refunds rf
                {$refundWhere}
                GROUP BY rf.user_id
            ) rf
                ON rf.user_id = u.id
            WHERE u.deleted_at IS NULL
            ORDER BY total_refund_amount DESC,
                total_returns DESC,
                u.id ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function customerPerformanceByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $groupExpression = $this->dateGroupExpression(
            $query->groupBy,
            'o.created_at'
        );

        $sql = "
            SELECT
                {$groupExpression} AS period,
                COUNT(DISTINCT o.user_id) AS total_customers,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.total_amount), 0) AS total_spent,
                COALESCE(AVG(o.total_amount), 0) AS average_order_value
            FROM orders o
            {$where}
            GROUP BY {$groupExpression}
            ORDER BY period ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function customerStatusSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'u.created_at'
        );

        $sql = "
            SELECT
                u.status,
                COUNT(u.id) AS total_customers,
                COUNT(
                    DISTINCT CASE
                        WHEN o.id IS NOT NULL
                        THEN u.id
                    END
                ) AS customers_with_orders
            FROM users u
            LEFT JOIN orders o
                ON o.user_id = u.id
            {$where}
            AND u.deleted_at IS NULL
            GROUP BY u.status
            ORDER BY total_customers DESC,
                u.status ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function salesRevenueSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
        SELECT
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.subtotal), 0) AS gross_sales,
            COALESCE(SUM(o.discount_amount), 0) AS total_discount,
            COALESCE(SUM(o.shipping_amount), 0) AS total_shipping,
            COALESCE(SUM(o.tax_amount), 0) AS total_tax,
            COALESCE(SUM(o.total_amount), 0) AS net_sales
        FROM orders o
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function salesRevenueByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $groupExpression =
            $this->dateGroupExpression(
                $query->groupBy,
                'o.created_at'
            );

        $sql = "
        SELECT
            {$groupExpression} AS period,
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.subtotal), 0) AS gross_sales,
            COALESCE(SUM(o.discount_amount), 0) AS total_discount,
            COALESCE(SUM(o.shipping_amount), 0) AS total_shipping,
            COALESCE(SUM(o.tax_amount), 0) AS total_tax,
            COALESCE(SUM(o.total_amount), 0) AS net_sales
        FROM orders o
        {$where}
        GROUP BY {$groupExpression}
        ORDER BY period ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function salesRevenueByCurrency(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at'
        );

        $sql = "
        SELECT
            o.currency,
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.subtotal), 0) AS gross_sales,
            COALESCE(SUM(o.discount_amount), 0) AS total_discount,
            COALESCE(SUM(o.shipping_amount), 0) AS total_shipping,
            COALESCE(SUM(o.tax_amount), 0) AS total_tax,
            COALESCE(SUM(o.total_amount), 0) AS net_sales
        FROM orders o
        {$where}
        GROUP BY o.currency
        ORDER BY net_sales DESC,
            o.currency ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function discountSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
        SELECT
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.discount_amount), 0)
                AS total_discount,
            COALESCE(AVG(o.discount_amount), 0)
                AS average_discount
        FROM orders o
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function taxShippingSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
        SELECT
            COALESCE(SUM(o.shipping_amount), 0)
                AS total_shipping,
            COALESCE(SUM(o.tax_amount), 0)
                AS total_tax
        FROM orders o
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function orderLifecycleSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
        SELECT
            COUNT(o.id) AS total_orders,
            COUNT(
                CASE
                    WHEN o.status = 'delivered'
                    THEN 1
                END
            ) AS delivered_orders,
            COUNT(
                CASE
                    WHEN o.status = 'cancelled'
                    THEN 1
                END
            ) AS cancelled_orders,
            COUNT(
                CASE
                    WHEN o.status IN ('returned', 'refunded')
                    THEN 1
                END
            ) AS returned_or_refunded_orders,
            COALESCE(SUM(o.total_amount), 0)
                AS total_order_amount,
            COALESCE(
                SUM(
                    CASE
                        WHEN o.status = 'delivered'
                        THEN o.total_amount
                        ELSE 0
                    END
                ),
                0
            ) AS delivered_order_amount
        FROM orders o
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function orderStatusPerformanceSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $sql = "
        SELECT
            o.status,
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.total_amount), 0)
                AS total_amount,
            COALESCE(AVG(o.total_amount), 0)
                AS average_order_amount
        FROM orders o
        {$where}
        GROUP BY o.status
        ORDER BY total_orders DESC,
            o.status ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function orderStatusPerformanceByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'o.created_at',
            'o.currency'
        );

        $groupExpression =
            $this->dateGroupExpression(
                $query->groupBy,
                'o.created_at'
            );

        $sql = "
        SELECT
            {$groupExpression} AS period,
            o.status,
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.total_amount), 0)
                AS total_amount
        FROM orders o
        {$where}
        GROUP BY {$groupExpression}, o.status
        ORDER BY period ASC,
            total_orders DESC,
            o.status ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function shipmentFulfillmentSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            's.created_at',
            's.currency'
        );

        $sql = "
        SELECT
            COUNT(s.id) AS total_shipments,
            COUNT(
                CASE
                    WHEN s.status = 'delivered'
                    THEN 1
                END
            ) AS delivered_shipments,
            COUNT(
                CASE
                    WHEN s.status IN (
                        'shipped',
                        'in_transit'
                    )
                    THEN 1
                END
            ) AS active_shipments,
            COUNT(
                CASE
                    WHEN s.status = 'cancelled'
                    THEN 1
                END
            ) AS cancelled_shipments,
            COALESCE(SUM(s.shipping_cost), 0)
                AS total_shipping_cost,
            COALESCE(
                AVG(
                    CASE
                        WHEN s.delivered_at IS NOT NULL
                        AND s.shipped_at IS NOT NULL
                        THEN TIMESTAMPDIFF(
                            HOUR,
                            s.shipped_at,
                            s.delivered_at
                        )
                    END
                ),
                0
            ) AS average_delivery_hours
        FROM shipments s
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function shipmentStatusPerformanceSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            's.created_at',
            's.currency'
        );

        $sql = "
        SELECT
            s.status,
            COUNT(s.id) AS total_shipments,
            COALESCE(SUM(s.shipping_cost), 0)
                AS total_shipping_cost,
            COUNT(
                CASE
                    WHEN s.delivered_at IS NOT NULL
                    THEN 1
                END
            ) AS delivered_count
        FROM shipments s
        {$where}
        GROUP BY s.status
        ORDER BY total_shipments DESC,
            s.status ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function shipmentDeliveryByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            's.created_at',
            's.currency'
        );

        $groupExpression =
            $this->dateGroupExpression(
                $query->groupBy,
                's.created_at'
            );

        $sql = "
        SELECT
            {$groupExpression} AS period,
            COUNT(s.id) AS total_shipments,
            COUNT(
                CASE
                    WHEN s.status = 'delivered'
                    THEN 1
                END
            ) AS delivered_shipments,
            COALESCE(SUM(s.shipping_cost), 0)
                AS total_shipping_cost
        FROM shipments s
        {$where}
        GROUP BY {$groupExpression}
        ORDER BY period ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function returnPerformanceSummary(
        ReportQuery $query
    ): array {
        [$returnWhere, $returnParams] =
            $this->buildWhere(
                $query,
                'r.created_at'
            );

        [$orderWhere, $orderParams] =
            $this->buildWhere(
                $query,
                'o.created_at',
                'o.currency'
            );

        $params = array_merge(
            $returnParams,
            $orderParams
        );

        $sql = "
        SELECT
            COUNT(DISTINCT o.id) AS total_orders,
            COUNT(DISTINCT r.id) AS total_returns,
            COUNT(
                DISTINCT CASE
                    WHEN r.status = 'completed'
                    THEN r.id
                END
            ) AS completed_returns,
            COUNT(
                DISTINCT CASE
                    WHEN r.status = 'rejected'
                    THEN r.id
                END
            ) AS rejected_returns,
            COALESCE(
                COUNT(DISTINCT r.id)
                /
                NULLIF(COUNT(DISTINCT o.id), 0),
                0
            ) AS return_rate
        FROM orders o
        LEFT JOIN returns r
            ON r.order_id = o.id
        {$orderWhere}
        AND (
            r.id IS NULL
            OR r.created_at >= :return_from_date
        )
    ";

        if (
            isset($returnParams[':from_date'])
        ) {
            $params[':return_from_date'] =
                $returnParams[':from_date'];
        } else {
            $params[':return_from_date'] =
                '1000-01-01 00:00:00';
        }

        return $this->fetchOne($sql, $params);
    }

    public function refundPerformanceSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'rf.created_at',
            'rf.currency'
        );

        $sql = "
        SELECT
            COUNT(rf.id) AS total_refunds,
            COUNT(
                CASE
                    WHEN rf.status = 'successful'
                    THEN 1
                END
            ) AS successful_refunds,
            COUNT(
                CASE
                    WHEN rf.status = 'failed'
                    THEN 1
                END
            ) AS failed_refunds,
            COALESCE(SUM(rf.amount), 0)
                AS total_refund_amount,
            COALESCE(
                SUM(
                    CASE
                        WHEN rf.status = 'successful'
                        THEN rf.amount
                        ELSE 0
                    END
                ),
                0
            ) AS successful_refund_amount,
            COALESCE(AVG(rf.amount), 0)
                AS average_refund_amount
        FROM refunds rf
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function refundPerformanceByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'rf.created_at',
            'rf.currency'
        );

        $groupExpression =
            $this->dateGroupExpression(
                $query->groupBy,
                'rf.created_at'
            );

        $sql = "
        SELECT
            {$groupExpression} AS period,
            COUNT(rf.id) AS total_refunds,
            COUNT(
                CASE
                    WHEN rf.status = 'successful'
                    THEN 1
                END
            ) AS successful_refunds,
            COALESCE(SUM(rf.amount), 0)
                AS total_refund_amount,
            COALESCE(
                SUM(
                    CASE
                        WHEN rf.status = 'successful'
                        THEN rf.amount
                        ELSE 0
                    END
                ),
                0
            ) AS successful_refund_amount
        FROM refunds rf
        {$where}
        GROUP BY {$groupExpression}
        ORDER BY period ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    private function statusSummary(
        ReportQuery $query,
        string $table,
        string $dateColumn,
        ?string $currencyColumn,
        string $statusColumn,
        string $groupColumn
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            $dateColumn,
            $currencyColumn
        );

        $alias = $this->resolveAlias($table);

        $sql = "
            SELECT
                {$groupColumn} AS status,
                COUNT(*) AS total
            FROM {$table} {$alias}
            {$where}
            GROUP BY {$groupColumn}
            ORDER BY total DESC,
                {$groupColumn} ASC
        ";

        return $this->fetchAll($sql, $params);
    }

    private function buildWhere(
        ReportQuery $query,
        string $dateColumn,
        ?string $currencyColumn = null
    ): array {
        $conditions = [];
        $params = [];

        if ($query->hasDateRange()) {
            $conditions[] = $dateColumn . ' >= :from_date';
            $conditions[] = $dateColumn . ' < :to_date';

            $params[':from_date'] =
                $query->startDateTime();

            $params[':to_date'] =
                $query->endDateTimeExclusive();
        }

        if (
            $currencyColumn !== null
            && $query->hasCurrencyFilter()
        ) {
            $conditions[] =
                $currencyColumn .
                ' = :currency';

            $params[':currency'] =
                $query->currency;
        }

        if ($query->hasStatusFilter()) {
            $alias =
                $this->resolveAliasFromColumn(
                    $dateColumn
                );

            $conditions[] =
                $alias .
                '.status = :status';

            $params[':status'] =
                $query->status;
        }

        if ($query->hasStatusesFilter()) {
            $alias =
                $this->resolveAliasFromColumn(
                    $dateColumn
                );

            $this->appendInFilter(
                $conditions,
                $params,
                $alias . '.status',
                'status',
                $query->statuses
            );
        }

        return [
            $this->whereClause($conditions),
            $params
        ];
    }

    private function whereClause(
        array $conditions
    ): string {
        if ($conditions === []) {
            return '';
        }

        return 'WHERE ' .
            implode(' AND ', $conditions);
    }

    private function resolveAlias(
        string $table
    ): string {
        return match ($table) {
            'orders' => 'o',
            'payments' => 'p',
            'payment_attempts' => 'pa',
            'shipments' => 's',
            'returns' => 'r',
            'refunds' => 'rf',
            'inventory_reservations' => 'ir',
            'inventory_movements' => 'im',
            default => 't',
        };
    }

    private function resolveAliasFromColumn(
        string $column
    ): string {
        $parts =
            explode('.', $column, 2);

        return $parts[0] ?? 't';
    }

    private function appendInFilter(
        array &$conditions,
        array &$params,
        string $column,
        string $parameterPrefix,
        array $values
    ): void {
        $placeholders = [];

        foreach (
            array_values($values)
            as $index => $value
        ) {
            $placeholder =
                ':' .
                $parameterPrefix .
                '_' .
                $index;

            $placeholders[] =
                $placeholder;

            $params[$placeholder] =
                $value;
        }

        if ($placeholders === []) {
            return;
        }

        $conditions[] =
            $column .
            ' IN (' .
            implode(', ', $placeholders) .
            ')';
    }

    private function dateGroupExpression(
        string $groupBy,
        string $column
    ): string {
        return match ($groupBy) {
            ReportContract::GROUP_WEEK =>
            "DATE_SUB(
                    DATE({$column}),
                    INTERVAL WEEKDAY({$column}) DAY
                )",

            ReportContract::GROUP_MONTH =>
            "DATE_FORMAT(
                    {$column},
                    '%Y-%m-01'
                )",

            default =>
            "DATE({$column})",
        };
    }

    private function fetchOne(
        string $sql,
        array $params
    ): array {
        $statement =
            $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue(
                $key,
                $value
            );
        }

        $statement->execute();

        $row =
            $statement->fetch(
                PDO::FETCH_ASSOC
            );

        return $row ?: [];
    }

    private function fetchAll(
        string $sql,
        array $params
    ): array {
        $statement =
            $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue(
                $key,
                $value
            );
        }

        $statement->execute();

        return $statement->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function inventoryStockSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'i.updated_at'
        );

        $sql = "
        SELECT
            COUNT(i.id) AS total_inventory_records,
            COALESCE(SUM(i.quantity), 0)
                AS total_quantity,
            COALESCE(SUM(i.reserved_quantity), 0)
                AS total_reserved_quantity,
            COALESCE(
                SUM(
                    CASE
                        WHEN i.quantity > i.reserved_quantity
                        THEN i.quantity - i.reserved_quantity
                        ELSE 0
                    END
                ),
                0
            ) AS total_available_quantity
        FROM inventory i
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryLowStockSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'i.updated_at'
        );

        $sql = "
        SELECT
            COUNT(i.id) AS low_stock_records,
            COALESCE(SUM(i.quantity), 0)
                AS low_stock_quantity
        FROM inventory i
        {$where}
        AND i.quantity > 0
        AND i.quantity <= i.low_stock_threshold
    ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryOutOfStockSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'i.updated_at'
        );

        $sql = "
        SELECT
            COUNT(i.id) AS out_of_stock_records
        FROM inventory i
        {$where}
        AND i.quantity = 0
    ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryReservedSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'i.updated_at'
        );

        $sql = "
        SELECT
            COUNT(
                CASE
                    WHEN i.reserved_quantity > 0
                    THEN 1
                END
            ) AS reserved_inventory_records,
            COALESCE(SUM(i.reserved_quantity), 0)
                AS total_reserved_quantity,
            COALESCE(
                AVG(
                    CASE
                        WHEN i.quantity > 0
                        THEN (
                            i.reserved_quantity * 100.0
                        ) / i.quantity
                    END
                ),
                0
            ) AS average_reserved_percentage
        FROM inventory i
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryAvailableSummary(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'i.updated_at'
        );

        $sql = "
        SELECT
            COUNT(i.id) AS inventory_records,
            COALESCE(
                SUM(
                    CASE
                        WHEN i.quantity > i.reserved_quantity
                        THEN i.quantity - i.reserved_quantity
                        ELSE 0
                    END
                ),
                0
            ) AS available_quantity,
            COALESCE(
                AVG(
                    CASE
                        WHEN i.quantity > 0
                        THEN (
                            GREATEST(
                                i.quantity -
                                i.reserved_quantity,
                                0
                            ) * 100.0
                        ) / i.quantity
                    END
                ),
                0
            ) AS average_available_percentage
        FROM inventory i
        {$where}
    ";

        return $this->fetchOne($sql, $params);
    }

    public function inventoryByVariant(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'i.updated_at'
        );

        $sql = "
        SELECT
            i.variant_id,
            i.quantity,
            i.reserved_quantity,
            GREATEST(
                i.quantity - i.reserved_quantity,
                0
            ) AS available_quantity,
            i.low_stock_threshold,
            CASE
                WHEN i.quantity = 0
                    THEN 'out_of_stock'
                WHEN i.quantity <= i.low_stock_threshold
                    THEN 'low_stock'
                ELSE 'in_stock'
            END AS stock_status
        FROM inventory i
        {$where}
        ORDER BY
            stock_status ASC,
            i.quantity ASC,
            i.variant_id ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function inventoryMovementByPeriod(
        ReportQuery $query
    ): array {
        [$where, $params] = $this->buildWhere(
            $query,
            'im.created_at'
        );

        $groupExpression =
            $this->dateGroupExpression(
                $query->groupBy,
                'im.created_at'
            );

        $sql = "
        SELECT
            {$groupExpression} AS period,
            COUNT(im.id) AS movement_count,
            COALESCE(SUM(im.quantity), 0)
                AS total_quantity
        FROM inventory_movements im
        {$where}
        GROUP BY {$groupExpression}
        ORDER BY period ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function inventoryMovementTypeSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere(
            $query,
            'im.created_at'
        );

        $sql = "
        SELECT
            im.movement_type,
            COUNT(im.id) AS movement_count,
            COALESCE(SUM(im.quantity), 0) AS total_quantity,
            COALESCE(AVG(im.quantity), 0) AS average_quantity
        FROM inventory_movements im
        {$where}
        GROUP BY im.movement_type
        ORDER BY im.movement_type ASC
    ";

        return $this->fetchAll($sql, $params);
    }

    public function inventoryReservationStatusSummary(ReportQuery $query): array
    {
        [$where, $params] = $this->buildWhere(
            $query,
            'ir.created_at'
        );

        $sql = "
        SELECT
            ir.status,
            COUNT(ir.id) AS reservation_count,
            COALESCE(SUM(ir.quantity), 0) AS total_quantity,
            COALESCE(AVG(ir.quantity), 0) AS average_quantity
        FROM inventory_reservations ir
        {$where}
        GROUP BY ir.status
        ORDER BY ir.status ASC
    ";

        return $this->fetchAll($sql, $params);
    }
}
