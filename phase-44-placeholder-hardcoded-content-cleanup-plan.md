# Nivad — Phase 44: Placeholder & Hardcoded Content Cleanup

## Scope and guardrails
Frontend content integrity: demo/fake business content, dummy links, unfinished CTAs, misleading static data, placeholders, and honest loading/empty/error content. Source of truth is the actual repository at the Phase 43 head. No guessed routes, product/category slugs, prices, availability, or legal/business facts.

**Backend Freeze remains active.** This phase does not authorize edits to backend routes, APIs, controllers, services, repositories, middleware, or database. Storefront and Admin assets remain separate. Valid instructional placeholders are retained unless their contract proves them invalid.

## Snapshot
- Base: `phase-43-offline-network-recovery`
- Working branch: `phase-44-placeholder-hardcoded-content-cleanup`
- Confirmed source findings: homepage partials had no-op links in `partials/cta.php`, `partials/product.php`, `partials/new-product.php`, and eight static category anchors in `partials/categories.php`; product sections contained hardcoded example product names/specifications/prices and cart/wishlist identifiers.
- Existing dynamic storefront source: `assets/js/modules/storefront.js` already loads categories from `/storefront/categories` and products from `/storefront/products`. Static fallback cards were therefore not required to preserve the API-backed grid.
- Confirmed routes: `partials/hero.php` already links to `/pages/store/products/index.php`; `pages/store/finder/index.php` exists and renders the Finder partial.

## Source-level implementation
- Removed hardcoded category anchors so unverified category names/slugs are not shown if the API fails; the existing API renderer remains the source of category links.
- Removed sample product cards, prices, brands, specifications, and fake wishlist/cart IDs from homepage product fallback markup. The live product grid remains intact and API-driven.
- Replaced the hardcoded “new arrivals” product/brand showcase with truthful copy and a link to the existing full catalog. No unsupported “new-only” filter is invented.
- Repaired homepage CTA and catalog links using existing page paths.
- Added honest, accessible empty/error states for category and product API failures. Each request reports its own state so one failed request does not erase the successful section.
- No backend or database files changed.

## Status legend
- **IMPLEMENTED**: source change is present; runtime behavior still needs environment verification.
- **PENDING**: the contract requires a broader source audit, route/API evidence, or runtime/browser validation not yet available.
- **DEPENDENCY UNKNOWN**: do not invent a route or business/legal fact to close it.

## Batch status

- **Batch 44.01 — Content Inventory & Audit:** PENDING — 0 implemented source changes; 10 pending.

### Batch 44.01 — Content Inventory & Audit

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.01.01 | Extract the real storefront page inventory from the repository tree. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.02 | Inventory shared storefront partials and section entry points. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.03 | Inventory forms and their current placeholder/label contracts. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.04 | Inventory CTA/action controls in the audited homepage partials. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.05 | Record internal links and route patterns evidenced by existing pages. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.06 | Record external links separately from internal routes. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.07 | Separate static UI copy from business data. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.08 | Identify homepage demo product and category fallback content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.09 | Record each confirmed suspicious item with exact file path. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.01.10 | Publish a source-backed initial audit without guessing missing dependencies. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.02 — Fake & Test Content Cleanup:** PARTIAL — 1 implemented source changes; 9 pending.

### Batch 44.02 — Fake & Test Content Cleanup

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.02.01 | Audit homepage product demo names against the live product contract. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.02 | Audit hardcoded homepage prices and discount claims. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.03 | Audit hardcoded brand names/specifications used as business data. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.04 | Audit homepage rating and review counts for fabricated values. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.05 | Audit dashboard statistics for static/demo values. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.06 | Audit account pages for fake account data. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.07 | Audit JavaScript modules for mock/demo product data. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.08 | Audit PHP partials for fake fallback business content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.02.09 | Remove confirmed fake homepage products/prices while preserving the live API grid. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.02.10 | Regress live product rendering and honest empty/error states. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.03 — Placeholder Audit & Cleanup:** PENDING — 0 implemented source changes; 10 pending.

