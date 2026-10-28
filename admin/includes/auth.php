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
        url('/admin/auth/login.php')
    );
}

$currentAdmin =
    $adminAuth->currentSafe();

if ($currentAdmin === null) {
    Response::redirect(
        url('/admin/auth/login.php')
    );
}

/*
 * Shared Admin Design System assets.
 * Keep the legacy page markup intact and inject assets only when a head exists.
 */
ob_start(static function (string $html): string {
    if (
        stripos($html, '</head>') === false
        || stripos($html, '/admin/assets/css/main.css') !== false
    ) {
        return $html;
    }

    $stylesheet = e(url('/admin/assets/css/main.css'));
    $script = e(url('/admin/assets/js/main.js'));
    $assets = "\n<link rel=\"stylesheet\" href=\"{$stylesheet}\">"
        . "\n<script type=\"module\" src=\"{$script}\"></script>\n";

    $updated = preg_replace(
        '/<\/head\s*>/i',
        $assets . '</head>',
        $html,
        1
    );

    return is_string($updated) ? $updated : $html;
});
