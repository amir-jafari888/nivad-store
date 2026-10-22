# Nivad — Phase 29 Authentication Execution Plan

**Source of truth:** `front-roadmap.txt`, current repository contracts on `master`, and the current Phase 29 implementation branch.
**Execution model:** seven batches, ten contracts each. The seventh batch is the final regression gate added to meet the execution grouping; it is not represented as a pre-existing official batch in the master roadmap.
**Scope:** frontend auth pages, JavaScript, CSS, accessibility, and static regression. Existing API routes and backend behavior are preserved. Live email delivery, real session behavior, and HTTP integration must be verified in the local runtime/environment.

## Batch 01 — Structure, encoding, route integrity (29.01–29.10)

- [x] 29.01 Login page structure and `data-auth-page`.
- [x] 29.02 Registration page structure and form hook.
- [x] 29.03 Forgot-password page and form hook.
- [x] 29.04 Reset-password page and form hook.
- [x] 29.05 Email verification and resend forms.
- [x] 29.06 Restore readable UTF-8 Persian copy on all auth pages.
- [x] 29.07 Preserve `data-auth-page` integration with the JS module.
- [x] 29.08 Align form names with existing API payload keys.
- [x] 29.09 Remove hardcoded `/nivad-store/` mount assumptions from auth pages.
- [x] 29.10 Use the project's `url()` helper for internal navigation.

## Batch 02 — Design system, theme, responsive (29.11–29.20)

- [x] 29.11 Reuse tokens already defined in `assets/css/setting.css`.
- [x] 29.12 Replace scattered semantic auth colors with design tokens.
- [x] 29.13 Tokenize surface, border, radius, shadow, spacing and typography.
- [x] 29.14 Add and import mobile auth stylesheet.
- [x] 29.15 Add and import tablet auth stylesheet.
- [x] 29.16 Add and import desktop auth stylesheet.
- [x] 29.17 Keep auth surfaces compatible with Light/Dark tokens.
- [x] 29.18 Prevent input overflow and enforce box sizing.
- [x] 29.19 Add focus-visible, invalid, disabled and feedback states.
- [x] 29.20 Provide mobile controls with at least 48px target height.

## Batch 03 — Login and registration (29.21–29.30)

- [x] 29.21 Preserve the existing `POST /login` contract.
- [x] 29.22 Send the `identifier` field.
- [x] 29.23 Send the `password` field.
- [x] 29.24 Retrieve CSRF token from `GET /auth/csrf`.
- [x] 29.25 Send `csrf_token` using URL-encoded form data.
- [x] 29.26 Preserve the existing `POST /register` contract.
- [x] 29.27 Send `first_name`, `last_name`, `email`, and `phone`.
- [x] 29.28 Send `password` and `password_confirmation`.
- [x] 29.29 Map API field errors to fields and expose `aria-invalid`.
- [x] 29.30 Prevent duplicate submissions and expose loading state.

## Batch 04 — Forgot/reset password (29.31–29.40)

- [x] 29.31 Preserve `POST /forgot-password`.
- [x] 29.32 Use native email validation and appropriate input semantics.
- [x] 29.33 Use generic recovery feedback to reduce account enumeration.
- [x] 29.34 Add request timeout and network-failure feedback.
- [x] 29.35 Preserve `POST /reset-password`.
- [x] 29.36 Read token from query string into token inputs.
- [x] 29.37 Require a reset token.
- [x] 29.38 Send `new_password` and `new_password_confirmation`.
- [x] 29.39 Handle non-2xx, non-JSON and API error payloads.
- [x] 29.40 Redirect successful reset through the configured base URL.

## Batch 05 — Email verification and session navigation (29.41–29.50)

- [x] 29.41 Preserve `POST /verify-email`.
- [x] 29.42 Support token query parameter prefilling.
- [x] 29.43 Preserve `POST /resend-email-verification`.
- [x] 29.44 Give resend form its own feedback region.
- [x] 29.45 Guard both forms against duplicate submission.
- [x] 29.46 Render response feedback using `textContent`.
- [x] 29.47 Query existing `GET /auth/status`.
- [x] 29.48 Update desktop and mobile account navigation.
- [x] 29.49 Preserve CSRF-protected `POST /logout`.
- [x] 29.50 Give 401/419 authentication/session failures explicit behavior.

## Batch 06 — Accessibility, recovery, regression (29.51–29.60)

- [x] 29.51 Associate each input with a label.
- [x] 29.52 Use live regions for user-visible feedback.
- [x] 29.53 Connect field errors with `aria-describedby` and `aria-invalid`.
- [x] 29.54 Set input types and autocomplete values.
- [x] 29.55 Add keyboard-visible focus styles.
- [x] 29.56 Handle network, timeout, malformed JSON and non-JSON responses.
- [x] 29.57 Handle authentication failures and validation error payloads.
- [x] 29.58 Add dedicated CSS for all three breakpoints.
- [x] 29.59 Preserve JS/CSS imports in `main.js` and `main.css`.
- [x] 29.60 Confirm all expected auth endpoints remain present in the API route file.

## Batch 07 — Final regression gate (29.61–29.70)

- [x] 29.61 Verify all five auth pages exist.
- [x] 29.62 Verify all three breakpoint-specific auth stylesheets exist.
- [x] 29.63 Verify every auth flow has a submit control.
- [x] 29.64 Verify user messages are never inserted as HTML.
- [x] 29.65 Verify same-origin request credentials.
- [x] 29.66 Verify CSRF body contract aligns with middleware.
- [x] 29.67 Verify all auth pages retain shared header/footer integration.
- [x] 29.68 Verify use of the existing base URL global.
- [x] 29.69 Verify auth module entry/export structure.
- [ ] 29.70 Run the root test script with PHP CLI and complete local HTTP/browser smoke tests.

## Test command

```bash
php test-phase-29-authentication.php
```

The static source assertions are automated in the test script. The final item remains pending until the script is run in a PHP-capable checkout and browser/API smoke tests are completed. Therefore this plan does **not** authorize a final Phase 29 lock yet.

## Changed paths

- `assets/js/modules/auth.js`
- `assets/css/auth.css`
- `assets/css/mobile/auth-mobile.css`
- `assets/css/tablet/auth-tablet.css`
- `assets/css/desktop/auth-desktop.css`
- `assets/css/mobile/mobile.css`
- `assets/css/tablet/tablet.css`
- `assets/css/desktop/desktop.css`
- `pages/auth/login.php`
- `pages/auth/register.php`
- `pages/auth/forgot-password.php`
- `pages/auth/reset-password.php`
- `pages/auth/verify-email.php`
- `test-phase-29-authentication.php`

## Known runtime dependencies

- Browser execution and real HTTP requests to the PHP API have not been exercised by the GitHub source editor.
- Email verification/reset delivery depends on the configured mail environment.
- Database/session integration is not asserted by this read-only static test.
