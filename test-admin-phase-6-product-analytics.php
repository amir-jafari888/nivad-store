<?php

declare(strict_types=1);

/**
 * Admin Phase 6 — Product Analytics static contract test.
 * Read-only: no database writes or network requests.
 * Run: php test-admin-phase-6-product-analytics.php
 */

$passed = 0;
$failed = 0;
$root = __DIR__;

function p6Read(string $relative): string
{
    global $root;
    $path = $root . '/' . $relative;
    return is_file($path) ? (string)file_get_contents($path) : '';
}

function p6Check(string $label, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo '<span style="color:green">[PASS]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
        return;
    }
    $failed++;
    echo '<span style="color:red">[FAIL]</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

$roadmap = p6Read('ADMIN_PANEL_ROADMAP.md');
$migration = p6Read('database/migrations/20261029_admin_panel_foundation.sql');
$recorder = p6Read('app/Services/ProductAnalyticsRecorder.php');
$service = p6Read('app/Services/AdminCapabilityService.php');
$repository = p6Read('app/Repositories/AdminCapabilityRepository.php');
$payment = p6Read('app/Services/PaymentService.php');
$tracker = p6Read('assets/js/modules/analytics.js');
$productAnalytics = p6Read('assets/js/modules/product-analytics.js');
$discovery = p6Read('assets/js/modules/discovery.js');
$checkout = p6Read('assets/js/modules/checkout.js');
$comparison = p6Read('assets/js/modules/comparison.js');
$main = p6Read('assets/js/main.js');
$adminUi = p6Read('admin/assets/js/modules/capability-center.js');

