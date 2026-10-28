# Phase 37 — Admin Dashboard Implementation Plan

## Status
- Implementation branch: `phase-37-admin-dashboard`
- Scope: Admin dashboard frontend and its existing dashboard metrics presentation.
- Backend Freeze: preserved. `AdminDashboardMetricsService` retains its original four-key output contract.
- Browser review: deliberately deferred until all Admin pages are designed for Mobile, Tablet, and Desktop.
- Current status: IN PROGRESS — no PASS or LOCK granted.

## Batch 01 — Audit and contracts (37.01–37.10)
- Preserved dashboard route/link targets and CSRF logout form.
- Preserved the established `orders`, `payments`, `order_items`, and `inventory` metrics keys.
- Confirmed current summary field names from `ReportRepository`.
- Documented the existing `salesRevenueByPeriod` contract as available for a later, explicitly approved data-wiring change.
- Date/grouping contract remains `Y-m-d`, exclusive end date, and `day|week|month`.
- Activity Feed and Alerts data providers remain UNKNOWN/not confirmed.

## Batch 02 — Dashboard shell and KPI (37.11–37.20)
- Replaced the malformed legacy dashboard markup with semantic Persian RTL HTML.
- Added Admin-owned stylesheet and existing theme module references.
- Added responsive shell, topbar, sidebar navigation, user label and CSRF-protected logout form.
- Added four data-driven KPI cards using the existing metric keys.
- Added number formatting and state copy; no synthetic totals or growth percentages.

## Batch 03 — Charts, tables, Activity and Alerts (37.21–37.30)
- Added UI structures for sales net amount and daily order-count trends, using the established report row field names when data is supplied.
- Added a period summary table and overflow containment.
- Because Backend Freeze is active and the current dashboard controller/service does not supply period rows, the UI explicitly shows a dependency/empty state rather than fake chart data.
- Activity Feed and Alerts show documented dependency states; no invented events, alerts, endpoints, or fields.
- Actual trend data wiring is deferred until Backend Freeze is lifted or an existing approved data path is confirmed.

## Batch 04 — Responsive, theme, RTL and integration (37.31–37.40)
- Added dashboard-specific Mobile (<=600px), Tablet (601–1009px), and Desktop (>=1010px) layout and component styles to the existing viewport files.
- Reuses Admin-owned tokens and the existing theme module; does not create a parallel token system.
- Adds accessible chart labels, table headers, focus styling, and status messaging.
- Static regression test added at project root: `test-phase-37-admin-dashboard.php`.
- The static test has not yet been executed in PHP/XAMPP. PHP lint/runtime, data integration, keyboard walkthrough, visual breakpoints, theme contrast, and browser regression remain unverified.

## Required next checks
1. Run `php -l pages/admin/dashboard.php`.
2. Run `php -l app/Services/AdminDashboardMetricsService.php`.
3. Run `php test-phase-37-admin-dashboard.php` from project root and repair any failures.
4. Verify real DB-backed dashboard rendering in XAMPP.
5. After all Admin pages have three responsive designs, perform the deferred browser review.
6. Do not set PASS/LOCK until evidence is recorded.
