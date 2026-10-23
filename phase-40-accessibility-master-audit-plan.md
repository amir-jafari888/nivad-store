# Nivad — Phase 40 Accessibility Master Audit

## 1. Source of Truth and Baseline

- Canonical phase definition: `front-roadmap.txt` → **PHASE 40 — Accessibility Master Audit**.
- Rules: `uiuxrules.md`, especially keyboard/accessibility, forms, feedback, modal/drawer, navigation, loading/error states.
- Storefront token source: `assets/css/setting.css`.
- Admin token source: `admin/assets/css/setting.css`.
- Repository baseline used for planning: `phase-39-responsive-master-audit`.
- Phase 39 runtime/browser checks are pending. **That does not block Phase 40 audit, static source analysis, contract extraction, or code fixes.** It only limits claims that require runtime/browser evidence.
- Scope: storefront and Admin frontend accessibility; PHP templates, HTML semantics, CSS, JavaScript interaction modules and existing frontend tests.
- Backend Freeze remains active: do not change routes, APIs, database, services, repositories, middleware, or server authorization.
- No invented selectors, controls, routes, state, or dependencies. Mark unproven behavior `UNKNOWN`.
- Every batch contains exactly 10 contracts. Workflow: `AUDIT → IMPLEMENT proven defects → TEST → REGRESSION → PASS → LOCK`.
- Initial status for all contracts: `NOT STARTED — AUDIT REQUIRED`. A listed contract is not evidence of completion.
- No new generic shared CSS file. Storefront/Admin styles remain isolated; use the existing design-token files and existing viewport-specific structure.
- Responsive baseline remains Mobile ≤600px, Tablet 601–1009px, Desktop ≥1010px. This phase tests accessibility at all three sizes but does not repeat the full Phase 39 visual audit.
- Browser/assistive-technology execution can be recorded as pending evidence without stopping source-based work. Never label an unexecuted check PASS.

## 2. Initial Evidence from Source Inventory

- The repository contains customer-facing PHP pages under `pages/`, shared storefront partials under `partials/`, storefront modules under `assets/js/modules/`, and a separate Admin interface under `admin/`.
- Existing Admin data-experience test source checks for a table live region and labelled row-selection checkboxes; the Admin dashboard test source checks semantic table headers. These are **source-level assertions, not proof that the tests have been executed or that the rendered UI is accessible**.
- Existing global accessibility coverage is not yet proven. All remaining implementation details must be confirmed during each contract's audit.
- The known Phase 39 CSS import corrections are inherited through the selected base branch; they are not treated as a Phase 40 PASS.

## 3. Batches and Contracts

### Batch 01 — Accessibility Baseline, Semantics & Landmarks

| Step | Contract | Initial Status |
|---|---|---|
| 40.01 | Inventory all customer PHP pages, shared partials, Admin pages, and interactive JS modules for accessibility coverage. | NOT STARTED — AUDIT REQUIRED |
| 40.02 | Verify `lang` and `dir` declarations on customer-facing document roots. | NOT STARTED — AUDIT REQUIRED |
| 40.03 | Verify language and direction declarations on Admin document roots, including the existing Admin theme contract. | NOT STARTED — AUDIT REQUIRED |
| 40.04 | Verify one clear primary page heading and a logical heading hierarchy on each audited page. | NOT STARTED — AUDIT REQUIRED |
| 40.05 | Verify semantic header, navigation, main, and footer landmarks where those regions exist. | NOT STARTED — AUDIT REQUIRED |
| 40.06 | Verify repeated navigation and content regions have distinguishable accessible names when needed. | NOT STARTED — AUDIT REQUIRED |
| 40.07 | Verify link text describes the destination or action without relying only on surrounding visual context. | NOT STARTED — AUDIT REQUIRED |
| 40.08 | Verify buttons and links use the correct native element for their behavior. | NOT STARTED — AUDIT REQUIRED |
| 40.09 | Audit icon-only controls for a programmatic accessible name and decorative icons for proper hiding. | NOT STARTED — AUDIT REQUIRED |
| 40.10 | Record the page/component coverage matrix and mark missing or unprovable source contracts explicitly. | NOT STARTED — AUDIT REQUIRED |

### Batch 02 — Keyboard Access & Predictable Navigation

