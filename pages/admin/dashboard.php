<?php

declare(strict_types=1);

$admin = is_array($admin ?? null) ? $admin : [];
$metrics = is_array($metrics ?? null) ? $metrics : [];

$orders = is_array($metrics['orders'] ?? null) ? $metrics['orders'] : [];
$payments = is_array($metrics['payments'] ?? null) ? $metrics['payments'] : [];
$orderItems = is_array($metrics['order_items'] ?? null) ? $metrics['order_items'] : [];
$inventory = is_array($metrics['inventory'] ?? null) ? $metrics['inventory'] : [];
$salesByPeriod = is_array($metrics['sales_by_period'] ?? null) ? $metrics['sales_by_period'] : [];
$salesByPeriodStatus = (string)($metrics['sales_by_period_status'] ?? 'dependency');

$formatNumber = static function (mixed $value): string {
    if (!is_numeric($value)) {
        return '۰';
    }

    return number_format((float)$value, 0, '.', ',');
};

$formatDate = static function (mixed $value): string {
    if (!is_string($value) || $value === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value))->format('m/d');
    } catch (Throwable $exception) {
        return '—';
    }
};

$maxNetSales = 0.0;
$maxOrders = 0.0;

foreach ($salesByPeriod as $periodRow) {
    $maxNetSales = max($maxNetSales, (float)($periodRow['net_sales'] ?? 0));
    $maxOrders = max($maxOrders, (float)($periodRow['total_orders'] ?? 0));
}

