# NivAD — Phase 34: JavaScript Integrity

Status: IMPLEMENTATION IN PROGRESS — NOT LOCKED.

## Scope

Audit and validate module imports/exports, initialization, DOM/event contracts, local/session storage, asynchronous request handling, error paths, and cross-module regression. Existing API, CSRF, route, storage-key, and event contracts must be preserved unless repository evidence proves a required correction.

## Audit / implementation log

### Audit 34-A — Entry point and key commerce modules

- Read `assets/js/main.js`: it imports 30 default initializers and invokes each in the entry point. Static check will verify imported relative paths, default exports, duplicate paths, and one initializer call per imported module.
- Read `assets/js/modules/cart.js`: cart normalization validates product identifiers, price and quantity; storage reads and writes are guarded; API helper checks HTTP/API failure and uses same-origin credentials and existing CSRF endpoint. `catalog.js` has `requestToken` state; race safety across all async modules is not yet proven.
- Read `assets/js/modules/wishlist.js`: `saveLocal()` and server-to-local restoration previously wrote two localStorage keys without catching storage exceptions. **Fixed in this phase:** both write paths now catch failures, emit `wishlist:storage-error`, and avoid announcing a successful UI update when persistence fails.
- Read `assets/js/modules/commerce-context.js`: versioned session storage, normalization, allowlisted setup steps/needs, and safe failure returns exist. Consumer behavior still needs regression coverage.
- Read `header.js`, `cart-drawer.js`, `search.js`, `catalog.js`, `finder.js`, `setup-builder.js`, `product-detail.js`, `product-media.js`, `auth.js`, `checkout.js`, `payment.js`, `account.js`, `customer-commerce.js`, `discovery.js`, `compatibility.js`, and `storefront.js` from the current master snapshot for initial module inventory. This is not a claim that every event path or API schema has been exhaustively runtime-verified.
- Found and fixed a confirmed duplicate listener in `assets/js/modules/catalog.js`: two `popstate` handlers could trigger duplicate catalog loads for one browser-history change. They are consolidated into one handler that hydrates URL filters and loads the URL page once. Added a static regression assertion.
- Catalog now rejects valid JSON that is not an object (for example `null`, arrays, or scalars), instead of silently returning empty data.
- Cart, wishlist, search suggestions, Finder, product detail, compatibility, authentication, and payment now reject malformed JSON/non-object payloads on successful HTTP responses rather than interpreting fallback `{}` as success. Existing HTTP/API failure handling remains in place.
- Payment CSRF retrieval now requires an HTTP-successful, object-shaped response containing a token; it no longer proceeds with a missing token as if CSRF retrieval succeeded.
- Catalog’s request-token guards and Search’s request-token guards were verified in source to suppress stale responses. Finder currently prevents concurrent runs by disabling form controls and guarding `run()` while loading; this is not an AbortController-based cancellation contract.
- Initial Phase 34 CI run: JavaScript syntax, PHP syntax, Phase 31+32 suite, and Phase 33 suite passed. The new 40-contract suite reported 39 PASS / 1 FAIL due to a test assertion incorrectly expecting Finder to use the variable name `root`; actual Finder uses `page` and has the required early DOM guard. The assertion was corrected; rerun is required.
- Existing keys retained: `digital-store-cart`, `digital-store-wishlist`, `digital-store-wishlist-variants`, and `nivad-commerce-context`.
- Existing events retained: `cart:updated`, `wishlist:updated`, and the established sync-error events.
- No backend/API schema was changed or invented.

### Added validation

- `test-phase-34-js-integrity.php`: 40 read-only static contract checks.
- `.github/workflows/phase-34-js-integrity.yml`: Node.js ES module syntax checks across `assets/js/**/*.js`, PHP syntax checks, and execution of Phase 31, 33, and 34 static suites.

## Contract status

The 40 checks are encoded in `test-phase-34-js-integrity.php`. The first run passed 39/40; the only failure was a test assertion using the wrong DOM variable name, corrected to the actual `page` guard. After correcting the DOM guard assertion, GitHub Actions reported **40 PASS / 0 FAIL** for the Phase 34 suite; JavaScript syntax, PHP syntax, and the Phase 31+32 and Phase 33 suites also passed. Subsequent race/schema/listener fixes were committed afterward, so a fresh run on the latest head is still required. Static checks cannot prove browser runtime, live API behavior, assistive-technology behavior, or absence of every race condition.

## Required remaining work before lock

1. Get the Phase 34 CI workflow green and fix every real failure.
2. Review the full 40-contract output; do not waive false failures without evidence.
3. Audit request sequencing/race handling in search, catalog, finder, product detail, setup builder, cart, and wishlist against the actual code.
4. Review event producers/consumers, repeated initialization, and DOM data attributes across relevant pages.
5. Run browser/XAMPP runtime regression for desktop/tablet/mobile, keyboard/focus, header/menu, search, cart, wishlist, product detail/media, auth, checkout/payment, and storage blocked/invalid scenarios.
6. Report PASS/FAIL/BLOCKED evidence. Keep `LOCK=NO` until tests and runtime requirements are resolved or the user explicitly accepts a documented residual risk.

## Lock record

- LOCK: NO.
- Browser/XAMPP runtime regression: NOT VERIFIED.
- Race-condition audit across every module: IN PROGRESS.
