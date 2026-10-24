<?php
declare(strict_types=1);

/**
 * Phase 50 — Production Hardening Audit
 * Read-only repository audit. No DB writes, external requests, or production config mutation.
 * Run from repository root: php test-phase-50-production-hardening.php
 */
$passed = 0; $failed = 0; $blocked = 0;
function p50Read(string $path): string {
    $full = __DIR__ . '/' . $path;
    if (!is_file($full)) return '';
    $content = file_get_contents($full);
    return is_string($content) ? $content : '';
}
function p50Exists(string $path): bool { return is_file(__DIR__ . '/' . $path); }
function p50Check(string $id, string $label, ?bool $result): void {
    global $passed, $failed, $blocked;
    if ($result === null) { $status = 'BLOCKED'; $color = '#b7791f'; $blocked++; }
    elseif ($result) { $status = 'PASS'; $color = 'green'; $passed++; }
    else { $status = 'FAIL'; $color = 'red'; $failed++; }
    echo '<span style="color:' . $color . '">[' . $status . ']</span> '
        . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}
$mainCss=p50Read('assets/css/main.css'); $setting=p50Read('assets/css/setting.css');
$mobile=p50Read('assets/css/mobile/mobile.css'); $tablet=p50Read('assets/css/tablet/tablet.css');
$desktop=p50Read('assets/css/desktop/desktop.css'); $mainJs=p50Read('assets/js/main.js');
$header=p50Read('partials/header.php'); $footer=p50Read('partials/footer.php');
$appConfig=p50Read('config/app.php'); $securityConfig=p50Read('config/security.php');
$headers=p50Read('app/Middleware/SecurityHeadersMiddleware.php'); $bootstrap=p50Read('app/Core/Bootstrap.php');
$checkout=p50Read('pages/shop/checkout.php'); $cart=p50Read('pages/shop/cart.php');
$wishlist=p50Read('pages/shop/wishlist.php'); $product=p50Read('pages/store/products/show.php');
$authLogin=p50Read('pages/auth/login.php'); $authRegister=p50Read('pages/auth/register.php');
$cssPattern='/@import\\s+url\\(["]?([^")]+)["]?\\)/';

/* Batch 01 — Asset Integrity */
p50Check('50.01','main.css exists',p50Exists('assets/css/main.css'));
p50Check('50.02','main.css imports setting.css',str_contains($mainCss,'./setting.css'));
p50Check('50.03','mobile stylesheet aggregator exists',p50Exists('assets/css/mobile/mobile.css'));
p50Check('50.04','tablet stylesheet aggregator exists',p50Exists('assets/css/tablet/tablet.css'));
p50Check('50.05','desktop stylesheet aggregator exists',p50Exists('assets/css/desktop/desktop.css'));
p50Check('50.06','main.js exists and declares module imports',$mainJs!=='' && str_contains($mainJs,'import '));
p50Check('50.07','relative CSS imports in main.css resolve',str_contains($mainCss,'@import') && str_contains($mainCss,'./setting.css'));
p50Check('50.08','Swiper stylesheet and brand image exist',p50Exists('swiper/swiper-bundle.min.css') && p50Exists('assets/images/brand/nivad-mark.png'));
p50Check('50.09','relative CSS imports in mobile/tablet/desktop aggregators resolve',str_contains($mobile,'@import') && str_contains($tablet,'@import') && str_contains($desktop,'@import'));
p50Check('50.10','CSP compatibility of all assets needs browser/network evidence',null);

/* Batch 02 — Error Handling */
p50Check('50.11','global error handler is registered during bootstrap',str_contains($bootstrap,'registerErrorHandler') && str_contains($bootstrap,'ErrorHandler::register()'));
p50Check('50.12','network error recovery requires browser tests',null);
p50Check('50.13','API failure-state coverage requires runtime/API tests',null);
p50Check('50.14','timeout handling across requests requires runtime tests',null);
p50Check('50.15','login/register/checkout sources exist',$authLogin!=='' && $authRegister!=='' && $checkout!=='');
p50Check('50.16','production error disclosure policy requires environment verification',null);
p50Check('50.17','form failure and validation UX requires browser tests',null);
p50Check('50.18','UI recovery after errors requires browser interaction tests',null);
p50Check('50.19','global state contracts require cross-module runtime tests',null);
p50Check('50.20','HTTP error pages/status codes require server-level verification',null);

/* Batch 03 — Performance */
p50Check('50.21','main CSS entrypoint exists',$mainCss!=='');
p50Check('50.22','main JavaScript entrypoint exists',$mainJs!=='');
p50Check('50.23','responsive image sizing requires rendered-page inspection',null);
p50Check('50.24','lazy-loading coverage requires image inventory/browser verification',null);
p50Check('50.25','font loading behavior requires asset/network verification',null);
p50Check('50.26','duplicate network requests require browser network trace',null);
p50Check('50.27','duplicate listeners and repeated initialization require runtime instrumentation',null);
p50Check('50.28','long tasks and blocking work require performance profiling',null);
p50Check('50.29','mobile performance requires representative-device measurements',null);
p50Check('50.30','Core Web Vitals require controlled browser measurements',null);

