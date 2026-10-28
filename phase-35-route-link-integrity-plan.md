# NIVAD — Phase 35: Route & Link Integrity

## Source of truth
- Roadmap: `front-roadmap.txt`, Phase 35 heading and scope.
- UI/UX constraints: `uiuxrules.md` — no guessing; preserve existing behavior and verify actual project contracts.
- Base branch: `phase-34-js-integrity`.
- Working branch: `phase-35-route-link-integrity`.

## Roadmap scope (verbatim topics, organized for execution)
The roadmap names Phase 35 **Route & Link Integrity** and calls for auditing:
Links, CTA, Forms, Routes, Redirects, Back Navigation, Broken Routes, Unauthorized Routes.

The 40 numbered checks below are an execution breakdown for the audit, not literal numbered entries copied from the roadmap.

## Batch 01 — Link & CTA Integrity
1. Internal page links resolve to an existing page or defined route.
2. Header navigation destinations match available storefront routes.
3. Footer destinations resolve and preserve base-path behavior.
4. CTA destinations match the intended user flow.
5. Product-card destinations use valid product URLs/identifiers.
6. Category, brand, and collection links match available routes and parameters.
7. Search/discovery links preserve supported query parameters.
8. Cart/wishlist links and unauthenticated login destinations resolve.
9. Authentication/account links point to existing pages and supported routes.
10. Admin navigation links resolve to the intended admin directories/pages.

## Batch 02 — Route & Parameter Integrity
11. Every static `.htaccess` rewrite target exists.
12. Storefront clean URLs map to the expected page handlers.
13. Product-detail slug handling matches the generated product URLs.
14. Category and brand parameters match their handlers.
15. Search, discovery, collection, and finder destinations preserve supported parameters.
16. Cart, checkout, and payment paths match existing handlers.
17. Authentication page URLs and API endpoints are distinguished correctly.
18. Account and customer-commerce links target existing pages/routes.
19. Admin directory routes and API endpoints are not conflated.
20. Web/API route declarations use valid handlers and middleware contracts.

## Batch 03 — Forms, Redirects & Navigation
21. Forms submit to the intended handler or are intentionally JS-managed.
22. Form HTTP methods match server-side route methods.
23. Form field names match the receiving contract.
24. Login/register/recovery links resolve to existing pages.
25. Login/logout redirects target valid destinations.
26. Checkout/payment redirects use supported paths and parameters.
27. Error returns keep users on a valid recoverable page.
28. Back navigation has a safe fallback when history is unavailable.
29. Query strings and route parameters are preserved only where supported.
30. Redirect targets are validated and do not rely on nonexistent page-query routing.

## Batch 04 — Broken Routes, Access & Regression
31. Identify links to missing files and undefined clean routes.
32. Identify duplicate/conflicting rewrite and application routes.
33. Check route declarations against HTTP methods used by clients.
34. Check missing, malformed, and unsupported route parameters.
35. Check protected-route redirects and unauthorized responses.
36. Check customer/admin route boundaries and middleware declarations.
37. Check expected 404/405 handling without claiming runtime proof from static inspection.
38. Check base URL/base path usage across route and link generation.
39. Regress storefront, customer commerce, account, auth, and admin navigation.
40. Run the final route/link integrity suite; lock only after all checks pass and runtime testing is available.

## Implementation log

### Confirmed and fixed on this branch
- `pages/shop/checkout.php`: changed the unauthenticated login destination from `/auth/login.php` to the existing `/pages/auth/login.php`.
- `pages/shop/wishlist.php`: changed the unauthenticated login destination from `/auth/login.php` to the existing `/pages/auth/login.php`.
- `assets/js/modules/payment.js`: changed the payment-success order-history link from the unsupported `/?page=account/orders/index` query destination to the existing `/pages/account/orders/index.php`.

### Test added
- `test-phase-35-route-link-integrity.php`: read-only static checks for rewrite target files, authentication links, JavaScript entry imports, customer commerce pages, and the payment order-history destination.
- The test has been added to GitHub but has **not** been executed in a PHP/XAMPP runtime in this session. Static source review is not a substitute for execution.

## Current status
- Phase: IN PROGRESS
- Batch 01: PARTIAL — confirmed broken destinations corrected; full link/CTA inventory and runtime verification remain.
- Batch 02: NOT VERIFIED
- Batch 03: NOT VERIFIED
- Batch 04: NOT VERIFIED
- LOCK: NO
- No claim is made that the phase or any full batch has passed.
