<?php
declare(strict_types=1);

/**
 * Phase 43 — Offline / Network / Recovery
 * Read-only source-contract test. No network calls or database mutations.
 */
$root = __DIR__;
$pass = 0;
$fail = 0;

function source(string $relative): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) ? (string) file_get_contents($path) : '';
}

function check(string $label, bool $condition): void
{
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo '<span style="color:green">[PASS] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
        return;
    }
    $fail++;
    echo '<span style="color:red">[FAIL] ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><br>' . PHP_EOL;
}

$runtime = source('assets/js/modules/network-recovery.js');
$entry = source('assets/js/main.js');
$tokens = source('assets/css/setting.css');
$mobileManifest = source('assets/css/mobile/mobile.css');
$tabletManifest = source('assets/css/tablet/tablet.css');
$desktopManifest = source('assets/css/desktop/desktop.css');
$mobileCss = source('assets/css/mobile/network-recovery-mobile.css');
$tabletCss = source('assets/css/tablet/network-recovery-tablet.css');
$desktopCss = source('assets/css/desktop/network-recovery-desktop.css');
$plan = source('phase-43-offline-network-recovery-plan.md');

check('43.T01 runtime module exists', $runtime !== '');
check('43.T02 runtime module is wired into storefront entry', str_contains($entry, 'import networkRecovery from "./modules/network-recovery.js";'));
check('43.T03 initializer is executed exactly once by entry', substr_count($entry, 'networkRecovery();') === 1);
check('43.T04 duplicate initialization guard exists', str_contains($runtime, '__NIVAD_NETWORK_RECOVERY__'));
check('43.T05 fetch wrapper has a duplicate-install guard', str_contains($runtime, '__NIVAD_FETCH_RESILIENCE__'));
check('43.T06 navigator.onLine is used only as a network hint', str_contains($runtime, 'navigator.onLine'));
check('43.T07 offline event listener exists', str_contains($runtime, 'addEventListener("offline"'));
check('43.T08 online event listener exists', str_contains($runtime, 'addEventListener("online"'));
check('43.T09 shared network state event is emitted', str_contains($runtime, 'nivad:network-state'));
check('43.T10 offline state notice exists', str_contains($runtime, 'اتصال اینترنت قطع است'));
check('43.T11 online recovery feedback exists', str_contains($runtime, 'اتصال شبکه برقرار شد'));
check('43.T12 request timeout is bounded to 15 seconds', str_contains($runtime, 'REQUEST_TIMEOUT_MS = 15000'));
check('43.T13 timeout uses AbortController', str_contains($runtime, 'new AbortController()'));
check('43.T14 timeout is distinguished from network error', str_contains($runtime, 'nivad:request-timeout') && str_contains($runtime, 'nivad:network-error'));
check('43.T15 retry maximum is explicitly bounded', str_contains($runtime, 'MAX_SAFE_RETRIES = 1'));
check('43.T16 automatic retry is restricted to GET/HEAD', str_contains($runtime, 'details.method === "GET" || details.method === "HEAD"'));
check('43.T17 non-idempotent requests are not eligible for automatic retry', str_contains($runtime, '(details.method === "GET" || details.method === "HEAD")') && str_contains($runtime, 'attempt < MAX_SAFE_RETRIES'));
check('43.T18 retryable server status list is bounded', str_contains($runtime, 'new Set([502, 503, 504])'));
check('43.T19 HTTP server failures emit a dedicated event', str_contains($runtime, 'nivad:server-unavailable'));
check('43.T20 session recovery event is scoped and explicit', str_contains($runtime, 'nivad:session-expired'));
check('43.T21 login/register failure paths are excluded from generic session banner', str_contains($runtime, 'isLoginEndpoint'));
check('43.T22 user is not automatically redirected on session event', !str_contains($runtime, 'window.location.href ='));
check('43.T23 notice is accessible and announced', str_contains($runtime, 'aria-live') && str_contains($runtime, 'aria-atomic'));
check('43.T24 notice content uses textContent, not remote HTML', str_contains($runtime, 'text.textContent = message'));
check('43.T25 notice has a dismiss control', str_contains($runtime, 'network-recovery-notice__dismiss'));
check('43.T26 notice has a sign-in recovery action for scoped session failures', str_contains($runtime, 'actionHref:') && str_contains($runtime, 'pages/auth/login.php'));
check('43.T27 Mobile CSS manifest registers the new stylesheet', str_contains($mobileManifest, 'network-recovery-mobile.css'));
check('43.T28 Tablet CSS manifest registers the new stylesheet', str_contains($tabletManifest, 'network-recovery-tablet.css'));
check('43.T29 Desktop CSS manifest registers the new stylesheet', str_contains($desktopManifest, 'network-recovery-desktop.css'));
check('43.T30 Mobile notice stylesheet exists', $mobileCss !== '');
check('43.T31 Tablet notice stylesheet exists', $tabletCss !== '');
check('43.T32 Desktop notice stylesheet exists', $desktopCss !== '');
check('43.T33 responsive notice uses setting.css z-index token', str_contains($tokens, '--z-index-network-notice:') && str_contains($mobileCss, 'var(--z-index-network-notice)') && str_contains($tabletCss, 'var(--z-index-network-notice)') && str_contains($desktopCss, 'var(--z-index-network-notice)'));
check('43.T34 notice uses theme-aware surface/text/border tokens', str_contains($mobileCss, 'var(--color-surface)') && str_contains($mobileCss, 'var(--color-text)') && str_contains($mobileCss, 'var(--color-border)'));
check('43.T35 focus-visible support exists', str_contains($mobileCss, ':focus-visible') && str_contains($tabletCss, ':focus-visible') && str_contains($desktopCss, ':focus-visible'));
check('43.T36 notice avoids section-local media-query duplication', !str_contains($mobileCss, '@media') && !str_contains($tabletCss, '@media') && !str_contains($desktopCss, '@media'));
check('43.T37 contract plan exists', $plan !== '');
preg_match_all('/^\\| (43\\.\\d{2}\\.\\d{2}) \\|/m', $plan, $matches);
$ids = $matches[1] ?? [];
check('43.T38 plan contains exactly 100 contract rows', count($ids) === 100);
check('43.T39 all 100 contract IDs are unique', count(array_unique($ids)) === 100);
check('43.T40 plan contains ten batches', substr_count($plan, '**Batch 43.') === 10);
check('43.T41 plan keeps phase PASS/LOCK unclaimed', str_contains($plan, 'PASS = NO') && str_contains($plan, 'LOCK = NO'));
check('43.T42 plan documents Backend Freeze', str_contains($plan, 'Backend Freeze remains active'));
check('43.T43 test is read-only and does not issue SQL writes', !preg_match('/\\b(INSERT\\s+INTO|UPDATE\\s+\\w+\\s+SET|DELETE\\s+FROM|DROP\\s+TABLE)\\b/i', file_get_contents(__FILE__) ?: ''));

$total = $pass + $fail;
echo '<hr>' . PHP_EOL;
echo '<strong>Phase 43 source audit: ' . $pass . '/' . $total . ' PASS; ' . $fail . ' FAIL.</strong><br>' . PHP_EOL;
echo '<strong>Browser/XAMPP/network simulation and route-by-route partial-failure checks remain PENDING. Phase PASS/LOCK is not granted.</strong><br>' . PHP_EOL;
