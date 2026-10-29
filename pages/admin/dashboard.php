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

$dashboardChartPeriods = [
    ['label' => 'روز ۱', 'net_sales' => 18, 'total_orders' => 7],
    ['label' => 'روز ۲', 'net_sales' => 24, 'total_orders' => 9],
    ['label' => 'روز ۳', 'net_sales' => 21, 'total_orders' => 6],
    ['label' => 'روز ۴', 'net_sales' => 29, 'total_orders' => 11],
    ['label' => 'روز ۵', 'net_sales' => 26, 'total_orders' => 8],
    ['label' => 'روز ۶', 'net_sales' => 34, 'total_orders' => 13],
    ['label' => 'روز ۷', 'net_sales' => 31, 'total_orders' => 10],
    ['label' => 'روز ۸', 'net_sales' => 39, 'total_orders' => 15],
    ['label' => 'روز ۹', 'net_sales' => 35, 'total_orders' => 12],
    ['label' => 'روز ۱۰', 'net_sales' => 43, 'total_orders' => 17],
    ['label' => 'روز ۱۱', 'net_sales' => 40, 'total_orders' => 14],
    ['label' => 'روز ۱۲', 'net_sales' => 48, 'total_orders' => 19],
    ['label' => 'روز ۱۳', 'net_sales' => 45, 'total_orders' => 16],
    ['label' => 'روز ۱۴', 'net_sales' => 54, 'total_orders' => 21],
];
$maxDemoNetSales = max(array_column($dashboardChartPeriods, 'net_sales'));
$maxDemoOrders = max(array_column($dashboardChartPeriods, 'total_orders'));

$firstName = trim((string)($admin['first_name'] ?? ''));
$lastName = trim((string)($admin['last_name'] ?? ''));
$adminName = trim($firstName . ' ' . $lastName);
if ($adminName === '') {
    $adminName = 'مدیر سیستم';
}
$adminInitial = mb_substr($firstName !== '' ? $firstName : $adminName, 0, 1, 'UTF-8')
    . ($lastName !== '' ? mb_substr($lastName, 0, 1, 'UTF-8') : '');
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>داشبورد مدیریت | نیواد</title>
    <link rel="stylesheet" href="<?= e(url('/admin/assets/css/main.css')) ?>">
    <script type="module" src="<?= e(url('/admin/assets/js/main.js')) ?>"></script>