| Step | Contract | Initial Status |
|---|---|---|
| 40.11 | Verify every interactive control is reachable and operable using keyboard alone. | NOT STARTED — AUDIT REQUIRED |
| 40.12 | Verify logical Tab and Shift+Tab order follows the interface and reading order. | NOT STARTED — AUDIT REQUIRED |
| 40.13 | Detect positive `tabindex` values or scripted focus order that may disrupt natural navigation. | NOT STARTED — AUDIT REQUIRED |
| 40.14 | Verify no keyboard trap exists in page content, navigation, menus, or overlays. | NOT STARTED — AUDIT REQUIRED |
| 40.15 | Verify visible skip navigation is available where repeated navigation makes it useful. | PARTIAL — shared storefront/Admin skip links added; target coverage remains incomplete across all pages. |
| 40.16 | Verify menus, dropdowns, tabs, accordions, and disclosure controls support their expected keyboard interaction. | NOT STARTED — AUDIT REQUIRED |
| 40.17 | Verify native buttons activate with Enter/Space and links activate with Enter without custom-key conflicts. | NOT STARTED — AUDIT REQUIRED |
| 40.18 | Verify keyboard users can reach and operate search suggestions and clear/submit controls. | PARTIAL — combobox/listbox semantics and arrow/Escape handling audited in source; manual keyboard verification pending. |
| 40.19 | Verify keyboard access to product actions, filters, sort, pagination, wishlist, and cart controls. | NOT STARTED — AUDIT REQUIRED |
| 40.20 | Verify Admin navigation, data-table tools, row selection, and action menus are keyboard operable. | NOT STARTED — AUDIT REQUIRED |

### Batch 03 — Focus Visibility & Focus Management

| Step | Contract | Initial Status |
|---|---|---|
| 40.21 | Audit global storefront focus indicators against existing focus tokens and actual component styles. | PARTIAL — existing global focus-visible rule verified; computed contrast/obscuring checks pending. |
| 40.22 | Audit Admin focus indicators against Admin-owned tokens without leaking storefront styles. | PARTIAL — Admin-owned focus-visible rule added; rendered verification pending. |
| 40.23 | Verify `:focus-visible` is not removed without an accessible replacement. | NOT STARTED — AUDIT REQUIRED |
| 40.24 | Verify focused controls remain visible and are not obscured by sticky headers, drawers, or floating UI. | NOT STARTED — AUDIT REQUIRED |
| 40.25 | Verify opening a modal or drawer moves focus into the active overlay according to its interaction contract. | NOT STARTED — AUDIT REQUIRED |
| 40.26 | Verify closing an overlay returns focus to the invoking control when it still exists. | NOT STARTED — AUDIT REQUIRED |
| 40.27 | Verify overlay focus remains contained where modal semantics require it. | NOT STARTED — AUDIT REQUIRED |
| 40.28 | Verify dynamically hidden or removed elements do not retain focus or leave focus in an invalid location. | NOT STARTED — AUDIT REQUIRED |
| 40.29 | Verify route/page transitions and validation failures provide a predictable focus destination where needed. | NOT STARTED — AUDIT REQUIRED |
| 40.30 | Verify focus remains visible and usable in Light and Dark themes and across viewport layouts. | NOT STARTED — AUDIT REQUIRED |

### Batch 04 — Forms, Labels & Validation

| Step | Contract | Initial Status |
|---|---|---|
| 40.31 | Verify every input, select, textarea, and custom field has a persistent programmatic label. | NOT STARTED — AUDIT REQUIRED |
| 40.32 | Verify related controls are grouped and group labels are programmatically associated where needed. | NOT STARTED — AUDIT REQUIRED |
| 40.33 | Verify required and optional fields are communicated in text and programmatically where appropriate. | NOT STARTED — AUDIT REQUIRED |
| 40.34 | Verify instructions, accepted formats, and constraints are associated with the relevant fields. | NOT STARTED — AUDIT REQUIRED |
| 40.35 | Verify invalid fields expose an appropriate invalid state and reference their error description. | NOT STARTED — AUDIT REQUIRED |
| 40.36 | Verify validation errors are specific, actionable, and located near the affected field. | NOT STARTED — AUDIT REQUIRED |
| 40.37 | Verify form-level errors are announced and do not rely on color or position alone. | NOT STARTED — AUDIT REQUIRED |
| 40.38 | Verify password visibility controls have clear accessible names and state announcements. | NOT STARTED — AUDIT REQUIRED |
| 40.39 | Verify disabled, readonly, processing, success, and error states are distinguishable beyond color alone. | NOT STARTED — AUDIT REQUIRED |
| 40.40 | Audit customer auth/account/checkout forms and Admin create/edit forms using only the actual fields present. | NOT STARTED — AUDIT REQUIRED |

### Batch 05 — Screen Reader Names, States & Live Feedback

