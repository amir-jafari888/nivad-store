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
| 39.01  | هرست واقعی صفحات، Routeها و Partialها | PENDING — runtime/browser evidence required |
| 39.02  | طبیق صفحات با routes/web.php | PENDING — runtime/browser evidence required |
| 39.03  | ررسی اتصال CSS در Storefront | PENDING — runtime/browser evidence required |
| 39.04  | ررسی اتصال CSS در Admin | PENDING — runtime/browser evidence required |
| 39.05  | ررسی Importهای Responsive | PENDING — runtime/browser evidence required |
| 39.06  | صلاح ۹ مسیر Import اشتباه و تأیید مسیر مقصد | PENDING — runtime/browser evidence required |
| 39.07  | طبیق Breakpointها با توکن‌ها | PENDING — runtime/browser evidence required |
| 39.08  | ررسی خانواده‌های CSS در سه Viewport | PENDING — runtime/browser evidence required |
| 39.09  | ررسی وابستگی‌ها و Selectorهای CSS | PENDING — runtime/browser evidence required |
| 39.10  | بت Baseline و ماتریس پوشش | PENDING — runtime/browser evidence required |

### Batch 02 — Landing Page & Global Storefront Shell

| Step | Contract | Status |
|---|---|---|
| 39.11  | eader در سه اندازه | PENDING — runtime/browser evidence required |
| 39.12  | avigation و منوها | PENDING — runtime/browser evidence required |
| 39.13  | earch و Search Suggestions | PENDING — runtime/browser evidence required |
| 39.14  | ero و ساختار صفحه اصلی | PENDING — runtime/browser evidence required |
| 39.15  | eatured Categories | PENDING — runtime/browser evidence required |
| 39.16  | iscovery و Collections | PENDING — runtime/browser evidence required |
| 39.17  | roduct Sections و Cards | PENDING — runtime/browser evidence required |
| 39.18  | mart Setup و Compatibility | PENDING — runtime/browser evidence required |
| 39.19  | uying Guide، Value Proposition و CTA | PENDING — runtime/browser evidence required |
| 39.20  | ooter و اتصال اجزا | PENDING — runtime/browser evidence required |

### Batch 03 — Catalog, Discovery & Search

| Step | Contract | Status |
|---|---|---|
| 39.21  | roduct Listing | PENDING — runtime/browser evidence required |
| 39.22  | ategory Page | PENDING — runtime/browser evidence required |
| 39.23  | rand Page | PENDING — runtime/browser evidence required |
| 39.24  | ollection Page | PENDING — runtime/browser evidence required |
| 39.25  | iscovery Page | PENDING — runtime/browser evidence required |
| 39.26  | earch Results | PENDING — runtime/browser evidence required |
| 39.27  | ecent Search و Suggestions در صورت وجود | PENDING — runtime/browser evidence required |
| 39.28  | ilters و Sort | PENDING — runtime/browser evidence required |
| 39.29  | agination و Listing Controls | PENDING — runtime/browser evidence required |
| 39.30  | mpty، Loading، Error و No Results | PENDING — runtime/browser evidence required |

### Batch 04 — Product Detail, Media & Trust

| Step | Contract | Status |
|---|---|---|
| 39.31  | roduct Detail Layout | PENDING — runtime/browser evidence required |
| 39.32  | allery و Thumbnails | PENDING — runtime/browser evidence required |
| 39.33  | roduct Image و Placeholder | PENDING — runtime/browser evidence required |
| 39.34  | rice، Availability و مشخصات | PENDING — runtime/browser evidence required |
| 39.35  | ariants | PENDING — runtime/browser evidence required |
| 39.36  | ompatibility UI | PENDING — runtime/browser evidence required |
| 39.37  | eviews و Rating | PENDING — runtime/browser evidence required |
| 39.38  | uestions و Answers | PENDING — runtime/browser evidence required |
| 39.39  | elated Products و Recommendations | PENDING — runtime/browser evidence required |
| 39.40  | dd to Cart، Wishlist و Product Actions | PENDING — runtime/browser evidence required |

### Batch 05 — Smart Commerce Experiences

| Step | Contract | Status |
|---|---|---|
| 39.41  | mart Product Finder | PENDING — runtime/browser evidence required |
| 39.42  | ompatibility Experience | PENDING — runtime/browser evidence required |
| 39.43  | mart Setup Builder | PENDING — runtime/browser evidence required |
| 39.44  | omplete Your Setup | PENDING — runtime/browser evidence required |
| 39.45  | udget Optimizer | PENDING — runtime/browser evidence required |
| 39.46  | omparison | PENDING — runtime/browser evidence required |
| 39.47  | lternatives | PENDING — runtime/browser evidence required |
| 39.48  | mart Bundle و Discount | PENDING — runtime/browser evidence required |
| 39.49  | rice Drop و Back-in-Stock | PENDING — runtime/browser evidence required |
| 39.50  | ecent Device و Cross-Page Context | PENDING — runtime/browser evidence required |

### Batch 06 — Cart, Checkout, Payment & Authentication

