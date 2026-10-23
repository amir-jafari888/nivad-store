# Nivad — Phase 39 Responsive Master Audit

## Scope and rules

- Source branch: `phase-38-admin-data-experience`; implementation branch: `phase-39-responsive-master-audit`.
- Responsive contract: Mobile ≤600px; Tablet 601–1009px; Desktop ≥1010px.
- Storefront tokens: `assets/css/setting.css`; Admin tokens: `admin/assets/css/setting.css`.
- Backend Freeze is preserved. No API, route, database, service, repository, or backend permission contract is changed in this phase.
- The audit test is read-only and does not connect to the database or mutate data.
- Static checks cannot establish visual correctness, browser interactions, actual PHP rendering, or API-backed behavior. These remain blocked until runtime/browser execution is performed.

## Changes made

1. Corrected the three broken CSS import paths in each of the Mobile, Tablet, and Desktop manifests for Budget Optimizer, Price/Stock Watch, and Recent Device (9 import paths total).
2. Added `test-phase-39-responsive-master-audit.php` to check responsive manifests/imports, breakpoint contracts, theme token presence, responsive CSS family coverage, Admin token namespace, PHP page inventory, and Backend Freeze constraints.
3. No backend files changed. No PR merge, PASS, or LOCK is implied by these changes.

## Batch execution register

### Batch 01 — Responsive Baseline & CSS Integrity

| Step | Contract | Status |
|---|---|---|
| 39.01 | فهرست واقعی صفحات، Routeها و Partialها | PARTIAL — source inventory/static test added; page-by-page runtime audit pending |
| 39.02 | تطبیق صفحات با routes/web.php | PENDING — runtime/browser evidence required |
| 39.03 | بررسی اتصال CSS در Storefront | PARTIAL — source inventory/static test added; page-by-page runtime audit pending |
| 39.04 | بررسی اتصال CSS در Admin | PARTIAL — source inventory/static test added; page-by-page runtime audit pending |
| 39.05 | بررسی Importهای Responsive | IMPLEMENTED — static verification pending execution |
| 39.06 | اصلاح ۹ مسیر Import اشتباه و تأیید مسیر مقصد | IMPLEMENTED — static verification pending execution |
| 39.07 | تطبیق Breakpointها با توکن‌ها | IMPLEMENTED — static verification pending execution |
| 39.08 | بررسی خانواده‌های CSS در سه Viewport | IMPLEMENTED — static verification pending execution |
| 39.09 | بررسی وابستگی‌ها و Selectorهای CSS | PENDING — runtime/browser evidence required |
| 39.10 | ثبت Baseline و ماتریس پوشش | PARTIAL — source inventory/static test added; page-by-page runtime audit pending |

### Batch 02 — Landing Page & Global Storefront Shell

| Step | Contract | Status |
|---|---|---|
| 39.11 | Header در سه اندازه | PENDING — runtime/browser evidence required |
| 39.12 | Navigation و منوها | PENDING — runtime/browser evidence required |
| 39.13 | Search و Search Suggestions | PENDING — runtime/browser evidence required |
| 39.14 | Hero و ساختار صفحه اصلی | PENDING — runtime/browser evidence required |
| 39.15 | Featured Categories | PENDING — runtime/browser evidence required |
| 39.16 | Discovery و Collections | PENDING — runtime/browser evidence required |
| 39.17 | Product Sections و Cards | PENDING — runtime/browser evidence required |
| 39.18 | Smart Setup و Compatibility | PENDING — runtime/browser evidence required |
| 39.19 | Buying Guide، Value Proposition و CTA | PENDING — runtime/browser evidence required |
| 39.20 | Footer و اتصال اجزا | PENDING — runtime/browser evidence required |

### Batch 03 — Catalog, Discovery & Search

| Step | Contract | Status |
|---|---|---|
| 39.21 | Product Listing | PENDING — runtime/browser evidence required |
| 39.22 | Category Page | PENDING — runtime/browser evidence required |
| 39.23 | Brand Page | PENDING — runtime/browser evidence required |
| 39.24 | Collection Page | PENDING — runtime/browser evidence required |
| 39.25 | Discovery Page | PENDING — runtime/browser evidence required |
| 39.26 | Search Results | PENDING — runtime/browser evidence required |
| 39.27 | Recent Search و Suggestions در صورت وجود | PENDING — runtime/browser evidence required |
| 39.28 | Filters و Sort | PENDING — runtime/browser evidence required |
| 39.29 | Pagination و Listing Controls | PENDING — runtime/browser evidence required |
| 39.30 | Empty، Loading، Error و No Results | PENDING — runtime/browser evidence required |

