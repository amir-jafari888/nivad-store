<?php

/** Non-DB global-search query contract. */
final class AdminSearchPolicy
{
    public static function normalize(array $input): array
    {
        $query = trim((string)($input['query'] ?? ''));
        if ($query === '' || mb_strlen($query) > 120) {
            throw new InvalidArgumentException('Search query must contain 1 to 120 characters.');
        }

        $page = max(1, (int)($input['page'] ?? 1));
        $limit = (int)($input['limit'] ?? 20);
        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Search page size must be between 1 and 100.');
        }

        $allowedScopes = ['products', 'orders', 'customers', 'admins', 'tickets', 'content'];
        $scope = (string)($input['scope'] ?? 'all');
        if ($scope !== 'all' && !in_array($scope, $allowedScopes, true)) {
            throw new InvalidArgumentException('Unsupported admin search scope.');
        }

        return ['query' => $query, 'page' => $page, 'limit' => $limit, 'scope' => $scope];
    }

    public static function assertProviderResult(array $result): array
    {
        $items = $result['items'] ?? null;
        $total = $result['total'] ?? null;
        if (!is_array($items) || !is_int($total) || $total < 0) {
            throw new UnexpectedValueException('Invalid admin search provider result.');
        }
        return ['items' => $items, 'total' => $total];
    }
}