| Step | Contract | Initial Status |
|---|---|---|
| 40.41 | Verify accessible names, roles, and current states match the actual behavior of interactive controls. | NOT STARTED — AUDIT REQUIRED |
| 40.42 | Verify ARIA is used only when native HTML semantics do not already provide the required behavior. | NOT STARTED — AUDIT REQUIRED |
| 40.43 | Verify `aria-expanded`, `aria-controls`, `aria-selected`, and related state attributes stay synchronized with real UI state where used. | NOT STARTED — AUDIT REQUIRED |
| 40.44 | Verify loading status is announced without forcing unnecessary focus changes. | NOT STARTED — AUDIT REQUIRED |
| 40.45 | Verify success and failure feedback for important actions is exposed to assistive technology. | NOT STARTED — AUDIT REQUIRED |
| 40.46 | Verify cart quantity, add/remove, wishlist, filter, and pagination updates provide useful accessible feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.47 | Verify live regions exist before announcements when required and do not announce stale or duplicate messages. | NOT STARTED — AUDIT REQUIRED |
| 40.48 | Verify error alerts use an appropriate announcement strategy without excessively interrupting users. | NOT STARTED — AUDIT REQUIRED |
| 40.49 | Verify decorative images are ignored by assistive technology and informative images have meaningful alternatives. | NOT STARTED — AUDIT REQUIRED |
| 40.50 | Verify tables, status badges, ratings, prices, and availability communicate meaning without color-only cues. | NOT STARTED — AUDIT REQUIRED |

### Batch 06 — Modal, Drawer & Composite Interaction Accessibility

| Step | Contract | Initial Status |
|---|---|---|
| 40.51 | Audit cart drawer semantics, accessible name, open/close state, and keyboard behavior. | NOT STARTED — AUDIT REQUIRED |
| 40.52 | Audit modal/dialog semantics and accessible names for existing confirmation or form dialogs. | NOT STARTED — AUDIT REQUIRED |
| 40.53 | Verify Escape closes dismissible overlays and does not silently discard critical user input. | NOT STARTED — AUDIT REQUIRED |
| 40.54 | Verify close controls are keyboard accessible and have meaningful accessible names. | NOT STARTED — AUDIT REQUIRED |
| 40.55 | Verify background content is not interactable by keyboard when a true modal is open. | NOT STARTED — AUDIT REQUIRED |
| 40.56 | Verify dropdowns and menus expose expanded/collapsed state and support expected keyboard behavior. | NOT STARTED — AUDIT REQUIRED |
| 40.57 | Verify tabs expose selected state and keyboard navigation consistent with their actual interaction pattern. | NOT STARTED — AUDIT REQUIRED |
| 40.58 | Verify accordions expose expanded state and control their corresponding panels correctly. | NOT STARTED — AUDIT REQUIRED |
| 40.59 | Verify toast timing, dismiss controls, and announcements do not hide essential information or block interaction. | NOT STARTED — AUDIT REQUIRED |
| 40.60 | Verify overlay scroll handling and focus behavior remain usable at mobile, tablet, and desktop sizes. | NOT STARTED — AUDIT REQUIRED |

### Batch 07 — Storefront Discovery, Catalog & Product Accessibility

| Step | Contract | Initial Status |
|---|---|---|
| 40.61 | Audit global header, mobile navigation, account, theme, wishlist, and cart control names and keyboard access. | NOT STARTED — AUDIT REQUIRED |
| 40.62 | Audit search input, suggestions, result count, no-results, loading, and error announcements. | NOT STARTED — AUDIT REQUIRED |
| 40.63 | Audit category, brand, collection, discovery, and listing heading structure and navigation labels. | NOT STARTED — AUDIT REQUIRED |
| 40.64 | Audit product cards for meaningful link names, image alternatives, price/status semantics, and action separation. | NOT STARTED — AUDIT REQUIRED |
| 40.65 | Audit product detail media/gallery controls, thumbnail names, and image failure alternatives. | NOT STARTED — AUDIT REQUIRED |
| 40.66 | Audit product variant and specification controls for labels, selected states, and understandable grouping. | NOT STARTED — AUDIT REQUIRED |
| 40.67 | Audit filters, sort controls, clear filters, and result counts for labels and status feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.68 | Audit pagination current-page state and keyboard-accessible navigation labels. | NOT STARTED — AUDIT REQUIRED |
| 40.69 | Audit reviews, ratings, questions, answers, and related-product regions for semantic structure. | NOT STARTED — AUDIT REQUIRED |
| 40.70 | Audit empty, error, unavailable, and partial-failure states across catalog and product pages. | NOT STARTED — AUDIT REQUIRED |

