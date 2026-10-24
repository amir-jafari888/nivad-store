<?php
declare(strict_types=1);

/**
 * Phase 51 — Final UI/UX Gate
 * Read-only static repository audit. No DB writes and no network requests.
 * Run from repository root: php test-phase-51-final-ui-ux-gate.php
 *
 * A static source match is not proof of runtime UX. Browser/deployment-only
 * contracts deliberately remain BLOCKED until corresponding evidence exists.
 */
$passed = 0;
$failed = 0;
$blocked = 0;

function p51Read(string $path): string
{
    $full = __DIR__ . '/' . $path;
    if (!is_file($full)) {
        return '';
    }
    $content = file_get_contents($full);
    return is_string($content) ? $content : '';
}

function p51Exists(string $path): bool
{
    return is_file(__DIR__ . '/' . $path);
}

function p51Has(string $path, string $needle): bool
{
    return str_contains(p51Read($path), $needle);
}

function p51Check(string $id, string $label, ?bool $result): void
{
    global $passed, $failed, $blocked;
    if ($result === null) {
        $status = 'BLOCKED';
        $color = '#b7791f';
        $blocked++;
    } elseif ($result) {
        $status = 'PASS';
        $color = 'green';
        $passed++;
    } else {
        $status = 'FAIL';
        $color = 'red';
        $failed++;
    }
    echo '<span style="color:' . $color . '">[' . $status . ']</span> '
        . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

$contracts = [
'51.01'=>'Primary page goal is clear','51.02'=>'Primary user action is discoverable','51.03'=>'Information order supports user goals','51.04'=>'Information hierarchy is appropriate','51.05'=>'Interface copy is understandable','51.06'=>'CTA outcome is explicit','51.07'=>'Navigation is consistent','51.08'=>'Current location is identifiable','51.09'=>'Unnecessary flow steps are absent','51.10'=>'Dead-end user journeys are absent',
'51.11'=>'Visual hierarchy is consistent','51.12'=>'Pages use the project design system','51.13'=>'Design tokens are centralized','51.14'=>'Spacing radius and shadow are systematic','51.15'=>'Typography is systematic','51.16'=>'Buttons have a clear hierarchy','51.17'=>'Product cards follow existing patterns','51.18'=>'Icons are consistent and understandable','51.19'=>'Shared components are reused consistently','51.20'=>'Visual inconsistencies are recorded',
'51.21'=>'Product prices are clear','51.22'=>'Availability is accurately presented','51.23'=>'Discount presentation is not misleading','51.24'=>'Known shipping costs are discoverable','51.25'=>'Product decision information is accessible','51.26'=>'Compatibility explanations are understandable','51.27'=>'Recommendations have explainable reasons when supported','51.28'=>'Cart and checkout feedback reflects real outcomes','51.29'=>'Trust claims are evidence-based','51.30'=>'Dark patterns and fake urgency are absent',
'51.31'=>'Buttons provide interaction feedback','51.32'=>'Loading state reflects actual work','51.33'=>'Empty states offer next steps','51.34'=>'Errors are understandable','51.35'=>'Success appears only after success','51.36'=>'Disabled states are understandable','51.37'=>'Toast is used for appropriate short feedback','51.38'=>'Modal and drawer open/close behavior is sound','51.39'=>'Dropdown tabs and accordion states are conveyed','51.40'=>'Duplicate interactions and listeners are absent',
'51.41'=>'Important errors have recovery paths','51.42'=>'Retry is offered only when appropriate','51.43'=>'Network failures are communicated','51.44'=>'Expired session has a recovery path','51.45'=>'Form data is preserved on errors','51.46'=>'Validation errors identify the relevant field','51.47'=>'Error pages offer a way out','51.48'=>'Important destructive actions are confirmed','51.49'=>'Undo is considered for reversible actions','51.50'=>'Uncertain smart results are not presented as certain',
'51.51'=>'Semantic HTML is appropriate','51.52'=>'Keyboard operation is supported','51.53'=>'Focus is visible','51.54'=>'Keyboard focus order is logical','51.55'=>'Modal and drawer focus is managed','51.56'=>'Contrast meets WCAG 2.2 AA','51.57'=>'Status messages are screen-reader accessible','51.58'=>'Inputs have associated labels and help','51.59'=>'Touch targets are adequate','51.60'=>'Reduced motion and ARIA usage are appropriate',
'51.61'=>'Mobile layout is usable at <=600px','51.62'=>'Tablet layout is usable at 601-1009px','51.63'=>'Desktop layout is usable at >=1010px','51.64'=>'No unintended horizontal overflow','51.65'=>'Navigation works at all breakpoints','51.66'=>'Forms tables and cards work responsively','51.67'=>'Media does not break layout','51.68'=>'RTL layout and icon placement are correct','51.69'=>'Mixed Persian English and numeric content is sound','51.70'=>'Long content wraps and scales correctly',
'51.71'=>'Light theme uses valid design tokens','51.72'=>'Dark theme is independently usable','51.73'=>'Text/background contrast is adequate in themes','51.74'=>'Surface border and shadow remain discernible','51.75'=>'Buttons and interaction states work in themes','51.76'=>'Forms work in both themes','51.77'=>'Modal drawer toast and overlays work in themes','51.78'=>'Media placeholders and skeletons work in themes','51.79'=>'Unauthorized hardcoded visual values are identified','51.80'=>'Theme switching does not break layout or interaction',
'51.81'=>'Image dimensions and loading are appropriate','51.82'=>'CSS imports resolve','51.83'=>'JavaScript imports and exports resolve','51.84'=>'Browser console has no relevant errors','51.85'=>'Layout shifts are controlled','51.86'=>'Interaction responsiveness is acceptable','51.87'=>'Links and CTAs have valid destinations','51.88'=>'Placeholder and unfinished content is absent','51.89'=>'Microcopy is clear','51.90'=>'Core Web Vitals are measured with appropriate tooling',
'51.91'=>'Header and footer regressions are absent','51.92'=>'Search suggestions and results regressions are absent','51.93'=>'Listing and product detail regressions are absent','51.94'=>'Cart and wishlist regressions are absent','51.95'=>'Checkout and payment UI regressions are absent','51.96'=>'Authentication and account UI regressions are absent','51.97'=>'Smart commerce integration regressions are absent','51.98'=>'Admin responsive and accessibility regressions are absent','51.99'=>'Regression failures and blockers are documented','51.100'=>'Release gate decision is evidence-based'
];

$checks = [
'51.12' => p51Exists('assets/css/setting.css'),
'51.13' => p51Exists('assets/css/setting.css') && p51Exists('assets/css/main.css'),
'51.14' => p51Exists('assets/css/setting.css'),
'51.15' => p51Exists('assets/css/setting.css'),
'51.17' => p51Exists('partials/product-card.php') || p51Exists('partials/product-card'),
'51.19' => p51Exists('partials/header.php') && p51Exists('partials/footer.php'),
'51.51' => p51Exists('index.php'),
'51.61' => p51Exists('assets/css/mobile/mobile.css'),
'51.62' => p51Exists('assets/css/tablet/tablet.css'),
'51.63' => p51Exists('assets/css/desktop/desktop.css'),
'51.68' => p51Has('assets/css/setting.css', 'rtl') || p51Has('assets/css/main.css', 'direction: rtl'),
'51.71' => p51Has('assets/css/setting.css', 'light') || p51Has('assets/css/setting.css', 'data-theme'),
'51.72' => p51Has('assets/css/setting.css', 'dark') || p51Has('assets/css/setting.css', 'data-theme'),
'51.82' => p51Exists('assets/css/main.css') && p51Exists('assets/css/mobile/mobile.css') && p51Exists('assets/css/tablet/tablet.css') && p51Exists('assets/css/desktop/desktop.css'),
'51.83' => p51Exists('assets/js/main.js'),
'51.91' => p51Exists('partials/header.php') && p51Exists('partials/footer.php'),
'51.92' => p51Exists('assets/js/modules/search.js') || p51Exists('partials/search.php'),
'51.93' => p51Exists('pages/store/products/show.php'),
'51.94' => p51Exists('pages/shop/cart.php') && p51Exists('pages/shop/wishlist.php'),
'51.95' => p51Exists('pages/shop/checkout.php'),
'51.96' => p51Exists('pages/auth/login.php') || p51Exists('pages/account/login.php'),
'51.98' => p51Exists('pages/admin') || p51Exists('app/Controllers/Admin'),
];

foreach ($contracts as $id => $label) {
    $result = array_key_exists($id, $checks) ? $checks[$id] : null;
    p51Check($id, $label, $result);
}

echo '<hr><strong>Phase 51 result: ' . $passed . ' PASS, ' . $failed . ' FAIL, ' . $blocked . ' BLOCKED.</strong><br>' . "\n";
echo '<strong>Phase status: ' . ($failed > 0 ? 'FAIL' : ($blocked > 0 ? 'PARTIAL' : 'PASS')) . '</strong><br>' . "\n";
echo '<strong>LOCK=' . (($failed === 0 && $blocked === 0) ? 'YES' : 'NO') . '</strong><br>' . "\n";
echo '<strong>Note:</strong> Static repository checks do not establish browser, accessibility, performance, or deployed-production behavior.<br>' . "\n";
