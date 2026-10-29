<?php

declare(strict_types=1);

/**
 * Explicit allowlist for bulk/background operations. Adding a job type here
 * requires a worker implementation and regression tests; arbitrary job names
 * are never accepted.
 */
final class AdminBulkOperationRegistry
{
    public static function all(): array
    {
        return [
            'catalog.products.status' => [
                'feature_id' => 'ADM-011',
                'max_items' => 100,
                'requires_approval' => false,
            ],
            'catalog.categories.status' => [
                'feature_id' => 'ADM-011',
                'max_items' => 100,
                'requires_approval' => false,
            ],
            'inventory.adjust' => [
                'feature_id' => 'ADM-013',
                'max_items' => 50,
                'requires_approval' => true,
            ],
            'orders.status' => [
                'feature_id' => 'ADM-012',
                'max_items' => 50,
                'requires_approval' => true,
            ],
            'coupons.status' => [
                'feature_id' => 'ADM-012',
                'max_items' => 100,
                'requires_approval' => false,
            ],
            'content.publish' => [
                'feature_id' => 'ADM-033',
                'max_items' => 50,
                'requires_approval' => true,
            ],
            'media.archive' => [
                'feature_id' => 'ADM-015',
                'max_items' => 100,
                'requires_approval' => false,
            ],
        ];
    }

    public static function find(string $jobType): ?array
    {
        return self::all()[strtolower(trim($jobType))] ?? null;
    }
}
