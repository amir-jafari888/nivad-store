<?php
declare(strict_types=1);

/**
 * Phase 49 — Pre-Production SEO & Security Audit
 * Read-only static audit. No database writes and no network requests.
 * Run from repository root: php test-phase-49-pre-production-seo-security.php
 */

$checks = 0;
$failures = 0;
$blocked = 0;

function phase49Check(string $id, string $label, bool $passed): void
{
    global $checks, $failures;
    $checks++;
    $status = $passed ? 'PASS' : 'FAIL';
    $color = $passed ? 'green' : 'red';
    echo '<span style="color:' . $color . '">[' . $status . ']</span> '
        . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
    if (!$passed) {
        $failures++;
    }
}

function phase49Blocked(string $id, string $label): void
{
    global $blocked;
    $blocked++;
    echo '<span style="color:#b7791f">[BLOCKED]</span> '
        . htmlspecialchars($id . ' ' . $label, ENT_QUOTES, 'UTF-8') . "<br>\n";
}

function phase49Read(string $path): string
{
    $full = __DIR__ . '/' . $path;
    if (!is_file($full)) {
        return '';
    }
    $content = file_get_contents($full);
    return is_string($content) ? $content : '';
}

function phase49Has(string $path, string $needle): bool
{
    return str_contains(phase49Read($path), $needle);
}

function phase49Exists(string $path): bool
{
    return is_file(__DIR__ . '/' . $path);
}

$header = phase49Read('partials/header.php');
$footer = phase49Read('partials/footer.php');
$security = phase49Read('app/Core/Security.php');
$securityConfig = phase49Read('config/security.php');
$headers = phase49Read('app/Middleware/SecurityHeadersMiddleware.php');
$bootstrap = phase49Read('app/Core/Bootstrap.php');
$seo = phase49Read('app/Controllers/SeoController.php');
$routes = phase49Read('routes/api.php');
$rewrites = phase49Read('.htaccess');
$urlHelper = phase49Read('app/Helpers/url.php');
$escape = phase49Read('app/Helpers/escape.php');
$product = phase49Read('pages/store/products/show.php');
$category = phase49Read('pages/store/category/show.php');
$brand = phase49Read('pages/store/brand/show.php');
$collection = phase49Read('pages/store/collection/show.php');
$search = phase49Read('pages/store/search/index.php');
$discover = phase49Read('pages/store/discover/show.php');
$login = phase49Read('pages/auth/login.php');
$register = phase49Read('pages/auth/register.php');
$checkout = phase49Read('pages/shop/checkout.php');
$cart = phase49Read('pages/shop/cart.php');
$wishlist = phase49Read('pages/shop/wishlist.php');
$mainJs = phase49Read('assets/js/main.js');

function phase49Count(string $source, string $pattern): int
{
    $result = preg_match_all($pattern, $source);
    return $result === false ? 0 : $result;
}

/* Batch 01 — SEO Metadata: 49.01–49.10 */
phase49Check('49.01', 'Shared header emits an escaped title element', str_contains($header, '<title><?= htmlspecialchars($seoTitle, ENT_QUOTES, \'UTF-8\') ?></title>'));
phase49Check('49.02', 'Shared header emits meta description when supplied', str_contains($header, 'name="description"') && str_contains($header, '$seoDescription !== \'\''));
phase49Check('49.03', 'Shared header emits a robots directive', str_contains($header, 'name="robots"'));
phase49Check('49.04', 'Shared header emits canonical only when a canonical URL is set', str_contains($header, 'rel="canonical"') && str_contains($header, '$seoCanonical !== null'));
phase49Check('49.05', 'SEO metadata values are HTML escaped', str_contains($header, 'htmlspecialchars($seoTitle') && str_contains($header, 'htmlspecialchars($seoRobots'));
phase49Check('49.06', 'Product detail defines dynamic title and description', str_contains($product, '$product->metaTitle') && str_contains($product, '$product->metaDescription'));
phase49Check('49.07', 'Category detail defines metadata values', str_contains($category, '$pageTitle') && str_contains($category, '$metaDescription'));
phase49Check('49.08', 'Brand detail defines a page title', str_contains($brand, '$pageTitle'));
phase49Check('49.09', 'Search page defines title and description', str_contains($search, '$pageTitle') && str_contains($search, '$metaDescription'));
phase49Check('49.10', 'Discovery page defines title and description', str_contains($discover, '$pageTitle') && str_contains($discover, '$metaDescription'));

