# NIVAD Admin Panel — Non-Database Infrastructure Progress
**Date:** 2026-10-29  
**Branch:** `feature/admin-panel-clean-2026-10-29`  
**Pull request:** #45 — draft; not merged. PR #44 was closed without merging because its branch carried 189 commits of ancestry ahead of `master`; this clean branch is a single snapshot based directly on `master`.  
**Scope:** Implement and connect reusable non-database infrastructure. UI is intentionally deferred. Database execution, target-schema verification, and production delivery remain explicitly gated.

## What is now implemented

### Shared capability and authorization contracts
- `app/Core/AdminFeatureRegistry.php`: canonical registry for all 44 ADM feature IDs, required permission identifiers, dependencies, areas, and DB/external-dependency flags.
- `app/Core/AdminPermissionCatalog.php`: canonical permission vocabulary now includes the legacy shell permission `admin.access` as well as granular feature permissions.
- `app/Services/AdminFeatureContractValidator.php`: validates feature metadata, permission references, dependency references, and cycles without a DB connection.
- `app/Services/AdminFeatureAccessService.php`: connects a feature ID to `AuthorizationService`; unknown features fail closed. DB-backed authorization construction remains deferred where possible.
- `app/Core/AdminRouteFeatureMap.php` and `app/Core/AdminRoutePermissionMap.php`: route families and exact-route permission contracts are explicit; the route audit reports zero unmapped admin routes.
- `app/Middleware/AdminFeatureMiddleware.php` and `app/Middleware/AdminRoutePermissionMiddleware.php`: session-first authorization middleware is prepared and fails closed on unmapped routes. Global granular attachment remains gated until real role/permission assignments are reviewed.
- `app/Core/AdminRequestContext.php`: correlation ID and `X-Request-ID` handling.
- `app/Core/AdminHttpErrorResponder.php` and `app/Core/ErrorHandler.php`: browser/API error separation with sanitized 500 responses and request IDs.
- `app/Core/AdminAuditSnapshotService.php` / `AdminActivityAuditService`: centrally redacted before/after snapshots and validated actor context connected to audit writing. Persistence remains gated.
- `app/Core/AdminFoundationRepositoryInterface.php`: persistence boundary for foundation workflows; the repository implementation exists, while contract tests can inject an in-memory fake.
- `app/Core/AdminCapabilityContractRegistry.php`: canonical non-UI command contracts for 14 remaining capability domains, including permissions, read/write semantics, DB boundaries, and audit requirements.
- `app/Services/AdminCapabilityReadinessService.php`: validates those capability contracts and reports `non_database_ready`, `database_blocked`, or `unknown` without opening storage.

### Shared security controls
- `app/Core/SensitiveDataRedactor.php`: central recursive redaction for passwords, tokens, secrets, cookies, payment identifiers, credentials, oversized values, unsupported values, and excessive nesting.
- Connected audit snapshots, admin notification payloads, and shared foundation payloads to the central redactor.
- `app/Services/AdminOperationPolicy.php`: explicit confirmation, approval, expiry, and separation-of-duties policy.
- `app/Services/AdminAccountPolicy.php`: account creation/password baseline, self-disable/delete protection, last-active-Super-Admin protection, and role-escalation rules. Policy is complete at the non-DB boundary; persistence remains gated.
- `app/Services/AdminSupportTicketPolicy.php`: validates ticket creation, message authorship/visibility, and lifecycle transitions; conversation persistence remains gated.
- `app/Services/AdminSecurityEventPolicy.php`: canonical event classification for auth, permission, account, session, sensitive-data, bulk-job, and delivery events.
- `app/Services/AdminNotificationPolicy.php`: event/severity validation, priority, email threshold, rate-limit decision contract, and deterministic recipient-aware deduplication key.
- `app/Services/AdminHealthCheckService.php`: non-invasive PHP/extensions/source readiness checks, now including capability registry/readiness files. It does not connect to MySQL or the network and reports DB readiness as `blocked`.
- `app/Services/AdminMediaStorageService.php`: validates image MIME/content/dimensions/size/hash and can store files under an explicitly configured private absolute path outside the web root. Metadata persistence remains gated.
- `app/Services/AdminGlobalSearchService.php` and `app/Core/AdminSearchProviderInterface.php`: permission-aware provider orchestration, bounded queries/results, result normalization, deduplication, and rejection of external/unsafe result URLs. Real data providers remain DB-gated.
- `app/Services/AdminPreviewTokenService.php`: short-lived HMAC-signed preview tokens bound to entity/admin identity; replay prevention requires persistence and remains gated.
- `app/Services/AdminSavedViewValidator.php`: safe filter/sort validation; per-module SQL field allowlists remain provider-level work.
- `app/Services/AdminContentPublishingPolicy.php`: validates publish-state transitions and normalizes explicit-timezone schedules to UTC. Persistence remains gated.
- `app/Services/AdminReportExportService.php`: bounded CSV serialization with safe field keys, Persian text support, and spreadsheet-formula injection protection. Data-provider wiring remains gated.
- `app/Core/AdminBulkOperationRegistry.php` / `AdminBulkOperationPolicy.php`: allowlisted job types, bounded batches, explicit confirmation, redaction, and approval-reference requirements. Worker execution and durable state remain gated.

