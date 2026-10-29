# NIVAD Admin Panel — Non-Database Infrastructure Progress
**Date:** 2026-10-29  
**Branch:** `feature/admin-panel-implementation-2026-10-29`  
**Pull request:** #44 — draft; not merged  
**Scope:** Implement and connect reusable non-database infrastructure. Database execution, target-schema verification, and production delivery remain explicitly gated.

## What is now implemented

### Shared capability and authorization contracts
- `app/Core/AdminFeatureRegistry.php`: canonical registry for all 44 ADM feature IDs, required permission identifiers, dependencies, areas, and DB/external-dependency flags.
- `app/Core/AdminPermissionCatalog.php`: canonical permission vocabulary now includes the legacy shell permission `admin.access` as well as the granular feature permissions.
- `app/Services/AdminFeatureContractValidator.php`: validates feature metadata, permission references, dependency references, and cycles without a DB connection.
- `app/Services/AdminFeatureAccessService.php`: connects a feature ID to `AuthorizationService`; unknown features fail closed.
- `app/Core/AdminRouteFeatureMap.php`: explicit mapping for route families with a clear feature contract. Ambiguous/unregistered route families intentionally return no mapping.
- `app/Core/AdminRoutePermissionMap.php`: exact-route permission contracts for all 14 routes that do not fit one canonical feature bucket; the route audit now finds zero routes without a permission contract.
- `app/Middleware/AdminFeatureMiddleware.php`: authenticates the admin session before checking the feature permission.
- `app/Middleware/AdminRoutePermissionMiddleware.php`: resolves the named route to an exact permission contract (feature-derived or exact-route override) and enforces it; it validates the session first and fails closed on unmapped admin routes. It is prepared but not globally attached until DB role assignments are reviewed.
- `app/Core/AdminRequestContext.php`: generates/validates a correlation ID and publishes `X-Request-ID` on protected responses. Persistence of that ID in the existing audit schema is not claimed.
- `app/Core/AdminHttpErrorResponder.php` and `app/Core/ErrorHandler.php`: browser requests now receive a localized HTML error document while API/JSON requests receive JSON status responses; 500-level responses do not expose exception messages, and logs omit raw exception messages while recording a request ID.
- `app/Services/AdminAuditSnapshotService.php`: produces centrally redacted before/after snapshots and a bounded deterministic field-level change list; connected to the audit writer. Database persistence is still gated.
- `app/Core/AdminActorContext.php`: validates the current admin session before deriving an actor ID and rejects caller-supplied actor IDs that do not match the authenticated identity. Connected to foundation mutations, audit writing, and admin notification creation.

### Shared security controls
- `app/Core/SensitiveDataRedactor.php`: central recursive redaction for passwords, tokens, secrets, cookies, payment identifiers, credentials, oversized values, unsupported values, and excessive nesting.
- Connected audit snapshots, admin notification payloads, and shared foundation payloads to the central redactor.
- `app/Services/AdminOperationPolicy.php`: pure policy for explicit confirmation, approval requirements, expiry, and separation of duties for sensitive actions.
- `app/Services/AdminAccountPolicy.php`: account creation/password baseline, self-disable/delete protection, last-active-Super-Admin protection, and role-escalation rules. It is policy-only; the DB-backed account manager is still gated.
- `app/Services/AdminSupportTicketPolicy.php`: validates ticket creation, message authorship/visibility, and lifecycle transitions; conversation persistence remains a DB/schema gate.
- `app/Services/AdminSecurityEventPolicy.php`: canonical event classification for auth, permission, account, session, sensitive-data, bulk-job, and delivery events.
- `app/Services/AdminNotificationPolicy.php`: event-key/severity validation, priority, configurable email threshold, rate-limit decision contract, and deterministic recipient-aware deduplication key.
- `app/Services/AdminHealthCheckService.php`: non-invasive PHP/extensions/source readiness checks. It does not connect to MySQL or the network and reports database readiness as `blocked`.
- `app/Services/AdminMediaStorageService.php`: validates image MIME/content/dimensions/size/hash and can store uploads under an explicitly configured private absolute path outside the web root. It does not write metadata to the DB.
- `app/Services/AdminGlobalSearchService.php` and `app/Core/AdminSearchProviderInterface.php`: permission-aware provider orchestration, bounded queries/results, result normalization, deduplication, and rejection of external/unsafe result URLs. Product/order/customer providers and the route/UI remain pending.
- `app/Services/AdminPreviewTokenService.php`: issues short-lived HMAC-signed preview tokens bound to a specific entity and admin identity; one-time/replay prevention still needs persistence.
- `app/Services/AdminSavedViewValidator.php`: validates saved filter/sort structures and restricts fields/operators; query providers must still map fields to explicit per-module allowlists.
- `app/Services/AdminContentPublishingPolicy.php`: validates publish-state transitions and requires explicit-timezone future schedules normalized to UTC; DB session timezone must be verified before persistence.
- `app/Services/AdminReportExportService.php`: bounded CSV serialization with explicit safe field keys, Persian text support, and spreadsheet-formula injection protection; report-specific field schemas and route wiring remain gated.
- `app/Core/AdminBulkOperationRegistry.php` and `app/Services/AdminBulkOperationPolicy.php`: allowlist bulk job types, bound batch sizes, require explicit confirmation, redact payloads, and require approval references for sensitive job classes. Actual approval state must be verified before a worker executes a job.

