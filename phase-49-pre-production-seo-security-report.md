# Phase 49 — Pre-Production SEO & Security Audit

## Scope
- Roadmap: `front-roadmap.txt`, Phase 49.
- Backend remains frozen. This phase performs frontend/production audit and static verification only.
- Test entrypoint: `php test-phase-49-pre-production-seo-security.php`
- CI workflow: `.github/workflows/phase-49-pre-production-seo-security.yml`

## Contract batches
1. `49.01–49.10` SEO Metadata
2. `49.11–49.20` Canonical & Indexation
3. `49.21–49.30` Robots & Sitemap
4. `49.31–49.40` Structured Data & Social Metadata
5. `49.41–49.50` URL, Routing & Redirect Integrity
6. `49.51–49.60` HTTPS & Security Headers
7. `49.61–49.70` Output Encoding & XSS Surface
8. `49.71–49.80` Session, CSRF & Form Security Integration
9. `49.81–49.90` Asset Security & CSP Compatibility
10. `49.91–49.100` Pre-Production Regression & Release Gate

## Confirmed audit observations
- Shared header emits escaped title, optional description, robots and conditional canonical metadata.
- Product/category/brand/search/discovery pages define metadata contracts.
- Robots and sitemap have rewrite rules and API routes; Sitemap includes active products and categories.
- HTTPS enforcement and baseline security headers are present in source.
- Session cookie and CSRF configuration are present in the security configuration.
- The shared header previously contained an inline script while the CSP set `default-src 'self'`. The inline script was removed because repository code search did not establish a consumer for `window.NIVAD_BASE_URL`; the shared `$basePath` variable remains available to PHP templates. The header now emits Open Graph and Twitter Card title/description metadata, and Open Graph URL when a canonical URL exists. Browser console verification remains required.
- Open Graph/Twitter metadata presence and full structured-data coverage are not established by source inspection alone; do not invent them.

## Runtime blockers
1. Live HTTP responses, redirect chains, and content types for `robots.txt` and `sitemap.xml`.
2. Browser-rendered canonical/robots/social/structured-data validation.
3. CSP console verification for inline styles/scripts and all required external assets.
4. Authenticated session/CSRF/API and checkout/cart flow tests.
5. Full DOM-XSS, Admin and external-asset regression in a running browser/application environment.

## Release gate
Static checks are read-only and do not replace HTTP/browser/security testing. Phase status must remain `PARTIAL` and `LOCK=NO` while runtime blockers remain. No backend routes, controllers, middleware or database schema are changed by this phase.