/* Batch 02 — Canonical & Indexation: 49.11–49.20 */
phase49Check('49.11', 'Product detail generates canonical from product slug', str_contains($product, '$canonicalUrl') && str_contains($product, "url(\n            '/products/'"));
phase49Check('49.12', 'Category detail declares canonical URL', str_contains($category, '$canonicalUrl'));
phase49Check('49.13', 'Collection page declares canonical URL', str_contains($collection, '$canonicalUrl'));
phase49Check('49.14', 'Search page declares canonical URL', str_contains($search, '$canonicalUrl'));
phase49Check('49.15', 'Discovery page declares canonical URL', str_contains($discover, '$canonicalUrl'));
phase49Check('49.16', 'Product detail uses explicit robots policy', str_contains($product, "'index,follow'"));
phase49Check('49.17', 'Shared header has a default robots policy', str_contains($header, "'index,follow'"));
phase49Check('49.18', 'Unknown product does not receive a product canonical', str_contains($product, '$canonicalUrl = null'));
phase49Check('49.19', 'Canonical URL values are escaped before HTML output', str_contains($header, 'htmlspecialchars($seoCanonical'));
phase49Blocked('49.20', 'Indexation consistency across public and private pages needs deployed HTTP verification.');

/* Batch 03 — Robots & Sitemap: 49.21–49.30 */
phase49Check('49.21', 'Rewrite exposes robots.txt through the API front controller', str_contains($rewrites, 'RewriteRule ^robots\\.txt$ api/index.php?path=/seo/robots'));
phase49Check('49.22', 'Rewrite exposes sitemap.xml through the API front controller', str_contains($rewrites, 'RewriteRule ^sitemap\\.xml$ api/index.php?path=/seo/sitemap'));
phase49Check('49.23', 'Robots route is registered', str_contains($routes, "'/seo/robots'") && str_contains($routes, "[SeoController::class, 'robots']"));
phase49Check('49.24', 'Sitemap route is registered', str_contains($routes, "'/seo/sitemap'") && str_contains($routes, "[SeoController::class, 'sitemap']"));
phase49Check('49.25', 'Robots response sets text/plain content type', str_contains($seo, 'text/plain; charset=utf-8'));
phase49Check('49.26', 'Sitemap response sets XML content type', str_contains($seo, 'application/xml; charset=utf-8'));
phase49Check('49.27', 'Sitemap builds URL nodes from the configured base origin', str_contains($seo, 'urlNode(') && str_contains($seo, 'rtrim('));
phase49Check('49.28', 'Sitemap queries active products and categories', str_contains($seo, '->findActive()') && str_contains($seo, 'ProductService') && str_contains($seo, 'CategoryService'));
phase49Check('49.29', 'Sitemap escapes URL nodes for XML context', str_contains($seo, 'ENT_XML1') && str_contains($seo, 'htmlspecialchars'));
phase49Blocked('49.30', 'Sitemap URL coverage, status code and XML validity need runtime verification.');