### Batch 44.03 — Placeholder Audit & Cleanup

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.03.01 | Enumerate form placeholder attributes in storefront forms. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.02 | Distinguish helpful examples from test-only placeholder text. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.03 | Audit search placeholders without removing useful guidance. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.04 | Audit product-filter placeholders and numeric examples. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.05 | Audit review/question form placeholders. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.06 | Audit account form placeholders. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.07 | Audit cart/checkout placeholders. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.08 | Audit admin form placeholders. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.09 | Change only placeholders proven invalid by their input contract. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.03.10 | Verify labels, initial values, validation and form behavior. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.04 — Dummy Links & Navigation:** PARTIAL — 6 implemented source changes; 4 pending.

### Batch 44.04 — Dummy Links & Navigation

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.04.01 | Enumerate href="#" in the homepage partials audited in this phase. | IMPLEMENTED — no-op anchors removed from audited homepage partials. |
| 44.04.02 | Search audited source for javascript:void(0) dummy links. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.04.03 | Audit links without an evidenced destination. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.04.04 | Audit links pointing to unsupported route patterns. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.04.05 | Repair the homepage all-products link to the existing products page. | IMPLEMENTED — points to the existing products page. |
| 44.04.06 | Repair the homepage product CTA to the existing Finder page. | IMPLEMENTED — points to the existing Finder page. |
| 44.04.07 | Remove static category fallback anchors whose category slugs are not verified. | IMPLEMENTED — unverified static category anchors removed; API-driven category links preserved. |
| 44.04.08 | Repair the new-products section link and remove the unsupported 'new only' promise. | IMPLEMENTED — the new-products CTA points to the existing catalog and no longer promises a new-only filter. |
| 44.04.09 | Apply only destinations verified in existing source; do not invent category slugs. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.04.10 | Check changed homepage links against existing page entry points. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.05 — Unfinished CTA & Actions:** PARTIAL — 2 implemented source changes; 8 pending.

### Batch 44.05 — Unfinished CTA & Actions

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.05.01 | Audit homepage CTA destination and intent. | IMPLEMENTED — homepage CTA now has an evidenced Finder destination. |
| 44.05.02 | Audit product-card actions after fake card removal. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.03 | Audit category/collection CTAs and dynamic category links. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.04 | Audit Smart Commerce CTAs. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.05 | Audit search/Finder CTAs. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.06 | Audit cart/checkout CTAs. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.07 | Audit account CTAs. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.08 | Identify buttons without event handlers in the remaining audited markup. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.05.09 | Use only confirmed existing capabilities for changed homepage actions. | IMPLEMENTED — only existing page routes are used; no category slug is guessed. |
| 44.05.10 | Verify the homepage's loading/empty/error content paths at source level. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.06 — Unauthorized Hardcoded UI Content:** PARTIAL — 1 implemented source changes; 9 pending.

### Batch 44.06 — Unauthorized Hardcoded UI Content

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.06.01 | Audit PHP hardcoded text separately from business values. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.02 | Audit JavaScript hardcoded text separately from business values. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.03 | Audit mock data that should be sourced from an established API. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.04 | Audit hardcoded price/stock values across all commerce pages. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.05 | Audit fixed order/payment status labels against live state. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.06 | Audit fixed counters/statistics against live data contracts. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.07 | Audit success/error messages for unsupported claims. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.08 | Separate legitimate UI copy from data that must be live. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.06.09 | Remove confirmed homepage demo business data without changing backend contracts. | IMPLEMENTED — confirmed homepage fake business data removed; backend contracts unchanged. |
| 44.06.10 | Run UI data/state source regression; full-project audit remains pending. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.07 — Test Text & Unfinished Content:** PENDING — 0 implemented source changes; 10 pending.

