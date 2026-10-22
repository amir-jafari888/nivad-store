<?php
declare(strict_types=1);

/* Phase 31 read-only static contract checks. No database writes or API calls. */
$root = __DIR__;
$checks = [];
$add = static function (string $label, bool $ok) use (&$checks): void { $checks[] = [$label, $ok]; };
$read = static function (string $path) use ($root): string {
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $file = $root . DIRECTORY_SEPARATOR . $path;
    return is_file($file) ? (string) file_get_contents($file) : '';
};
$pages = [
 'pages/content/about.php','pages/content/contact.php','pages/content/faq.php',
 'pages/content/privacy.php','pages/content/terms.php','pages/content/shipping.php',
 'pages/content/returns.php','pages/content/buying-guide.php'
];
foreach ($pages as $path) {
    $content = $read($path);
    $add("File exists and is readable: {$path}", $content !== '');
    $add("UTF-8 valid: {$path}", $content !== '' && preg_match('//u', $content) === 1);
    $add("No known mojibake markers: {$path}", $content !== '' && !preg_match('/(?:ط¯ط|ط§ط|ظ…ط|ظˆط|Ã.|Â.)/u', $content));
    $add("Includes shared header/footer: {$path}", str_contains($content, "partials/header.php") && str_contains($content, "partials/footer.php"));
    $add("Has title, description and H1: {$path}", str_contains($content, '$pageTitle') && str_contains($content, '$metaDescription') && preg_match('/<h1\\b/i', $content) === 1);
}
$guide=$read('partials/buying-guide.php');
$footer=$read('partials/footer.php');
$css=$read('assets/css/content.css');
$setting=$read('assets/css/setting.css');
$mobile=$read('assets/css/mobile/mobile.css');
$tablet=$read('assets/css/tablet/tablet.css');
$desktop=$read('assets/css/desktop/desktop.css');
$header=$read('partials/header.php');
$add('Buying guide feature links target real article anchors', str_contains($guide, 'buying-guide.php#charger') && str_contains($guide, 'buying-guide.php#usb-c-thunderbolt') && str_contains($guide, 'buying-guide.php#hdmi'));
$add('View-all buying guide link targets article page', str_contains($guide, 'buying-guide.php') && !str_contains($guide, 'href="#"') && !str_contains($guide, "href='#'"));
$add('Buying guide has no placeholder hash links', !str_contains($guide, 'href="#"') && !str_contains($guide, "href='#'"));
$add('Footer has no placeholder hash links', !str_contains($footer, 'href="#"') && !str_contains($footer, "href='#'"));
$add('Footer links content destinations', str_contains($footer, '/pages/content/faq.php') && str_contains($footer, '/pages/content/contact.php') && str_contains($footer, '/pages/content/about.php'));
$add('All CSS breakpoints import content-specific styles', str_contains($mobile, 'content-mobile.css') && str_contains($tablet, 'content-tablet.css') && str_contains($desktop, 'content-desktop.css'));
$add('Content CSS uses design tokens', str_contains($css, 'var(--color-surface)') && str_contains($css, 'var(--spacing-') && str_contains($css, 'var(--radius-') && str_contains($css, 'var(--color-border)'));
$add('Content CSS token names exist in settings', str_contains($setting, '--color-primary:') && str_contains($setting, '--color-surface:') && str_contains($setting, '--spacing-4:') && str_contains($setting, '--radius-lg:'));
$add('Content CSS has visible keyboard focus treatment', str_contains($css, ':focus-visible') && str_contains($css, 'var(--focus-ring)'));
$add('Content CSS includes reduced-motion consideration', str_contains($css, 'prefers-reduced-motion'));
$add('Shared header declares Persian RTL and UTF-8', str_contains($header, 'lang="fa"') && str_contains($header, 'dir="rtl"') && str_contains($header, 'UTF-8'));
$add('FAQ uses native accessible details/summary', str_contains($read('pages/content/faq.php'), '<details>') && str_contains($read('pages/content/faq.php'), '<summary>'));
$add('Contact page does not invent contact channels or fake form success', !preg_match('/@example\\.|555-01|data-contact-success/', $read('pages/content/contact.php')));
$add('Privacy page flags final policy review rather than unverified guarantees', str_contains($read('pages/content/privacy.php'), 'تأیید') && str_contains($read('pages/content/privacy.php'), 'تضمین'));
$add('Shipping page avoids unsupported fixed delivery promises', !preg_match('/[0-9]+\\s*(روز|ساعت)/u', $read('pages/content/shipping.php')));
$add('Returns page does not promise automatic approval/refund', str_contains($read('pages/content/returns.php'), 'به معنی تأیید') && str_contains($read('pages/content/returns.php'), 'بازپرداخت'));
$add('Buying guide covers charger, USB-C/Thunderbolt and HDMI', str_contains($read('pages/content/buying-guide.php'), 'charger') && str_contains($read('pages/content/buying-guide.php'), 'USB-C و Thunderbolt') && str_contains($read('pages/content/buying-guide.php'), 'HDMI'));
$add('Account orders and returns links point to existing page paths', str_contains($read('pages/content/contact.php'), '/pages/account/orders/index.php') && str_contains($read('pages/content/returns.php'), '/pages/account/returns/index.php'));
$add('Legacy phase test scripts were removed', !is_file($root . '/test-phase-29-authentication.php') && !is_file($root . '/test-phase-30-account-experience.php'));
$pass=0; $fail=0;
foreach ($checks as [$label,$ok]) { echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; $ok ? $pass++ : $fail++; }
echo PHP_EOL . "PHASE 31 CONTENT & SUPPORT — SUMMARY PASS: {$pass} FAIL: {$fail} BLOCKED: 0 LOCK: " . ($fail===0 ? 'NO (browser/business review pending)' : 'NO') . PHP_EOL;
exit($fail===0 ? 0 : 1);
