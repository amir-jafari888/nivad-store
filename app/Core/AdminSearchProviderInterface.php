<?php

declare(strict_types=1);

/**
 * Contract for a permission-scoped admin search source.
 * Implementations may query products/orders/users only after DB/schema review.
 */
interface AdminSearchProviderInterface
{
    public function featureId(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $limit): array;
}