| Step | Contract | Status |
|---|---|---|
| 39.51  | art Drawer | PENDING — runtime/browser evidence required |
| 39.52  | art Page | PENDING — runtime/browser evidence required |
| 39.53  | uantity و Remove Controls | PENDING — runtime/browser evidence required |
| 39.54  | art Totals و Price States | PENDING — runtime/browser evidence required |
| 39.55  | heckout Layout | PENDING — runtime/browser evidence required |
| 39.56  | ddress، Shipping و Order Review | PENDING — runtime/browser evidence required |
| 39.57  | ayment Success | PENDING — runtime/browser evidence required |
| 39.58  | ayment Failure و Recovery | PENDING — runtime/browser evidence required |
| 39.59  | ogin، Register و Forgot Password | PENDING — runtime/browser evidence required |
| 39.60  | eset Password، Verify Email و Session States | PENDING — runtime/browser evidence required |

### Batch 07 — Account, Content & Customer Experience

| Step | Contract | Status |
|---|---|---|
| 39.61  | ccount Dashboard | PENDING — runtime/browser evidence required |
| 39.62  | rofile و Account Forms | PENDING — runtime/browser evidence required |
| 39.63  | ddress Management | PENDING — runtime/browser evidence required |
| 39.64  | rders List | PENDING — runtime/browser evidence required |
| 39.65  | rder Detail | PENDING — runtime/browser evidence required |
| 39.66  | otifications | PENDING — runtime/browser evidence required |
| 39.67  | ccount Reviews | PENDING — runtime/browser evidence required |
| 39.68  | eturns و Return Detail | PENDING — runtime/browser evidence required |
| 39.69  | bout، Contact و FAQ | PENDING — runtime/browser evidence required |
| 39.70  | rivacy، Terms، Shipping، Returns و Buying Guide | PENDING — runtime/browser evidence required |

### Batch 08 — Admin Shell & Catalog Management

| Step | Contract | Status |
|---|---|---|
| 39.71  | dmin Shell، Navigation و Dashboard | PENDING — runtime/browser evidence required |
| 39.72  | dmin Authentication | PENDING — runtime/browser evidence required |
| 39.73  | roducts List/Create/Edit | PENDING — runtime/browser evidence required |
| 39.74  | ategories و Brands | PENDING — runtime/browser evidence required |
| 39.75  | ollections و Discovery | PENDING — runtime/browser evidence required |
| 39.76  | ttributes | PENDING — runtime/browser evidence required |
| 39.77  | roduct Types | PENDING — runtime/browser evidence required |
| 39.78  | pecifications و Groups | PENDING — runtime/browser evidence required |
| 39.79  | oupons | PENDING — runtime/browser evidence required |
| 39.80  | liders و فرم‌های مدیریتی | PENDING — runtime/browser evidence required |

### Batch 09 — Admin Operations, Tables & Forms

| Step | Contract | Status |
|---|---|---|
| 39.81  | rders و Detail | PENDING — runtime/browser evidence required |
| 39.82  | ayments و Attempts | PENDING — runtime/browser evidence required |
| 39.83  | nventory، Movements و Reservations | PENDING — runtime/browser evidence required |
| 39.84  | hipping Methods، Zones و Shipments | PENDING — runtime/browser evidence required |
| 39.85  | eturns و Refunds | PENDING — runtime/browser evidence required |
| 39.86  | eviews و Questions | PENDING — runtime/browser evidence required |
| 39.87  | sers و Detail | PENDING — runtime/browser evidence required |
| 39.88  | otifications و Audit Logs | PENDING — runtime/browser evidence required |
| 39.89  | ompatibility، Devices و Setups | PENDING — runtime/browser evidence required |
| 39.90  | eports و جداول/فرم‌های باقی‌مانده | PENDING — runtime/browser evidence required |

### Batch 10 — Cross-Page Responsive, Theme & RTL Integrity

| Step | Contract | Status |
|---|---|---|
| 39.91  | تصال CSS همه صفحات | PENDING — runtime/browser evidence required |
| 39.92  | esign Tokens | PENDING — runtime/browser evidence required |
| 39.93  | ight Theme | PENDING — runtime/browser evidence required |
| 39.94  | ark Theme | PENDING — runtime/browser evidence required |
| 39.95  | TL در Layout، Grid و Navigation | PENDING — runtime/browser evidence required |
| 39.96  | TL در Forms، Tables، Drawers و Modals | PENDING — runtime/browser evidence required |
| 39.97  | تن فارسی/انگلیسی، مدل‌ها و اعداد | PENDING — runtime/browser evidence required |
| 39.98  | mages، Media و Broken Image | PENDING — runtime/browser evidence required |
| 39.99  | verflow، Sticky و Scroll | PENDING — runtime/browser evidence required |
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
4. Exercise representative and then all in-scope pages at widths 600px, 601px, 1009px, and 1010px, plus practical viewport sizes inside each range.
5. Check Light/Dark, RTL, overflow, keyboard/focus, forms, tables, drawers, media, loading/error/empty states and existing JavaScript behavior.
6. Record browser/runtime evidence per step; only then decide PASS/LOCK. Until then, Phase 39 remains IN PROGRESS and LOCK=NO.
