# Nivad — Phase 43: Offline / Network / Recovery

## Scope
Offline state, online recovery, request timeout, safe retry, API failure, server unavailable, partial failure and session recovery. Frontend only; Backend Freeze remains active.

## Implementation snapshot
- Branch: `phase-43-offline-network-recovery`
- Base: `phase-42-performance-ux`
- Central runtime: `assets/js/modules/network-recovery.js`, initialized by `assets/js/main.js`.
- Request timeout: 15 seconds. One retry maximum for GET/HEAD only, limited to network failures, timeout and HTTP 502/503/504. POST/PUT/PATCH/DELETE are never retried automatically.
- Offline/online/server/timeout/session feedback is announced via one reusable status notice, with responsive CSS in the existing Mobile/Tablet/Desktop architecture and tokens from `assets/css/setting.css`.
- Events emitted: `nivad:network-state`, `nivad:network-error`, `nivad:request-timeout`, `nivad:server-unavailable`, `nivad:session-expired`.
- Runtime tests, full request inventory, route-by-route partial-failure audit and browser checks are not claimed as complete.

## Batch statuses
- **Batch 43.01 — Network State Foundation:** PARTIAL — event listeners, network state events, singleton guards implemented; browser verification pending.
- **Batch 43.02 — Offline UX:** PARTIAL — shared accessible notice and mobile/tablet/desktop styles implemented; real browser/theme contrast checks pending.
- **Batch 43.03 — Online Recovery:** PARTIAL — online recovery notice and safe retry infrastructure implemented; no automatic replay of stale requests by design.
- **Batch 43.04 — Request Timeout:** PARTIAL — centralized 15s timeout wraps window.fetch and separates timeout/network errors; exhaustive inventory of all request mechanisms pending.
- **Batch 43.05 — Retry Strategy:** PARTIAL — one retry maximum for GET/HEAD only, on network/timeout/502/503/504; non-idempotent methods are never retried.
- **Batch 43.06 — API Failure:** PARTIAL — centralized HTTP/network/session events and server error notice; module-specific payload/error mapping still needs audit.
- **Batch 43.07 — Server Unavailable:** PARTIAL — server 5xx distinction, feedback and bounded retry implemented; production server outage simulation pending.
- **Batch 43.08 — Partial Failure & Recovery:** PENDING — existing section-level states remain; every page/section partial-failure contract has not been audited end-to-end.
- **Batch 43.09 — Session Recovery:** PARTIAL — 401/419 scoped session event and sign-in recovery link; exact session contracts and context preservation need route-by-route audit.
- **Batch 43.10 — Recovery Regression & Exit Gate:** PENDING — source regression test added; PHP runtime, browser/network throttling, session and mobile regression unavailable here.

## Contract register — 100 contracts