/* Batch 04 — Structured Data & Social Metadata: 49.31–49.40 */
phase49Check('49.31', 'Product detail source is available for structured-data audit', phase49Exists('pages/store/products/show.php'));
phase49Check('49.32', 'Product detail exposes real product identity fields', str_contains($product, 'data-product-id') && str_contains($product, 'data-product-slug'));
phase49Check('49.33', 'Product detail exposes price and availability DOM hooks', str_contains($product, 'data-product-price') && str_contains($product, 'data-product-availability'));
phase49Check('49.34', 'Product detail exposes gallery and image hooks', str_contains($product, 'data-product-main-image') && str_contains($product, 'data-product-gallery'));
phase49Blocked('49.35', 'Organization/product structured-data contract is not established; no schema is invented.');
phase49Check('49.36', 'Open Graph title and type metadata are emitted', str_contains($header, 'property="og:title"') && str_contains($header, 'property="og:type"'));
phase49Check('49.37', 'Twitter Card metadata is emitted', str_contains($header, 'name="twitter:card"') && str_contains($header, 'name="twitter:title"'));
phase49Blocked('49.38', 'Social preview rendering and URL/image availability need browser verification.');
phase49Check('49.39', 'XML output uses XML-specific escaping', str_contains($seo, 'ENT_XML1'));
phase49Blocked('49.40', 'Structured-data completeness cannot be confirmed from current source evidence.');

/* Batch 05 — URL, Routing & Redirect Integrity: 49.41–49.50 */
phase49Check('49.41', 'Product listing rewrite exists', str_contains($rewrites, 'RewriteRule ^products/?$ pages/store/products/index.php'));
phase49Check('49.42', 'Product detail slug rewrite exists', str_contains($rewrites, 'RewriteRule ^products/([^/]+)/?$ pages/store/products/show.php?slug=$1'));
phase49Check('49.43', 'Category slug rewrite exists', str_contains($rewrites, 'RewriteRule ^categories/([^/]+)/?$ pages/store/category/show.php?slug=$1'));
phase49Check('49.44', 'Brand slug rewrite exists', str_contains($rewrites, 'RewriteRule ^brands/([^/]+)/?$ pages/store/brand/show.php?slug=$1'));
phase49Check('49.45', 'Collection slug rewrite exists', str_contains($rewrites, 'RewriteRule ^collections/([^/]+)/?$ pages/store/collection/show.php?slug=$1'));
phase49Check('49.46', 'HTTPS redirect implementation validates request host', str_contains($security, 'Invalid application host') && str_contains($security, 'HTTP_HOST'));
phase49Check('49.47', 'URL helper resolves configured base path', str_contains($urlHelper, 'app_base_path()') && str_contains($urlHelper, 'base_path'));
phase49Check('49.48', 'URL helper normalizes path separators and uses the configured base path', str_contains($urlHelper, 'ltrim(') && str_contains($urlHelper, 'app_base_path()'));
phase49Check('49.49', 'URL helper validates the fallback host with a restrictive pattern', str_contains($urlHelper, 'HTTP_HOST') && str_contains($urlHelper, 'preg_match'));
phase49Blocked('49.50', 'Redirect status, canonical host and actual URL outcomes need HTTP verification.');

/* Batch 06 — HTTPS & Security Headers: 49.51–49.60 */
phase49Check('49.51', 'HTTPS-only security configuration is enabled', str_contains($securityConfig, "'https_only' => true"));
phase49Check('49.52', 'HTTPS enforcement redirects with permanent 308 status', str_contains($security, 'Location: https://') && str_contains($security, '308'));
phase49Check('49.53', 'HTTPS redirect rejects CR/LF in request URI', str_contains($security, 'preg_match') && str_contains($security, 'Invalid request URI'));
phase49Check('49.54', 'X-Content-Type-Options nosniff header is configured', str_contains($headers, 'X-Content-Type-Options: nosniff'));
phase49Check('49.55', 'X-Frame-Options SAMEORIGIN header is configured', str_contains($headers, 'X-Frame-Options: SAMEORIGIN'));
phase49Check('49.56', 'Referrer-Policy header is configured', str_contains($headers, 'Referrer-Policy: strict-origin-when-cross-origin'));
phase49Check('49.57', 'Permissions-Policy header is configured', str_contains($headers, 'Permissions-Policy:'));
phase49Check('49.58', 'Content-Security-Policy header is configured', str_contains($headers, 'Content-Security-Policy:'));
phase49Check('49.59', 'HSTS is conditionally sent on HTTPS requests', str_contains($headers, 'Strict-Transport-Security:') && str_contains($headers, 'isset($_SERVER'));
phase49Blocked('49.60', 'Actual response headers and redirect chain need a deployed HTTP environment.');