### Batch 04 — Product Detail, Media & Trust

| Step | Contract | Status |
|---|---|---|
| 39.31 | Product Detail Layout | PENDING — runtime/browser evidence required |
| 39.32 | Gallery و Thumbnails | PENDING — runtime/browser evidence required |
| 39.33 | Product Image و Placeholder | PENDING — runtime/browser evidence required |
| 39.34 | Price، Availability و مشخصات | PENDING — runtime/browser evidence required |
| 39.35 | Variants | PENDING — runtime/browser evidence required |
| 39.36 | Compatibility UI | PENDING — runtime/browser evidence required |
| 39.37 | Reviews و Rating | PENDING — runtime/browser evidence required |
| 39.38 | Questions و Answers | PENDING — runtime/browser evidence required |
| 39.39 | Related Products و Recommendations | PENDING — runtime/browser evidence required |
| 39.40 | Add to Cart، Wishlist و Product Actions | PENDING — runtime/browser evidence required |

### Batch 05 — Smart Commerce Experiences

| Step | Contract | Status |
|---|---|---|
| 39.41 | Smart Product Finder | PENDING — runtime/browser evidence required |
| 39.42 | Compatibility Experience | PENDING — runtime/browser evidence required |
| 39.43 | Smart Setup Builder | PENDING — runtime/browser evidence required |
| 39.44 | Complete Your Setup | PENDING — runtime/browser evidence required |
| 39.45 | Budget Optimizer | PENDING — runtime/browser evidence required |
| 39.46 | Comparison | PENDING — runtime/browser evidence required |
| 39.47 | Alternatives | PENDING — runtime/browser evidence required |
| 39.48 | Smart Bundle و Discount | PENDING — runtime/browser evidence required |
| 39.49 | Price Drop و Back-in-Stock | PENDING — runtime/browser evidence required |
| 39.50 | Recent Device و Cross-Page Context | PENDING — runtime/browser evidence required |

### Batch 06 — Cart, Checkout, Payment & Authentication

| Step | Contract | Status |
|---|---|---|
| 39.51 | Cart Drawer | PENDING — runtime/browser evidence required |
| 39.52 | Cart Page | PENDING — runtime/browser evidence required |
| 39.53 | Quantity و Remove Controls | PENDING — runtime/browser evidence required |
| 39.54 | Cart Totals و Price States | PENDING — runtime/browser evidence required |
| 39.55 | Checkout Layout | PENDING — runtime/browser evidence required |
| 39.56 | Address، Shipping و Order Review | PENDING — runtime/browser evidence required |
| 39.57 | Payment Success | PENDING — runtime/browser evidence required |
| 39.58 | Payment Failure و Recovery | PENDING — runtime/browser evidence required |
| 39.59 | Login، Register و Forgot Password | PENDING — runtime/browser evidence required |
| 39.60 | Reset Password، Verify Email و Session States | PENDING — runtime/browser evidence required |

### Batch 07 — Account, Content & Customer Experience

| Step | Contract | Status |
|---|---|---|
| 39.61 | Account Dashboard | PENDING — runtime/browser evidence required |
| 39.62 | Profile و Account Forms | PENDING — runtime/browser evidence required |
| 39.63 | Address Management | PENDING — runtime/browser evidence required |
| 39.64 | Orders List | PENDING — runtime/browser evidence required |
| 39.65 | Order Detail | PENDING — runtime/browser evidence required |
| 39.66 | Notifications | PENDING — runtime/browser evidence required |
| 39.67 | Account Reviews | PENDING — runtime/browser evidence required |
| 39.68 | Returns و Return Detail | PENDING — runtime/browser evidence required |
| 39.69 | About، Contact و FAQ | PENDING — runtime/browser evidence required |
| 39.70 | Privacy، Terms، Shipping، Returns و Buying Guide | PENDING — runtime/browser evidence required |

### Batch 08 — Admin Shell & Catalog Management

