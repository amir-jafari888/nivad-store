<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/app/Core/Bootstrap.php';

Bootstrap::boot();

$adminAuth =
    new AdminAuthService();

if (
    !$adminAuth->validateCurrentSession()
) {
    Response::redirect(
        url('/admin/auth/login')
    );
}

$currentAdmin =
    $adminAuth->currentSafe();

if ($currentAdmin === null) {
    Response::redirect(
        url('/admin/auth/login')
    );
}

/*
 * Shared Admin Design System asset injection.
 * The output buffer keeps legacy page templates intact while loading the
 * shared stylesheet after inline legacy styles.
 */
ob_start(static function (string $html): string {
    if (
        stripos($html, '</head>') === false
        || stripos($html, '/assets/css/admin/admin.css') !== false
    ) {
        return $html;
    }

    $stylesheet = e(url('/assets/css/admin/admin.css'));
    $script = e(url('/assets/js/admin-theme.js'));
    $assets = "\n<link rel=\"stylesheet\" href=\"{$stylesheet}\">"
        . "\n<script defer src=\"{$script}\"></script>\n";

    $updated = preg_replace(
        '/<\/head\s*>/i',
        $assets . '</head>',
        $html,
        1
    );

    return is_string($updated) ? $updated : $html;
});
