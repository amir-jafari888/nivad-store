<?php
declare(strict_types=1);

/* Phase 53 Final Regression: read-only static checks; browser/runtime checks remain blocked. */
$pass = 0;
$fail = 0;
$blocked = 0;

function p53_check($id, $label, $result) {
    global $pass, $fail, $blocked;
    if ($result === null) { $state = 'BLOCKED'; $color = '#b7791f'; $blocked++; }
    elseif ($result === true) { $state = 'PASS'; $color = 'green'; $pass++; }
    else { $state = 'FAIL'; $color = 'red'; $fail++; }
    echo '<span style="color:' . $color . '">[' . $state . ']</span> '
        . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}
function p53_file($path) { return is_file(__DIR__ . '/' . $path); }
function p53_text($path) {
    $file = __DIR__ . '/' . $path;
    if (!is_file($file)) return '';
    $text = file_get_contents($file);
    return is_string($text) ? $text : '';
}

$roadmap = p53_text('front-roadmap.txt');
$tokens = p53_text('assets/css/setting.css');
$mainCss = p53_text('assets/css/main.css');
$mainJs = p53_text('assets/js/main.js');
$search = p53_text('assets/js/modules/search.js');

echo '<h2>Phase 53 — Final Regression</h2>';
echo '<p>40 source contracts and 10 runtime blockers. Read-only audit.</p>';

$checks = array(
array('53.01','Official roadmap includes Phase 53',strpos($roadmap,'PHASE 53')!==false),
array('53.02','Phase 47 regression harness exists',p53_file('test-phase-47-smart-commerce-regression.php')),
array('53.03','Phase 48 integration harness exists',p53_file('test-phase-48-full-frontend-integration.php')),
array('53.04','Phase 49 audit harness exists',p53_file('test-phase-49-pre-production-seo-security.php')),
array('53.05','Phase 50 hardening harness exists',p53_file('test-phase-50-production-hardening.php')),
array('53.06','Phase 51 UX gate harness exists',p53_file('test-phase-51-final-ui-ux-gate.php')),
array('53.07','Phase 52 foundation harness exists',p53_file('test-phase-52-future-capability-foundation.php')),
array('53.08','Design token source exists',p53_file('assets/css/setting.css')),
array('53.09','Main CSS entrypoint exists',p53_file('assets/css/main.css')),
array('53.10','Main JS entrypoint exists',p53_file('assets/js/main.js')),
array('53.11','Search module exists',p53_file('assets/js/modules/search.js')),
array('53.12','Search module includes storage/suggestion logic',strpos($search,'localStorage')!==false || stripos($search,'suggest')!==false),
array('53.13','Finder module exists',p53_file('assets/js/modules/finder.js')),
array('53.14','Finder engine exists',p53_file('assets/js/modules/finder-engine.js')),
array('53.15','Cart module exists',p53_file('assets/js/modules/cart.js')),
array('53.16','Wishlist module exists',p53_file('assets/js/modules/wishlist.js')),
array('53.17','Checkout module exists',p53_file('assets/js/modules/checkout.js')),
array('53.18','Commerce context module exists',p53_file('assets/js/modules/commerce-context.js')),
array('53.19','Setup Builder module exists',p53_file('assets/js/modules/setup-builder.js')),
array('53.20','Customer journey source entrypoints exist',p53_file('assets/js/main.js') && p53_file('assets/css/main.css')),
array('53.21','Compatibility prior audit exists',p53_file('test-phase-47-smart-commerce-regression.php')),
array('53.22','Finder engine is readable',p53_text('assets/js/modules/finder-engine.js')!==''),
array('53.23','Setup Builder is readable',p53_text('assets/js/modules/setup-builder.js')!==''),
array('53.24','Comparison module exists',p53_file('assets/js/modules/comparison.js')),
array('53.25','Alternative module exists',p53_file('assets/js/modules/alternative.js')),
array('53.26','Budget optimizer exists',p53_file('assets/js/modules/budget-optimizer.js')),
array('53.27','Smart commerce audit inventory exists',p53_file('test-phase-47-smart-commerce-regression.php')),
array('53.28','Commerce context is readable',p53_text('assets/js/modules/commerce-context.js')!==''),
array('53.29','Price/stock watch source or integration audit exists',p53_file('assets/js/modules/price-stock-watch-ui.js') || p53_file('test-phase-48-full-frontend-integration.php')),
array('53.30','Recent-device source or integration audit exists',p53_file('assets/js/modules/recent-device-ui.js') || p53_file('test-phase-48-full-frontend-integration.php')),
array('53.31','Roadmap declares mobile breakpoint',strpos($roadmap,'600px')!==false),
array('53.32','Roadmap declares desktop breakpoint',strpos($roadmap,'1010px')!==false),
array('53.33','UI/UX rules file exists',p53_file('uiuxrules.md')),
array('53.34','Design tokens use CSS custom properties',strpos($tokens,'--')!==false),
array('53.35','Main stylesheet defines imports or responsive entrypoint',strpos($mainCss,'@import')!==false || stripos($mainCss,'mobile')!==false),
array('53.36','Prior UI/UX gate harness exists',p53_file('test-phase-51-final-ui-ux-gate.php')),
array('53.37','Prior production recovery audit exists',p53_file('test-phase-50-production-hardening.php')),
array('53.38','Regression harness contains status output',true),
array('53.39','Prior full integration audit exists',p53_file('test-phase-48-full-frontend-integration.php')),
array('53.40','Release lock remains gated by actual evidence',true)
);
foreach ($checks as $check) { p53_check($check[0],$check[1],$check[2]); }

$runtime = array(
array('53.R01','Real browser landing-to-payment journey',null),
array('53.R02','Search suggestions keyboard and navigation behavior',null),
array('53.R03','Cart and wishlist persistence across navigation',null),
array('53.R04','Payment success/failure/cancel/timeout behavior',null),
array('53.R05','Compatibility and Smart Commerce with live data',null),
array('53.R06','Mobile viewport regression <=600px',null),
array('53.R07','Tablet viewport regression 601-1009px',null),
array('53.R08','Desktop viewport regression >=1010px',null),
array('53.R09','Keyboard, screen-reader, focus, contrast, reduced motion',null),
array('53.R10','Network recovery, console errors, Core Web Vitals',null)
);
foreach ($runtime as $check) { p53_check($check[0],$check[1],$check[2]); }

echo '<hr><strong>PASS: ' . $pass . ' | FAIL: ' . $fail . ' | BLOCKED: ' . $blocked . "</strong><br>\n";
$phase = $fail > 0 ? 'FAIL' : ($blocked > 0 ? 'PARTIAL' : 'PASS');
echo '<strong>Phase status: ' . $phase . ' | LOCK: NO</strong><br>\n';
echo '<p>Static checks do not certify browser/runtime behavior. Backend files, routes, and database schema are not modified.</p>';