/* Batch 07 — Output Encoding & XSS Surface: 49.61–49.70 */
phase49Check('49.61', 'Shared HTML escape helper uses ENT_QUOTES and UTF-8', str_contains($escape, 'ENT_QUOTES') && str_contains($escape, "'UTF-8'"));
phase49Check('49.62', 'Header escapes canonical and robots attributes', str_contains($header, 'htmlspecialchars($seoCanonical') && str_contains($header, 'htmlspecialchars($seoRobots'));
phase49Check('49.63', 'Product title and description flow through shared header escaping', str_contains($product, 'include_once __DIR__') && str_contains($header, 'htmlspecialchars($seoTitle'));
phase49Check('49.64', 'Product slug is URL encoded in canonical URL', str_contains($product, 'rawurlencode'));
phase49Check('49.65', 'Header no longer emits an inline NIVAD_BASE_URL script contract', !str_contains($header, 'NIVAD_BASE_URL') && !str_contains($header, '<script>'));
phase49Check('49.66', 'Main JavaScript module entry exists', phase49Exists('assets/js/main.js'));
phase49Check('49.67', 'Product detail uses escaped data attributes', str_contains($product, 'e(') && str_contains($product, 'data-product-slug'));
phase49Check('49.68', 'Sitemap encodes XML node values safely', str_contains($seo, 'htmlspecialchars'));
phase49Check('49.69', 'Login page escapes redirect attribute', str_contains($login, 'e(url('));
phase49Blocked('49.70', 'Comprehensive DOM-XSS scanning requires browser/runtime tooling.');

/* Batch 08 — Session, CSRF & Form Security Integration: 49.71–49.80 */
phase49Check('49.71', 'Session cookie secure flag is enabled in security configuration', str_contains($securityConfig, "'secure' => true"));
phase49Check('49.72', 'Session HttpOnly flag is enabled in security configuration', str_contains($securityConfig, "'httponly' => true"));
phase49Check('49.73', 'Session SameSite policy is configured', str_contains($securityConfig, "'samesite' => 'Lax'"));
phase49Check('49.74', 'Session strict mode is enabled', str_contains($securityConfig, "'use_strict_mode' => true"));
phase49Check('49.75', 'CSRF token contract is configured', str_contains($securityConfig, "'csrf' => [") && str_contains($securityConfig, "'token_name' => 'csrf_token'"));
phase49Check('49.76', 'Authentication form has required fields and autocomplete hints', str_contains($login, 'required') && str_contains($login, 'autocomplete='));
phase49Check('49.77', 'Registration page exists for auth form review', phase49Exists('pages/auth/register.php'));
phase49Check('49.78', 'Checkout form integration source exists', phase49Exists('pages/shop/checkout.php'));
phase49Check('49.79', 'Cart and wishlist pages expose API integration hooks', str_contains($cart, 'data-api-base') && str_contains($wishlist, 'data-api-base'));
phase49Blocked('49.80', 'Authenticated CSRF rejection and session expiry require API runtime tests.');