### Batch 44.07 — Test Text & Unfinished Content

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.07.01 | Search for TODO/FIXME markers in the complete frontend source. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.02 | Search for remaining explicit test text. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.03 | Audit inappropriate default content in empty/error states. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.04 | Audit 'coming soon' claims and disabled features. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.05 | Audit About page content completeness. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.06 | Audit FAQ/help content completeness. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.07 | Audit purchase/returns guidance completeness. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.08 | Audit contact/legal content completeness. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.09 | Record content requiring a verified business/legal dependency. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.07.10 | Verify primary paths contain no confirmed test text; broad sweep pending. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.08 — Product & Commerce Content Integrity:** PARTIAL — 3 implemented source changes; 7 pending.

### Batch 44.08 — Product & Commerce Content Integrity

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.08.01 | Audit product names/prices/specifications in homepage fallback markup. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.08.02 | Audit product image/alt content in homepage fallback markup. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.03 | Audit stock/status labels in commerce views. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.04 | Audit discount/old-price claims. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.08.05 | Audit cart product content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.06 | Audit checkout summary content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.07 | Audit payment success/failure copy. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.08 | Audit Wishlist/Compare content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.09 | Audit Compatibility/Setup content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.08.10 | Prevent homepage fallback markup from presenting invented products/prices. | IMPLEMENTED — no sample prices/product identifiers remain in the audited homepage fallback sections. |

- **Batch 44.09 — Responsive, Theme & Accessibility Content:** PENDING — 0 implemented source changes; 10 pending.

### Batch 44.09 — Responsive, Theme & Accessibility Content

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.09.01 | Audit mobile content wrapping and CTA labels. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.02 | Audit tablet content wrapping and CTA labels. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.03 | Audit desktop content wrapping and CTA labels. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.04 | Audit Light Theme copy contrast for changed states. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.05 | Audit Dark Theme copy contrast for changed states. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.06 | Audit clipping of labels/CTAs in browser viewports. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.07 | Audit helper and error text in forms. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.08 | Audit accessible names of changed homepage links. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.09 | Ensure API loading/empty/error announcements use status semantics. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.09.10 | Run responsive/theme content regression; browser evidence remains pending. | PENDING — full source/runtime evidence not established; do not mark PASS. |

- **Batch 44.10 — Final Content Regression & Exit Gate:** PARTIAL — 4 implemented source changes; 6 pending.

### Batch 44.10 — Final Content Regression & Exit Gate

| Contract | Requirement | Status / evidence |
|---|---|---|
| 44.10.01 | Regress homepage section content after removing fake fallbacks. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.10.02 | Regress all changed link destinations against existing files. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.10.03 | Regress form placeholders; preserve valid instructional examples. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.10.04 | Regress CTA/button behavior. | IMPLEMENTED — see Source-level implementation above; runtime regression pending. |
| 44.10.05 | Regress product/category pages and dynamic API content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.10.06 | Regress cart/checkout content contracts. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.10.07 | Regress account/support/legal content. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.10.08 | Confirm healthy features were not removed from the broader project. | PENDING — full source/runtime evidence not established; do not mark PASS. |
| 44.10.09 | Confirm Backend Freeze and storefront/admin asset boundaries remain intact. | IMPLEMENTED — changed paths are frontend-only; Backend Freeze retained. |
| 44.10.10 | Record evidence and keep PASS/LOCK blocked until all pending tests are executed. | PENDING — full source/runtime evidence not established; do not mark PASS. |

## Final gate
**Phase status: IN PROGRESS — PASS = NO — LOCK = NO.**

The 100-contract register is not a claim that all contracts passed. The implementation closes confirmed homepage defects only. Full-project admin/account/form/content sweep, XAMPP PHP execution, browser checks, responsive/theme checks, and route-by-route runtime regression remain pending. Contracts without verified source evidence or environment execution must remain PENDING / DEPENDENCY UNKNOWN / TEST ENVIRONMENT BLOCKED, never PASS.