$adminName = (string)($admin['name'] ?? $admin['username'] ?? 'مدیر');
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl" data-admin-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>داشبورد مدیریت | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script>
        (() => {
            try {
                const saved = localStorage.getItem('nivad-admin-theme');
                if (saved === 'light' || saved === 'dark') {
                    document.documentElement.dataset.adminTheme = saved;
                }
            } catch (_) {}
        })();
    </script>
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-dashboard-page">
    <header class="admin-dashboard-topbar">
        <a class="admin-dashboard-brand" href="<?= e(url('/admin')) ?>" aria-label="نیواد، صفحه داشبورد">
            <span class="admin-dashboard-brand-mark" aria-hidden="true">N</span>
            <span>نیواد <small>پنل مدیریت</small></span>
        </a>
        <div class="admin-dashboard-user">
            <span class="admin-dashboard-user-name"><?= e($adminName) ?></span>
            <form method="post" action="<?= e(url('/admin/logout')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <button class="admin-dashboard-logout" type="submit">خروج</button>
            </form>
        </div>
    </header>

    <div class="admin-dashboard-shell">
        <aside class="admin-dashboard-sidebar" aria-label="ناوبری مدیریت">
            <p class="admin-dashboard-nav-label">مدیریت فروشگاه</p>
            <nav>
                <a href="<?= e(url('/admin')) ?>" aria-current="page"><span aria-hidden="true">▦</span> داشبورد</a>
                <a href="<?= e(url('/admin/products')) ?>"><span aria-hidden="true">▣</span> محصولات</a>
                <a href="<?= e(url('/admin/categories')) ?>"><span aria-hidden="true">◫</span> دسته‌بندی‌ها</a>
                <a href="<?= e(url('/admin/brands')) ?>"><span aria-hidden="true">◇</span> برندها</a>
                <a href="<?= e(url('/admin/orders')) ?>"><span aria-hidden="true">▤</span> سفارش‌ها</a>
                <a href="<?= e(url('/admin/inventory')) ?>"><span aria-hidden="true">▥</span> موجودی</a>
                <a href="<?= e(url('/admin/payments')) ?>"><span aria-hidden="true">◈</span> پرداخت‌ها</a>
                <a href="<?= e(url('/admin/reports')) ?>"><span aria-hidden="true">▧</span> گزارش‌ها</a>
            </nav>
            <div class="admin-dashboard-sidebar-note">
                <span class="admin-dashboard-status-dot" aria-hidden="true"></span>
                <span>نمای کلی فروشگاه</span>
            </div>
        </aside>

        <main class="admin-dashboard-main">
            <header class="admin-dashboard-page-heading">
                <div>
                    <p class="admin-dashboard-eyebrow">مرکز کنترل فروشگاه</p>
                    <h1>داشبورد مدیریت</h1>
                    <p>خلاصه‌ای از سفارش‌ها، پرداخت‌ها، کالاها و موجودی فروشگاه.</p>
                </div>
                <div class="admin-dashboard-date-chip">
                    <span aria-hidden="true">◷</span>
                    <span>روند ۱۴ روز اخیر</span>
                </div>
            </header>

            <section class="admin-dashboard-kpis" aria-label="شاخص‌های اصلی">
                <article class="admin-dashboard-kpi admin-dashboard-kpi-orders">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-icon" aria-hidden="true">▤</span><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                    <h2>کل سفارش‌ها</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($orders['total_orders'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">مبلغ سفارش‌ها: <strong><?= e($formatNumber($orders['total_amount'] ?? 0)) ?></strong></p>
                </article>
                <article class="admin-dashboard-kpi admin-dashboard-kpi-payments">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-icon" aria-hidden="true">◈</span><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                    <h2>تعداد پرداخت‌ها</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($payments['total_payments'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">مبلغ پرداخت‌ها: <strong><?= e($formatNumber($payments['total_amount'] ?? 0)) ?></strong></p>
                </article>
                <article class="admin-dashboard-kpi admin-dashboard-kpi-items">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-icon" aria-hidden="true">▣</span><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                    <h2>تعداد کالاهای سفارش‌داده‌شده</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($orderItems['total_quantity'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">خطوط سفارش: <strong><?= e($formatNumber($orderItems['total_lines'] ?? 0)) ?></strong></p>
                </article>
                <article class="admin-dashboard-kpi admin-dashboard-kpi-inventory">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-icon" aria-hidden="true">▥</span><span class="admin-dashboard-kpi-caption">وضعیت فعلی</span></div>
                    <h2>مجموع موجودی</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($inventory['total_quantity'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">موجودی در دسترس: <strong><?= e($formatNumber($inventory['total_available_quantity'] ?? 0)) ?></strong></p>
                </article>
            </section>

            <section class="admin-dashboard-chart-grid" aria-label="روند عملکرد">
                <article class="admin-dashboard-panel admin-dashboard-sales-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">عملکرد مالی</p><h2>روند فروش خالص</h2></div>
                        <span class="admin-dashboard-panel-meta">۱۴ روز اخیر</span>
                    </div>
                    <?php if ($salesByPeriodStatus === 'error') { ?>
                        <div class="admin-dashboard-state admin-dashboard-state-error" role="status">
                            <strong>داده‌های نمودار در دسترس نیست.</strong>
                            <span>دریافت روند فروش با خطا مواجه شد؛ سایر شاخص‌ها جداگانه نمایش داده می‌شوند.</span>
                        </div>
                    <?php } elseif ($salesByPeriodStatus !== 'ready' || $salesByPeriod === []) { ?>
                        <div class="admin-dashboard-state" role="status">
                            <strong>اتصال داده‌های دوره‌ای به View هنوز تأیید نشده یا برای این بازه داده‌ای وجود ندارد.</strong>
                            <span>با ثبت سفارش‌های جدید، روند فروش در این بخش نمایش داده می‌شود.</span>
                        </div>
                    <?php } else { ?>
                        <div class="admin-dashboard-chart" role="img" aria-label="نمودار میله‌ای فروش خالص روزانه در ۱۴ روز اخیر">
                            <div class="admin-dashboard-chart-bars">
                                <?php foreach ($salesByPeriod as $row) {
                                    $value = max(0, (float)($row['net_sales'] ?? 0));
                                    $height = $maxNetSales > 0 ? max(3, ($value / $maxNetSales) * 100) : 3;
                                    ?>
                                    <div class="admin-dashboard-chart-column" title="<?= e($formatDate($row['period'] ?? null) . ': ' . $formatNumber($value)) ?>">
                                        <span class="admin-dashboard-chart-value"><?= e($formatNumber($value)) ?></span>
                                        <span class="admin-dashboard-chart-bar" style="--bar-height: <?= e((string)$height) ?>%"></span>
                                        <span class="admin-dashboard-chart-label"><?= e($formatDate($row['period'] ?? null)) ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="admin-dashboard-chart-legend"><span class="admin-dashboard-legend-dot" aria-hidden="true"></span><span>فروش خالص بر اساس مبلغ سفارش‌ها</span></div>
                </article>

                <article class="admin-dashboard-panel admin-dashboard-orders-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">حجم سفارش‌ها</p><h2>سفارش‌های روزانه</h2></div>
                        <span class="admin-dashboard-panel-meta">۱۴ روز اخیر</span>
                    </div>
                    <?php if ($salesByPeriodStatus === 'error') { ?>
                        <div class="admin-dashboard-state admin-dashboard-state-error" role="status">
                            <strong>داده‌های نمودار در دسترس نیست.</strong>
                            <span>برای جلوگیری از نمایش اطلاعات نادرست، نمودار سفارش‌ها موقتاً نمایش داده نمی‌شود.</span>
                        </div>
                    <?php } elseif ($salesByPeriodStatus !== 'ready' || $salesByPeriod === []) { ?>
                        <div class="admin-dashboard-state" role="status">
                            <strong>اتصال داده‌های دوره‌ای تأیید نشده یا سفارشی در این بازه ثبت نشده است.</strong>
                            <span>با ثبت سفارش، روند روزانه در این قسمت ظاهر می‌شود.</span>
                        </div>
                    <?php } else { ?>
                        <div class="admin-dashboard-chart" role="img" aria-label="نمودار میله‌ای تعداد سفارش‌های روزانه در ۱۴ روز اخیر">
                            <div class="admin-dashboard-chart-bars">
                                <?php foreach ($salesByPeriod as $row) {
                                    $value = max(0, (float)($row['total_orders'] ?? 0));
                                    $height = $maxOrders > 0 ? max(3, ($value / $maxOrders) * 100) : 3;
                                    ?>
                                    <div class="admin-dashboard-chart-column" title="<?= e($formatDate($row['period'] ?? null) . ': ' . $formatNumber($value)) ?>">
                                        <span class="admin-dashboard-chart-value"><?= e($formatNumber($value)) ?></span>
                                        <span class="admin-dashboard-chart-bar admin-dashboard-chart-bar-secondary" style="--bar-height: <?= e((string)$height) ?>%"></span>
                                        <span class="admin-dashboard-chart-label"><?= e($formatDate($row['period'] ?? null)) ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="admin-dashboard-chart-legend"><span class="admin-dashboard-legend-dot admin-dashboard-legend-dot-secondary" aria-hidden="true"></span><span>تعداد سفارش‌ها در هر روز</span></div>
                </article>
            </section>

            <section class="admin-dashboard-bottom-grid" aria-label="گزارش‌ها و وابستگی‌ها">
                <article class="admin-dashboard-panel admin-dashboard-period-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">جزئیات داده</p><h2>خلاصه ۱۴ روز اخیر</h2></div>
                    </div>
                    <?php if ($salesByPeriodStatus === 'error') { ?>
                        <div class="admin-dashboard-state admin-dashboard-state-error" role="status"><strong>گزارش دوره‌ای در دسترس نیست.</strong><span>بعداً دوباره تلاش کنید.</span></div>
                    <?php } elseif ($salesByPeriodStatus !== 'ready' || $salesByPeriod === []) { ?>
                        <div class="admin-dashboard-state" role="status"><strong>اتصال داده‌های دوره‌ای به View تأیید نشده یا داده‌ای برای نمایش وجود ندارد.</strong><span>در این بازه هنوز سفارشی ثبت نشده است.</span></div>
                    <?php } else { ?>
                        <div class="admin-dashboard-table-wrap" tabindex="0" aria-label="جدول خلاصه دوره‌ای، برای مشاهده افقی قابل پیمایش است">
                            <table class="admin-dashboard-table">
                                <thead><tr><th scope="col">تاریخ</th><th scope="col">سفارش</th><th scope="col">فروش ناخالص</th><th scope="col">تخفیف</th><th scope="col">فروش خالص</th></tr></thead>
                                <tbody>
                                    <?php foreach (array_reverse($salesByPeriod) as $row) { ?>
                                        <tr>
                                            <td><?= e((string)($row['period'] ?? '—')) ?></td>
                                            <td><?= e($formatNumber($row['total_orders'] ?? 0)) ?></td>
                                            <td><?= e($formatNumber($row['gross_sales'] ?? 0)) ?></td>
                                            <td><?= e($formatNumber($row['total_discount'] ?? 0)) ?></td>
                                            <td><strong><?= e($formatNumber($row['net_sales'] ?? 0)) ?></strong></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </article>

                <div class="admin-dashboard-support-stack">
                    <article class="admin-dashboard-panel">
                        <div class="admin-dashboard-panel-heading"><div><p class="admin-dashboard-eyebrow">فعالیت‌ها</p><h2>رویدادهای اخیر</h2></div></div>
                        <div class="admin-dashboard-state admin-dashboard-state-pending">
                            <strong>اتصال به قرارداد داده نیاز دارد.</strong>
                            <span>در ممیزی فعلی، قرارداد تأییدشده‌ای برای Activity Feed داشبورد پیدا نشد؛ رویداد ساختگی نمایش داده نمی‌شود.</span>
                        </div>
                    </article>
                    <article class="admin-dashboard-panel">
                        <div class="admin-dashboard-panel-heading"><div><p class="admin-dashboard-eyebrow">هشدارهای عملیاتی</p><h2>نیازمند توجه</h2></div></div>
                        <div class="admin-dashboard-state admin-dashboard-state-pending">
                            <strong>منبع هشدار هنوز تأیید نشده است.</strong>
                            <span>برای جلوگیری از هشدارهای نادرست، اتصال به موجودی کم یا سفارش‌های نیازمند اقدام تا زمان اثبات قرارداد داده معلق است.</span>
                        </div>
                    </article>
                </div>
            </section>

            <footer class="admin-dashboard-footer">
                <span>نیواد · پنل مدیریت</span>
                <span>مقادیر از قراردادهای فعلی گزارش‌گیری خوانده می‌شوند.</span>
            </footer>
        </main>
    </div>
</body>
</html>
