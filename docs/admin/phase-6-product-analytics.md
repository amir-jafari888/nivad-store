# Admin Phase 6 — Product Analytics

Status: implementation on feature branch; CI and XAMPP/browser runtime gates pending.

## Canonical scope

Source of truth: `ADMIN_PANEL_ROADMAP.md`, Phase 6 — Product Analytics.

- Batch 01 — Unique Viewers
- Batch 02 — Search Analytics
- Batch 03 — Purchase Conversion
- Batch 04 — Checkout Funnel
- Batch 05 — Related Products
- Batch 06 — Discovery Analytics
- Batch 07 — Comparison Analytics

Each Batch contains exactly 10 implementation contracts. The implementation reuses the already-defined `admin_analytics_events` table from `database/migrations/20261029_admin_panel_foundation.sql`; this Phase does not introduce a parallel analytics table or require a new migration.

## Data and privacy contract

- Analytics collection is opt-in and requires `nivad-analytics-consent = accepted`.
- A first-party random visitor identifier is created only after consent. The raw identifier is sent only to the same-origin collector and hashed server-side.
- Session identifiers and authenticated customer identifiers are stored only as SHA-256 pseudonyms.
- Search text is never persisted. The service normalizes and hashes it before storage.
- Purchase completion is recorded server-side only after a successful payment attempt has been finalized and a payment row has been persisted.
- Client-side checkout events describe observed funnel actions; they are not treated as proof of payment.
- The collector is CSRF-protected, same-origin, rate-limited, and validates event names and product/variant references.
- Client event IDs are checked before insertion to reduce duplicate writes. The verified payment event is inside the payment finalization transaction.

## Batch 01 — Unique Viewers (06.01–06.10)

1. [x] Audit the existing product event collector and its consent gate.
2. [x] Reuse the canonical Admin analytics event table.
3. [x] Create a persistent random first-party visitor identifier only after consent.
4. [x] Send the visitor identifier through the existing CSRF-protected endpoint.
5. [x] Hash the visitor identifier server-side; never persist the raw value.
6. [x] Hash the PHP session identifier separately from the visitor identifier.
7. [x] Keep event timestamps in the database event timestamp.
8. [x] Apply the existing bounded date-window validation to reports.
9. [x] Aggregate distinct visitor hashes for product views.
10. [ ] Verify unique-viewer counts across multiple browser sessions in XAMPP.

## Batch 02 — Search Analytics (06.11–06.20)

1. [x] Audit the existing search form and suggestion list selectors.
2. [x] Record search suggestion impressions only when suggestions are visible.
3. [x] Record the visible suggestion count with the impression event.
4. [x] Record search form submission as a search click event.
5. [x] Record suggestion selection as a search click event.
6. [x] Normalize and hash search terms on the server.
7. [x] Exclude raw search terms from persisted event dimensions.
8. [x] Apply event rate limits to search telemetry.
9. [x] Aggregate search impressions and clicks from persisted events.
10. [ ] Verify search impression/click counts against browser interaction and consent states.

## Batch 03 — Purchase Conversion (06.21–06.30) — PARTIAL / DEPENDENCY BLOCKED

1. [x] Audit the authoritative payment-attempt finalization path.
2. [x] Require payment attempt status `success` before finalization.
3. [x] Persist the payment record before recording conversion.
4. [x] Record `purchase_completed` from the server, not the public event collector.
5. [x] Associate the event with the actual order ID.
6. [x] Include payment and attempt references without storing gateway secrets.
7. [x] Include a pseudonymous session identifier for funnel attribution.
8. [x] Include a pseudonymous customer identifier when an authenticated customer exists.
9. [x] Keep the conversion event in the payment transaction; rollback removes both on failure.
10. [ ] Verify exactly one conversion event per finalized payment against MySQL and gateway test flow.

**Dependency finding:** `PaymentController::complete()` currently returns HTTP 503 because valid gateway confirmation is not configured. Repository search found no call site for `PaymentService::createSuccessfulPaymentFromAttempt()`. The server-side event hook is implemented inside that finalization method, but the current application cannot reach it through a live payment flow. This is intentionally not replaced with a client-forgeable purchase event.

## Batch 04 — Checkout Funnel (06.31–06.40) — PARTIAL / PURCHASE STAGE BLOCKED