### Gmail / notifications
- `app/Services/GmailSmtpTransport.php`: Gmail-only SMTP transport using environment variables, TLS verification, header validation, and stable non-secret error codes. No SMTP connection is made unless `send()` is called.
- `AdminNotificationService` applies event/severity policy, redacts payloads, and validates/enqueues email requests through the outbox boundary.
- `app/Services/AdminEmailOutboxWorker.php` plus `app/Core/AdminEmailOutboxRepositoryInterface.php`: batch-limited sender orchestration, success tracking, permanent-failure handling, capped exponential retry, and fakeable sender tests. Atomic DB claiming/status updates remain gated.
- Durable outbox state, DB-backed deduplication/rate-limit state, retries, and real Gmail delivery are not claimed complete.

### Database-facing primitives prepared but NOT executed
- Existing branch includes an additive migration for the shared foundation tables and an idempotent permission-definition seed.
- Repositories/services cover audit snapshots, admin-session inventory, recipient-scoped notifications/outbox, content revisions, saved views, operation jobs, incidents, approval requests, media metadata, support tickets, and analytics events.
- No migration or seed has been run. No local/production DB has been opened or modified.

## Non-UI capability contract coverage
The non-UI command layer now has explicit contracts for:
1. Admin accounts — list/create/update/enable/disable/soft-delete/restore/role/password operations.
2. Admin sessions — inventory and revocation commands.
3. Audit — list/detail/diff/timeline/export.
4. Notifications — inbox/read state/creation/email queue.
5. Content — revisions/preview/publish/schedule/rollback.
6. Bulk operations — validation/enqueue/status/cancel/retry/saved views.
7. Approvals — request/list/detail/approve/reject.
8. Incidents — register/list/detail/assign/resolve.
9. Media — upload validation/file storage/metadata/attach/detach/delete.
10. Support — create/list/detail/assign/reply/transition.
11. Analytics — event/aggregation/time-series/top-entity contracts.
12. Global search — search/suggest provider contracts.
13. Reports — summary/orders/payments/inventory/customers/CSV export contracts.
14. Settings — read/update contracts.

The contracts do **not** fake DB results. Any operation marked `database_required` remains explicitly blocked until the real schema is supplied and reviewed.

## Route audit result
Static inspection of `routes/api.php` found **161 named admin API routes**:
- All 161 include `AdminAuthMiddleware`.
- All 161 have an explicit permission contract (zero missing contracts).
- All mutating admin API routes include `CsrfMiddleware`.
- 14 route names do not belong to a canonical feature bucket, but have explicit exact-route permission contracts.
- The existing route table has not yet been changed to apply granular feature middleware globally. This remains gated until real role/permission assignments are verified.

## Automated checks
The existing successful Admin Panel run remains:
- Authentication/authorization regression: **21 PASS, 0 FAIL**
- Shared foundation regression: **30 PASS, 0 FAIL**
- Non-database foundation contracts: **67 PASS, 0 FAIL**
- Admin route contract audit: **7 PASS, 0 FAIL**

A new `test-admin-capability-contracts.php` and corresponding CI step have been added to cover the 14-domain capability contract layer and DB gates. GitHub Actions execution for the newest commit must be treated as pending until a run result is available; no local test execution was possible in this environment because direct GitHub network access is unavailable.

These are source/static/unit checks, not a live XAMPP/browser/database/Gmail integration run. No SQL is executed by the non-DB contract tests.

## Explicit database gates — do not pass without review
1. Read the actual local schema and current role/permission assignments.
2. Review the additive migration against that schema; verify backup and restore before applying.
3. Apply migration and permission seed only in a controlled target environment after review.
4. Verify the actual Super Admin role slug/status/deletion state and permission matrix.
5. Wire granular middleware to the existing 161 routes after validating the exact permission map and current role assignments.
6. Implement/verify DB adapters and run DB-backed tests for admin account lifecycle and role assignment, session inventory/revocation, audit persistence, notification/read states/outbox, content revisions/scheduling, job claiming/state updates, incident resolution, approval state transitions, media metadata, support messages, and analytics.
7. Configure Gmail app-password environment variables outside Git, then run a deliberate delivery test and verify outbox status/retry behavior.
8. Configure `NIVAD_PRIVATE_STORAGE_PATH` outside the document root before accepting uploads; connect returned metadata to the DB only after schema approval.

## Status interpretation
UI is intentionally deferred. The non-UI boundary is now covered by explicit operation contracts, pure policies, storage interfaces, security controls, search orchestration, report export, media storage validation, Gmail transport/outbox orchestration, and readiness checks. The remaining blockers are the real DB schema/role data and live external-service configuration. `master` has not been merged.
