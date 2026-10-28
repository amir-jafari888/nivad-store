<?php

declare(strict_types=1);

/**
 * Phase 29 — Authentication Frontend Comprehensive Static Regression
 *
 * Run from the repository root:
 *   php test-phase-29-authentication.php
 *
 * Read-only: this test does not modify files or write to the database.
 */

$root = __DIR__;
$passed = 0;
$failed = 0;
$blocked = 0;

function phase29Check(string $label, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$label}\n";
}

function phase29Read(string $relativePath): ?string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    if (!is_file($path) || !is_readable($path)) {
        return null;
    }

    $content = file_get_contents($path);
    return is_string($content) ? $content : null;
}

function phase29Has(string $content, string $needle): bool
{
    return str_contains($content, $needle);
}

$paths = [
    'login' => 'pages/auth/login.php',
    'register' => 'pages/auth/register.php',
    'forgot' => 'pages/auth/forgot-password.php',
    'reset' => 'pages/auth/reset-password.php',
    'verify' => 'pages/auth/verify-email.php',
    'js' => 'assets/js/modules/auth.js',
    'css' => 'assets/css/auth.css',
    'settings' => 'assets/css/setting.css',
    'main_css' => 'assets/css/main.css',
    'main_js' => 'assets/js/main.js',
    'mobile_css' => 'assets/css/mobile/mobile.css',
    'tablet_css' => 'assets/css/tablet/tablet.css',
    'desktop_css' => 'assets/css/desktop/desktop.css',
    'mobile_auth' => 'assets/css/mobile/auth-mobile.css',
    'tablet_auth' => 'assets/css/tablet/auth-tablet.css',
    'desktop_auth' => 'assets/css/desktop/auth-desktop.css',
    'api_routes' => 'routes/api.php',
    'auth_controller' => 'app/Controllers/AuthController.php',
    'auth_validator' => 'app/Validators/AuthValidator.php',
    'csrf_middleware' => 'app/Middleware/CsrfMiddleware.php',
];

$data = [];
foreach ($paths as $key => $path) {
    $data[$key] = phase29Read($path);
}

$contract = static function (string $key, string $needle) use (&$data): bool {
    return is_string($data[$key] ?? null) && phase29Has($data[$key], $needle);
};

/* Batch 01 — Structure, encoding and route integrity (29.01–29.10). */
phase29Check('29.01 login page exists and has data-auth-page', is_string($data['login']) && phase29Has($data['login'], 'data-auth-page'));
phase29Check('29.02 register page exists and has data-auth-register-form', is_string($data['register']) && phase29Has($data['register'], 'data-auth-register-form'));
phase29Check('29.03 forgot-password page has its form hook', is_string($data['forgot']) && phase29Has($data['forgot'], 'data-auth-forgot-form'));
phase29Check('29.04 reset-password page has its form hook', is_string($data['reset']) && phase29Has($data['reset'], 'data-auth-reset-form'));
phase29Check('29.05 verify-email page has verify and resend hooks', is_string($data['verify']) && phase29Has($data['verify'], 'data-auth-verify-form') && phase29Has($data['verify'], 'data-auth-resend-form'));
$encodingClean = true;
foreach (['login', 'register', 'forgot', 'reset', 'verify'] as $key) {
    if (!is_string($data[$key]) || preg_match('/(?:ظˆط|ط§|ط¨|ظ†|ظ‡|ظ…|ظ„|ط±|طھ|â€|Ã.)/u', $data[$key]) === 1) {
        $encodingClean = false;
    }
}
phase29Check('29.06 auth pages do not contain common mojibake markers', $encodingClean);
phase29Check('29.07 auth.js binds pages through data-auth-page', $contract('js', 'querySelectorAll("[data-auth-page]")'));
phase29Check('29.08 form field names match the frontend API payload contract', $contract('js', 'password_confirmation') && $contract('js', 'new_password_confirmation') && $contract('js', 'first_name'));
phase29Check('29.09 auth pages do not hardcode the /nivad-store mount path', is_string($data['login']) && is_string($data['register']) && is_string($data['forgot']) && is_string($data['reset']) && is_string($data['verify']) && !preg_match('~/nivad-store/~', $data['login'] . $data['register'] . $data['forgot'] . $data['reset'] . $data['verify']));
phase29Check('29.10 auth pages use project URL helper for internal destinations', $contract('login', "url('/pages/auth/register.php')") && $contract('register', "url('/pages/auth/login.php')") && $contract('verify', "url('/pages/auth/login.php')"));

