<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/includes/auth.php';

$firstName = trim((string)($currentAdmin['first_name'] ?? ''));
$lastName = trim((string)($currentAdmin['last_name'] ?? ''));
$displayName = trim($firstName . ' ' . $lastName);

if ($displayName === '') {
    $displayName = 'مدیر محترم';
}

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>عدم دسترسی | نیواد</title>
</head>
<body class="admin-access-denied-page">
<main class="admin-access-denied-main">
    <section class="admin-access-denied-card" role="alert" aria-labelledby="admin-access-denied-title">
        <span class="admin-access-denied-code" aria-hidden="true">403</span>
        <p class="admin-access-denied-eyebrow">ADMIN ACCESS CONTROL</p>
        <h1 id="admin-access-denied-title">دسترسی به این بخش مجاز نیست</h1>
        <p class="admin-access-denied-message">
            <?= e($displayName) ?>، شما مجوز دسترسی به این بخش را ندارید.
        </p>
        <p class="admin-access-denied-help">
            برای دریافت دسترسی مناسب، با مدیر ارشد سیستم هماهنگ کنید.
        </p>
        <a class="admin-access-denied-action" href="<?= e(url('/admin')) ?>">بازگشت به داشبورد</a>
    </section>
</main>
</body>
</html>