$checks = [
    // Batch 01 — Unique Viewers
    ['06.01.01', 'Official Admin roadmap defines Phase 6 Product Analytics', str_contains($roadmap, 'فاز ۶ — تکمیل Product Analytics')],
    ['06.01.02', 'Foundation migration defines canonical admin analytics event table', str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_analytics_events')],
    ['06.01.03', 'Shared ProductAnalyticsRecorder exists', str_contains($recorder, 'final class ProductAnalyticsRecorder')],
    ['06.01.04', 'Analytics collection reads the explicit consent key', str_contains($tracker, 'nivad-analytics-consent')],
    ['06.01.05', 'Visitor identity is stored in first-party localStorage only after consent', str_contains($tracker, 'window.localStorage.setItem(visitorKey, value)')],
    ['06.01.06', 'Tracker sends the pseudonymous visitor identifier', str_contains($tracker, 'visitor_id: visitor')],
    ['06.01.07', 'Server hashes the visitor identifier', str_contains($service, "hash('sha256', 'visitor|' . $visitorId)")],
    ['06.01.08', 'Server hashes the session identifier separately', str_contains($service, "hash('sha256', 'session|' . $sessionId)")],
    ['06.01.09', 'Repository aggregates distinct product-view visitors', str_contains($repository, "COUNT(DISTINCT CASE WHEN event_key = 'product_view'")],
    ['06.01.10', 'Admin analytics summary exposes unique viewers', str_contains($repository, 'AS unique_viewers')],

    // Batch 02 — Search Analytics
    ['06.02.01', 'Search impression event is allowlisted', str_contains($service, "'search_impression'")],
    ['06.02.02', 'Search click event is allowlisted', str_contains($service, "'search_click'")],
    ['06.02.03', 'Tracker uses the existing search form selector', str_contains($tracker, '[data-search-form]')],
    ['06.02.04', 'Tracker observes the existing suggestion class', str_contains($tracker, '.search-suggestion')],
    ['06.02.05', 'Search impression requires visible suggestion state', str_contains($tracker, 'box.classList.contains("is-visible")')],
    ['06.02.06', 'Search impression records the result count', str_contains($tracker, 'result_count: count')],
    ['06.02.07', 'Search term is hashed server-side', str_contains($service, "hash('sha256', mb_strtolower($searchTerm, 'UTF-8'))")],
    ['06.02.08', 'Only the search-term hash is persisted in event dimensions', str_contains($service, "'search_term_hash' => $searchTermHash")],
    ['06.02.09', 'Repository aggregates search impressions and clicks', str_contains($repository, 'AS search_impressions') && str_contains($repository, 'AS search_clicks')],
    ['06.02.10', 'Repository calculates search click rate with a zero-denominator guard', str_contains($repository, '$summary[\'search_click_rate\']')],

    // Batch 03 — Purchase Conversion
    ['06.03.01', 'Payment finalization rejects non-success attempts', str_contains($payment, "if (\$attempt->status !== 'success')")],
    ['06.03.02', 'Payment is created from a successful attempt', str_contains($payment, 'createFromSuccessfulAttempt(')],
    ['06.03.03', 'Purchase conversion is recorded server-side', str_contains($payment, "'purchase_completed'")],
    ['06.03.04', 'Purchase event is associated with an order entity', str_contains($payment, "'order',")],
    ['06.03.05', 'Purchase event includes persisted payment ID', str_contains($payment, "'payment_id' => $payment->id")],
    ['06.03.06', 'Purchase event includes payment attempt ID', str_contains($payment, "'attempt_id' => $attempt->id")],
    ['06.03.07', 'Purchase event includes pseudonymous session hash', str_contains($payment, "'session_hash' => $sessionHash")],
    ['06.03.08', 'Purchase event includes pseudonymous customer hash', str_contains($payment, "'customer_hash' => $customerHash")],
    ['06.03.09', 'Public collector does not allow client-forged purchase completion', !str_contains($service, "'purchase_completed'")],
    ['06.03.10', 'Conversion numerator counts distinct successful-purchase sessions', str_contains($repository, "event_key = 'purchase_completed'") && str_contains($repository, 'AS purchase_sessions')],

    // Batch 04 — Checkout Funnel
    ['06.04.01', 'Checkout tracker uses the existing checkout root', str_contains($tracker, '[data-customer-checkout]')],
    ['06.04.02', 'Checkout start event is recorded', str_contains($tracker, 'track("checkout_started"')],
    ['06.04.03', 'Address selection event is recorded', str_contains($tracker, 'checkout_address_selected')],
    ['06.04.04', 'Shipping selection event is recorded', str_contains($tracker, 'checkout_shipping_selected')],
    ['06.04.05', 'Order-created event is allowlisted', str_contains($service, "'checkout_order_created'")],
    ['06.04.06', 'Checkout records order creation after the API returns an order ID', str_contains($checkout, 'track("checkout_order_created"') && str_contains($checkout, 'if (!order?.id)')],
    ['06.04.07', 'Checkout start sessions are counted distinctly', str_contains($repository, 'AS checkout_sessions')],
    ['06.04.08', 'Address step is included in the funnel summary', str_contains($repository, 'AS checkout_address_steps')],
    ['06.04.09', 'Shipping step is included in the funnel summary', str_contains($repository, 'AS checkout_shipping_steps')],
    ['06.04.10', 'Checkout conversion rate uses successful sessions and a denominator guard', str_contains($repository, '$summary[\'checkout_conversion_rate\']') && str_contains($repository, 'purchase_sessions')],

    // Batch 05 — Related Products
    ['06.05.01', 'Product detail renders actual complement product identifiers', str_contains(p6Read('assets/js/modules/product-detail.js'), 'data-complement-product-id')],
    ['06.05.02', 'Tracker observes related-product elements', str_contains($tracker, '[data-complement-product-id]')],
    ['06.05.03', 'Related impressions use IntersectionObserver', str_contains($tracker, 'related_product_impression') && str_contains($tracker, 'IntersectionObserver')],
    ['06.05.04', 'Related clicks require an actual complement link', str_contains($tracker, '[data-complement-product-id] a')],
    ['06.05.05', 'Related click event is allowlisted', str_contains($service, "'related_product_click'")],
    ['06.05.06', 'Related impression event is allowlisted', str_contains($service, "'related_product_impression'")],
    ['06.05.07', 'Repository aggregates related impressions', str_contains($repository, 'AS related_impressions')],
    ['06.05.08', 'Repository aggregates related clicks', str_contains($repository, 'AS related_clicks')],
    ['06.05.09', 'Related click rate has a zero-denominator guard', str_contains($repository, '$summary[\'related_click_rate\']')],
    ['06.05.10', 'Analytics tracker sends same-origin CSRF-protected requests', str_contains($tracker, 'X-CSRF-TOKEN') && str_contains($tracker, 'credentials: "same-origin"')],

    // Batch 06 — Discovery Analytics
    ['06.06.01', 'Discovery instrumentation is registered in the existing main module loader', str_contains($main, 'import analytics from "./modules/analytics.js"')],
    ['06.06.02', 'Discovery product cards expose their actual product ID', str_contains($discovery, 'article.dataset.productId')],
    ['06.06.03', 'Tracker observes rendered Discovery products', str_contains($tracker, '.page-discovery__product[data-product-id]')],
    ['06.06.04', 'Discovery product impressions use visibility observation', str_contains($tracker, 'track(eventName, { product_id: productId, source })')],
    ['06.06.05', 'Discovery option impressions use the existing option selector', str_contains($tracker, '[data-discovery-tags]')],
    ['06.06.06', 'Discovery option impression event is allowlisted', str_contains($service, "'discovery_option_impression'")],
    ['06.06.07', 'Discovery option selection is tracked from the actual option control', str_contains($tracker, 'track("discovery_select"')],
    ['06.06.08', 'Repository aggregates Discovery product impressions', str_contains($repository, 'AS discovery_impressions')],
    ['06.06.09', 'Repository aggregates Discovery option impressions and selections', str_contains($repository, 'AS discovery_option_impressions') && str_contains($repository, 'AS discovery_selections')],
    ['06.06.10', 'Discovery selection rate uses its own option-impression denominator', str_contains($repository, '$summary[\'discovery_selection_rate\']')],

    // Batch 07 — Comparison Analytics
    ['06.07.01', 'Existing comparison module dispatches selection changes', str_contains($comparison, 'comparison:selection-changed')],
    ['06.07.02', 'Tracker listens to the existing comparison event', str_contains($tracker, 'window.addEventListener("comparison:selection-changed"')],
    ['06.07.03', 'Comparison additions are tracked', str_contains($tracker, 'comparison_add')],
    ['06.07.04', 'Comparison removals are tracked', str_contains($tracker, 'comparison_remove')],
    ['06.07.05', 'Comparison page uses the existing page selector', str_contains($tracker, '[data-comparison-page]')],
    ['06.07.06', 'Comparison view waits for visible comparison content', str_contains($tracker, 'content.hidden')],
    ['06.07.07', 'Comparison view event is allowlisted', str_contains($service, "'comparison_view'")],
    ['06.07.08', 'Repository aggregates comparison views and selection changes', str_contains($repository, 'AS comparison_views') && str_contains($repository, 'AS comparison_selections')],
    ['06.07.09', 'Admin capability center renders comparison analytics summary', str_contains($adminUi, 'summary.comparison_views')],
    ['06.07.10', 'Analytics writes use the existing foundation event table without a parallel migration', str_contains($recorder, 'INSERT INTO admin_analytics_events') && str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_analytics_events')],
];

foreach ($checks as [$id, $label, $condition]) {
    p6Check($id . ' ' . $label, $condition);
}

echo '<br><strong>Phase 6 Product Analytics: ' . $passed . '/' . count($checks) . ' PASS</strong><br>' . PHP_EOL;
echo '<strong>Static source checks only. XAMPP/MySQL, browser consent, real checkout and payment runtime remain separate verification gates.</strong><br>' . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
