# Phase 57 — Admin Core Experience

## Scope
Implement only the first Admin roadmap phase: dashboard, activity feed, action queue, basic system health, permission-aware quick actions, shared shell, safe admin profile, settings contract clarity, and the Light-only theme decision.

## Batch 01 — Core Admin Experience (57.01–57.10)

1. **57.01 — Re-audit existing contracts:** preserve the four established dashboard metric groups and sales-period report contract; verify the current permission names and existing page paths.
2. **57.02 — Dashboard overview composition:** add a dedicated overview service that composes activity, action queue, health and permission context without changing the existing metrics service contract.
3. **57.03 — Real Activity Feed:** read recent admin-authored events from `audit_logs`, join the actual admin identity, exclude non-admin events, and expose no IP/User-Agent or secret fields in the dashboard.
4. **57.04 — Data-backed Action Queue:** use existing low-stock/out-of-stock report contracts plus exact open-incident and pending-admin-ticket counts. Each source is permission-gated and failure-isolated.
5. **57.05 — System Health:** centralize the existing PHP, database `SELECT 1`, and timezone probes and reuse the same contract in the capability API and dashboard. No unverified queue, cron, disk, gateway, or external-service health claims.
6. **57.06 — Quick Actions:** expose only existing destinations whose corresponding create/read permission is granted; settings remains available to authenticated admins.
7. **57.07 — Shared Admin Shell:** route the dashboard through the same authenticated RTL shell as other Admin pages; remove the duplicate dashboard-only navigation and preserve the existing navigation state module.
8. **57.08 — Admin Profile:** add a read-only profile page using only the current safe session identity (first name, last name, and admin ID); do not invent profile-edit APIs or display email/username as a name.
9. **57.09 — Settings contract:** correct the settings copy to describe server-side account persistence with browser-local cache/fallback; preserve the existing preferences API and controls.
10. **57.10 — Theme and regression gate:** keep the Admin on the Light-only design direction defined by the master register, add static contract coverage, and extend CI syntax/test checks.

## Verification state
- Implementation has been committed to `feature/admin-master-ideas-foundation`.
- Static tests and PHP syntax checks are delegated to the updated GitHub Actions workflow.
- XAMPP/MariaDB runtime, real permission-matrix behavior, and browser/mobile/tablet/desktop walkthroughs still require local verification.
- **No PASS or LOCK is claimed until those checks are observed and recorded.**
- No SQL migration or database write is required by this phase.