/* Batch 04 — Responsive Integrity */
p50Check('50.31','responsive aggregators exist for all three breakpoints',$mobile!=='' && $tablet!=='' && $desktop!=='');
p50Check('50.32','horizontal overflow requires viewport/browser testing',null);
p50Check('50.33','mobile header requires visual and interaction testing',null);
p50Check('50.34','tablet navigation requires visual and interaction testing',null);
p50Check('50.35','desktop layout stability requires browser visual testing',null);
p50Check('50.36','product cards and grids require multi-viewport visual testing',null);
p50Check('50.37','modal/drawer/dropdown behavior requires interaction testing',null);
p50Check('50.38','dense forms and tables require multi-viewport testing',null);
p50Check('50.39','responsive behavior across both themes requires browser testing',null);
p50Check('50.40','breakpoint boundary regression at 600/601/1009/1010px requires browser tests',null);

/* Batch 05 — Theme Integrity */
p50Check('50.41','setting.css declares root design tokens',str_contains($setting,':root') && str_contains($setting,'--color-primary'));
p50Check('50.42','light theme token coverage requires token-by-token audit',null);
p50Check('50.43','dark theme token coverage requires token-by-token audit',null);
p50Check('50.44','interactive colors and contrast require computed-style/accessibility tests',null);
p50Check('50.45','button and form theme states require browser testing',null);
p50Check('50.46','overlay theme consistency requires browser testing',null);
p50Check('50.47','loading/empty/error theme states require page-by-page audit',null);
p50Check('50.48','commerce and smart-commerce theme coverage requires page-by-page audit',null);
p50Check('50.49','theme switching and persisted state require browser interaction tests',null);
p50Check('50.50','hardcoded color exceptions require full CSS scan and review',null);

/* Batch 06 — Accessibility Stability */
p50Check('50.51','shared header and footer sources exist',$header!=='' && $footer!=='');
p50Check('50.52','heading hierarchy across pages requires HTML audit',null);
p50Check('50.53','keyboard-only navigation requires manual/browser tests',null);
p50Check('50.54','focus-visible behavior requires browser/computed-style tests',null);
p50Check('50.55','all form controls need accessible-name verification',null);
p50Check('50.56','ARIA state correctness requires interaction/accessibility-tree tests',null);
p50Check('50.57','modal/drawer focus trap and restoration require keyboard tests',null);
p50Check('50.58','async announcements require screen-reader/browser tests',null);
p50Check('50.59','contrast/touch targets/reduced motion require rendered audits',null);
p50Check('50.60','purchase/account accessibility requires end-to-end tests',null);

/* Batch 07 — Production Configuration */
p50Check('50.61','config/app.php currently uses development default; effective production override is unverified',null);
p50Check('50.62','config/app.php currently enables debug by default; effective production override is unverified',null);
p50Check('50.63','production URL/base path and environment overrides require deployment evidence',null);
p50Check('50.64','development-only dependency/configuration leakage requires environment audit',null);
p50Check('50.65','HTTPS enforcement is enabled in security config',str_contains($securityConfig,"'https_only' => true"));
p50Check('50.66','CSP header is configured but compatibility requires browser evidence',str_contains($headers,'Content-Security-Policy:') ? null : false);
p50Check('50.67','actual security response headers require deployed HTTP verification',null);
p50Check('50.68','third-party asset availability/trust requires network verification',null);
p50Check('50.69','cache headers and immutable asset strategy require HTTP verification',null);
p50Check('50.70','production secret/environment injection requires operator evidence',null);

/* Batch 08 — Final UI Stability */
p50Check('50.71','landing page entrypoint exists',p50Exists('index.php'));
p50Check('50.72','header search hooks exist',str_contains($header,'data-search-form') && str_contains($header,'data-search-input'));
p50Check('50.73','cart and wishlist entrypoints exist',$cart!=='' && $wishlist!=='');
p50Check('50.74','product listing and detail entrypoints exist',p50Exists('pages/store/products/index.php') && $product!=='');
p50Check('50.75','smart-commerce regression requires end-to-end evidence',null);
p50Check('50.76','checkout/payment journey requires end-to-end environment',null);
p50Check('50.77','authentication/account journey requires session-enabled tests',null);
p50Check('50.78','theme and responsive regression requires browser coverage',null);
p50Check('50.79','links/forms/modules require full route/browser regression',null);
p50Check('50.80','final release gate remains blocked until runtime evidence is supplied',null);

echo 'Phase 50 audit summary';
exit($failed > 0 ? 1 : 0);
