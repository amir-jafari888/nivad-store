# Phase 4 — Incident & Error Detection

This file is the implementation breakdown for the 12-batch Phase 4 scope. It does not replace the canonical Admin Panel roadmap. Each batch has exactly 10 sub-steps. Code implementation status is separate from environment verification.

Status legend:
- [x] Implemented in code / statically verified where applicable.
- [ ] Verification pending or capability unavailable in the audited codebase.
- [~] Partially covered; see the note.

## Batch 01 — PHP Runtime Error Capture
1. [x] Keep the centralized exception handler registered from Bootstrap.
2. [x] Preserve conversion of reportable PHP warnings/notices into ErrorException.
3. [x] Capture PHP warnings/notices before throwing the converted exception.
4. [x] Capture fatal shutdown error types after Bootstrap registration.
5. [x] Record exception class, safe file basename and line number.
6. [x] Attach the request correlation identifier.
7. [x] Exclude raw exception messages from incident context.
8. [x] Prevent recursive incident-capture failures from replacing the original error.
9. [x] Avoid duplicate capture for ErrorException already captured at the PHP error boundary.
10. [ ] Verify real warning, uncaught exception and fatal-error scenarios in the target XAMPP runtime.

## Batch 02 — HTTP and API Error Capture
11. [x] Capture HTTP 404 responses.
12. [x] Capture HTTP 429 responses.
13. [x] Capture HTTP 5xx responses.
14. [x] Instrument the shared Response::error boundary.
15. [x] Preserve the existing HTML response path for admin browser exceptions.
16. [x] Preserve JSON error responses for API/AJAX requests.
17. [x] Preserve request correlation headers on unhandled exception responses.
18. [~] HTTP 405 method mismatch remains outside the capture allowlist.
19. [x] Exclude expected 4xx validation/authentication responses from incident noise.
20. [ ] Verify representative 404/429/500 requests against the live application.

## Batch 03 — Database Failure Capture
21. [x] Classify uncaught PDOException instances as database incidents.
22. [x] Provide a dedicated reporting boundary for caught PDO exceptions.
23. [x] Connect caught payment-controller PDO exceptions to that boundary.
24. [x] Avoid persisting SQL text, bound values or raw PDO messages.
25. [x] Record safe exception type, file basename and line.
26. [x] Preserve local error_log fallback when the database is unavailable.
27. [x] Keep database capture recursion guarded.
28. [x] Preserve existing transaction rollback behavior.
29. [~] Other locally caught PDO exceptions require adoption of Database::reportFailure at their owning service boundaries.
30. [ ] Verify query, transaction and connection failures against the target database.

## Batch 04 — Browser JavaScript Error Capture
31. [x] Capture uncaught window runtime errors.
32. [x] Capture unhandled Promise rejections.
33. [x] Capture browser resource-load failures.
34. [x] Send telemetry through the same-origin API URL.
35. [x] Protect the telemetry endpoint with CSRF validation.
36. [x] Apply a per-session request rate limit.
37. [x] Collect metadata only; do not send raw messages or stack traces.
38. [x] Strip query strings and fragments from recorded paths.
39. [~] Source-map symbolication is not configured in the current repository.
40. [ ] Verify runtime, rejection and resource events in supported browsers.

## Batch 05 — Payment Failure Detection
41. [x] Capture payment attempts explicitly marked failed by the current payment flow.
42. [x] Associate an event with the payment-attempt identifier.
43. [x] Store the error code without storing the customer-facing provider message.
44. [x] Classify expected payment failure events as warning severity.
45. [x] Reuse incident fingerprinting and occurrence aggregation.
46. [x] Capture caught PDO exceptions in payment request paths.
47. [~] A real payment-gateway callback/adapter is not present in the audited path.
48. [~] Gateway timeout/retry telemetry cannot be verified until a provider adapter exists.
49. [ ] Verify payment-failure capture using a controlled test attempt.
50. [x] Keep the existing payment notification event dispatch intact.

## Batch 06 — External Service and SMTP Detection
51. [x] Add a reusable external-service failure capture adapter.
52. [x] Capture thrown SMTP transport exceptions.
53. [x] Capture SMTP failures returned as transport results.
54. [x] Associate delivery failures with outbox row and attempt number.
55. [x] Preserve the existing outbox retry and terminal-failure policy.
56. [x] Avoid collecting recipient addresses, message bodies or raw transport exception text.
57. [x] Sanitize provider error codes before incident metadata capture.
58. [~] No generic external HTTP-client adapter was found for universal instrumentation.
59. [ ] Verify SMTP failure and retry behavior with the configured transport.
60. [~] Payment provider external-call coverage remains blocked until a real gateway adapter is present.

