# Nivad Phase 30 — Account Experience

Scope: customer-facing account UI only. Preserve existing backend routes/controllers/services, database, CSRF, authentication, payments, and Wishlist contracts. No schema changes.

Execution cycle for each batch: AUDIT → IMPLEMENT → TEST → REGRESSION → PASS → LOCK → NEXT BATCH. UNKNOWN/BLOCKED must not be reported as PASS. Phase LOCK requires all contracts and browser/API smoke tests.

## Batch 01 — Shared foundation (30.01–30.10)
30.01 inventory actual account pages; 30.02 trace PHP/partials/JS/CSS/API; 30.03 align dashboard hook; 30.04 handle HTTP/JSON/network errors; 30.05 preserve CSRF and same-origin credentials; 30.06 handle expired sessions; 30.07 verify internal destinations; 30.08 loading/empty/error/success states; 30.09 use setting.css tokens; 30.10 independent mobile/tablet/desktop styling.

## Batch 02 — Dashboard (30.11–30.20)
30.11 connect data-account-page; 30.12 fetch status with error feedback; 30.13 render security summary; 30.14 render verified status only from real fields; 30.15 map actual session response shape; 30.16 unread notification count; 30.17 order count; 30.18 empty state; 30.19 complete navigation; 30.20 validate page destinations.

## Batch 03 — Profile (30.21–30.30)
30.21 GET profile; 30.22 map first/last name; 30.23 keep email/phone read-only; 30.24 PATCH allowed fields only; 30.25 validation feedback; 30.26 duplicate-submit guard; 30.27 success only on successful response; 30.28 network/JSON/session failures; 30.29 accessible labels/focus/live feedback; 30.30 responsive/theme regression.

## Batch 04 — Addresses (30.31–30.40)
30.31 GET addresses; 30.32 render real fields; 30.33 empty state; 30.34 POST create; 30.35 PUT update; 30.36 POST default; 30.37 DELETE with confirmation; 30.38 invalid ID/API failures; 30.39 reflect default and feedback; 30.40 CRUD regression.

## Batch 05 — Orders (30.41–30.50)
30.41 GET list; 30.42 render ID/status/amount; 30.43 empty state; 30.44 real detail URL; 30.45 GET detail; 30.46 render returned items; 30.47 render only proven detail/history; 30.48 invalid ID and 401/403/404; 30.49 preserve checkout/payment; 30.50 orders regression.

## Batch 06 — Reviews (30.51–30.60)
30.51 GET /reviews/mine; 30.52 render title/body/rating/status; 30.53 loading/empty/error; 30.54 explicit labels/native validation; 30.55 validate product ID/rating; 30.56 POST /reviews with existing payload; 30.57 prevent duplicates; 30.58 success/error feedback and reset only on success; 30.59 no fabricated moderation state; 30.60 review/accessibility regression.

## Batch 07 — Returns (30.61–30.70)
30.61 GET list; 30.62 render actual ID/status; 30.63 loading/empty/error; 30.64 real relative detail URL; 30.65 GET detail; 30.66 render items; 30.67 render status history; 30.68 preserve existing POST payload contract; 30.69 invalid IDs and API errors; 30.70 returns regression.

## Batch 08 — Notifications (30.71–30.80)
30.71 GET list; 30.72 map real fields safely; 30.73 empty state; 30.74 PATCH read one; 30.75 PATCH read all; 30.76 refresh only after success; 30.77 prevent duplicate actions; 30.78 failure feedback/retry; 30.79 live regions/keyboard; 30.80 notification/count regression.

## Batch 09 — Wishlist (30.81–30.90)
30.81 reuse existing Wishlist; 30.82 link existing destination; 30.83 preserve API; 30.84 preserve guest/auth behavior; 30.85 preserve header/mobile counts; 30.86 preserve remove/product actions; 30.87 preserve empty/error/loading; 30.88 prevent duplicate listeners; 30.89 Account↔Wishlist↔Product↔Cart smoke test; 30.90 Wishlist regression.

## Batch 10 — Security (30.91–30.100)
30.91 GET security; 30.92 GET verification; 30.93 GET sessions; 30.94 render only API fields; 30.95 POST logout-other-sessions; 30.96 POST revoke session; 30.97 POST change password; 30.98 CSRF/session/network errors; 30.99 confirm sensitive actions/prevent duplicates; 30.100 security regression.

## Batch 11 — Final regression (30.101–30.110)
30.101 PHP lint; 30.102 JS syntax/import checks; 30.103 validate routes/parameters; 30.104 compare API contracts to controllers; 30.105 Phase 29 auth regression; 30.106 profile/address regression; 30.107 orders/returns/reviews/notifications regression; 30.108 Wishlist/commerce regression; 30.109 responsive/theme/encoding/a11y regression; 30.110 evidence-based final status and lock gate.

## Implemented in this branch
- Hardened account API request handling for network errors, malformed JSON and non-success HTTP/API responses.
- Fixed dashboard initializer selector and actual top-level session-array handling.
- Connected session revocation, logout-other-sessions and password-change UI to existing routes.
- Improved profile/address/order/return loading/error/empty feedback, safe output rendering, duplicate-submit guards and relative links.
- Improved notification and review error handling and submission state.
- Replaced mojibake dashboard text and repaired order headings.
- Added semantic-token account styling and dedicated mobile/tablet/desktop stylesheets.

## Test limitation
Static checks cannot prove browser rendering, authenticated HTTP behavior, DB permissions, email delivery, or concurrency. Those must be tested locally in XAMPP/browser before phase LOCK.
