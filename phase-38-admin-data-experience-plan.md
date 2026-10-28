# Phase 38 — Admin Data Experience

## Scope
Implements the frontend layer for the Phase 38 roadmap items: tables, client-side search and filters, client-side pagination, form submit states, safe bulk selection/export, empty/loading/error feedback announcements, and declared-permission UI hooks.

## Implementation decisions grounded in repository evidence
- Backend remains frozen. No files under `app/`, `routes/`, database migrations, or API contracts were changed.
- `routes/api.php` has no bulk-action endpoints and no declared server-side pagination parameters found during this audit. Therefore, pagination and filtering in this phase operate only on rows already loaded into the current page; the UI explicitly says this.
- Bulk infrastructure currently supports selection, clear-selection, and CSV export only. It does not send bulk mutations or simulate a backend bulk API.
- `AdminAuthMiddleware` checks the broad `admin.access` permission. `AdminRoutePermissionMiddleware` exists but is not referenced in `routes/api.php` (0 occurrences in the audited file). The frontend adds a fail-closed hook for controls explicitly marked with `data-admin-permission` when a page supplies `data-admin-permissions`, but this is not a replacement for server-side authorization. Per-route permission enforcement remains a backend dependency under Backend Freeze.
- The existing theme module and Admin token architecture are preserved.

## Files
- Added `admin/assets/js/modules/data-experience.js`.
- Updated `admin/assets/js/main.js` to register the module.
- Updated the Admin table styles for Mobile, Tablet, and Desktop.
- Connected Admin CSS/JS assets to existing Admin pages with HTML document shells, including list and form/detail screens where found.
- Added `test-phase-38-admin-data-experience.php`, a 40-check read-only static test.
- Added this plan.

## Behavior
- Tables receive search, optional status filter, configurable client-side page size, accessible status text, selection, and CSV export.
- Search/filter/pagination are local to currently loaded records. They do not claim server-side pagination.
- Form submit buttons are protected against duplicate standard submissions; handlers that already prevent default (AJAX forms) are left to their existing handler.
- Feedback containers receive live-region semantics and error announcements.
- Permission hooks are fail-closed for controls that explicitly opt into the permission contract.

## Verification status
- Static test file is added but has not been executed in PHP/XAMPP from this GitHub workflow.
- PHP lint, browser rendering, real API payloads, and database-backed interactions remain unverified.
- Browser visual review remains deferred until all pages have been designed at all three viewport sizes, per project direction.
- Phase status: IN PROGRESS. PASS = NOT GRANTED. LOCK = NO.