</head>
<body class="admin-dashboard-page">
    <a class="admin-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <header class="admin-dashboard-topbar">
        <button class="admin-dashboard-mobile-menu" type="button" data-admin-menu-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="باز کردن منوی مدیریت"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <a class="admin-dashboard-brand" href="<?= e(url('/admin')) ?>" aria-label="نیواد، صفحه داشبورد">
            <span class="admin-dashboard-brand-mark" aria-hidden="true">N</span>
            <span>نیواد <small>پنل مدیریت</small></span>
        </a>
        <div class="admin-dashboard-user" aria-label="حساب مدیر واردشده: <?= e($adminName) ?>">
            <span class="admin-dashboard-avatar" aria-hidden="true"><?= e($adminInitial) ?></span>
            <span class="admin-dashboard-user-name"><span><?= e($firstName !== '' ? $firstName : 'مدیر') ?></span><span><?= e($lastName) ?></span></span>
        </div>
    </header>

    <div class="admin-dashboard-shell">
        <aside class="admin-dashboard-sidebar" id="admin-sidebar" data-admin-sidebar aria-label="ناوبری مدیریت">
            <p class="admin-dashboard-nav-label">مدیریت فروشگاه</p>
            <nav class="admin-dashboard-nav">
                <a class="admin-dashboard-nav-link" href="<?= e(url('/admin')) ?>" aria-current="page"><span aria-hidden="true">⌂</span> داشبورد</a>
                <details class="admin-dashboard-nav-group" open>
                    <summary>کاتالوگ محصولات</summary>
                    <div class="admin-dashboard-nav-submenu">
                        <a href="<?= e(url('/admin/products')) ?>">محصولات</a>
                        <a href="<?= e(url('/admin/categories')) ?>">دسته‌بندی‌ها</a>
                        <a href="<?= e(url('/admin/brands')) ?>">برندها</a>
                        <a href="<?= e(url('/admin/collections')) ?>">مجموعه‌ها</a>
                        <a href="<?= e(url('/admin/attributes')) ?>">ویژگی‌ها</a>
                        <a href="<?= e(url('/admin/product-types')) ?>">نوع محصول</a>
                        <a href="<?= e(url('/admin/specifications')) ?>">مشخصات فنی</a>
                    </div>
                </details>
                <details class="admin-dashboard-nav-group">
                    <summary>ویترین و دیسکاوری</summary>
                    <div class="admin-dashboard-nav-submenu">
                        <a href="<?= e(url('/admin/sliders')) ?>">اسلایدرها</a>
                        <a href="<?= e(url('/admin/discovery')) ?>">دیسکاوری</a>
                        <a href="<?= e(url('/admin/compatibility')) ?>">سازگاری</a>
                        <a href="<?= e(url('/admin/setups')) ?>">ستاپ‌ها</a>
                    </div>
                </details>
                <details class="admin-dashboard-nav-group">
                    <summary>فروش و انبار</summary>
                    <div class="admin-dashboard-nav-submenu">
                        <a href="<?= e(url('/admin/orders')) ?>">سفارش‌ها</a>
                        <a href="<?= e(url('/admin/payments')) ?>">پرداخت‌ها</a>
                        <a href="<?= e(url('/admin/coupons')) ?>">کدهای تخفیف</a>
                        <a href="<?= e(url('/admin/inventory')) ?>">موجودی و گردش انبار</a>
                        <a href="<?= e(url('/admin/shipping/shipments')) ?>">ارسال و روش‌های ارسال</a>
                    </div>
                </details>
                <details class="admin-dashboard-nav-group">
                    <summary>مشتریان و پشتیبانی</summary>
                    <div class="admin-dashboard-nav-submenu">
                        <a href="<?= e(url('/admin/users')) ?>">کاربران</a>
                        <a href="<?= e(url('/admin/questions')) ?>">پرسش‌ها</a>
                        <a href="<?= e(url('/admin/reviews')) ?>">دیدگاه‌ها</a>
                        <a href="<?= e(url('/admin/returns')) ?>">مرجوعی‌ها</a>
                        <a href="<?= e(url('/admin/refunds')) ?>">بازپرداخت‌ها</a>
                        <a href="<?= e(url('/admin/notifications')) ?>">اعلان‌ها</a>
                    </div>
                </details>
                <details class="admin-dashboard-nav-group">
                    <summary>گزارش و ممیزی</summary>
                    <div class="admin-dashboard-nav-submenu">
                        <a href="<?= e(url('/admin/reports')) ?>">گزارش‌های فروش</a>
                        <a href="<?= e(url('/admin/audit-logs')) ?>">گزارش رویدادها</a>
                    </div>
                </details>
                <a class="admin-dashboard-nav-link" href="<?= e(url('/admin/settings')) ?>">تنظیمات رابط کاربری</a>
            </nav>
            <div class="admin-dashboard-account">
                <span class="admin-dashboard-avatar" aria-hidden="true"><?= e($adminInitial) ?></span>
                <span class="admin-dashboard-account-name"><?= e($adminName) ?></span>
                <form method="post" action="<?= e(url('/admin/logout')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <button class="admin-dashboard-logout" type="submit">خروج از پنل</button>
                </form>
            </div>
        </aside>

        <main id="main-content" tabindex="-1" class="admin-dashboard-main">
            <header class="admin-dashboard-page-heading">
                <div>
                    <p class="admin-dashboard-eyebrow">مرکز کنترل فروشگاه</p>
                    <h1>داشبورد مدیریت</h1>
                    <p>خلاصه‌ای از سفارش‌ها، پرداخت‌ها، کالاها و موجودی فروشگاه.</p>
                </div>
                <div class="admin-dashboard-date-chip">
                    <span aria-hidden="true">◷</span>
                    <span>نمونه نمودار · ۱۴ روز</span>
                </div>
            </header>

            <section class="admin-dashboard-kpis" aria-label="شاخص‌های اصلی">
                <article class="admin-dashboard-kpi admin-dashboard-kpi-orders">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                    <h2>کل سفارش‌ها</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($orders['total_orders'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">مبلغ سفارش‌ها: <strong><?= e($formatNumber($orders['total_amount'] ?? 0)) ?></strong></p>
                </article>
                <article class="admin-dashboard-kpi admin-dashboard-kpi-payments">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                    <h2>تعداد پرداخت‌ها</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($payments['total_payments'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">مبلغ پرداخت‌ها: <strong><?= e($formatNumber($payments['total_amount'] ?? 0)) ?></strong></p>
                </article>
                <article class="admin-dashboard-kpi admin-dashboard-kpi-items">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">تمام بازه‌ها</span></div>
                    <h2>تعداد کالاهای سفارش‌داده‌شده</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($orderItems['total_quantity'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">خطوط سفارش: <strong><?= e($formatNumber($orderItems['total_lines'] ?? 0)) ?></strong></p>
                </article>
                <article class="admin-dashboard-kpi admin-dashboard-kpi-inventory">
                    <div class="admin-dashboard-kpi-top"><span class="admin-dashboard-kpi-caption">وضعیت فعلی</span></div>
                    <h2>مجموع موجودی</h2>
                    <p class="admin-dashboard-kpi-value"><?= e($formatNumber($inventory['total_quantity'] ?? 0)) ?></p>
                    <p class="admin-dashboard-kpi-foot">موجودی در دسترس: <strong><?= e($formatNumber($inventory['total_available_quantity'] ?? 0)) ?></strong></p>
                </article>
            </section>

            <section class="admin-dashboard-chart-grid" aria-label="روند عملکرد فروشگاه">
                <article class="admin-dashboard-panel admin-dashboard-sales-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">عملکرد مالی</p><h2>روند فروش خالص</h2></div>
                        <span class="admin-dashboard-panel-meta">داده نمایشی · آماده اتصال</span>
                    </div>
                    <div class="admin-dashboard-chart" role="img" aria-label="نمودار نمونه فروش خالص در ۱۴ روز نمایشی">
                        <div class="admin-dashboard-chart-bars">
                            <?php foreach ($dashboardChartPeriods as $row) {
                                $value = (float)$row['net_sales'];
                                $height = $maxDemoNetSales > 0 ? max(3, ($value / $maxDemoNetSales) * 100) : 3;
                                ?>
                                <div class="admin-dashboard-chart-column" title="<?= e($row['label'] . ': ' . $formatNumber($value) . ' واحد نمایشی') ?>">
                                    <span class="admin-dashboard-chart-value"><?= e($formatNumber($value)) ?></span>
                                    <span class="admin-dashboard-chart-bar" style="--bar-height: <?= e((string)$height) ?>%"></span>
                                    <span class="admin-dashboard-chart-label"><?= e(str_replace('روز ', '', $row['label'])) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="admin-dashboard-chart-legend"><span class="admin-dashboard-legend-dot" aria-hidden="true"></span><span>مبالغ نمونه هستند؛ واحد و منبع واقعی پس از اتصال داده تعیین می‌شود.</span></div>
                </article>

                <article class="admin-dashboard-panel admin-dashboard-orders-panel">
                    <div class="admin-dashboard-panel-heading">
                        <div><p class="admin-dashboard-eyebrow">حجم سفارش‌ها</p><h2>سفارش‌های روزانه</h2></div>
                        <span class="admin-dashboard-panel-meta">داده نمایشی · آماده اتصال</span>
                    </div>
                    <div class="admin-dashboard-chart" role="img" aria-label="نمودار نمونه تعداد سفارش‌ها در ۱۴ روز نمایشی">
                        <div class="admin-dashboard-chart-bars">
                            <?php foreach ($dashboardChartPeriods as $row) {
                                $value = (float)$row['total_orders'];
                                $height = $maxDemoOrders > 0 ? max(3, ($value / $maxDemoOrders) * 100) : 3;
                                ?>
                                <div class="admin-dashboard-chart-column" title="<?= e($row['label'] . ': ' . $formatNumber($value) . ' سفارش نمایشی') ?>">
                                    <span class="admin-dashboard-chart-value"><?= e($formatNumber($value)) ?></span>
                                    <span class="admin-dashboard-chart-bar admin-dashboard-chart-bar-secondary" style="--bar-height: <?= e((string)$height) ?>%"></span>
                                    <span class="admin-dashboard-chart-label"><?= e(str_replace('روز ', '', $row['label'])) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="admin-dashboard-chart-legend"><span class="admin-dashboard-legend-dot admin-dashboard-legend-dot-secondary" aria-hidden="true"></span><span>تعداد سفارش‌ها در داده نمایشی؛ آماده جایگزینی با سری زمانی واقعی.</span></div>
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
