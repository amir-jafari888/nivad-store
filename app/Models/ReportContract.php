<?php

declare(strict_types=1);

final class ReportContract
{
    public const GROUP_DAY = 'day';
    public const GROUP_WEEK = 'week';
    public const GROUP_MONTH = 'month';

    public const PERIOD_DATE_FORMAT = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public const DATA_SOURCES = [
        'orders',
        'order_items',
        'payments',
        'payment_attempts',
        'users',
        'products',
        'product_variants',
        'inventory',
        'inventory_movements',
        'inventory_reservations',
        'shipments',
        'returns',
        'refunds',
    ];

    public const GROUP_BY = [
        self::GROUP_DAY,
        self::GROUP_WEEK,
        self::GROUP_MONTH,
    ];

    public const METRICS = [
        'count',
        'amount',
        'quantity',
        'average',
    ];

    public const FILTER_KEYS = [
        'currency',
        'status',
        'statuses',
    ];

    public static function isDataSource(string $source): bool
    {
        return in_array($source, self::DATA_SOURCES, true);
    }

    public static function isGroupBy(string $groupBy): bool
    {
        return in_array($groupBy, self::GROUP_BY, true);
    }

    public static function isMetric(string $metric): bool
    {
        return in_array($metric, self::METRICS, true);
    }
}