### Gmail / notifications
- `app/Services/GmailSmtpTransport.php`: Gmail-only SMTP transport using environment variables, TLS verification, header validation, and stable non-secret error codes. No SMTP connection is made unless `send()` is called.
- `AdminNotificationService` now applies the shared event/severity policy, redacts payloads, and validates/enqueues email requests through the outbox repository.
- `GmailSmtpTransport` validates recipient, subject, display name and body bounds before opening a socket; credentials remain environment-only. No real delivery test was run.
- `app/Services/AdminEmailOutboxWorker.php` plus `app/Core/AdminEmailOutboxRepositoryInterface.php`: batch-limited sender orchestration, success tracking, permanent-failure handling, capped exponential retry, and a fakeable sender for tests. The atomic database adapter (`claimDue`/status updates) remains a DB gate.
- Durable outbox processing, DB-backed deduplication/rate-limit state, retries, and real Gmail delivery are not claimed complete.

### Database-facing primitives prepared but NOT executed
- Existing branch includes an additive migration for 11 shared foundation tables and an idempotent permission-definition seed.
- Repositories/services cover audit snapshots, admin-session inventory, recipient-scoped notifications/outbox, content revisions, saved views, operation jobs, incidents, approval requests, media metadata, support tickets, and analytics events.
- No migration or seed has been run. No local/production DB has been opened or modified.

## Route audit result
Static inspection of `routes/api.php` found **161 named admin API routes**:
- All 161 include `AdminAuthMiddleware`.
- All 161 now have an explicit permission contract (zero missing contracts).
- All mutating admin API routes include `CsrfMiddleware`.
- 14 route names do not belong to a canonical feature bucket, but now have explicit exact-route permission contracts in `app/Core/AdminRoutePermissionMap.php`: notification creation, reviews, questions/answers, and customer-user management. Their permissions are not inferred from a broad route prefix.
- The existing route table has not yet been changed to apply granular feature middleware globally. Doing so before the target DB's role/permission assignments are verified could lock out legitimate admins or alter authorization unexpectedly.

## Automated checks
Latest successful run on this branch:
- Authentication/authorization regression: **21 PASS, 0 FAIL**
- Shared foundation regression: **30 PASS, 0 FAIL**
- Non-database foundation contracts: **67 PASS, 0 FAIL**
- Admin route contract audit: **7 PASS, 0 FAIL**
- PHP lint: all files in the workflow passed.

These are source/static/unit checks, not a live XAMPP/browser/database/Gmail integration run.

## Explicit database gates — do not pass without review
1. Read the actual local schema and current role/permission assignments.
2. Review the additive migration against that schema; verify backup and restore before applying.
3. Apply migration and permission seed only in a controlled target environment after review.
4. Verify the actual Super Admin role slug/status/deletion state and permission matrix.
5. Wire granular middleware to the existing 161 routes after validating the exact permission map and current role assignments. The middleware is prepared but is not globally attached to the route table yet.
6. Run DB-backed tests for admin account lifecycle, sessions, audit persistence, notifications/outbox, content revisions, jobs, incidents, approvals, media metadata, support messages, and analytics.
7. Configure Gmail app-password environment variables outside Git, then run a deliberate delivery test and verify outbox status/retry behavior.
8. Configure `NIVAD_PRIVATE_STORAGE_PATH` outside the document root before accepting uploads; then connect returned metadata to the DB only after schema approval.

## Status interpretation
This is a significant non-database foundation tranche, **not a claim that all 44 user-facing capabilities are finished**. Some capabilities depend on existing data/schema and others still need feature-specific routes, providers, UI, and integration tests. The DB execution boundary remains intact and `master` has not been merged.
