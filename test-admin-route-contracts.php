<?php

declare(strict_types=1);

/**
 * Static audit of the API route table. Does not boot the application or DB.
 * Known unclassified route families are surfaced explicitly rather than
 * receiving an inferred permission.
 */

$passed = 0;
$failed = 0;

function adminRouteCheck(string $name, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$name}\n";
    } else {
        $failed++;
        echo "[FAIL] {$name}\n";
    }
}

require_once __DIR__ . '/app/Core/AdminFeatureRegistry.php';
require_once __DIR__ . '/app/Core/AdminRouteFeatureMap.php';

$routeFile = __DIR__ . '/routes/api.php';
$source = is_file($routeFile) ? file_get_contents($routeFile) : false;
adminRouteCheck('API route source is readable', is_string($source));

if (!is_string($source)) {
    echo "Summary: {$passed} PASS, {$failed} FAIL\n";
    exit(1);
}

preg_match_all('/\$router->(get|post|put|patch|delete)\(/i', $source, $matches, PREG_OFFSET_CAPTURE);
$starts = $matches[0] ?? [];
$adminRoutes = [];
$unclassified = [];
$unauthenticated = [];
$mutationsWithoutCsrf = [];
$unknownFeatureReferences = [];

for ($index = 0, $count = count($starts); $index < $count; $index++) {
    $start = $starts[$index][1];
    $end = $starts[$index + 1][1] ?? strlen($source);
    $block = substr($source, $start, $end - $start);

    if (preg_match('/^\$router->\w+\(\s*\'([^\']+)\'/s', $block, $pathMatch) !== 1) {
        continue;
    }

    $path = $pathMatch[1];
    if (!str_starts_with($path, '/admin')) {
        continue;
    }

    preg_match_all("/'([^']+)'/", $block, $quoted);
    $routeName = null;
    foreach (array_reverse($quoted[1] ?? []) as $candidate) {
        if (str_contains($candidate, '.')) {
            $routeName = $candidate;
            break;
        }
    }

    $method = strtoupper($matches[1][$index][0]);
    $route = [
        'method' => $method,
        'path' => $path,
        'name' => $routeName,
        'block' => $block,
    ];
    $adminRoutes[] = $route;

    if (!str_contains($block, 'AdminAuthMiddleware::class')) {
        $unauthenticated[] = $routeName ?? $path;
    }

    if (
        in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) &&
        !str_contains($block, 'CsrfMiddleware::class')
    ) {
        $mutationsWithoutCsrf[] = $routeName ?? $path;
    }

    if ($routeName === null) {
        $unclassified[] = $path;
        continue;
    }

    $featureId = AdminRouteFeatureMap::featureForRoute($routeName);
    if ($featureId === null) {
        $unclassified[] = $routeName;
        continue;
    }

    if (AdminFeatureRegistry::find($featureId) === null) {
        $unknownFeatureReferences[] = [
            'route' => $routeName,
            'feature' => $featureId,
        ];
    }
}

$expectedUnclassified = [
    'admin.notifications.create',
    'admin.reviews.index',
    'admin.reviews.show',
    'admin.reviews.status',
    'admin.reviews.note',
    'admin.questions.index',
    'admin.questions.show',
    'admin.questions.status',
    'admin.questions.note',
    'admin.questions.answers.store',
    'admin.answers.status',
    'admin.users.index',
    'admin.users.show',
    'admin.users.status',
];
sort($unclassified);
sort($expectedUnclassified);

adminRouteCheck('All 161 admin API routes are represented in the static inventory', count($adminRoutes) === 161);
adminRouteCheck('Every admin API route requires AdminAuthMiddleware', $unauthenticated === []);
adminRouteCheck('Every mutating admin API route requires CSRF middleware', $mutationsWithoutCsrf === []);
adminRouteCheck('Every mapped route points to a known feature ID', $unknownFeatureReferences === []);
adminRouteCheck('Only explicitly unresolved route families remain unclassified', $unclassified === $expectedUnclassified);

echo "Admin API routes audited: " . count($adminRoutes) . "\n";
echo "Unclassified routes (DB/feature-contract gate): " . count($unclassified) . "\n";
if ($unclassified !== []) {
    echo "Unclassified route names:\n";
    foreach ($unclassified as $routeName) {
        echo " - {$routeName}\n";
    }
}
echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
echo "Mode: static route-source audit; no application boot, DB, or HTTP requests.\n";

exit($failed === 0 ? 0 : 1);
