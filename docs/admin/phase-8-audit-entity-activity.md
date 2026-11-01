# Admin Phase 8 — Audit and Entity Activity

Status: PARTIAL — initial implementation and audit timeline API are on a feature branch. This phase is not PASS/LOCK.

Canonical scope: `ADMIN_PANEL_ROADMAP.md`, Phase 8 — 4 Batches. The implementation must not claim complete coverage until each legacy mutation path is verified.

## Baseline audit

- The project already has `audit_logs`, `AuditLogRepository`, `AuditLogService` and `AdminAuditTrail`.
- The existing log schema contract includes actor, action, module, entity type/id, description, before/after JSON, IP, User-Agent and timestamp.
- Existing confirmed audit producers include Admin account/security operations, authentication events, product create/update, CMS section operations, support operations, capability operations and customer support events.
- The existing audit page is `admin/audit-logs/index.php`.
- The existing permission catalog already defines `admin.audit.view`; no new permission seed or schema migration is required for the timeline endpoint.
- Coverage for legacy category/brand CRUD, pricing, stock mutations, order status changes and refunds is not yet proven complete. Those paths remain AUDIT REQUIRED / PARTIAL; no blanket coverage claim is made.
- The real production/local `audit_logs` schema and engine were not queried from this environment.

## Implemented in this branch

- Added validated per-entity timeline query against the existing `audit_logs` table.
- Added actor-name join against `admin_users`.
- Added bounded pagination using a descending record ID cursor.
- Timeline response deliberately excludes IP address and User-Agent.
- Before/after JSON snapshots are redacted through the existing `SensitiveDataRedactor` before being returned.
- Added authenticated and permission-mapped API route using the existing `admin.audit.view` permission.
- Extended the existing audit-log page with entity type/ID lookup and timeline rendering using text nodes (no HTML injection for event values).
- Added a static contract test file. It has not been executed in a PHP runtime in this environment.

## Batch status

### Batch 01 — Legacy CRUD Coverage
- Existing audit helper and confirmed producers identified.
- Complete legacy CRUD mutation inventory and missing hooks: NOT COMPLETE.
- Category, brand, product lifecycle, variants, attributes, inventory and related legacy operations need route-by-route verification.
- Status: PARTIAL.

### Batch 02 — Business Operations
- Existing evidence confirms support for some Admin operations, but complete price, stock, order-status and refund event coverage is not established.
- Status: PARTIAL / AUDIT REQUIRED.

### Batch 03 — Content Operations
- CMS section create/update/publish and related operations already emit audit events in the current code.
- Category and brand content/catalog mutations are not yet verified as fully covered.
- Status: PARTIAL.

### Batch 04 — Entity Activity Timeline
- Read API, actor join, pagination, permission mapping and audit page UI implemented.
- Runtime DB and browser verification pending.
- Status: IMPLEMENTED / RUNTIME PENDING.

## Verification gates

- Static test: `php test-admin-phase-8-audit-entity-activity.php`
- PHP syntax: changed PHP files.
- Runtime: verify the actual `audit_logs` columns and run entity timeline requests against XAMPP/MySQL.
- Security: verify the active Admin role has `admin.audit.view`; inspect redaction output with representative audit snapshots.
- Browser: verify entity type/ID lookup, empty state, long timelines and pagination.
- Mutation coverage: audit each legacy write route and add before/after audit hooks only after the actual mutation contract is confirmed.

## Dependency

This Phase 8 branch is intentionally based on Phase 7 branch `feature/admin-phase-7-support-center-2026-10-31`. Merge Phase 7 first, then retarget/merge Phase 8. Do not merge Phase 8 directly into master while its Phase 7 dependency remains unmerged.

No migration is introduced by the current timeline work. No database runtime, XAMPP or browser PASS is claimed.
