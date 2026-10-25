<?php
declare(strict_types=1);

/**
 * Phase 46 — E2E Account Journey
 * Read-only static contract test. No database writes or API mutations.
 * Run from project root: php test-phase-46-account-journey.php
 */

$checks = 0;
$failures = 0;

function phase46Check(string $label, bool $passed): void
{
    global $checks, $failures;
    $checks++;
    echo '<span style="color:' . ($passed ? 'green' : 'red') . '">[' . ($passed ? 'PASS' : 'FAIL') . ']</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
    if (!$passed) {
        $failures++;
    }
}

function phase46Read(string $path): string
{
    if (!is_file($path)) {
        return '';
    }
    $content = file_get_contents($path);
    return is_string($content) ? $content : '';
}

$requiredFiles = [
    'pages/auth/register.php',
    'pages/auth/login.php',
    'pages/account/index.php',
    'pages/account/orders/index.php',
    'pages/account/orders/show.php',
    'pages/account/reviews.php',
    'pages/account/returns/index.php',
    'pages/account/returns/show.php',
    'pages/account/notifications.php',
    'assets/js/modules/auth.js',
    'assets/js/modules/account.js',
    'assets/js/modules/customer-shop.js',
    'assets/js/modules/notification.js',
    'assets/js/main.js',
    'routes/api.php',
    'app/Controllers/ReviewController.php',
    'app/Controllers/ReturnController.php',
    'app/Controllers/NotificationController.php',
];

foreach ($requiredFiles as $path) {
    phase46Check("46 file exists: {$path}", is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)));
}

$login = phase46Read(__DIR__ . '/pages/auth/login.php');
$register = phase46Read(__DIR__ . '/pages/auth/register.php');
$accountPage = phase46Read(__DIR__ . '/pages/account/index.php');
$orders = phase46Read(__DIR__ . '/pages/account/orders/index.php');
$orderDetail = phase46Read(__DIR__ . '/pages/account/orders/show.php');
$reviewsPage = phase46Read(__DIR__ . '/pages/account/reviews.php');
$returnsPage = phase46Read(__DIR__ . '/pages/account/returns/index.php');
$returnDetail = phase46Read(__DIR__ . '/pages/account/returns/show.php');
$notificationsPage = phase46Read(__DIR__ . '/pages/account/notifications.php');
$authJs = phase46Read(__DIR__ . '/assets/js/modules/auth.js');
$accountJs = phase46Read(__DIR__ . '/assets/js/modules/account.js');
$customerShopJs = phase46Read(__DIR__ . '/assets/js/modules/customer-shop.js');
$notificationJs = phase46Read(__DIR__ . '/assets/js/modules/notification.js');
$mainJs = phase46Read(__DIR__ . '/assets/js/main.js');
$apiRoutes = phase46Read(__DIR__ . '/routes/api.php');

phase46Check('46.01 Register page exposes auth form hook', str_contains($register, 'data-auth-register-form') && str_contains($register, 'data-auth-page'));
phase46Check('46.02 Login page exposes auth form hook', str_contains($login, 'data-auth-login-form') && str_contains($login, 'data-auth-page'));
phase46Check('46.03 Login redirects to account dashboard', str_contains($login, "/pages/account/index.php"));
phase46Check('46.04 Auth module requests CSRF token', str_contains($authJs, 'request("/auth/csrf")') && str_contains($authJs, 'typeof token !== "string"'));
phase46Check('46.05 Auth module blocks duplicate form submission', str_contains($authJs, 'form.dataset.submitting === "true"'));
phase46Check('46.06 Account dashboard exposes metrics/navigation hooks', str_contains($accountPage, 'data-account-page') && str_contains($accountPage, 'data-account-metric="orders"') && str_contains($accountPage, 'data-account-metric="notifications"'));
phase46Check('46.07 Orders list exposes customer-shop hooks', str_contains($orders, 'data-customer-page="orders"') && str_contains($orders, 'data-customer-list'));
phase46Check('46.08 Order detail exposes customer-shop hooks', str_contains($orderDetail, 'data-customer-page="order"') && str_contains($orderDetail, 'data-customer-list'));
phase46Check('46.09 Account review list and form hooks exist', str_contains($reviewsPage, 'data-account-reviews') && str_contains($reviewsPage, 'data-account-review-form'));
phase46Check('46.10 Account module loads and submits reviews', str_contains($accountJs, 'request("/reviews/mine")') && str_contains($accountJs, 'request("/reviews"') && str_contains($accountJs, 'initReviews();'));
phase46Check('46.11 Review payload validates product ID and rating', str_contains($accountJs, 'Number.isSafeInteger(productId)') && str_contains($accountJs, 'rating > 5'));
phase46Check('46.12 Return list and request form hooks exist', str_contains($returnsPage, 'data-account-returns') && str_contains($returnsPage, 'data-account-return-form'));
phase46Check('46.13 Return detail hook and identifier validation exist', str_contains($returnDetail, 'data-account-return-detail') && str_contains($accountJs, 'شناسه مرجوعی نامعتبر است.'));
phase46Check('46.14 Return request uses existing return endpoint', str_contains($accountJs, 'request("/returns"') && str_contains($apiRoutes, "'/returns'"));
phase46Check('46.15 Notification page exposes list/read-all hooks', str_contains($notificationsPage, 'data-notifications-list') && str_contains($notificationsPage, 'data-notifications-read-all'));
phase46Check('46.16 Notification module is initialized by main.js', str_contains($mainJs, 'import initializeNotifications from "./modules/notification.js";') && str_contains($mainJs, 'initializeNotifications();'));
phase46Check('46.17 Notification module has list/read/read-all endpoints', str_contains($notificationJs, 'INDEX: "/notifications"') && str_contains($notificationJs, 'READ_ALL: "/notifications/read-all"') && str_contains($notificationJs, 'READ: (id)'));
phase46Check('46.18 Notification mutations validate CSRF token', str_contains($notificationJs, 'notificationApiUrl("/auth/csrf")') && str_contains($notificationJs, 'typeof token !== "string"'));
phase46Check('46.19 Notification state supports model read fields', str_contains($notificationJs, 'value.isRead ?? value.is_read ?? value.read'));
phase46Check('46.20 Customer journey rejects malformed API JSON', str_contains($customerShopJs, 'پاسخ سرور قابل پردازش نیست.'));
phase46Check('46.21 Customer journey fails closed when CSRF token is missing', str_contains($customerShopJs, 'typeof token !== "string" || token.length === 0'));
phase46Check('46.22 Review API route is registered', str_contains($apiRoutes, "'/reviews/mine'"));
phase46Check('46.23 Notification API routes are registered', str_contains($apiRoutes, "'/notifications'") && str_contains($apiRoutes, "'/notifications/{id}/read'") && str_contains($apiRoutes, "'/notifications/read-all'"));
phase46Check('46.24 Return detail/list API routes are registered', str_contains($apiRoutes, "'/returns'") && str_contains($apiRoutes, "'/returns/{id}'"));
phase46Check('46.25 Shared main entry initializes account and customer commerce', str_contains($mainJs, 'account();') && str_contains($mainJs, 'customerShop();') && str_contains($mainJs, 'customerCommerce();'));

echo "<hr>Phase 46 static checks: {$checks}; failures: {$failures}.<br>\n";
echo $failures === 0
    ? '<strong style="color:green">STATIC CHECKS PASS — browser/API smoke tests still required before LOCK.</strong>'
    : '<strong style="color:red">STATIC CHECKS FAIL — LOCK = NO.</strong>';
echo "<br>\n";
exit($failures === 0 ? 0 : 1);
