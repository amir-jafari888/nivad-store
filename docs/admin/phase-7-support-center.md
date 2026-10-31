# Admin Phase 7 — Support Center

Status: implementation on feature branch; database, XAMPP and browser verification pending.

Canonical source: `ADMIN_PANEL_ROADMAP.md`, Phase 7 — Support Center.
Exactly 5 Batches × 10 Sub-steps = 50 contracts.

## Scope and implementation contract

- SLA targets are configurable per priority. The migration creates priority rows with NULL targets and inactive status; no unapproved response-time promise is invented.
- SLA duration currently means elapsed calendar minutes. Business hours, holidays, working calendars and pause-on-customer-wait rules are not defined in the canonical roadmap and are not inferred here.
- First response means the first public Admin message on a customer-created ticket. Internal notes and Admin-created tickets are excluded.
- Resolution time uses `resolved_at` or `closed_at`; unresolved tickets are measured against current database time.
- Status history records transitions observed after the Phase 7 migration. Existing historical transitions are not fabricated.
- The existing support subsystem is retained. The additive migration reconciles its repository-facing columns with the canonical foundation table and creates the missing message/history/SLA tables.
- The migration is manual and must be applied only after backup and schema preflight.

## Batch 01 — SLA (07.01–07.10)

1. [x] Audit existing priority and status contracts.
2. [x] Add per-priority SLA policy persistence.
3. [x] Store first-response and resolution targets separately.
4. [x] Seed the four known priorities without invented target values.
5. [x] Add read API for SLA policies.
6. [x] Add write API for SLA policy configuration.
7. [x] Validate target bounds and priority vocabulary.
8. [x] Require resolution target to be at least the first-response target.
9. [x] Protect reads/writes with explicit support permissions and CSRF on writes.
10. [ ] Verify policy save/read against the user's MySQL schema.

## Batch 02 — Response Metrics (07.11–07.20)

1. [x] Define first response as first public Admin message.
2. [x] Exclude internal notes from response calculations.
3. [x] Exclude Admin-created internal tickets from customer response averages.
4. [x] Calculate elapsed minutes to first response.
5. [x] Count customer tickets awaiting a first response.
6. [x] Calculate first-response SLA success counts.
7. [x] Calculate first-response SLA breach counts.
8. [x] Calculate resolution SLA success and breach counts.
9. [x] Add bounded date-range metrics endpoint.
10. [ ] Verify metric calculations against real MySQL ticket/message fixtures.

## Batch 03 — Advanced Filters (07.21–07.30)

1. [x] Filter by ticket status.
2. [x] Filter by priority.
3. [x] Filter by assigned Admin.
4. [x] Filter by customer ID.
5. [x] Filter by creation start date.
6. [x] Filter by creation end date.
7. [x] Search ticket number.
8. [x] Search subject.
9. [x] Search exact ticket ID.
10. [ ] Verify combined filters, empty results and pagination/limit behavior in browser.

## Batch 04 — Status History (07.31–07.40)

1. [x] Add append-only status history table.
2. [x] Record initial status for newly created Admin tickets.
3. [x] Record initial status for newly created customer tickets.
4. [x] Lock ticket row before Admin status transition.
5. [x] Persist previous and next status in one transaction.
6. [x] Record authenticated Admin actor on Admin transitions.
7. [x] Record customer-triggered transition to pending_admin.
8. [x] Expose ordered status history in ticket detail.
9. [x] Render history in the Admin ticket detail UI.
10. [ ] Verify transaction rollback, concurrent updates and timeline ordering on MySQL.

## Batch 05 — Support Reports (07.41–07.50)

1. [x] Validate report date range and cap it at one year.
2. [x] Aggregate ticket counts by status.
3. [x] Aggregate ticket counts by priority.
4. [x] Calculate average first response by status/priority.
5. [x] Aggregate ticket volume by assigned Admin.
6. [x] Calculate completed ticket count by assigned Admin.
7. [x] Calculate average first response by assigned Admin.
8. [x] Calculate average resolution time by assigned Admin.
9. [x] Render support metrics and report series in Admin UI.
10. [ ] Reconcile report totals with a real database sample and browser session.

## Files

- `app/Repositories/AdminSupportRepository.php`
- `app/Services/AdminSupportService.php`
- `app/Controllers/AdminCapabilityController.php`
- `app/Repositories/AdminCapabilityRepository.php`
- `app/Services/AdminCapabilityService.php`
- `app/Core/AdminRoutePermissionMap.php`
- `routes/api.php`
- `admin/capabilities/index.php`
- `admin/assets/js/modules/capability-center.js`
- `database/migrations/20261031_admin_support_phase7.sql`
- `database/verification/20261031_admin_support_phase7_verify.sql`
- `test-admin-phase-7-support-center.php`
- `.github/workflows/admin-phase-7-support-center.yml`

## Verification gates

- Static contracts: `php test-admin-phase-7-support-center.php`
- PHP syntax: all changed PHP files.
- JavaScript syntax: Admin capability center module.
- CI: `.github/workflows/admin-phase-7-support-center.yml`
- XAMPP/MySQL: apply the migration manually after backup; run the read-only verification SQL.
- Browser: test filters, policy configuration, metrics, ticket status history and reports.
- SLA values remain inactive until a manager configures actual target minutes.

## Current gate

- Implementation branch: `feature/admin-phase-7-support-center-2026-10-31`
- Base: `master` at `d2a0b73886f31f1b289150f182f7ea41bc1c5314`
- Migration: additive; no DROP/DELETE; manual execution only.
- Static contract test: pending CI execution.
- PHP/JS syntax: pending CI execution.
- XAMPP/MySQL runtime: pending.
- Browser verification: pending.
- PASS: not granted.
- LOCK: NO.
