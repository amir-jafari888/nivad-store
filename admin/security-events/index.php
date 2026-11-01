<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Bootstrap.php';
Bootstrap::boot();
require_once __DIR__ . '/../includes/auth.php';

if (!$adminAuth->can('admin.security_events.view')) {
    Response::redirect(url('/admin/access-denied.php'));
}

$items = (new AdminSecurityOperationsService())->recent(100);
$csrfToken = Csrf::token();
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
    <meta name="csrf-token" content="<?= securityEventEscape($csrfToken) ?>">
    <title>عملیات امنیتی | نیواد</title>
</head>
<body>
<main>
    <h1>عملیات امنیتی و سیگنال‌های مشکوک</h1>
    <p>این موارد هشدارهای نیازمند بررسی انسانی هستند و به‌تنهایی اثبات تقلب یا تخلف نیستند.</p>
    <button type="button" id="run-security-scan">اجرای اسکن امنیتی</button>
    <p id="scan-result" role="status" aria-live="polite"></p>
    <table>
        <thead><tr><th>شدت</th><th>نوع</th><th>موضوع</th><th>شرح</th><th>تعداد</th><th>آخرین مشاهده</th><th>وضعیت</th><th>اقدام</th></tr></thead>
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
                <td><?php if ($item['status'] === 'open'): ?><button type="button" class="review-security-event" data-id="<?= (int)$item['id'] ?>">ثبت بررسی</button><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
<script>
(() => {
    const button = document.getElementById("run-security-scan");
    const result = document.getElementById("scan-result");
    button?.addEventListener("click", async () => {
        button.disabled = true;
        result.textContent = "در حال اسکن...";
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
            const response = await fetch("<?= securityEventEscape(url('/api/index.php?path=/admin/security-events/scan')) ?>", {
                method: "POST", credentials: "same-origin",
                headers: { "Accept": "application/json", "X-CSRF-TOKEN": csrf }
            });
            const payload = await response.json();
            if (!response.ok || payload.success !== true) throw new Error(payload.message || "اسکن انجام نشد.");
            result.textContent = "تعداد سیگنال‌های ثبت‌شده: " + (payload.data?.total ?? 0) + ". صفحه را برای مشاهده به‌روزرسانی کنید.";
        } catch (error) { result.textContent = error.message || "خطا در اسکن امنیتی."; }
        finally { button.disabled = false; }
    });
    document.querySelectorAll(".review-security-event").forEach((reviewButton) => {
        reviewButton.addEventListener("click", async () => {
            const note = window.prompt("یادداشت بررسی (اختیاری):", "");
            if (note === null) return;
            reviewButton.disabled = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
                const id = reviewButton.dataset.id;
                const response = await fetch("<?= securityEventEscape(url('/api/index.php?path=/admin/security-events/')) ?>" + encodeURIComponent(id) + "/review", {
                    method: "PATCH", credentials: "same-origin",
                    headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": csrf },
                    body: JSON.stringify({ note })
                });
                const payload = await response.json();
                if (!response.ok || payload.success !== true) throw new Error(payload.message || "ثبت بررسی انجام نشد.");
                window.location.reload();
            } catch (error) {
                window.alert(error.message || "خطا در ثبت بررسی.");
                reviewButton.disabled = false;
            }
        });
    });
})();
</script>

</body>
</html>
