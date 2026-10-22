# Nivad — Phase 32 Commerce Context & Cross-Page Continuity

Status: IMPLEMENTATION IN PROGRESS — LOCK=NO
Base: master at b68d69223ab9215b6a98cd5731e10ed7e514d173
Branch: phase-32-commerce-context

## Objective
Add a narrow, versioned, tab-scoped context for non-sensitive storefront intent while preserving existing API, cart, and checkout contracts. Context is navigation assistance only, never authoritative for price, stock, compatibility, totals, identity, or payment.

## Batch plan — 8 batches × 10 contracts

| Batch | IDs | Scope | Status |
|---|---|---|---|
| 01 | 32.01–32.10 | Journey inventory, schema, safe storage and allowlists | PARTIAL |
| 02 | 32.11–32.20 | Landing/Search/Finder continuity and URL state | PARTIAL |
| 03 | 32.21–32.30 | Search result ↔ Product Detail continuity | PARTIAL |
| 04 | 32.31–32.40 | Product ↔ Compatibility device context | PARTIAL |
| 05 | 32.41–32.50 | Setup Builder context | PARTIAL — runtime restoration proof pending |
| 06 | 32.51–32.60 | Cart and drawer consistency | AUDIT REQUIRED |
| 07 | 32.61–32.70 | Guest/authenticated cart sync and Checkout handoff | RUNTIME/BACKEND CONTRACT PROOF REQUIRED |
| 08 | 32.71–32.80 | Syntax, E2E regression and lock gate | IN PROGRESS |

### Batch 01 — 32.01–32.10
32.01 Inventory real journey pages/modules; 32.02 verify actual routes and parameters; 32.03 classify transient vs. tab-scoped state; 32.04 version and allowlist context fields; 32.05 reject protocol-relative return paths; 32.06 normalize stored data; 32.07 malformed context returns safe defaults; 32.08 storage failures do not block interactions; 32.09 do not invent expiry policy; 32.10 reuse one canonical test script.

### Batch 02 — 32.11–32.20
32.11 audit real Landing CTAs; 32.12 record submitted search query; 32.13 preserve only real filter/sort/page parameters; 32.14 audit Finder enums; 32.15 separate recent searches from active context; 32.16 prevent stale intent replacing current query; 32.17 test refresh; 32.18 test return-to-results; 32.19 handle invalid URL parameters; 32.20 test responsive Search/Finder.

### Batch 03 — 32.21–32.30
32.21 audit product-card destinations; 32.22 use valid IDs/slugs; 32.23 record loaded product and selected variant; 32.24 preserve filters only when routes support them; 32.25 test product refresh; 32.26 test invalid product routes; 32.27 never trust context for price/stock; 32.28 validate selected variants against live data; 32.29 audit duplicate listeners; 32.30 test Search → Product → Search.

### Batch 04 — 32.31–32.40
32.31 audit product/device identifiers; 32.32 record selected device ID; 32.33 validate it against live API; 32.34 preserve product context where supported; 32.35 separate selection from verified compatibility; 32.36 test empty/error/inactive responses; 32.37 prevent stale results after device changes; 32.38 use existing product routes; 32.39 never trust browser context as compatibility proof; 32.40 test Product → Compatibility → Product.

### Batch 05 — 32.41–32.50
32.41 audit Setup Builder state; 32.42 record device/step; 32.43 allowlist needs; 32.44 record product IDs; 32.45 record variant IDs separately; 32.46 do not invent accessory contracts; 32.47 budget is a preference only; 32.48 verify hard-coded API base against deployment; 32.49 revalidate IDs before any restoration; 32.50 test step navigation/refresh.

### Batch 06 — 32.51–32.60
32.51 preserve cart item contract; 32.52 audit variant identity; 32.53 keep existing add-to-cart API; 32.54 validate quantity/display values; 32.55 preserve cart:updated contract; 32.56 compare drawer/cart page; 32.57 test quantity and removal; 32.58 test storage denial; 32.59 test server/local conflicts; 32.60 test Product and Setup Builder cart entry.

### Batch 07 — 32.61–32.70
32.61 inspect actual cart API; 32.62 verify guest cart; 32.63 verify authenticated cart; 32.64 verify guest-to-auth merge; 32.65 verify conflicts; 32.66 validate variant and quantity; 32.67 verify server-side price/stock/subtotal; 32.68 verify address ownership; 32.69 verify shipping/coupon contract; 32.70 test Checkout errors, expired session and empty cart.

### Batch 08 — 32.71–32.80
32.71 syntax-check changed JS; 32.72 verify DOM hooks; 32.73 verify API base/routes; 32.74 cross-check API against controllers/services; 32.75 test Landing → Search → Product; 32.76 test Product → Compatibility → Setup Builder; 32.77 test Setup Builder → Drawer → Cart; 32.78 test Cart → Checkout including CSRF/auth failures; 32.79 test refresh/history/storage/responsive/accessibility; 32.80 report evidence and request explicit approval before LOCK.

## Implemented in this change
- Add assets/js/modules/commerce-context.js with versioned schema, sessionStorage, allowlisted sections, safe same-origin path validation, bounded IDs and safe defaults.
- Record submitted search query and local return path.
- Record loaded product/variant identifiers and selected compatibility device ID.
- Record and restore Setup Builder step/device/needs/product IDs/variant IDs/budget, validating restoration against fresh API responses.
- Extend the existing canonical read-only PHP test instead of creating another phase test file.
- Add a Phase 32 GitHub Actions workflow for JavaScript syntax and the canonical PHP contract suite.

## Remaining blockers
- Setup Builder now restores a saved device only when present in the live device response, then restores only selected product/variant pairs found in the live Setup Products response with a valid price. Runtime refresh/step-flow proof remains pending.
- Finder intent/budget now restore from allowlisted values, persist on changes/submission, and clear on reset; browser/history proof remains pending.
- Search returnPath now points to the generated result URL; preservation of filters/sort/page is still pending because the current search submit flow redirects to `/search?q=...` and does not expose a verified filter contract here.
- Cart sync existing-item updates now use the shared API helper and no longer reference undefined `item.id` or `csrfToken`. Server-only items are preserved when the browser cache is empty/stale, and matching quantities are reconciled conservatively using the greater value; additive guest-cart merge semantics still require an explicit product contract and XAMPP/browser proof. Checkout, CSRF, price/stock and browser-history scenarios remain runtime gates.
- Setup Builder API base now honors the existing `window.NIVAD_BASE_URL` deployment base and its module initializer is compatible with the main entry point. Verify under XAMPP.
- No backend API/schema, payment, price, stock, or authentication behavior is changed.
- Previously reported login/register GET-query-string issue remains a separate unresolved security regression.

All 80 contracts need evidence before LOCK=YES. Static checks alone do not authorize locking.
