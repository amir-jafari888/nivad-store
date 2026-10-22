<?php
declare(strict_types=1);

/* Read-only static checks. No database writes and no API calls. */
$root = __DIR__;
$checks = [];
$add = static function (string $label, bool $ok) use (&$checks): void { $checks[] = [$label, $ok]; };
$read = static function (string $path) use ($root): string {
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $file = $root . DIRECTORY_SEPARATOR . $path;
    return is_file($file) ? (string) file_get_contents($file) : '';
};
$paths = [
 'pages/account/index.php','pages/account/profile.php','pages/account/addresses.php',
 'pages/account/orders/index.php','pages/account/orders/show.php','pages/account/returns/index.php',
 'pages/account/returns/show.php','pages/account/reviews.php','pages/account/notifications.php',
 'assets/js/modules/account.js','assets/js/modules/customer-commerce.js','assets/js/modules/customer-shop.js',
 'assets/css/account.css','assets/css/mobile/account-mobile.css','assets/css/tablet/account-tablet.css','assets/css/desktop/account-desktop.css'
];
foreach ($paths as $path) $add("File exists: {$path}", $read($path) !== '');
$a=$read('assets/js/modules/account.js'); $c=$read('assets/js/modules/customer-commerce.js');
$d=$read('pages/account/index.php'); $r=$read('pages/account/reviews.php'); $api=$read('routes/api.php');
$css=$read('assets/css/account.css'); $setting=$read('assets/css/setting.css');
$add('Dashboard selector matches data-account-page', str_contains($c, 'querySelector("[data-account-page]")'));
$add('Request helper reports network errors', str_contains($c, 'ارتباط با سرور برقرار نشد'));
$add('Request helper rejects malformed responses', str_contains($c, 'پاسخ سرور قابل خواندن نیست'));
$add('Request helper checks HTTP/API failure', str_contains($c, '!response.ok || payload.success === false'));
$add('CSRF header preserved', str_contains($a, 'X-CSRF-TOKEN') && str_contains($c, 'X-CSRF-TOKEN'));
$add('Same-origin credentials preserved', str_contains($a, 'credentials: "same-origin"') && str_contains($c, 'credentials: "same-origin"'));
$add('Session list accepts controller data array', str_contains($c, 'Array.isArray(sessionsResult?.data)'));
$add('Session revoke uses existing route', str_contains($c, '/account/sessions/revoke') && str_contains($api, "'/account/sessions/revoke'"));
$add('Other-session logout uses existing route', str_contains($c, '/logout-other-sessions') && str_contains($api, "'/logout-other-sessions'"));
$add('Password change uses existing route/fields', str_contains($c, '/account/password') && str_contains($c, 'new_password_confirmation'));
$add('Profile GET/PATCH contract preserved', str_contains($a, 'request("/account/profile")') && str_contains($a, 'method: "PATCH"'));
$add('Profile update payload is restricted', str_contains($a, 'first_name: form.elements.first_name.value.trim()') && str_contains($a, 'last_name: form.elements.last_name.value.trim()'));
$add('Address CRUD/default operations present', str_contains($a, '/account/addresses') && str_contains($a, 'editingId === null ?') && str_contains($a, 'DELETE') && str_contains($a, '/default'));
$add('Address deletion asks for confirmation', str_contains($a, 'window.confirm("از حذف این آدرس مطمئن هستید؟")'));
$add('Order list/detail calls present', str_contains($a, 'request("/orders")') && str_contains($a, 'request(`/orders/${encodeURIComponent(id)}`)'));
$add('Return list/detail routes are present', str_contains($api, "'/returns'") && str_contains($a, 'request("/returns")') && str_contains($a, 'request(`/returns/${encodeURIComponent(id)}`)'));
$add('Return details consume items and history', str_contains($a, 'data.items') && str_contains($a, 'data.history'));
$add('Return creation form matches controller fields', str_contains($read('pages/account/returns/index.php'), 'name="orderId"') && str_contains($read('pages/account/returns/index.php'), 'name="orderItemId"') && str_contains($a, 'items: [{ orderItemId, quantity }]') && str_contains($a, 'request("/returns"'));
$add('Controller-only form fields use URL-encoded body', str_contains($c, 'application/x-www-form-urlencoded;charset=UTF-8') && str_contains($c, 'options.body.set("csrf_token", token)'));
$add('Review form has labels and native validation', str_contains($r, '<label>') && !str_contains($r, 'novalidate'));
$add('Review list/store API contracts preserved', str_contains($c, '/reviews/mine') && str_contains($c, '/reviews') && str_contains($api, '/reviews/mine'));
$add('Notification read/read-all routes preserved', str_contains($c, '/notifications/read-all') && str_contains($api, "'/notifications/read-all'"));
$add('Notification/review errors are handled', str_contains($c, 'دریافت اعلان‌ها ناموفق بود') && str_contains($c, 'ثبت دیدگاه ناموفق بود'));
$add('Duplicate submission guard present', str_contains($a, 'form.dataset.busy === "true"') && str_contains($c, 'passwordForm.dataset.busy === "true"'));
$add('Dashboard uses accessible live feedback', str_contains($d, 'aria-live="polite"') && str_contains($d, 'role="status"'));
$add('Dashboard has notification and order metrics', str_contains($d, 'data-account-metric="notifications"') && str_contains($d, 'data-account-metric="orders"'));
$add('Profile/address validation errors are mapped to fields', str_contains($a, 'const fieldErrors =') && str_contains($a, 'aria-invalid') && str_contains($a, 'aria-describedby'));
$add('Dashboard links point to actual relative pages', str_contains($d, 'href="orders/index.php"') && str_contains($d, 'href="returns/index.php"'));
$add('Account CSS uses semantic tokens', str_contains($css, 'var(--color-surface)') && str_contains($css, 'var(--color-border)') && str_contains($css, 'var(--radius-lg)'));
$add('Account CSS avoids white hardcoded panel background', !preg_match('/background:\\s*#fff(?:fff)?\\b/i', $css));
$add('Account CSS uses tokens defined in setting.css', str_contains($setting, '--color-surface:') && str_contains($setting, '--spacing-4') && str_contains($setting, '--radius-lg'));
foreach (['mobile','tablet','desktop'] as $bp) $add("{$bp} stylesheet imports account breakpoint CSS", str_contains($read("assets/css/{$bp}/{$bp}.css"), "account-{$bp}.css"));
$pass=0; $fail=0;
foreach ($checks as [$label,$ok]) { echo ($ok ? '[PASS] ' : '[FAIL] ') . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n"; $ok ? $pass++ : $fail++; }
echo "<strong>PHASE 30 ACCOUNT EXPERIENCE — SUMMARY PASS: {$pass} FAIL: {$fail} BLOCKED: 0 LOCK: NO</strong><br>\n";
exit($fail === 0 ? 0 : 1);