| Step | Contract | Status |
|---|---|---|
| 39.71 | Admin Shell، Navigation و Dashboard | PENDING — runtime/browser evidence required |
| 39.72 | Admin Authentication | PENDING — runtime/browser evidence required |
| 39.73 | Products List/Create/Edit | PENDING — runtime/browser evidence required |
| 39.74 | Categories و Brands | PENDING — runtime/browser evidence required |
| 39.75 | Collections و Discovery | PENDING — runtime/browser evidence required |
| 39.76 | Attributes | PENDING — runtime/browser evidence required |
| 39.77 | Product Types | PENDING — runtime/browser evidence required |
| 39.78 | Specifications و Groups | PENDING — runtime/browser evidence required |
| 39.79 | Coupons | PENDING — runtime/browser evidence required |
| 39.80 | Sliders و فرم‌های مدیریتی | PENDING — runtime/browser evidence required |

### Batch 09 — Admin Operations, Tables & Forms

| Step | Contract | Status |
|---|---|---|
| 39.81 | Orders و Detail | PENDING — runtime/browser evidence required |
| 39.82 | Payments و Attempts | PENDING — runtime/browser evidence required |
| 39.83 | Inventory، Movements و Reservations | PENDING — runtime/browser evidence required |
| 39.84 | Shipping Methods، Zones و Shipments | PENDING — runtime/browser evidence required |
| 39.85 | Returns و Refunds | PENDING — runtime/browser evidence required |
| 39.86 | Reviews و Questions | PENDING — runtime/browser evidence required |
| 39.87 | Users و Detail | PENDING — runtime/browser evidence required |
| 39.88 | Notifications و Audit Logs | PENDING — runtime/browser evidence required |
| 39.89 | Compatibility، Devices و Setups | PENDING — runtime/browser evidence required |
| 39.90 | Reports و جداول/فرم‌های باقی‌مانده | PENDING — runtime/browser evidence required |

### Batch 10 — Cross-Page Responsive, Theme & RTL Integrity

| Step | Contract | Status |
|---|---|---|
| 39.91 | اتصال CSS همه صفحات | PARTIAL — source inventory/static test added; page-by-page runtime audit pending |
| 39.92 | Design Tokens | PENDING — runtime/browser evidence required |
| 39.93 | Light Theme | PENDING — runtime/browser evidence required |
| 39.94 | Dark Theme | PENDING — runtime/browser evidence required |
| 39.95 | RTL در Layout، Grid و Navigation | PENDING — runtime/browser evidence required |
| 39.96 | RTL در Forms، Tables، Drawers و Modals | PENDING — runtime/browser evidence required |
| 39.97 | متن فارسی/انگلیسی، مدل‌ها و اعداد | PENDING — runtime/browser evidence required |
| 39.98 | Images، Media و Broken Image | PENDING — runtime/browser evidence required |
| 39.99 | Overflow، Sticky و Scroll | PENDING — runtime/browser evidence required |
| 39.100 | جدایی CSS فروشگاه و Admin | PENDING — runtime/browser evidence required |

### Batch 11 — Full Responsive Regression & Evidence

| Step | Contract | Status |
|---|---|---|
| 39.101 | Regression صفحه اصلی و Shared Shell | PENDING — runtime/browser evidence required |
| 39.102 | Regression Catalog، Discovery و Search | PENDING — runtime/browser evidence required |
| 39.103 | Regression Product Detail و Media | PENDING — runtime/browser evidence required |
| 39.104 | Regression Smart Commerce | PENDING — runtime/browser evidence required |
| 39.105 | Regression Cart، Checkout و Payment | PENDING — runtime/browser evidence required |
| 39.106 | Regression Auth، Account و Content | PENDING — runtime/browser evidence required |
| 39.107 | Regression Admin Shell و Catalog | PENDING — runtime/browser evidence required |
| 39.108 | Regression Admin Operations و Data Experience | PENDING — runtime/browser evidence required |
| 39.109 | کنترل نهایی Overflow، Breakpoint، Theme و Imports | PENDING — runtime/browser evidence required |
| 39.110 | گزارش نهایی و تعیین PASS/BLOCKED | PENDING — runtime/browser evidence required |

## Required execution before PASS

1. Run `php -l test-phase-39-responsive-master-audit.php`.
2. Run `php test-phase-39-responsive-master-audit.php` from the repository root and fix every confirmed FAIL.
3. Run PHP syntax checks for affected pages and verify CSS requests in a browser.
4. Exercise in-scope pages at widths 600px, 601px, 1009px, and 1010px, plus practical viewport sizes inside each range.
5. Check Light/Dark, RTL, overflow, keyboard/focus, forms, tables, drawers, media, loading/error/empty states and existing JavaScript behavior.
6. Record browser/runtime evidence per step; only then decide PASS/LOCK. Until then, Phase 39 remains IN PROGRESS and LOCK=NO.
