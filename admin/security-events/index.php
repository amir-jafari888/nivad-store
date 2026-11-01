<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

if (!$adminAuth->can('admin.security_events.view')) {
    Response::redirect(url('/admin/access-denied.php'));
}

$items = (new AdminSecurityOperationsService())->recent(100);
function securityEventEscape($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>عملیات امنیتی | نیواد</title>
</head>
<body>
<main>
    <h1>عملیات امنیتی و سیگنال‌های مشکوک</h1>
    <p>این موارد هشدارهای نیازمند بررسی انسانی هستند و به‌تنهایی اثبات تقلب یا تخلف نیستند.</p>
    <form method="post" action="<?= securityEventEscape(url('/api/index.php?path=/admin/security-events/scan')) ?>">
        <button type="submit">اجرای اسکن امنیتی</button>
    </form>
    <table>
        <thead><tr><th>شدت</th><th>نوع</th><th>موضوع</th><th>شرح</th><th>تعداد</th><th>آخرین مشاهده</th><th>وضعیت</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= securityEventEscape($item['severity']) ?></td>
                <td><?= securityEventEscape($item['event_key']) ?></td>
                <td><?= securityEventEscape($item['subject_type'] . ':' . $item['subject_id']) ?></td>
                <td><?= securityEventEscape($item['summary']) ?></td>
                <td><?= securityEventEscape($item['occurrence_count']) ?></td>
                <td><?= securityEventEscape($item['last_seen_at']) ?></td>
                <td><?= securityEventEscape($item['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