| Contract | Requirement | Source-level status |
|---|---|---|
| 43.01.01 | Audit وضعیت فعلی online/offline | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.02 | Audit navigator.onLine | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.03 | Audit online event | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.04 | Audit offline event | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.05 | تعیین Network State Contract | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.06 | تعیین رفتار UI هنگام Offline | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.07 | تعیین رفتار UI هنگام Online | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.08 | جلوگیری از تکرار Listenerهای Network | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.09 | سازگاری Network State با Theme | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.01.10 | Lock Network State Foundation | PENDING — requires dedicated audit or runtime evidence |
| 43.02.01 | Audit Offline Banner/Notice موجود | PENDING — requires dedicated audit or runtime evidence |
| 43.02.02 | طراحی State مشخص Offline | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.03 | پیام قابل فهم برای کاربر | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.04 | جلوگیری از نمایش پیام تکراری | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.05 | Offline State در Mobile | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.06 | Offline State در Tablet | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.07 | Offline State در Desktop | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.08 | Offline State در Light/Dark | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.09 | Accessibility Offline Announcement | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.02.10 | Lock Offline UX | PENDING — requires dedicated audit or runtime evidence |
| 43.03.01 | تشخیص بازگشت شبکه | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.02 | حذف/به‌روزرسانی Offline State | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.03 | جلوگیری از Recovery Loop | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.04 | تعیین رفتار Requestهای متوقف‌شده | PENDING — requires dedicated audit or runtime evidence |
| 43.03.05 | تعیین رفتار Requestهای قابل Retry | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.06 | Feedback بازگشت شبکه | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.07 | Accessibility برای Recovery | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.08 | Light/Dark Recovery State | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.09 | Responsive Recovery State | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.03.10 | Lock Online Recovery | PENDING — requires dedicated audit or runtime evidence |
| 43.04.01 | Inventory تمام Requestهای Frontend | PENDING — requires dedicated audit or runtime evidence |
| 43.04.02 | شناسایی Requestهای بدون Timeout | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.03 | تعریف Timeout Contract | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.04 | تفکیک Timeout از Network Error | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.05 | Timeout برای Search | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.06 | Timeout برای Commerce Requests | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.07 | Timeout برای Account/Auth Requests | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.08 | Timeout برای Smart Features | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.09 | Timeout UI/Feedback | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.04.10 | Lock Timeout Contract | PENDING — requires dedicated audit or runtime evidence |
| 43.05.01 | Inventory Retryهای موجود | PENDING — requires dedicated audit or runtime evidence |
| 43.05.02 | جلوگیری از Retry بی‌نهایت | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.03 | تعریف Maximum Retry | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.04 | Retry فقط برای عملیات قابل تکرار | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.05 | جلوگیری از Retry برای عملیات خطرناک/غیرقابل‌تکرار | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.06 | Retry بعد از Timeout | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.07 | Retry بعد از Network Recovery | PENDING — requires dedicated audit or runtime evidence |
| 43.05.08 | Retry Feedback | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.09 | Retry Accessibility | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.05.10 | Lock Retry Contract | PENDING — requires dedicated audit or runtime evidence |
| 43.06.01 | Inventory API Failure Handling | PENDING — requires dedicated audit or runtime evidence |
| 43.06.02 | تفکیک HTTP Error از Network Error | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.03 | تفکیک 4xx از 5xx | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.04 | تعریف Generic API Failure State | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.05 | Search API Failure | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.06 | Catalog/Product API Failure | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.07 | Commerce API Failure | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.08 | Smart Feature API Failure | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.09 | Auth/API Failure Boundary | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.06.10 | Lock API Failure Contract | PENDING — requires dedicated audit or runtime evidence |
| 43.07.01 | تشخیص Server Unavailable | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.02 | تفکیک Server Failure از Offline | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.03 | تعریف Server Error UI | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.04 | Recovery Action | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.05 | Retry Action | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.06 | جلوگیری از Retry Storm | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.07 | Server Failure در Mobile | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.08 | Server Failure در Tablet/Desktop | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.09 | Accessibility Server Failure | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.07.10 | Lock Server Unavailable Contract | PENDING — requires dedicated audit or runtime evidence |
| 43.08.01 | شناسایی Partial Failureهای واقعی | PENDING — requires dedicated audit or runtime evidence |
| 43.08.02 | جلوگیری از شکست کل صفحه به‌خاطر یک Section | PENDING — requires dedicated audit or runtime evidence |
| 43.08.03 | Recovery مستقل Sectionها | PENDING — requires dedicated audit or runtime evidence |
| 43.08.04 | Partial Failure در Search | PENDING — requires dedicated audit or runtime evidence |
| 43.08.05 | Partial Failure در Catalog | PENDING — requires dedicated audit or runtime evidence |
| 43.08.06 | Partial Failure در Smart Features | PENDING — requires dedicated audit or runtime evidence |
| 43.08.07 | Partial Failure در Commerce UI | PENDING — requires dedicated audit or runtime evidence |
| 43.08.08 | حفظ داده‌های سالم | PENDING — requires dedicated audit or runtime evidence |
| 43.08.09 | Feedback برای بخش Failed | PENDING — requires dedicated audit or runtime evidence |
| 43.08.10 | Lock Partial Failure Contract | PENDING — requires dedicated audit or runtime evidence |
| 43.09.01 | Audit Session Failure State | PENDING — requires dedicated audit or runtime evidence |
| 43.09.02 | Audit Session Expiration Handling | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.09.03 | تفکیک Session Expired از Offline | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.09.04 | حفظ Context قابل حفظ کاربر | PENDING — requires dedicated audit or runtime evidence |
| 43.09.05 | Recovery مسیر Authentication | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.09.06 | Recovery مسیر Account | PENDING — requires dedicated audit or runtime evidence |
| 43.09.07 | Recovery مسیر Checkout | PENDING — requires dedicated audit or runtime evidence |
| 43.09.08 | جلوگیری از Loop در Session Recovery | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.09.09 | Accessibility Session Recovery | PARTIAL — foundation present; runtime/page-level evidence pending |
| 43.09.10 | Lock Session Recovery | PENDING — requires dedicated audit or runtime evidence |
| 43.10.01 | Offline Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.02 | Online Recovery Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.03 | Timeout Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.04 | Retry Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.05 | API Failure Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.06 | Server Failure Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.07 | Partial Failure Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.08 | Session Recovery Regression | PENDING — requires dedicated audit or runtime evidence |
| 43.10.09 | Backend Freeze Verification | PENDING — requires dedicated audit or runtime evidence |
| 43.10.10 | Final Evidence + PASS/LOCK Gate | PENDING — requires dedicated audit or runtime evidence |

## Regression / safety notes
- No backend routes, controllers, services, repositories, middleware, database schema or API contracts were changed.
- No automatic replay of stale requests after the network returns; users can retry through the existing feature controls to avoid duplicate side effects or stale data.
- Session recovery does not force a redirect. A sign-in link is shown only for scoped session failures or HTTP 419; auth login/register failures are excluded from the generic session notice.
- The global fetch wrapper and timeout behavior require browser compatibility and request-level regression tests before PASS/LOCK.
- Phase status: **IN PROGRESS**. PASS = NO. LOCK = NO.