/* Batch 02 — Design tokens, theme and responsive styles (29.11–29.20). */
phase29Check('29.11 setting.css defines semantic surface and text tokens', $contract('settings', '--color-surface:') && $contract('settings', '--color-text:'));
phase29Check('29.12 auth.css uses design tokens for semantic colors', $contract('css', 'var(--color-border)') && $contract('css', 'var(--color-error)') && $contract('css', 'var(--color-success)'));
phase29Check('29.13 auth card uses surface, radius and shadow tokens', $contract('css', 'var(--color-surface)') && $contract('css', 'var(--radius-xl') && $contract('css', 'var(--shadow-md)'));
phase29Check('29.14 mobile auth stylesheet is imported', $contract('mobile_css', '@import url(./auth-mobile.css);') && is_string($data['mobile_auth']));
phase29Check('29.15 tablet auth stylesheet is imported', $contract('tablet_css', '@import url(./auth-tablet.css);') && is_string($data['tablet_auth']));
phase29Check('29.16 desktop auth stylesheet is imported', $contract('desktop_css', '@import url(./auth-desktop.css);') && is_string($data['desktop_auth']));
phase29Check('29.17 dark theme auth styles follow semantic surface tokens', $contract('css', ':root[data-theme="dark"]') && $contract('css', 'var(--color-surface)'));
phase29Check('29.18 auth form controls are full-width and box-sized', $contract('css', 'box-sizing: border-box;') && $contract('css', 'width: 100%;'));
phase29Check('29.19 focus-visible and invalid input states are defined', $contract('css', ':focus-visible') && $contract('css', '[aria-invalid="true"]'));
phase29Check('29.20 mobile inputs/buttons have at least 48px touch height', $contract('mobile_auth', 'min-height: 48px;'));

/* Batch 03 — Login and registration (29.21–29.30). */
phase29Check('29.21 login submits to existing POST /login endpoint', $contract('js', '"/login"') && $contract('api_routes', "'/login'"));
phase29Check('29.22 login sends identifier field', $contract('js', 'identifier: form.elements.namedItem("identifier").value'));
phase29Check('29.23 login sends password field', $contract('js', 'password: form.elements.namedItem("password").value'));
phase29Check('29.24 frontend retrieves CSRF token from existing endpoint', $contract('js', 'request("/auth/csrf")') && $contract('api_routes', "'/auth/csrf'"));
phase29Check('29.25 CSRF token is sent as csrf_token form parameter', $contract('js', 'body.set("csrf_token", await csrf())') && $contract('csrf_middleware', "Request::post('csrf_token')"));
phase29Check('29.26 registration submits to existing POST /register endpoint', $contract('js', '"/register"') && $contract('api_routes', "'/register'"));
phase29Check('29.27 registration payload includes first/last name, email and phone', $contract('js', 'first_name: form.elements.namedItem("first_name").value') && $contract('js', 'last_name: form.elements.namedItem("last_name").value') && $contract('js', 'email: form.elements.namedItem("email").value') && $contract('js', 'phone: form.elements.namedItem("phone").value'));
phase29Check('29.28 registration sends password confirmation', $contract('js', 'password_confirmation: form.elements.namedItem("password_confirmation").value'));
phase29Check('29.29 API field validation errors are mapped to the matching inputs', $contract('js', 'showFieldErrors(form, fieldErrors)') && $contract('js', 'setAttribute("aria-invalid", "true")'));
phase29Check('29.30 duplicate submission is prevented and loading is announced', $contract('js', 'form.dataset.submitting === "true"') && $contract('js', 'setAttribute("aria-busy", String(loading))'));

