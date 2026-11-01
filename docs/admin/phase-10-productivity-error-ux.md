# Admin Phase 10 — Productivity and Error UX

Status: PARTIAL. This implementation is on a feature branch stacked on Phase 9. It is not PASS/LOCK.

Canonical scope: `ADMIN_PANEL_ROADMAP.md`, Phase 10 — 12 Batches.

## Baseline audit

- Global search already existed for products, orders, customers and support tickets, but the result contract was limited and the CMS/Admin directory were not included.
- A command/search input existed, but Ctrl/Cmd+K only focused the search field. No explicit server-validated command execution contract existed.
- `AdminBulkOperationRegistry` and `AdminBulkOperationPolicy` existed, but they only validated requests; no trusted execution service existed. Approval-required job types had no complete worker/execution proof.
- Saved-view validation and persistence existed, but filters were not constrained to module-specific field contracts. Repository SQL used `module` while the foundation migration defines `module_key`; it also selected/wrote `is_default`, which the foundation migration did not create.
- Queue monitoring already inspected `admin_operation_jobs` and `admin_email_outbox` through the Phase 4 incident repository.
- Existing payment-attempt data provides gateway/status telemetry, not live gateway connectivity.
- Inventory schema/service confirms `quantity`, `reserved_quantity` and `low_stock_threshold`.
- No verified backup/restore producer, Webhook producer/consumer, or customer-abuse policy/source was established by the source audit.

## Implemented

### Batch 10.01 — Global Search
- Search now includes Admin accounts and CMS pages/sections, in addition to products, orders, customers and support tickets.
- Each result type is filtered through its specific permission before it is returned.
- CMS search checks table existence before querying CMS tables.
- Still partial: not every possible Admin entity/module is indexed.

### Batch 10.02 — Command Palette
- Added a command palette with Ctrl/Cmd+K and a visible shared-shell trigger.
- Commands are explicit allowlisted IDs and are filtered by permissions on the server.
- Execution is server-validated and audited.
- Current commands navigate to authorized Admin modules; arbitrary write/business commands are not exposed.

### Batch 10.03 — Bulk Actions
- Added server-side transactional status changes for products, categories and coupons.
- The Admin UI sends selected row IDs and status to the server.
- Both global bulk permission and the specific module permission are checked.
- A failed item rolls back the entire batch.
- Other registry operations that need approval or a worker remain fail-closed; no fake asynchronous execution is claimed.

### Batch 10.04 — Saved Views
- Connected saved views to table search/status controls for products, categories, orders, customers, tickets, inventory and payments where the table is present.
- Saved views are owner-scoped and can be marked as default.
- Added module-specific field allowlists for filter/sort validation.
- Corrected repository SQL to use the foundation schema's `module_key`.
- Added a migration for the missing `is_default` column.
- Still partial: saved sort is validated and stored but is not yet applied to server-side queries; shared views and broader filter operators are not wired into table query builders.

### Batch 10.05 — Backup Awareness
- Added schema-evidence discovery for tables whose names include `backup`.
- The UI/API result explicitly states that this does not prove a backup file exists or that restore has been tested.
- No backup/restore job is claimed.

### Batch 10.06 — Queue Monitoring
- Reuses the existing Phase 4 queue monitor for `admin_operation_jobs` and `admin_email_outbox`.
- Other worker/queue systems are not inferred.

### Batch 10.07 — Webhook Monitoring
- Added schema discovery for webhook-related tables.
- Delivery status remains unverified; no Webhook pipeline was assumed from the presence of a payment integration.

### Batch 10.08 — Payment Gateway Health
- Added 24-hour gateway/status aggregates from persisted `payment_attempts`.
- This is telemetry only. No outbound connectivity check or live gateway health claim is made.

### Batch 10.09 — Inventory Anomalies
- Added read-only detection for negative quantity, negative reserved quantity, reserved quantity exceeding on-hand quantity, and low stock.
- Low stock is distinguished from data-integrity anomalies.
- Uses columns already present in `InventoryRepository`.

### Batch 10.10 — Customer Abuse Signals
- BLOCKED: no verified customer-abuse event source or approved policy was established.
- The endpoint returns a blocked state and does not automatically label, restrict or penalize customers.

### Batch 10.11 — Global Error UX
- Added an accessible global Admin error banner with a reload recovery action.
- Safe GET requests receive one retry on network failure and selected transient gateway/server responses.
- Mutation requests are never automatically retried.

### Batch 10.12 — JS Error Boundary
- Added global browser error and unhandled-rejection capture.
- Only metadata is sent to the existing rate-limited incident endpoint; raw message, stack trace and form/query values are not sent.
- This is a global first-pass boundary, not a per-component framework boundary.

## Batch status summary

| Batch | Scope | Status |
|---|---|---|
| 10.01 | Global Search | PARTIAL |
| 10.02 | Command Palette | PARTIAL |
| 10.03 | Bulk Actions | PARTIAL |
| 10.04 | Saved Views | PARTIAL |
| 10.05 | Backup Awareness | PARTIAL |
| 10.06 | Queue Monitoring | PARTIAL |
| 10.07 | Webhook Monitoring | UNKNOWN / DEPENDENCY UNKNOWN |
| 10.08 | Payment Gateway Health | PARTIAL |
| 10.09 | Inventory Anomalies | IMPLEMENTED — runtime pending |
| 10.10 | Customer Abuse Signals | BLOCKED |
| 10.11 | Global Error UX | PARTIAL |
| 10.12 | JS Error Boundary | PARTIAL |

## Database migration

New additive migration:
- `database/migrations/20261101_admin_productivity_phase10.sql`

It adds only `admin_saved_views.is_default` when that column is absent. It does not drop or rewrite saved-view data.

Execution order:
1. Confirm `SELECT DATABASE()` returns `nivad_store`.
2. Run `database/verification/20261101_admin_productivity_phase10_preflight.sql`.
3. Confirm `admin_saved_views` exists and its `module_key` column is present.
4. Back up the database, then run the migration.
5. Run `database/verification/20261101_admin_productivity_phase10_verify.sql`.
6. Test saved-view create/load/default behavior in XAMPP and browser.

## Automated verification

- Phase 10 static contracts: 25/25 PASS.
- Phase 10 PHP syntax checks: PASS.
- Phase 10 JavaScript module syntax checks: PASS.
- Phase 7 regression contracts: 50/50 PASS.
- Phase 8 regression contracts: 10/10 PASS.
- Phase 9 regression contracts: 16/16 PASS.
- Admin UI Shell and Dashboard integrity workflows: PASS.
- Product Analytics regression workflow: PASS.

Known unrelated baseline:
- Admin Foundation Regression still fails in `test-admin-nondb-foundations.php:135` with `Call to undefined method AdminAccountPolicy::validateCreate()`. The same failure existed before Phase 10 and is not introduced by this branch.

## Not verified

No local XAMPP/MySQL, browser interaction, backup/restore, live gateway connectivity or external Webhook delivery was executed. CI/static PASS does not establish those runtime conditions.

**No PASS/LOCK claim for Phase 10.**
