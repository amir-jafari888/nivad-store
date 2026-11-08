<?php

declare(strict_types=1);

/**
 * Admin UI shell contract regression — read-only static test.
 * Does not boot the application, connect to the database, or mutate data.
 */

$root = __DIR__;
$passed = 0;
$failed = 0;

$read = static function (string $relativePath) use ($root): string {
    $path = $root . DIRECTORY_SEPARATOR
        . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }
    $content = file_get_contents($path);
    return is_string($content) ? $content : '';
};

$check = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
        return;
    }
    $failed++;
    echo "[FAIL] {$label}\n";
};

$tokens = $read('admin/assets/css/setting.css');
$storeTokens = $read('assets/css/setting.css');
$desktop = $read('admin/assets/css/desktop/shared-shell-desktop.css');
$tablet = $read('admin/assets/css/tablet/shared-shell-tablet.css');
$mobile = $read('admin/assets/css/mobile/shared-shell-mobile.css');
$mainCss = $read('admin/assets/css/main.css');
$mainJs = $read('admin/assets/js/main.js');
$navigationJs = $read('admin/assets/js/modules/navigation-state.js');
$sharedAuth = $read('admin/includes/auth.php');
$dashboard = $read('pages/admin/dashboard.php');
$metricsService = $read('app/Services/AdminDashboardMetricsService.php');
$overviewService = $read('app/Services/AdminDashboardOverviewService.php');
$healthService = $read('app/Services/AdminSystemHealthService.php');
$profilePage = $read('admin/profile/index.php');
$profileController = $read('app/Controllers/AdminProfileController.php');
$profileRepository = $read('app/Repositories/AdminRepository.php');
$profileJs = $read('admin/assets/js/modules/admin-profile.js');
$sessionRepository = $read('app/Repositories/AdminSessionRepository.php');
$apiRoutes = $read('routes/api.php');
$productsPage = $read('admin/products/index.php');
$productCreatePage = $read('admin/products/create.php');
$productRewrites = $read('.htaccess');
$productCss = $read('admin/assets/css/admin-shell-overrides.css');
$categoriesPage = $read('admin/categories/index.php');
$categoryCreatePage = $read('admin/categories/create.php');