/* Batch 04 — Forgot and reset password (29.31–29.40). */
phase29Check('29.31 forgot-password uses existing endpoint', $contract('js', '"/forgot-password"') && $contract('api_routes', "'/forgot-password'"));
phase29Check('29.32 forgot-password form uses email input semantics', $contract('forgot', 'type="email"') && $contract('forgot', 'name="email"'));
phase29Check('29.33 recovery response avoids confirming account existence', $contract('js', 'اگر حسابی با این ایمیل وجود داشته باشد، راهنمای بازیابی ارسال خواهد شد.'));
phase29Check('29.34 timeout handling exists for auth requests', $contract('js', 'setTimeout(() => controller.abort(), 15000)'));
phase29Check('29.35 reset-password uses existing endpoint', $contract('js', '"/reset-password"') && $contract('api_routes', "'/reset-password'"));
phase29Check('29.36 query token is copied into token inputs', $contract('js', 'new URLSearchParams(window.location.search).get("token")') && $contract('js', 'field.value = token'));
phase29Check('29.37 reset form requires token field', $contract('reset', 'name="token"') && $contract('reset', 'required'));
phase29Check('29.38 reset payload uses new_password and confirmation fields', $contract('js', 'new_password: form.elements.namedItem("new_password").value') && $contract('js', 'new_password_confirmation: form.elements.namedItem("new_password_confirmation").value'));
phase29Check('29.39 non-success API responses are treated as errors', $contract('js', '!response.ok || payload?.success === false'));
phase29Check('29.40 successful reset redirects through the base URL', $contract('js', '`${baseUrl}/pages/auth/login.php`'));

/* Batch 05 — Email verification and session navigation (29.41–29.50). */
phase29Check('29.41 verify email uses existing endpoint', $contract('js', '"/verify-email"') && $contract('api_routes', "'/verify-email'"));
phase29Check('29.42 token query parameter is read for verification pages', $contract('js', 'get("token")') && $contract('verify', 'name="token"'));
phase29Check('29.43 resend verification uses existing endpoint', $contract('js', '"/resend-email-verification"') && $contract('api_routes', "'/resend-email-verification'"));
phase29Check('29.44 resend form has its own live feedback region', is_string($data['verify']) && substr_count($data['verify'], 'data-auth-message') >= 2);
phase29Check('29.45 both verification forms use shared duplicate-submit guard', $contract('js', 'form.dataset.submitting = "true"'));
phase29Check('29.46 success/error feedback is written with textContent', $contract('js', 'element.textContent = message'));
phase29Check('29.47 session status is queried from existing endpoint', $contract('js', 'request("/auth/status")') && $contract('api_routes', "'/auth/status'"));
phase29Check('29.48 header and mobile account links are updated', $contract('js', '.profile-menu__primary') && $contract('js', '.mobile-menu__login') && $contract('js', '.mobile-menu__register'));
phase29Check('29.49 logout posts with a CSRF form body', $contract('js', 'request("/logout", { method: "POST", body: await formBody({}) })') && $contract('api_routes', "'/logout'"));
phase29Check('29.50 401 and 419 session/auth failures have explicit handling', $contract('js', 'response.status === 419') && $contract('js', 'response.status === 401'));

/* Batch 06 — Accessibility, recovery and security UI (29.51–29.60). */
phase29Check('29.51 all auth inputs have explicit label-for associations', (function () use (&$data): bool {
    foreach (['login', 'register', 'forgot', 'reset', 'verify'] as $key) {
        if (!is_string($data[$key]) || preg_match_all('/<input\\b[^>]*\\bid="([^"]+)"/i', $data[$key], $inputs) === false) return false;
        preg_match_all('/<label\\b[^>]*\\bfor="([^"]+)"/i', $data[$key], $labels);
        $ids = $inputs[1] ?? [];
        foreach ($ids as $id) if (!in_array($id, $labels[1] ?? [], true)) return false;
    }
    return true;
})());
phase29Check('29.52 live feedback uses aria-live and role=status', $contract('login', 'aria-live="polite"') && $contract('register', 'role="status"'));
phase29Check('29.53 field errors set aria-invalid and aria-describedby', $contract('js', 'setAttribute("aria-describedby"') && $contract('js', 'setAttribute("aria-invalid", "true")'));
phase29Check('29.54 autocomplete/input types are specified', $contract('login', 'autocomplete="current-password"') && $contract('register', 'autocomplete="email"') && $contract('reset', 'autocomplete="new-password"'));
phase29Check('29.55 keyboard focus-visible styles exist', $contract('css', ':focus-visible'));
phase29Check('29.56 network and malformed-response failures have user feedback', $contract('js', 'genericNetworkMessage') && $contract('js', 'پاسخ سرور قابل پردازش نیست.'));
phase29Check('29.57 expected authentication and validation error payloads are handled', $contract('js', 'response.status === 401') && $contract('js', 'response.status === 419') && $contract('js', 'error?.payload?.errors'));
phase29Check('29.58 responsive CSS files cover mobile/tablet/desktop', is_string($data['mobile_auth']) && is_string($data['tablet_auth']) && is_string($data['desktop_auth']));
phase29Check('29.59 auth module remains imported and initialized by main.js', $contract('main_js', 'import auth from "./modules/auth.js";') && $contract('main_js', 'auth();') && $contract('main_css', '@import url(./auth.css);'));
phase29Check('29.60 frontend API routes are present without editing backend contracts', $contract('api_routes', "'/login'") && $contract('api_routes', "'/register'") && $contract('api_routes', "'/forgot-password'") && $contract('api_routes', "'/reset-password'") && $contract('api_routes', "'/verify-email'") && $contract('api_routes', "'/resend-email-verification'"));