## Batch 07 — Route and 404 Detection
61. [x] Capture NotFoundException raised by RouteDispatcher.
62. [x] Capture 404 responses returned through Response::error.
63. [x] Include the normalized route path in incident context.
64. [x] Exclude query strings from the route fingerprint.
65. [x] Record browser resource failures separately from PHP route failures.
66. [x] Correlate route events with request identifiers.
67. [~] Method-not-allowed 405 responses are not currently incident events.
68. [~] No scheduled route crawler exists in the audited application.
69. [x] Add route-aware incident grouping.
70. [ ] Verify missing customer/admin/API routes in a running web server.

## Batch 08 — Fingerprinting and Frequency Analysis
71. [x] Use a stable SHA-256 incident fingerprint.
72. [x] Include source and normalized title in the fingerprint.
73. [x] Include route, exception type, file and line context where available.
74. [x] Increment occurrence_count for repeated incidents.
75. [x] Preserve first_seen_at on duplicate occurrences.
76. [x] Update last_seen_at on duplicate occurrences.
77. [x] Persist one timeline event per captured occurrence.
78. [x] Bound frequency-analysis windows to 1–720 hours.
79. [x] Return top recurring incidents for the selected window.
80. [ ] Verify grouping and aggregate counts with seeded database fixtures.

## Batch 09 — Severity and Priority Analysis
81. [x] Preserve canonical info, warning and critical incident severities.
82. [x] Classify 404 as informational.
83. [x] Classify 429 as warning.
84. [x] Classify 5xx as critical.
85. [x] Classify database operation failures as critical.
86. [x] Classify browser runtime telemetry as warning.
87. [x] Classify SMTP delivery failures as warning.
88. [x] Classify recorded payment failures as warning.
89. [~] Escalation rules and timed severity promotion are not implemented.
90. [~] SLA deadlines and on-call escalation are not present in the audited requirements/data model.

## Batch 10 — Incident Timeline and Lifecycle
91. [x] Keep admin_system_incidents as the canonical incident master.
92. [x] Reopen a resolved incident when the same fingerprint occurs again.
93. [x] Preserve open, acknowledged, resolved and ignored lifecycle states.
94. [x] Support incident assignment without altering the foundation master table.
95. [x] Store assignment and notification metadata in an additive table.
96. [x] Record status changes as timeline events.
97. [x] Preserve the existing admin audit-trail call on status updates.
98. [x] Expose a bounded, ordered incident timeline endpoint.
99. [x] Redact legacy context JSON before returning timeline data.
100. [ ] Verify assignment, acknowledgement, resolution and reopen transitions against the target database.

## Batch 11 — Background Jobs and Queue Monitoring
101. [x] Audit the existing admin_operation_jobs table.
102. [x] Audit the existing admin_email_outbox table.
103. [x] Confirm that no generic queue table/adapter is present in the audited foundation.
104. [x] Aggregate persisted job statuses for admin_operation_jobs.
105. [x] Detect stale queued/running operation jobs.
106. [x] Aggregate persisted email outbox statuses.
107. [x] Detect stale pending/processing outbox rows.
108. [x] Capture SMTP worker delivery failures without aborting the batch.
109. [x] Report missing queue tables as missing instead of inventing a healthy state.
110. [ ] Verify stale-job thresholds and worker recovery using live queue rows.

## Batch 12 — Admin UI, Permissions and Regression
111. [x] Add a dedicated authenticated incident-management page.
112. [x] Add incident status filtering.
113. [x] Add incident severity filtering.
114. [x] Add frequency and severity analysis panels.
115. [x] Add per-incident timeline inspection.
116. [x] Add read-only monitoring of the two audited queues.
117. [x] Map new admin API routes to explicit permissions.
118. [x] Use existing Admin design tokens and responsive layout rules.
119. [x] Pass the dedicated 32-check Phase 4 static contract suite.
120. [ ] Complete database migration, XAMPP, browser and SMTP integration regression before LOCK.

## Current gate
- Implementation branch: feature/admin-phase-4-incident-detection-2026-10-30
- PR: #53 (stacked on Phase 3 PR #52)
- Phase 4 static contract suite: 32/32 PASS on the latest verified run before the most recent follow-up commit.
- Target database migration and runtime integration: NOT RUN.
- LOCK: NO.