$check('Admin design token file exists', $tokens !== '');
$check('Unused dark theme module has been removed after the Light-only decision', !is_file($root . '/admin/assets/js/modules/theme.js'));
$check('Admin palette stays aligned with storefront primary/background/surface tokens',
    preg_match('/--admin-color-primary:\s*(#[0-9a-f]{6})/i', $tokens, $adminPrimary) === 1
    && preg_match('/--color-primary:\s*(#[0-9a-f]{6})/i', $storeTokens, $storePrimary) === 1
    && strtolower($adminPrimary[1]) === strtolower($storePrimary[1])
    && preg_match('/--admin-color-bg:\s*(#[0-9a-f]{6})/i', $tokens, $adminBg) === 1
    && preg_match('/--color-background:\s*(#[0-9a-f]{6})/i', $storeTokens, $storeBg) === 1
    && strtolower($adminBg[1]) === strtolower($storeBg[1])
);
$check('Admin uses the existing Persian/Latin font assets', str_contains($tokens, 'font-family: "Vazirmatn"') && str_contains($tokens, 'font-family: "Inter"'));
$check('Desktop sidebar is anchored to the physical right in RTL', str_contains($desktop, 'inset-inline-start:0;inset-inline-end:auto'));
$check('Desktop content reserves space for the fixed sidebar', str_contains($desktop, 'margin-inline-start:264px') && str_contains($desktop, '.admin-shared-shell main{'));
$check('Tablet navigation uses an accessible right-side drawer', str_contains($tablet, 'transform:translateX(110%)') && str_contains($tablet, 'inset-inline-start:0'));
$check('Mobile navigation uses an accessible right-side drawer', str_contains($mobile, 'transform:translateX(110%)') && str_contains($mobile, '.admin-nav-open .admin-shared-sidebar'));
$check('Shared shell emits the real mobile toggle and sidebar hooks', str_contains($sharedAuth, 'data-admin-menu-toggle') && str_contains($sharedAuth, 'data-admin-sidebar') && str_contains($sharedAuth, 'aria-controls="admin-shared-sidebar"'));
$check('Responsive navigation supports toggle, Escape, backdrop and inert content', str_contains($navigationJs, 'data-admin-menu-toggle') && str_contains($navigationJs, 'event.key === "Escape"') && str_contains($navigationJs, 'event.key !== "Tab"') && str_contains($navigationJs, 'admin-nav-close') && str_contains($navigationJs, 'sidebar.inert') && str_contains($navigationJs, 'admin-nav-backdrop'));
$check('Responsive navigation module is wired through main.js', str_contains($mainJs, 'navigation-state.js') && str_contains($mainJs, 'adminNavigationState()'));
$check('Shared shell adds skip navigation to legacy pages with a semantic main target', str_contains($sharedAuth, '$skipLink = \'<a class="admin-skip-link"') && str_contains($sharedAuth, 'preg_match(\'/<main\\\\b([^>]*)>/i\'') && str_contains($sharedAuth, 'main-content'));
$check('Shared shell profile exposes first and last name separately', str_contains($sharedAuth, 'admin-shared-first-name') && str_contains($sharedAuth, 'admin-shared-last-name'));
$check('Dashboard chart uses accessible real-data bar-chart markup', str_contains($dashboard, 'role="img"') && str_contains($dashboard, 'نمودار میله‌ای') && str_contains($dashboard, 'admin-dashboard-chart-bar'));
$check('Dashboard receives the shared first/last-name identity from the shell', str_contains($sharedAuth, 'admin-shared-first-name') && str_contains($sharedAuth, 'admin-shared-last-name'));
$check('Dashboard charts use real report rows and the current 14-day query', str_contains($dashboard, '$salesByPeriod') && str_contains($metricsService, 'salesRevenueByPeriod($currentQuery)') && str_contains($dashboard, '۱۴ روز اخیر'));
$check('Dashboard separates all-time KPI data from current and previous 14-day comparisons', str_contains($metricsService, 'period_comparison') && str_contains($metricsService, "modify('-13 days')") && str_contains($metricsService, "modify('-27 days')") && str_contains($metricsService, 'unset($allTimeData[\'from_date\'], $allTimeData[\'to_date\'])'));
$check('Dashboard view displays period comparisons and audit event references', str_contains($dashboard, 'admin-dashboard-period-comparison') && str_contains($dashboard, 'شناسه رویداد: #'));
$check('Action queue sorts items by explicit priority', str_contains($overviewService, "'critical' => 0") && str_contains($overviewService, 'usort($queueItems'));
$check('System health checks required PHP extensions and database connectivity', str_contains($healthService, "['pdo', 'pdo_mysql', 'json', 'mbstring']") && str_contains($healthService, "query('SELECT 1')"));
$check('Responsive CSS imports preserve the established 600/601–1009/1010 breakpoints', str_contains($mainCss, 'max-width: 600px') && str_contains($mainCss, 'min-width: 601px') && str_contains($mainCss, 'max-width: 1009px') && str_contains($mainCss, 'min-width: 1010px'));
$check('No database or destructive operation is introduced by this UI contract test', !preg_match('/\\b(INSERT|UPDATE|DELETE|DROP|ALTER)\\b/i', $dashboard));
$check('Profile page exposes editable identity and password forms with read-only assigned roles',
    str_contains($profilePage, 'data-admin-profile-form')
    && str_contains($profilePage, 'data-admin-password-form')
    && str_contains($profilePage, 'roleNamesForAdmin')
    && !str_contains($profilePage, 'role_ids')
);
$check('Profile update is session-bound, reauthenticated, CSRF-protected and does not mutate roles',
    str_contains($profileController, 'requireCurrentPassword')
    && str_contains($profileController, 'Password::hash')
    && str_contains($profileController, 'updateOwnProfile')
    && !str_contains($profileController, 'role_ids')
    && str_contains($apiRoutes, "'admin.profile.update'")
    && str_contains($profileJs, 'X-CSRF-TOKEN')
);
$check('Password changes revoke other active sessions without revoking the current session',
    str_contains($profileController, 'revokeOtherSessionsForAdmin')
    && str_contains($sessionRepository, 'session_token_hash <> :current_token_hash')
    && str_contains($sessionRepository, 'revokeOtherSessionsForAdmin')
);


$check('Product create and edit pretty URLs rewrite to their existing PHP page files',
    str_contains($productRewrites, 'RewriteRule ^nivad-panel/products/create/?$ admin/products/create.php [END,QSA]')
    && str_contains($productRewrites, 'RewriteRule ^nivad-panel/products/edit/?$ admin/products/edit.php [END,QSA]')
    && str_contains($productRewrites, 'RewriteRule ^nivad-panel/products/?$ admin/products/index.php [END,QSA]')
);
$check('Products index uses the shared admin shell and existing product data contract',
    str_contains($productsPage, "require_once __DIR__ . '/../includes/auth.php'")
    && str_contains($productsPage, 'adminFindAll()')
    && str_contains($productsPage, 'admin-products-table')
    && str_contains($productsPage, "url('/admin/products/create')")
    && str_contains($productsPage, "url('/admin/products/edit?id='")
);
$check('Products index filters are escaped and restricted to known statuses',
    str_contains($productsPage, "Request::query('q'")
    && str_contains($productsPage, "Request::query('status'")
    && str_contains($productsPage, 'in_array($statusFilter, $allowedStatuses, true)')
    && str_contains($productsPage, 'e($search)')
);
$check('Products catalog has responsive styling and shared spacing tokens',
    str_contains($productCss, '.admin-products-summary')
    && str_contains($productCss, '@media (max-width: 600px)')
    && str_contains($productCss, '--admin-space-')
);
$check('Product filter controls share one bottom-aligned desktop row and a 46px control height',
    str_contains($productCss, '.admin-products-filters {')
    && str_contains($productCss, 'align-items: end;')
    && str_contains($productCss, '.admin-products-filter-actions {')
    && str_contains($productCss, 'align-self: end;')
    && str_contains($productCss, 'height: 46px;')
    && str_contains($productCss, '.admin-products-filter-actions button {')
);