### Batch 08 — Smart Commerce, Cart, Checkout, Payment & Account

| Step | Contract | Initial Status |
|---|---|---|
| 40.71 | Audit Smart Product Finder questions, progress, selections, and recommendation results for accessible structure. | NOT STARTED — AUDIT REQUIRED |
| 40.72 | Audit compatibility outcomes (compatible, incompatible, conditional, unknown, checking, error) for text and announcements. | NOT STARTED — AUDIT REQUIRED |
| 40.73 | Audit Setup Builder and Complete Your Setup for step context, selection state, and summary updates. | NOT STARTED — AUDIT REQUIRED |
| 40.74 | Audit Budget Optimizer, comparison, alternatives, and smart bundle controls and results for accessible names/states. | NOT STARTED — AUDIT REQUIRED |
| 40.75 | Audit price-drop, back-in-stock, and recently selected device controls for labels and feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.76 | Audit cart page and cart drawer for item names, quantity controls, removal, totals, and empty state. | NOT STARTED — AUDIT REQUIRED |
| 40.77 | Audit checkout address, shipping, review, and progression controls for labels, errors, and step context. | NOT STARTED — AUDIT REQUIRED |
| 40.78 | Audit payment processing, success, failure, cancellation, timeout, and retry messaging for accessible status and recovery. | NOT STARTED — AUDIT REQUIRED |
| 40.79 | Audit login, registration, password recovery/reset, email verification, and session-expired feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.80 | Audit account profile, addresses, orders, order detail, notifications, reviews, and returns interactions. | NOT STARTED — AUDIT REQUIRED |

### Batch 09 — Admin Accessibility

| Step | Contract | Initial Status |
|---|---|---|
| 40.81 | Audit Admin login, authentication feedback, and page-level heading/landmark structure. | NOT STARTED — AUDIT REQUIRED |
| 40.82 | Audit Admin shell navigation, current-page indication, theme control, and keyboard access. | NOT STARTED — AUDIT REQUIRED |
| 40.83 | Audit dashboard metric cards, charts, and summaries for text equivalents and semantic grouping. | NOT STARTED — AUDIT REQUIRED |
| 40.84 | Audit Admin data tables for captions/labels where needed, semantic headers, and row/column relationships. | NOT STARTED — AUDIT REQUIRED |
| 40.85 | Audit Admin search, status filters, page-size controls, pagination, and clear-filter feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.86 | Audit row selection, select-all state, selected-row count, and CSV export feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.87 | Audit Admin create/edit forms, field errors, required fields, and submission-processing feedback. | NOT STARTED — AUDIT REQUIRED |
| 40.88 | Audit Admin action buttons, confirmations, destructive actions, and accessible names across actual modules. | NOT STARTED — AUDIT REQUIRED |
| 40.89 | Audit Admin empty, loading, error, and permission-related UI states without assuming server authorization. | NOT STARTED — AUDIT REQUIRED |
| 40.90 | Verify Admin accessibility styles and behavior remain isolated from storefront CSS/JS and use Admin tokens. | NOT STARTED — AUDIT REQUIRED |

### Batch 10 — Contrast, Touch, Zoom, Theme & Motion Preferences

| Step | Contract | Initial Status |
|---|---|---|
| 40.91 | Audit normal text contrast against applicable WCAG 2.2 AA requirements using actual token/value pairs. | NOT STARTED — AUDIT REQUIRED |
| 40.92 | Audit large text and non-text UI contrast, including control boundaries and meaningful icons. | NOT STARTED — AUDIT REQUIRED |
| 40.93 | Verify information, errors, selection, stock, and status are not communicated by color alone. | NOT STARTED — AUDIT REQUIRED |
| 40.94 | Audit minimum target size and spacing for pointer/touch controls against WCAG 2.2 AA target-size requirements, recording exceptions explicitly. | NOT STARTED — AUDIT REQUIRED |
| 40.95 | Verify text resizing and browser zoom do not hide content or functionality at 200% and relevant narrow viewport conditions. | NOT STARTED — AUDIT REQUIRED |
| 40.96 | Verify reflow at narrow widths without two-dimensional scrolling except for content that genuinely requires it. | NOT STARTED — AUDIT REQUIRED |
| 40.97 | Verify Light/Dark theme token combinations preserve text, control, focus, and status contrast. | NOT STARTED — AUDIT REQUIRED |
| 40.98 | Verify RTL layout, mixed Persian/English text, numbers, and directional icons do not create accessibility ambiguity. | NOT STARTED — AUDIT REQUIRED |
| 40.99 | Verify reduced-motion preference is respected for non-essential transitions and animated states where motion exists. | PARTIAL — storefront and Admin token styles include reduced-motion rules; animated component audit pending. |
| 40.100 | Verify accessible behavior and content remain consistent across mobile, tablet, desktop, Light, and Dark contexts. | NOT STARTED — AUDIT REQUIRED |

