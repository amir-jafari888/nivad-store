# Admin Phase 9 — Anti-Fraud and Security Operations

Status: PARTIAL. Implementation is on a feature branch stacked on Phase 8. This phase is not PASS/LOCK.

Canonical scope: `ADMIN_PANEL_ROADMAP.md`, Phase 9 — 7 Batches.

## Baseline evidence

- Admin login attempts are persisted in the existing `login_attempts` table. `AdminLoginAttemptService` enforces 5 failures per identifier / 15 minutes and 20 per IP / 15 minutes.
- Admin authentication success/failure and session-invalid events already exist.
- `audit_logs` records Admin actor, action, module, entity and timestamp; Admin role assignment and other security changes are already audited in parts of the access-management flow.
- `AdminReauthenticationService` verifies the current password and does not issue a reusable step-up token. Existing Admin account/role operations already call it.
- `RefundService` operates on the existing `refunds` and `payments` contracts. Refund amounts are checked against the linked payment individually, but cumulative refunds were not proven constrained.
- Bulk policy/registry exist, but the Operations UI itself states that server-side bulk mutation execution and audit coverage are not yet present. The detector therefore only reads explicit bulk-operation audit events if they exist; it does not claim to detect a complete bulk pipeline.

## Implemented

- Additive `admin_security_events` migration with unique fingerprints, severity, status, subject, occurrence count and human review metadata.
- Repository deduplication and safe context redaction.
- Suspicious-login scan using the existing login attempt table and existing rate-limit thresholds.
- Admin activity burst scan over actual `audit_logs`.
- Bulk operation burst scan restricted to explicit `bulk_operations` audit events.
- Refund anomaly scan when aggregate pending/processing/successful refunds exceed the linked payment amount.
- Privilege-change review signals sourced from explicit audit records.
- Security events API with view/manage permission mappings.
- Admin security events page with scan and review actions.
- Current-password re-authentication added to Admin refund creation and refund status mutation, with audit events.
- Dedicated static contract test and PHP 8.2 CI workflow.

## Batch status

| Batch | Scope | Status |
|---|---|---|
| 09.01 | Suspicious Login | PARTIAL — existing login throttling thresholds reused; device/geolocation/new-IP baseline not available |
| 09.02 | Admin Behavior | PARTIAL — action-volume heuristic; thresholds need owner calibration and false-positive review |
| 09.03 | Bulk Changes | PARTIAL — detector reads bulk audit events, but server-side bulk execution/audit producers are not yet established |
| 09.04 | Refund Anomalies | PARTIAL — detects cumulative refunds exceeding payment amount; other patterns need business policy |
| 09.05 | Privilege Escalation | PARTIAL — flags selected audited role/permission changes; full event-name coverage requires audit producer inventory |
| 09.06 | Security Alerts | IMPLEMENTED BASE — persisted, deduplicated, permission-protected, reviewable; email delivery is not claimed |
| 09.07 | Sensitive Re-auth | PARTIAL — Admin account/role operations already protected; refund creation/status now protected; remaining sensitive routes require inventory |

## Important policy boundaries

- Signals are review prompts, not fraud verdicts.
- No automatic account lock, refund rejection or privilege rollback is performed.
- Admin activity threshold (30 audited actions / 10 minutes) and bulk threshold (10 audited bulk actions / 10 minutes) are initial technical defaults and require owner approval/calibration before production enforcement.
- Login detection reuses the existing authentication rate-limit threshold; it does not infer device identity or geolocation.
- Email notification is not implemented by this phase.
- Repeated scan results are bucketed to avoid inflating occurrence counts during repeated scans of the same time window.

## Database and verification

1. Run `database/verification/20261101_admin_security_operations_phase9_preflight.sql` and confirm the actual DB schema before applying the additive migration.
2. Apply `database/migrations/20261101_admin_security_operations_phase9.sql` only after review and backup.
3. Run `database/verification/20261101_admin_security_operations_phase9_verify.sql`.
4. Run `php test-admin-phase-9-security-operations.php`.
5. Test scan, deduplication, review and refund re-authentication in XAMPP/MySQL and browser.
6. Verify role grants for `admin.security_events.view` and `admin.security_events.manage`; the code catalog does not itself grant permissions.

No local database, browser or production fraud data was accessed. No runtime PASS or LOCK is claimed.
