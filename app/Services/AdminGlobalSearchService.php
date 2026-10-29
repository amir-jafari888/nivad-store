<?php

declare(strict_types=1);

/**
 * Permission-aware search orchestrator. Data providers are injected so their
 * DB dependencies can be added only after the relevant schema contracts pass.
 */
final class AdminGlobalSearchService
{
    /** @var array<int, AdminSearchProviderInterface> */
    private array $providers;
    private \Closure $canAccess;

    public function __construct(
        array $providers = [],
        ?callable $canAccess = null
    ) {
        foreach ($providers as $provider) {
            if (!$provider instanceof AdminSearchProviderInterface) {
                throw new InvalidArgumentException('Every search provider must implement AdminSearchProviderInterface.');
            }
        }

        $this->providers = array_values($providers);
        $this->canAccess = $canAccess !== null
            ? \Closure::fromCallable($canAccess)
            : static fn(int $adminId, string $featureId): bool =>
                (new AdminFeatureAccessService())->canAccess($adminId, $featureId);
    }

    public function search(int $adminUserId, string $query, int $limit = 20): array
    {
        if ($adminUserId < 1) {
            throw new InvalidArgumentException('A valid admin ID is required.');
        }

        $query = trim($query);
        if ($query === '' || mb_strlen($query) < 2) {
            return [
                'query' => $query,
                'results' => [],
                'total' => 0,
            ];
        }

        if (mb_strlen($query) > 120) {
            $query = mb_substr($query, 0, 120);
        }

        $limit = max(1, min(20, $limit));
        $results = [];
        $seen = [];

        foreach ($this->providers as $provider) {
            $featureId = strtoupper(trim($provider->featureId()));

            if (
                AdminFeatureRegistry::find($featureId) === null ||
                !(($this->canAccess)($adminUserId, $featureId))
            ) {
                continue;
            }

            $providerResults = $provider->search($query, min(10, $limit));
            foreach ($providerResults as $item) {
                if (count($results) >= $limit || !is_array($item)) {
                    break;
                }

                $normalized = $this->normalizeResult($item);
                if ($normalized === null) {
                    continue;
                }

                $key = $normalized['type'] . ':' . $normalized['id'];
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $results[] = $normalized;
            }
        }

        usort(
            $results,
            static fn(array $a, array $b): int => $b['score'] <=> $a['score']
        );

        return [
            'query' => $query,
            'results' => $results,
            'total' => count($results),
        ];
    }

    private function normalizeResult(array $item): ?array
    {
        $id = $item['id'] ?? null;
        $type = $item['type'] ?? null;
        $title = $item['title'] ?? null;
        $url = $item['url'] ?? null;

        if (
            (!is_string($id) && !is_int($id)) ||
            !is_string($type) ||
            preg_match('/^[a-z][a-z0-9_-]{0,39}$/', $type) !== 1 ||
            !is_string($title) ||
            trim($title) === '' ||
            mb_strlen($title) > 200 ||
            !is_string($url) ||
            !$this->isInternalAdminUrl($url)
        ) {
            return null;
        }

        $subtitle = $item['subtitle'] ?? null;
        if ($subtitle !== null && !is_string($subtitle)) {
            $subtitle = null;
        }

        $score = $item['score'] ?? 0;
        $score = is_numeric($score) ? (float) $score : 0.0;

        return [
            'id' => (string) $id,
            'type' => $type,
            'title' => trim($title),
            'subtitle' => $subtitle !== null ? mb_substr(trim($subtitle), 0, 240) : null,
            'url' => $url,
            'score' => max(0.0, min(1.0, $score)),
        ];
    }

    private function isInternalAdminUrl(string $url): bool
    {
        if (
            $url === '' ||
            str_contains($url, "\r") ||
            str_contains($url, "\n") ||
            str_starts_with($url, '//') ||
            str_starts_with($url, '\\\\')
        ) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host'])) {
            return false;
        }

        $path = $parts['path'] ?? '';
        return is_string($path)
            && ($path === '/admin' || str_starts_with($path, '/admin/'))
            && !str_contains($path, '..');
    }
}
