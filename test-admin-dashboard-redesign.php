<?php

declare(strict_types=1);

/**
 * Nivad Admin Dashboard Redesign — static regression contract.
 * Read-only: no database writes or application mutations.
 */

$root = __DIR__;
$checks = 0;
$passed = 0;
$failed = 0;

function adminRedesignCheck(bool $ok, string $label): void
{
    global $checks, $passed, $failed;
    $checks++;
    if ($ok) {
        $passed++;
        echo '<span style="color:green">[PASS] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
    } else {
        $failed++;
        echo '<span style="color:red">[FAIL] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
    }
}

function adminRedesignRead(string $relative): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? (string)file_get_contents($path) : '';
}

$dashboardEntry = adminRedesignRead('admin/index.php');
$dashboardView = adminRedesignRead('pages/admin/dashboard.php');
$authInclude = adminRedesignRead('admin/includes/auth.php');
$logout = adminRedesignRead('admin/logout.php');
$htaccess = adminRedesignRead('.htaccess');
$mainCss = adminRedesignRead('admin/assets/css/main.css');
$mainJs = adminRedesignRead('admin/assets/js/main.js');
$tokens = adminRedesignRead('admin/assets/css/setting.css');
$settings = adminRedesignRead('admin/settings/index.php');
$preferences = adminRedesignRead('admin/assets/js/modules/admin-preferences.js');
$navigationState = adminRedesignRead('admin/assets/js/modules/navigation-state.js');

adminRedesignCheck(str_contains($dashboardEntry, "pages/admin/dashboard.php"), 'Direct /admin entry reuses the shared dashboard view');
adminRedesignCheck(str_contains($authInclude, "first_name") && str_contains($authInclude, "last_name"), 'Shared authenticated shell identity uses first and last name fields');
adminRedesignCheck(!str_contains($dashboardView, "['email']") && !str_contains($dashboardView, "['username']"), 'Dashboard does not expose email or username as the display-name fallback');
adminRedesignCheck(str_contains($dashboardView, 'admin-dashboard-main') && str_contains($authInclude, 'admin-shared-nav-group') && str_contains($authInclude, 'admin-shared-sidebar'), 'Dashboard and authenticated pages share grouped mega-navigation');
adminRedesignCheck(str_contains($authInclude, "url('/admin/settings')") && is_file($root . '/admin/settings/index.php'), 'Shared navigation points to an implemented settings page');
adminRedesignCheck(str_contains($authInclude, 'use ($nav, $topbar)'), 'Shared shell closure captures both navigation and topbar safely');
adminRedesignCheck(str_contains($logout, 'Csrf::verify') && str_contains($logout, 'Request::isPost') && str_contains($logout, 'AdminAuth::logout'), 'Direct logout endpoint enforces POST and CSRF before clearing admin auth');
adminRedesignCheck(str_contains($htaccess, 'RewriteRule ^admin/logout/?$ admin/logout.php'), 'Apache rewrite points /admin/logout to the direct endpoint');
adminRedesignCheck(!str_contains($mainJs, 'modules/theme.js') && !str_contains($mainJs, 'adminTheme()'), 'Theme toggle module is no longer initialized');
adminRedesignCheck(!str_contains($tokens, 'data-admin-theme="dark"'), 'Dark theme token override has been removed');
adminRedesignCheck(!is_file($root . '/admin/assets/css/auth.css'), 'Legacy root auth.css is removed from the three-breakpoint CSS architecture');
adminRedesignCheck(str_contains($settings, 'data-admin-preferences-form') && str_contains($preferences, 'localStorage'), 'Settings page saves browser-local interface preferences');
adminRedesignCheck(str_contains($mainCss, './setting.css') && str_contains($mainCss, './mobile/mobile.css') && str_contains($mainCss, './tablet/tablet.css') && str_contains($mainCss, './desktop/desktop.css'), 'Admin CSS entrypoint retains token source and breakpoint bundles');
adminRedesignCheck(str_contains($mainJs, 'modules/admin-preferences.js') && str_contains($mainJs, 'modules/navigation-state.js'), 'Admin interface modules are initialized through main.js');
adminRedesignCheck(str_contains($navigationState, 'aria-current') && str_contains($navigationState, 'closest("details")'), 'Navigation state marks the active page and opens its mega-menu group');

$cssRoot = $root . '/admin/assets/css';
$cssDirs = ['mobile', 'tablet', 'desktop'];
$mediaViolations = [];
$missingDashboardStyles = [];
$missingSharedStyles = [];
foreach ($cssDirs as $dir) {
    $dirPath = $cssRoot . '/' . $dir;
    if (!is_dir($dirPath)) {
        $mediaViolations[] = $dir . ' (directory missing)';
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'css') {
            continue;
        }
        $source = (string)file_get_contents($file->getPathname());
        if (preg_match('/@media\s/i', $source) === 1) {
            $mediaViolations[] = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }
    $dashboardPath = $dirPath . '/dashboard-' . $dir . '.css';
    $sharedPath = $dirPath . '/shared-shell-' . $dir . '.css';
    if (!is_file($dashboardPath)) $missingDashboardStyles[] = $dashboardPath;
    if (!is_file($sharedPath)) $missingSharedStyles[] = $sharedPath;
}
adminRedesignCheck($mediaViolations === [], 'No @media rules exist inside admin responsive CSS folders' . ($mediaViolations ? ': ' . implode(', ', $mediaViolations) : ''));
adminRedesignCheck($missingDashboardStyles === [], 'Dashboard has dedicated CSS for mobile, tablet, and desktop');
adminRedesignCheck($missingSharedStyles === [], 'Shared shell has dedicated CSS for mobile, tablet, and desktop');

echo '<hr>' . PHP_EOL;
echo 'Checks: ' . $checks . ' | PASS: ' . $passed . ' | FAIL: ' . $failed . '<br>' . PHP_EOL;
echo $failed === 0 ? 'STATIC CONTRACT RESULT: PASS (browser/runtime checks still required)' : 'STATIC CONTRACT RESULT: FAIL' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
