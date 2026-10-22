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