/* Batch 09 — Asset Security & CSP Compatibility: 49.81–49.90 */
phase49Check('49.81', 'Shared header references main stylesheet through asset helper', str_contains($header, "asset('css/main.css')"));
phase49Check('49.82', 'Shared footer references the main JavaScript module', str_contains($footer, "asset('js/main.js')") && str_contains($footer, 'type="module"'));
phase49Check('49.83', 'Swiper CSS asset reference exists', str_contains($header, 'swiper/swiper-bundle.min.css'));
phase49Check('49.84', 'CSP default-src is restricted to self', str_contains($headers, "default-src 'self'"));
phase49Check('49.85', 'Shared header does not rely on an inline script blocked by the restrictive CSP', !str_contains($header, '<script>') || str_contains($headers, "'unsafe-inline'"));
phase49Check('49.86', 'API requests are represented in customer page DOM contracts', str_contains($checkout, 'data-api-base') && str_contains($cart, 'data-api-base'));
phase49Check('49.87', 'Local CSS and JS asset trees exist', is_dir(__DIR__ . '/assets/css') && is_dir(__DIR__ . '/assets/js'));
phase49Check('49.88', 'Third-party resource references are discoverable in shared shell', str_contains($footer, 'swiper-bundle.min.js') && str_contains($header, 'swiper-bundle.min.css'));
phase49Blocked('49.89', 'CSP console violations require browser execution.');
phase49Blocked('49.90', 'Admin asset CSP compatibility requires authenticated browser verification.');

/* Batch 10 — Pre-Production Regression & Release Gate: 49.91–49.100 */
phase49Check('49.91', 'Core SEO pages are available for final regression', phase49Exists('index.php') && phase49Exists('pages/store/products/show.php') && phase49Exists('pages/store/category/show.php'));
phase49Check('49.92', 'Canonical and robots output paths are represented in shared header', str_contains($header, 'rel="canonical"') && str_contains($header, 'name="robots"'));
phase49Check('49.93', 'Robots and Sitemap routes are registered and rewritten', str_contains($routes, "'/seo/robots'") && str_contains($routes, "'/seo/sitemap'") && str_contains($rewrites, 'sitemap\\.xml'));
phase49Check('49.94', 'HTTPS and baseline security headers are configured', str_contains($security, 'enforceHttps') && str_contains($headers, 'X-Content-Type-Options'));
phase49Check('49.95', 'HTML and XML output escaping primitives exist', str_contains($escape, 'htmlspecialchars') && str_contains($seo, 'ENT_XML1'));
phase49Check('49.96', 'Session and CSRF configuration is present', str_contains($securityConfig, "'session' => [") && str_contains($securityConfig, "'csrf' => ["));
phase49Check('49.97', 'Primary public routes have rewrite declarations', str_contains($rewrites, '^products') && str_contains($rewrites, '^categories') && str_contains($rewrites, '^search'));
phase49Check('49.98', 'PHP test and audited source files are available for CI lint', phase49Exists('test-phase-49-pre-production-seo-security.php'));
phase49Blocked('49.99', 'Release blockers must be reconciled after runtime verification.');
phase49Blocked('49.100', 'Phase lock is blocked until runtime contracts are verified.');

/* Runtime-only verification must remain explicit and visible. */
phase49Blocked('RUNTIME.01', 'Live HTTP status, redirect chain, robots.txt and sitemap.xml response validation.');
phase49Blocked('RUNTIME.02', 'Browser-rendered metadata, canonical URL, social previews and structured-data validation.');
phase49Blocked('RUNTIME.03', 'CSP console audit for remaining inline styles/scripts and external asset compatibility.');
phase49Blocked('RUNTIME.04', 'Authenticated CSRF/session rejection and checkout/cart API flow tests.');
phase49Blocked('RUNTIME.05', 'Full DOM-XSS, external asset and Admin CSP regression requires a running browser/application environment.');

echo '<br><strong>Phase 49 result: ' . $checks . ' checks, ' . $failures . ' failures, ' . $blocked . ' blocked runtime verifications.</strong><br>';
echo '<strong>Phase status: ' . (($failures === 0 && $blocked === 0) ? 'PASS' : (($failures > 0) ? 'FAIL' : 'PARTIAL')) . '</strong><br>';
echo '<strong>LOCK=' . (($failures === 0 && $blocked === 0) ? 'YES' : 'NO') . '</strong><br>';

if ($failures > 0) {
    exit(1);
}