$check('Product status dropdown keeps the browser-native arrow visible',
    !str_contains($productCss, 'appearance: none;')
    && !str_contains($productCss, 'background-position: left 14px center;')
    && !str_contains($productCss, 'padding-inline-end: calc(var(--admin-space-4) + 20px);')
);
$check('Product filter actions do not use compensating top padding',
    !str_contains($productCss, 'padding-block-start: calc(12px * 1.6 + var(--admin-space-2));')
);
$check('Product creation uses active brand and product-type selectors instead of manual IDs',
    str_contains($productCreatePage, '$brandService->findActive()')
    && str_contains($productCreatePage, '$productTypeService->findActive()')
    && str_contains($productCreatePage, '<select name="brandId"')
    && str_contains($productCreatePage, '<select name="productTypeId"')
    && !str_contains($productCreatePage, 'شناسه برند<input')
    && !str_contains($productCreatePage, 'شناسه نوع محصول<input')
);
$check('Product creation groups core details, descriptions, publishing and optional SEO',
    str_contains($productCreatePage, 'product-create-basic-title')
    && str_contains($productCreatePage, 'product-create-description-title')
    && str_contains($productCreatePage, 'product-create-publish-title')
    && str_contains($productCreatePage, '<details class="admin-product-create-seo">')
    && str_contains($productCss, '.admin-product-create-fields')
    && str_contains($productCss, '@media (max-width: 600px)')
);



$check('Categories index reuses the shared admin shell and CategoryService data contract',
    str_contains($categoriesPage, "require_once __DIR__ . '/../includes/auth.php'")
    && str_contains($categoriesPage, 'new CategoryService()')
    && str_contains($categoriesPage, '$service->findAll()')
    && str_contains($categoriesPage, 'admin-products-table')
    && str_contains($categoriesPage, "url('/admin/categories/create')")
    && str_contains($categoriesPage, "url('/admin/categories/edit?id='")
);
$check('Categories index filters escaped search terms and only supported active/inactive statuses',
    str_contains($categoriesPage, "Request::query('q'")
    && str_contains($categoriesPage, "Request::query('status'")
    && str_contains($categoriesPage, "['active', 'inactive']")
    && str_contains($categoriesPage, 'in_array($statusFilter, $allowedStatuses, true)')
    && str_contains($categoriesPage, 'e($search)')
);
$check('Categories index reuses product catalog layout, filters, table and empty-state components',
    str_contains($categoriesPage, 'admin-products-page admin-categories-page')
    && str_contains($categoriesPage, 'admin-products-summary admin-categories-summary')
    && str_contains($categoriesPage, 'admin-products-filters')
    && str_contains($categoriesPage, 'admin-products-table-wrap')
    && str_contains($categoriesPage, 'admin-products-empty')
);
$check('Category creation preserves CSRF, validator, duplicate slug and parent-existence checks',
    str_contains($categoryCreatePage, 'Csrf::verify')
    && str_contains($categoryCreatePage, 'new CategoryValidator()')
    && str_contains($categoryCreatePage, '$service->findBySlug($payload[\\'slug\\'])')
    && str_contains($categoryCreatePage, '$service->exists($payload[\\'parentId\\'])')
    && str_contains($categoryCreatePage, '$service->create(')
);
$check('Category creation uses active parent selector and only supported category fields',
    str_contains($categoryCreatePage, '$service->findActive()')
    && str_contains($categoryCreatePage, '<select name="parentId">')
    && str_contains($categoryCreatePage, 'name="description"')
    && str_contains($categoryCreatePage, 'name="status"')
    && str_contains($categoryCreatePage, 'name="sortOrder"')
    && !str_contains($categoryCreatePage, 'name="sku"')
);
$check('Category creation reuses the exact product-create form sections, controls and sticky actions',
    str_contains($categoryCreatePage, 'admin-product-create-page')
    && str_contains($categoryCreatePage, 'admin-product-create-section')
    && str_contains($categoryCreatePage, 'admin-product-create-fields')
    && str_contains($categoryCreatePage, 'admin-product-create-field')
    && str_contains($categoryCreatePage, 'admin-product-create-actions')
    && str_contains($productCss, '.admin-product-create-section')
    && str_contains($productCss, '.admin-product-create-actions')
);
$check('Category index adds only a responsive three-stat layout and inactive status treatment',
    str_contains($productCss, '.admin-categories-summary')
    && str_contains($productCss, 'grid-template-columns: repeat(3, minmax(0, 1fr));')
    && str_contains($productCss, '.admin-categories-page .admin-products-status-inactive')
);

echo "\nSummary: {$passed} PASS, {$failed} FAIL\n";
exit($failed === 0 ? 0 : 1);