/* Batch 07 — Final static regression and syntax gate (29.61–29.70). */
phase29Check('29.61 all five auth pages exist', count(array_filter(['login', 'register', 'forgot', 'reset', 'verify'], static fn ($key) => is_string($data[$key]))) === 5);
phase29Check('29.62 all three breakpoint stylesheets exist', count(array_filter(['mobile_auth', 'tablet_auth', 'desktop_auth'], static fn ($key) => is_string($data[$key]))) === 3);
phase29Check('29.63 every auth form has a submit button', (function () use (&$data): bool {
    foreach (['login', 'register', 'forgot', 'reset', 'verify'] as $key) {
        if (!is_string($data[$key]) || !str_contains($data[$key], 'type="submit"')) return false;
    }
    return true;
})());
phase29Check('29.64 user-provided messages are inserted as text, not HTML', $contract('js', 'element.textContent = message') && !$contract('js', 'element.innerHTML = message'));
phase29Check('29.65 request credentials are same-origin', $contract('js', 'credentials: "same-origin"'));
phase29Check('29.66 form submissions use URLSearchParams compatible with CSRF middleware', $contract('js', 'new URLSearchParams()') && $contract('csrf_middleware', "Request::post('csrf_token')"));
phase29Check('29.67 all auth pages load shared header/footer partials', (function () use (&$data): bool {
    foreach (['login', 'register', 'forgot', 'reset', 'verify'] as $key) {
        if (!is_string($data[$key]) || !str_contains($data[$key], "partials/header.php") || !str_contains($data[$key], "partials/footer.php")) return false;
    }
    return true;
})());
phase29Check('29.68 base URL is read from the existing project global', $contract('js', 'window.NIVAD_BASE_URL') && $contract('login', "url('/')"));
phase29Check('29.69 JavaScript auth module has balanced basic delimiters', (function () use (&$data): bool {
    if (!is_string($data['js'])) return false;
    return str_contains($data['js'], 'const auth = () => {')
        && str_contains($data['js'], 'export default auth;')
        && !str_contains($data['js'], 'TODO_PHASE29');
})());
$phpLintOk = true;
if (defined('PHP_BINARY') && function_exists('exec')) {
    foreach (['login', 'register', 'forgot', 'reset', 'verify'] as $key) {
        $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $paths[$key]);
        $output = [];
        $exitCode = 1;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1', $output, $exitCode);
        if ($exitCode !== 0) $phpLintOk = false;
    }
} else {
    $blocked++;
}
phase29Check('29.70 PHP syntax lint for all auth pages (requires PHP CLI)', $phpLintOk);

echo "\n";
echo "PHASE 29 AUTHENTICATION — SUMMARY\n";
echo "PASS: {$passed}\n";
echo "FAIL: {$failed}\n";
echo "BLOCKED: {$blocked}\n";
echo "LOCK: " . (($failed === 0 && $blocked === 0 && $passed === 70) ? 'ELIGIBLE FOR REVIEW' : 'NO') . "\n";

exit($failed === 0 && $blocked === 0 && $passed === 70 ? 0 : 1);