### Batch 11 — Automated Checks, Regression & Evidence

| Step | Contract | Initial Status |
|---|---|---|
| 40.101 | Build a read-only static accessibility test using actual files, selectors, and contracts confirmed during audit. | PARTIAL — read-only static test added; execution/lint pending. |
| 40.102 | Verify the static test reports individual PASS/FAIL results and a truthful summary without fake passes. | NOT STARTED — AUDIT REQUIRED |
| 40.103 | Include checks for document language/direction, landmarks, headings, labels, image alternatives, and button/link semantics where reliably testable. | PARTIAL — initial checks cover language/direction, selected landmarks, labels, and interaction semantics; full page coverage pending. |
| 40.104 | Include checks for keyboard/focus hooks, dialog state, live regions, and ARIA state synchronization where statically provable. | NOT STARTED — AUDIT REQUIRED |
| 40.105 | Include checks for contrast/token declarations and responsive viewport coverage without claiming computed contrast from token names alone. | NOT STARTED — AUDIT REQUIRED |
| 40.106 | Run available source-level JS/PHP/CSS checks and record unexecuted environment-dependent checks separately. | NOT STARTED — AUDIT REQUIRED |
| 40.107 | Define keyboard-only manual test cases for storefront and Admin critical journeys. | NOT STARTED — AUDIT REQUIRED |
| 40.108 | Define screen-reader/manual verification cases for forms, search, cart, checkout, payment, and Admin tables. | NOT STARTED — AUDIT REQUIRED |
| 40.109 | Run regression coverage against Phase 34 JS integrity, Phase 35 routes/links, Phase 38 Admin data experience, and Phase 39 responsive contracts where available. | NOT STARTED — AUDIT REQUIRED |
| 40.110 | Publish evidence, unresolved issues, environment limitations, dependency notes, and batch PASS/FAIL decisions; LOCK only after all required evidence exists. | NOT STARTED — AUDIT REQUIRED |

## 4. Execution Policy

- Do not wait for browser/XAMPP access to begin source audits, static analysis, safe frontend fixes, or test-script preparation.
- When a contract requires rendered layout, computed contrast, actual keyboard interaction, or screen-reader output, mark that evidence as `PENDING ENVIRONMENT`; continue with the remaining contracts.
- A batch may not be marked PASS if a required contract failed or if the evidence needed to validate it is unavailable. Environment-limited contracts remain explicitly pending; they must not silently block unrelated source work.
- Do not add ARIA mechanically. Prefer native semantic HTML and only add ARIA when its role/state/name is verified against the actual interaction.
- Do not modify backend files or invent API-backed states. Document backend-dependent accessibility gaps as dependencies.
- Phase 40 status at extraction: `PLANNED — NOT STARTED`; `PASS = NO`; `LOCK = NO`.


## 5. Implementation Checkpoint — 2026-10-23

Source-level changes currently present on `phase-40-accessibility-master-audit`:

1. Added keyboard skip-navigation links to the shared storefront header and selected storefront/Admin pages.
2. Added a token-based storefront skip-link focus style; added Admin-owned skip-link, global `:focus-visible`, and `prefers-reduced-motion` styles.
3. Added `id="main-content"` and programmatic focus targets to the audited cart, checkout, auth, search, product-detail, and selected Admin pages.
4. Improved storefront search suggestions with explicit combobox/listbox relationships, selected-option state, and non-interactive option elements compatible with the existing active-descendant keyboard flow.
5. Added a read-only static test file, `test-phase-40-accessibility-master-audit.php`, with initial assertions for these source contracts.

These changes are **partial implementation, not completion of all 110 contracts**. The static test has not yet been executed or PHP-linted in the target XAMPP environment. Search interaction changes have not been browser-tested. Skip-target coverage has not been proven for every page. Contrast calculations, 200% zoom/reflow, modal focus trapping/restoration, touch target measurement, and screen-reader verification remain pending. Backend Freeze remains unchanged.

Current truthful phase status: `IN PROGRESS`; `PASS = NO`; `LOCK = NO`.
