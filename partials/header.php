<?php

declare(strict_types=1);
require_once __DIR__ . '/../app/Core/Bootstrap.php';
Bootstrap::boot();
$baseUrl = rtrim(url(), '/');
$seoTitle = trim((string) ($pageTitle ?? 'فروشگاه دیجیتال'));
$seoDescription = trim((string) ($metaDescription ?? ''));
$seoCanonical = isset($canonicalUrl) && is_string($canonicalUrl) && trim($canonicalUrl) !== '' ? trim($canonicalUrl) : null;
$seoRobots = isset($robots) && is_string($robots) && trim($robots) !== '' ? trim($robots) : 'index,follow';
$storeUrl = url('/');
$productsUrl = url('/products');
$categoriesUrl = url('/products');
$discoverUrl = url('/discover');
$compatibilityUrl = url('/pages/store/compatibility.php');
$buyingGuideUrl = url('/pages/content/faq.php');
$supportUrl = url('/pages/content/contact.php');
$basePath = app_base_path();
$aboutUrl = url('/pages/content/about.php');
$loginUrl = url('/pages/auth/login.php');
$registerUrl = url('/pages/auth/register.php');
$cartUrl = url('/pages/shop/cart.php');
$wishlistUrl = url('/pages/shop/wishlist.php');
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($seoDescription !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= htmlspecialchars($seoRobots, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($seoCanonical !== null): ?>
        <link rel="canonical" href="<?= htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="نیواد">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($seoDescription !== ''): ?>
        <meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:image" content="<?= e(asset('images/brand/nivad-mark.png')) ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($seoDescription !== ''): ?>
        <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e('/swiper/swiper-bundle.min.css') ?>">
</head>

<body>
    <button id="backToTop" class="back-to-top" type="button" aria-label="بازگشت به بالا">
        <svg class="progress-ring" width="60" height="60" aria-hidden="true">
            <circle class="progress-ring__circle" stroke="currentColor" stroke-width="3" fill="transparent" r="27" cx="30" cy="30"></circle>
        </svg>
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>
    <header class="site-header">
        <div class="header-main">
            <div class="header-main__start">
                <button class="menu-toggle" type="button" aria-label="باز کردن منو" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle>
                    <svg class="icon icon-menu" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                    <span class="sr-only">منو</span>
                </button>
                <a
                    href="<?= e(url('/')) ?>"
                    class="brand-logo"
                    aria-label="دیجی‌استور - صفحه اصلی">
                    <span class="brand-logo__mark" aria-hidden="true">
                        <img
                            src="<?= e(asset('images/brand/nivad-mark.png')) ?>"
                            alt=""
                            width="42"
                            height="42"
                            decoding="async" />
                    </span>
                    <span class="brand-logo__text">دیجی‌استور</span>
                </a>
            </div>
            <div class="header-search-wrap">
                <form
                    class="header-search"
                    role="search"
                    action="<?= e(url('/search')) ?>"
                    method="get"
                    data-search-form>
                    <label class="sr-only" for="search-input">جستجوی محصول، برند یا مدل</label>
                    <div class="header-search__field">
                        <svg class="header-search__icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                            <path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                        <input
                            id="search-input"
                            type="search"
                            name="q"
                            placeholder="جستجوی محصول، برند یا مدل..."
                            autocomplete="off"
                            enterkeyhint="search"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            aria-controls="search-suggestions"
                            data-search-input>
                        <button class="header-search__submit" type="submit" aria-label="جستجو">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            <div class="header-actions">
                <button class="header-action wishlist-button" type="button" aria-label="علاقه‌مندی‌ها" data-wishlist-toggle>
                    <span class="header-action__icon">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                        </svg>
                        <span class="wishlist-count" aria-label="۰ علاقه‌مندی">0</span>
                    </span>
                    <span class="header-action__label">علاقه‌مندی</span>
                </button>
                <button class="header-action profile-button" type="button" aria-label="حساب کاربری" aria-expanded="false" aria-controls="profile-menu" data-profile-toggle>
                    <span class="header-action__icon">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.5" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                            <path d="M5 20c.8-3.2 3.1-5 7-5s6.2 1.8 7 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </span>
                    <span class="header-action__content">
                        <span class="header-action__label">حساب کاربری</span>
                        <span class="header-action__description">ورود / ثبت‌نام</span>
                    </span>
                </button>
                <button class="header-action cart-button" type="button" aria-label="سبد خرید" data-cart-toggle>
                    <span class="header-action__icon">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                            <circle cx="10" cy="20" r="1" fill="currentColor"></circle>
                            <circle cx="18" cy="20" r="1" fill="currentColor"></circle>
                        </svg>
                        <span class="cart-count" aria-label="۰ کالا">0</span>
                    </span>
                    <span class="header-action__label">سبد خرید</span>
                </button>
                <button class="header-action theme-button" type="button" aria-label="فعال کردن حالت تاریک" aria-pressed="false" data-theme-toggle>
                    <span class="header-action__icon">
                        <svg class="icon theme-icon theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                        <svg class="icon theme-icon theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                    <span class="header-action__label">حالت نمایش</span>
                </button>
            </div>
        </div>
        <div class="header-navigation">
            <nav class="desktop-nav" aria-label="ناوبری اصلی">
                <div class="desktop-nav__inner">
                    <a
                        href="<?= e($storeUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="store">
                        فروشگاه
                    </a>
                    <a
                        href="<?= e($productsUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="products">
                        محصولات
                    </a>
                    <a
                        href="<?= e($categoriesUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="categories">
                        دسته‌بندی‌ها
                    </a>
                    <a
                        href="<?= e($discoverUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="discover">
                        کشف هوشمند
                    </a>
                    <a
                        href="<?= e($compatibilityUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="compatibility">
                        سازگاری دستگاه
                    </a>
                    <a
                        href="<?= e($buyingGuideUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="guide">
                        راهنمای خرید
                    </a>
                    <a
                        href="<?= e($supportUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="support">
                        پشتیبانی
                    </a>
                    <a
                        href="<?= e($aboutUrl) ?>"
                        class="desktop-nav__link"
                        data-nav-link="about">
                        درباره ما
                    </a>
                </div>
            </nav>
        </div>
        <div id="profile-menu" class="profile-menu" data-profile-menu hidden>
            <div class="profile-menu__header">
                <span class="profile-menu__eyebrow">حساب کاربری</span>
                <strong>برای ادامه وارد حساب خود شوید</strong>
            </div>
            <div class="profile-menu__actions">
                <a href="<?= e($loginUrl) ?>" class="profile-menu__primary">ورود</a>
                <a href="<?= e($registerUrl) ?>" class="profile-menu__secondary">ثبت‌نام</a>
            </div>
        </div>
        <nav id="mobile-menu" class="mobile-menu" aria-label="منوی اصلی" aria-hidden="true" data-mobile-menu>
            <div class="mobile-menu__header">
                <div class="mobile-menu__account">
                    <div class="mobile-menu__account-icon">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.5" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                            <path d="M5 20c.8-3.2 3.1-5 7-5s6.2 1.8 7 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div class="mobile-menu__account-content">
                        <span>حساب کاربری</span>
                        <strong>ورود / ثبت‌نام</strong>
                    </div>
                </div>
                <div class="mobile-menu__account-actions">
                    <a href="<?= e($loginUrl) ?>" class="mobile-menu__login">ورود</a>
                    <a href="<?= e($registerUrl) ?>" class="mobile-menu__register">ثبت‌نام</a>
                </div>
            </div>
            <ul class="mobile-menu__links">
                <li>
                    <a
                        href="<?= e($storeUrl) ?>"
                        data-nav-link="store">
                        فروشگاه
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($productsUrl) ?>"
                        data-nav-link="products">
                        محصولات
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($categoriesUrl) ?>"
                        data-nav-link="categories">
                        دسته‌بندی‌ها
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($discoverUrl) ?>"
                        data-nav-link="discover">
                        کشف هوشمند
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($compatibilityUrl) ?>"
                        data-nav-link="compatibility">
                        سازگاری دستگاه
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($buyingGuideUrl) ?>"
                        data-nav-link="guide">
                        راهنمای خرید
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($supportUrl) ?>"
                        data-nav-link="support">
                        پشتیبانی
                    </a>
                </li>
                <li>
                    <a
                        href="<?= e($aboutUrl) ?>"
                        data-nav-link="about">
                        درباره ما
                    </a>
                </li>
            </ul>
            <div class="mobile-menu__footer">
                <a href="<?= e($wishlistUrl) ?>" class="mobile-menu__utility" aria-label="مشاهده علاقه‌مندی‌ها">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                    </svg>
                    <span>علاقه‌مندی‌ها</span>
                </a>
                <a href="<?= e($cartUrl) ?>" class="mobile-menu__utility" aria-label="مشاهده سبد خرید">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        <circle cx="10" cy="20" r="1" fill="currentColor"></circle>
                        <circle cx="18" cy="20" r="1" fill="currentColor"></circle>
                    </svg>
                    <span>سبد خرید</span>
                </a>
                <button type="button" class="mobile-theme-toggle" aria-label="تغییر حالت نمایش" aria-pressed="false" data-theme-toggle>
                    <span class="mobile-theme-toggle__icon">
                        <svg class="icon theme-icon theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                        <svg class="icon theme-icon theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                    <span data-theme-label>حالت تاریک</span>
                </button>
            </div>
        </nav>
    </header>