1. [x] Record checkout start when the real checkout root is initialized.
2. [x] Record address selection from the existing address control.
3. [x] Record shipping selection from the existing shipping control.
4. [x] Record order creation only after the order API returns a valid order ID.
5. [x] Use the server-verified purchase event as the funnel completion stage.
6. [x] Group funnel progress by pseudonymous session hash.
7. [x] Aggregate checkout starts.
8. [x] Aggregate address, shipping and order-created steps.
9. [x] Aggregate successful purchase sessions separately from order creation.
10. [ ] Verify funnel drop-off against real checkout/payment sessions.

The client-side checkout steps are instrumented. The final successful-purchase stage remains unavailable until the verified payment gateway completion path is implemented and connected.

## Batch 05 — Related Products (06.41–06.50)

1. [x] Audit the existing product complements renderer.
2. [x] Reuse the actual complement product ID emitted by the renderer.
3. [x] Record impressions through IntersectionObserver visibility.
4. [x] Record clicks only on the actual complement product link.
5. [x] Keep related-product events distinct from ordinary product views.
6. [x] Aggregate related-product impressions.
7. [x] Aggregate related-product clicks.
8. [x] Calculate click rate only when an impression denominator exists.
9. [x] Apply consent and collector validation to related-product events.
10. [ ] Verify visible-impression and click counts on desktop/tablet/mobile.

## Batch 06 — Discovery Analytics (06.51–06.60)

1. [x] Audit the existing Discovery options and product renderer.
2. [x] Expose the actual product ID on rendered Discovery product cards.
3. [x] Record product impressions only after an element crosses the visibility threshold.
4. [x] Record Discovery option impressions only after visibility.
5. [x] Record selection of an existing Discovery option.
6. [x] Keep option selection distinct from product-card impression.
7. [x] Aggregate Discovery product impressions.
8. [x] Aggregate Discovery option impressions.
9. [x] Aggregate Discovery selections.
10. [ ] Verify Discovery option/product metrics against real browser interactions.

## Batch 07 — Comparison Analytics (06.61–06.70)

1. [x] Audit the existing `comparison:selection-changed` event contract.
2. [x] Record product additions to comparison.
3. [x] Record product removals from comparison.
4. [x] Record comparison view after comparison content is actually visible.
5. [x] Use the existing comparison selection IDs; do not invent product data.
6. [x] Keep comparison events separate from product views.
7. [x] Aggregate comparison views.
8. [x] Aggregate comparison selection changes.
9. [x] Surface analytics summary metrics in the existing Admin capability center.
10. [ ] Verify comparison add/remove/view counts with a real browser session.

## Implementation files

- `app/Services/ProductAnalyticsRecorder.php`
- `app/Services/AdminCapabilityService.php`
- `app/Repositories/AdminCapabilityRepository.php`
- `app/Controllers/ProductAnalyticsController.php`
- `app/Controllers/AdminCapabilityController.php`
- `app/Services/PaymentService.php`
- `assets/js/modules/analytics.js`
- `assets/js/modules/product-analytics.js`
- `assets/js/modules/discovery.js`
- `assets/js/modules/checkout.js`
- `assets/js/main.js`
- `admin/assets/js/modules/capability-center.js`

## Verification gates

- Static contract test: `php test-admin-phase-6-product-analytics.php`
- PHP syntax: all changed PHP files.
- JavaScript syntax: all changed JS modules.
- CI: `.github/workflows/admin-phase-6-product-analytics.yml`
- XAMPP/MySQL: verify `admin_analytics_events` exists from the foundation migration; verify inserts, aggregation, date bounds and duplicate handling.
- Browser: verify consent accepted/rejected/unknown, search, checkout, related products, Discovery and Comparison.
- Payment: verify a real successful test payment creates exactly one server-side `purchase_completed` event; failed/cancelled/pending attempts must not create it.

## Current gate

- Implementation branch: `feature/admin-phase-6-product-analytics-2026-10-31`
- Base branch: `feature/admin-phase-5-cms-2026-10-30` (stacked; Phase 5 PR remains separate).
- New migration: none; uses the existing foundation event table.
- Static contract test: 70/70 PASS.
- PHP syntax: PASS.
- JavaScript module syntax: PASS.
- XAMPP/MySQL runtime: pending.
- Browser and gateway verification: pending.
- Purchase conversion: PARTIAL / DEPENDENCY UNKNOWN (payment completion route currently returns 503; no active finalization call site).
- Existing foundation regression: a separate pre-existing non-database test still fails on undefined `AdminAccountPolicy::validateCreate()`; this is unrelated to Phase 6 changes.
- PASS: not granted.
- LOCK: NO.
