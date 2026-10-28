<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';

Bootstrap::boot();

require_once __DIR__ . '/includes/auth.php';

/*
 * The direct /admin directory entry and the Router-backed dashboard share
 * the same view and metrics contract to prevent two divergent dashboards.
 */
$admin = $currentAdmin;
$metrics = (new AdminDashboardMetricsService())->metrics();

require dirname(__DIR__) . '/pages/admin/dashboard.php';
