<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';

Bootstrap::boot();

if (!Request::isPost()) {
    Response::header('Allow', 'POST');
    Response::text('Method not allowed.', 405);
}

$token = Request::post('csrf_token');

if (!Csrf::verify(is_string($token) ? $token : null)) {
    Response::text('درخواست نامعتبر است. صفحه را تازه‌سازی کنید و دوباره تلاش کنید.', 419);
}

$adminAuth = new AdminAuthService();

if ($adminAuth->validateCurrentSession()) {
    AdminAuth::logout();
}

Response::redirect(url('/admin/auth/login.php'));
