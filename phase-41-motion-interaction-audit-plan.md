# Nivad — Phase 41 Motion & Interaction Audit

## 1. Source of Truth and Scope

- Canonical phase definition: `front-roadmap.txt` → **PHASE 41 — Motion & Interaction Audit**.
- Exact roadmap scope: Hover, Focus, Press, Transition, Enter, Exit, Drawer, Modal, Toast, Dropdown, Accordion, Skeleton, Reduced Motion; no unnecessary effects.
- Rules: `uiuxrules.md`, especially sections 05, 06, 17–20, 41–43, 62–66, plus motion tokens and responsive standards in `front-roadmap.txt`.
- Baseline branch: `phase-40-accessibility-master-audit`. Phase 40 is still an open draft PR and is not merged. Phase 41 intentionally stacks on that branch so its proposed accessibility work is retained; it does not imply Phase 40 has passed.
- Actual source inventory confirmed on the baseline includes `assets/css/setting.css`, `assets/css/main.css`, mobile/tablet/desktop CSS manifests and section styles, `assets/js/main.js`, storefront interaction modules (including `cart-drawer.js` and `search.js`), `admin/assets/css/setting.css`, Admin viewport styles and Admin JS modules.
- Initial code evidence: cart drawer has distinct mobile/tablet/desktop CSS with opacity/transform transitions and active states; Admin tokens define `--admin-transition`; storefront settings define motion-related tokens (exact usage and completeness still require audit). These observations are leads for verification, not PASS results.
- Scope: storefront and Admin HTML/PHP templates, existing CSS, JavaScript interaction modules and tests. Audit only controls/effects proven to exist in source. If a modal, toast, dropdown, accordion or skeleton implementation cannot be found, mark it `UNKNOWN` or `NOT PRESENT IN AUDITED SOURCE`; do not invent a component.
- Backend Freeze remains active. Do not modify routes, APIs, database, services, repositories, middleware or server authorization.
- No new generic shared CSS files. Preserve storefront/Admin CSS and JS isolation. Storefront motion values must use `assets/css/setting.css` tokens; Admin values must use `admin/assets/css/setting.css` tokens. Add a token only when an audit proves it is missing and needed.
- Responsive breakpoints: Mobile ≤600px, Tablet 601–1009px, Desktop ≥1010px. Test motion behavior at each viewport where the component exists; do not repeat the full Phase 39 visual audit.
- Runtime/browser/assistive-technology checks may remain pending if the environment is unavailable. This does not block source inventory, static audits, contract extraction or source-level fixes, but unexecuted tests must never be marked PASS.
- Each batch has exactly 10 contracts. Workflow: `AUDIT → IMPLEMENT proven defects → TEST → REGRESSION → PASS → LOCK`. Initial status for all contracts: `NOT STARTED — AUDIT REQUIRED`. No PASS/LOCK without evidence.

## 2. Batch and Contract Inventory

### Batch 01 — Motion Baseline, Tokens & Source Inventory

| Step | Contract | Initial Status |
|---|---|---|
| 41.01 | Inventory all existing CSS transition, animation, transform, opacity, keyframe and motion-related declarations in storefront and Admin assets. | NOT STARTED — AUDIT REQUIRED |
| 41.02 | Map each motion declaration to its actual page, component, viewport stylesheet and interaction trigger where provable. | NOT STARTED — AUDIT REQUIRED |
| 41.03 | Audit storefront duration, easing and delay tokens in `assets/css/setting.css`; identify missing tokens only from observed usage. | PARTIAL — existing fast/normal/slow tokens confirmed; full usage inventory pending |
| 41.04 | Audit Admin motion tokens in `admin/assets/css/setting.css` and verify Admin motion does not depend on storefront tokens. | PARTIAL — Admin-owned transition token and reduced-motion rule confirmed; full usage inventory pending |
| 41.05 | Detect hard-coded motion durations/easing values and classify only confirmed tokenization defects. | NOT STARTED — AUDIT REQUIRED |
| 41.06 | Detect duplicated or conflicting transition declarations for the same component/state across viewport styles. | NOT STARTED — AUDIT REQUIRED |
| 41.07 | Inventory JavaScript class/style changes that trigger visual transitions or animations. | NOT STARTED — AUDIT REQUIRED |
| 41.08 | Inventory motion on shared shell elements, including header, navigation, search suggestions, back-to-top and cart drawer, where present. | NOT STARTED — AUDIT REQUIRED |
| 41.09 | Inventory Admin interactive elements and theme transitions using actual Admin files. | NOT STARTED — AUDIT REQUIRED |
| 41.10 | Produce a component-to-motion coverage matrix with evidence paths and mark missing/unknown components explicitly. | NOT STARTED — AUDIT REQUIRED |

### Batch 02 — Hover, Focus, Press & Pointer Feedback

| Step | Contract | Initial Status |
|---|---|---|
| 41.11 | Audit hover feedback on existing links, buttons, icon buttons, product cards and other proven interactive elements. | NOT STARTED — AUDIT REQUIRED |
| 41.12 | Verify hover changes communicate interactivity without changing layout or causing disruptive movement. | NOT STARTED — AUDIT REQUIRED |
| 41.13 | Verify important hover feedback has an equivalent usable focus/active state for non-pointer users. | NOT STARTED — AUDIT REQUIRED |
| 41.14 | Audit `:active`/press feedback for existing buttons, icon controls and card actions. | NOT STARTED — AUDIT REQUIRED |
| 41.15 | Ensure press transforms or scale effects do not clip content, obscure focus or reduce target usability. | NOT STARTED — AUDIT REQUIRED |
| 41.16 | Verify hover/press styles do not accidentally persist after pointer release, blur or state changes. | NOT STARTED — AUDIT REQUIRED |
| 41.17 | Audit hover effects on touch-capable layouts for sticky, ghost or misleading hover states. | NOT STARTED — AUDIT REQUIRED |
| 41.18 | Verify disabled, loading and processing controls do not present misleading hover/press feedback. | NOT STARTED — AUDIT REQUIRED |
| 41.19 | Check light/dark theme interaction states against their owning design tokens. | NOT STARTED — AUDIT REQUIRED |
| 41.20 | Verify storefront and Admin interaction feedback remains isolated to each system's CSS and tokens. | NOT STARTED — AUDIT REQUIRED |

### Batch 03 — Transitions, State Changes & Layout Stability

| Step | Contract | Initial Status |
|---|---|---|
| 41.21 | Audit transitions for color, background, border, shadow, opacity and transform; remove unrelated properties from transition lists only when confirmed defective. | NOT STARTED — AUDIT REQUIRED |
| 41.22 | Detect transitions on layout-heavy properties that cause unnecessary reflow or jarring movement. | NOT STARTED — AUDIT REQUIRED |
| 41.23 | Verify transition start/end values correspond to real component states and do not leave elements visually stranded. | NOT STARTED — AUDIT REQUIRED |
| 41.24 | Check interrupted/reversed transitions when users rapidly repeat the same action. | NOT STARTED — AUDIT REQUIRED |
| 41.25 | Verify dynamic content changes do not create avoidable layout shifts due to motion or missing reserved space. | NOT STARTED — AUDIT REQUIRED |
| 41.26 | Audit z-index, visibility, opacity and pointer-event coordination for transitioning overlays and hidden controls. | NOT STARTED — AUDIT REQUIRED |
| 41.27 | Verify CSS state classes added/removed by JavaScript match the selectors that animate those states. | NOT STARTED — AUDIT REQUIRED |
| 41.28 | Verify transitions do not delay essential action availability or make controls appear unresponsive. | NOT STARTED — AUDIT REQUIRED |
| 41.29 | Check that animation completion is not assumed when CSS duration may be zero or reduced. | NOT STARTED — AUDIT REQUIRED |
| 41.30 | Confirm motion-related CSS imports resolve through existing viewport manifests without adding duplicate imports. | NOT STARTED — AUDIT REQUIRED |

### Batch 04 — Enter/Exit, Visibility & Mount Lifecycle

| Step | Contract | Initial Status |
|---|---|---|
| 41.31 | Audit enter transitions for existing panels, suggestions, alerts, floating controls and other proven dynamic regions. | NOT STARTED — AUDIT REQUIRED |
| 41.32 | Audit exit transitions and confirm elements are hidden/removed at the correct lifecycle point. | NOT STARTED — AUDIT REQUIRED |
| 41.33 | Verify exiting elements cannot receive pointer or keyboard interaction after they are visually dismissed. | NOT STARTED — AUDIT REQUIRED |
| 41.34 | Verify visibility, opacity, transform and display changes do not conflict or cause inaccessible invisible content. | NOT STARTED — AUDIT REQUIRED |
| 41.35 | Check rapid open-close-open sequences for stale classes, duplicate nodes or stuck intermediate states. | NOT STARTED — AUDIT REQUIRED |
| 41.36 | Verify JS event handlers do not rely on an animation event that may not fire for zero-duration/reduced-motion styles. | NOT STARTED — AUDIT REQUIRED |
| 41.37 | Audit dynamically inserted and removed UI for consistent enter/exit behavior without duplicate initialization. | NOT STARTED — AUDIT REQUIRED |
| 41.38 | Verify page navigation or route changes do not leave transient overlays or animations active unexpectedly. | NOT STARTED — AUDIT REQUIRED |
| 41.39 | Check reduced-motion behavior for both entry and exit paths, including repeated interactions. | NOT STARTED — AUDIT REQUIRED |
| 41.40 | Record per-component enter/exit state diagrams from actual implementation, marking undocumented behavior UNKNOWN. | NOT STARTED — AUDIT REQUIRED |

### Batch 05 — Cart Drawer & Other Proven Drawers

| Step | Contract | Initial Status |
|---|---|---|
| 41.41 | Audit cart drawer open-state synchronization between trigger, overlay, panel and JavaScript state classes. | PARTIAL — source reviewed; exit lifecycle fix applied, runtime check pending |
| 41.42 | Verify cart drawer entry/exit transforms and opacity resolve correctly at mobile, tablet and desktop breakpoints. | PARTIAL — closing-state CSS added to all three viewport stylesheets; browser verification pending |
| 41.43 | Verify overlay and panel transitions remain visually coordinated and do not expose intermediate click-through states. | PARTIAL — closing state disables pointer interaction; browser verification pending |
| 41.44 | Verify closing by close control, overlay and Escape follows the same final visual and interaction state where those paths exist. | PARTIAL — all source paths call shared close handler; runtime verification pending |
| 41.45 | Check scroll locking/overscroll behavior during drawer open/close and after interrupted transitions. | NOT STARTED — AUDIT REQUIRED |
| 41.46 | Verify drawer content can scroll independently without moving or trapping the page after close. | NOT STARTED — AUDIT REQUIRED |
| 41.47 | Verify cart updates, empty state and loading/error states do not conflict with drawer transitions. | NOT STARTED — AUDIT REQUIRED |
| 41.48 | Audit responsive width, height, transform direction and reduced-motion handling of the cart drawer. | PARTIAL — responsive exit lifecycle and global reduced-motion rules reviewed; viewport runtime checks pending |
| 41.49 | Audit other drawer implementations only if confirmed in actual source; do not create a new drawer abstraction for this audit. | NOT STARTED — AUDIT REQUIRED |
| 41.50 | Verify drawer focus management and accessible open/close behavior remain intact after any motion fixes; coordinate with Phase 40 contracts. | PARTIAL — existing focus return and trap retained; runtime keyboard verification pending |

### Batch 06 — Modal, Toast & Temporary Feedback

| Step | Contract | Initial Status |
|---|---|---|
| 41.51 | Search actual source for modal/dialog implementations and record exact files/selectors before auditing their motion. | NOT STARTED — AUDIT REQUIRED |
| 41.52 | For each existing modal, audit enter/exit, backdrop and panel timing for coherent state changes. | NOT STARTED — AUDIT REQUIRED |
| 41.53 | Verify modal close/dismiss paths reach one consistent final state without leaving backdrop or body-scroll residue. | NOT STARTED — AUDIT REQUIRED |
| 41.54 | Verify modal animation does not break focus placement, focus return, Escape handling or reduced-motion behavior. | NOT STARTED — AUDIT REQUIRED |
| 41.55 | Search actual source for toast/snackbar implementations and record proven markup, triggers and lifetimes. | NOT STARTED — AUDIT REQUIRED |
| 41.56 | For each existing toast, audit entry/exit timing and prevent motion from making feedback too brief to perceive. | NOT STARTED — AUDIT REQUIRED |
| 41.57 | Verify repeated toast events do not stack duplicates, overlap critical controls or produce stale timers. | NOT STARTED — AUDIT REQUIRED |
| 41.58 | Verify toast dismissal and automatic expiry leave no invisible interactive node or stale timer. | NOT STARTED — AUDIT REQUIRED |
| 41.59 | Verify critical errors/actions are not communicated only through short-lived animated toast feedback. | NOT STARTED — AUDIT REQUIRED |
| 41.60 | If modal/toast source cannot be proven, record the component as absent/unknown rather than fabricating an implementation or PASS. | NOT STARTED — AUDIT REQUIRED |

### Batch 07 — Dropdown, Accordion, Tabs & Disclosure Controls

| Step | Contract | Initial Status |
|---|---|---|
| 41.61 | Inventory actual dropdown/menu implementations and their open/closed state mechanisms. | NOT STARTED — AUDIT REQUIRED |
| 41.62 | Audit dropdown enter/exit transitions and ensure hidden menu items cannot be clicked during closing. | NOT STARTED — AUDIT REQUIRED |
| 41.63 | Verify outside-click, Escape and trigger-toggle paths leave dropdown visuals and state synchronized where implemented. | NOT STARTED — AUDIT REQUIRED |
| 41.64 | Verify dropdown motion does not cause overflow clipping, viewport escape or collision with sticky/floating elements. | NOT STARTED — AUDIT REQUIRED |
| 41.65 | Inventory actual accordion/disclosure controls and the mechanism used to expand/collapse content. | NOT STARTED — AUDIT REQUIRED |
| 41.66 | Audit accordion height/content transitions for clipping, sudden jumps and hidden content remaining interactive. | NOT STARTED — AUDIT REQUIRED |
| 41.67 | Verify repeated expand/collapse actions and dynamic content changes do not leave stale height or state values. | NOT STARTED — AUDIT REQUIRED |
| 41.68 | Inventory actual tabs and audit any motion between tab panels without inventing tab components. | NOT STARTED — AUDIT REQUIRED |
| 41.69 | Verify expanded/selected state semantics stay synchronized with motion state, in coordination with accessibility contracts. | NOT STARTED — AUDIT REQUIRED |
| 41.70 | Verify dropdown, accordion and tab motion is consistent across themes and all applicable viewport styles. | NOT STARTED — AUDIT REQUIRED |

### Batch 08 — Skeleton, Loading & Dynamic Feedback

| Step | Contract | Initial Status |
|---|---|---|
| 41.71 | Inventory actual skeleton/loading indicators in storefront and Admin source, including CSS animation or shimmer. | NOT STARTED — AUDIT REQUIRED |
| 41.72 | Verify skeleton motion is subtle, bounded and does not distract from surrounding content. | NOT STARTED — AUDIT REQUIRED |
| 41.73 | Verify skeletons are removed or replaced when loading ends, errors occur or empty results are returned. | NOT STARTED — AUDIT REQUIRED |
| 41.74 | Verify repeated or failed requests cannot leave skeleton animation running indefinitely without a meaningful state. | NOT STARTED — AUDIT REQUIRED |
| 41.75 | Audit button-level loading/processing feedback and ensure animated indicators do not shift button geometry unnecessarily. | NOT STARTED — AUDIT REQUIRED |
| 41.76 | Audit search suggestion loading/empty/error transitions and stale-request cancellation behavior. | NOT STARTED — AUDIT REQUIRED |
| 41.77 | Audit cart, wishlist, product media and table filtering/pagination feedback where actual loading or state transitions exist. | NOT STARTED — AUDIT REQUIRED |
| 41.78 | Verify loading animations do not obscure status text, focus indicators or important controls. | NOT STARTED — AUDIT REQUIRED |
| 41.79 | Verify Admin loading and data-table feedback uses Admin-owned motion tokens and styles. | NOT STARTED — AUDIT REQUIRED |
| 41.80 | Verify every audited loading animation has a bounded completion/error path or document the proven dependency that prevents confirmation. | NOT STARTED — AUDIT REQUIRED |

### Batch 09 — Reduced Motion, Preferences & Safety

| Step | Contract | Initial Status |
|---|---|---|
| 41.81 | Audit storefront `prefers-reduced-motion: reduce` coverage against all observed transitions, keyframes, transforms and smooth scrolling. | PARTIAL — global duration/iteration and scroll-behavior rules confirmed; full declaration inventory pending |
| 41.82 | Audit Admin reduced-motion coverage separately without leaking storefront selectors or tokens. | PARTIAL — Admin-owned reduced-motion rule confirmed; full declaration inventory pending |
| 41.83 | Ensure nonessential movement is removed or minimized under reduced motion while state changes remain understandable. | NOT STARTED — AUDIT REQUIRED |
| 41.84 | Verify reduced motion does not leave hidden elements, stuck states or broken JS lifecycle assumptions. | NOT STARTED — AUDIT REQUIRED |
| 41.85 | Detect infinite, looping or auto-starting animations and confirm whether each is necessary, stoppable and safe. | NOT STARTED — AUDIT REQUIRED |
| 41.86 | Check flashing, rapid oscillation, parallax and large spatial movement if any such effects are found in source. | NOT STARTED — AUDIT REQUIRED |
| 41.87 | Verify smooth scrolling and scroll-triggered effects respect reduced-motion preference. | PARTIAL — both settings files set scroll-behavior:auto under reduced motion; runtime verification pending |
| 41.88 | Verify user preference changes do not require a page reload where the browser updates media-query matching dynamically. | NOT STARTED — AUDIT REQUIRED |
| 41.89 | Check reduced-motion and normal-motion behavior in both themes and each applicable viewport stylesheet. | NOT STARTED — AUDIT REQUIRED |
| 41.90 | Record untestable OS/browser preference checks as pending runtime evidence rather than PASS. | NOT STARTED — AUDIT REQUIRED |

### Batch 10 — Responsive, Theme & Admin/Storefront Regression

| Step | Contract | Initial Status |
|---|---|---|
| 41.91 | Verify motion behavior at Mobile ≤600px for each existing audited component. | NOT STARTED — AUDIT REQUIRED |
| 41.92 | Verify motion behavior at Tablet 601–1009px for each existing audited component. | NOT STARTED — AUDIT REQUIRED |
| 41.93 | Verify motion behavior at Desktop ≥1010px for each existing audited component. | NOT STARTED — AUDIT REQUIRED |
| 41.94 | Detect viewport-specific overrides that accidentally disable, duplicate or contradict component transitions. | NOT STARTED — AUDIT REQUIRED |
| 41.95 | Verify direction-sensitive motion is appropriate for the actual RTL layout and does not assume LTR placement. | NOT STARTED — AUDIT REQUIRED |
| 41.96 | Verify light/dark theme changes do not trigger unintended layout movement or excessive cross-theme animation. | NOT STARTED — AUDIT REQUIRED |
| 41.97 | Verify Admin and storefront motion rules remain isolated after any fixes. | NOT STARTED — AUDIT REQUIRED |
| 41.98 | Check sticky headers, floating controls, overlays and drawers for motion overlap or focus obstruction regressions. | NOT STARTED — AUDIT REQUIRED |
| 41.99 | Verify animation/transition fixes preserve existing interaction behavior and do not change API or storage contracts. | NOT STARTED — AUDIT REQUIRED |
| 41.100 | Run a source-level regression map covering all audited components, viewport manifests and motion tokens. | NOT STARTED — AUDIT REQUIRED |

### Batch 11 — Test Coverage, Evidence & Phase Exit Criteria

| Step | Contract | Initial Status |
|---|---|---|
| 41.101 | Inventory existing tests and scripts that cover motion/interaction behavior before adding or changing tests. | NOT STARTED — AUDIT REQUIRED |
| 41.102 | Define read-only source assertions for token usage, reduced-motion rules and prohibited duplicate/conflicting declarations. | PARTIAL — read-only PHP source test created; execution/lint pending |
| 41.103 | Add source assertions for verified component state selectors and JS/CSS state synchronization without assuming runtime success. | PARTIAL — drawer lifecycle and responsive selectors covered by source test; execution pending |
| 41.104 | Ensure test assertions tolerate explicitly absent components while failing on broken references to components proven to exist. | NOT STARTED — AUDIT REQUIRED |
| 41.105 | Run available syntax/static checks when tooling is available and report each actual command/result. | NOT STARTED — AUDIT REQUIRED |
| 41.106 | Record browser interaction tests for hover, focus, press, enter/exit and repeated rapid input as pending or executed with evidence. | NOT STARTED — AUDIT REQUIRED |
| 41.107 | Record reduced-motion tests with browser/OS preference evidence; do not infer results from CSS source alone. | NOT STARTED — AUDIT REQUIRED |
| 41.108 | Run regression checks for drawer/modal/toast/dropdown/accordion/skeleton only where each implementation is proven to exist. | NOT STARTED — AUDIT REQUIRED |
| 41.109 | Confirm no backend/API/database/server authorization files were changed during this frontend-only phase. | NOT STARTED — AUDIT REQUIRED |
| 41.110 | Publish final evidence matrix, unresolved defects/dependencies, PASS/FAIL status per batch and LOCK decision; phase cannot PASS while required evidence is missing. | NOT STARTED — AUDIT REQUIRED |

## 3. Initial Status and Guardrails

- Phase status: **IN PROGRESS — CONTRACT EXTRACTION CREATED; IMPLEMENTATION/AUDIT NOT YET COMPLETED**.
- Batch count: **11**.
- Contract count: **110**.
- PASS: **NO**.
- LOCK: **NO**.
- The contract list defines the audit plan; it does not claim any contract has passed.
- Source-level work may continue while XAMPP/browser tooling is unavailable. Runtime-dependent results remain pending.
- Preserve Phase 40's unmerged draft state; do not merge either phase without user approval.
- Backend Freeze: **PRESERVED**.

## 4. Implementation Checkpoint — 2026-10-23

### Source changes applied on `phase-41-motion-interaction-audit`

- Fixed a confirmed cart drawer exit-lifecycle defect: removing `is-open` previously hid the drawer before the panel transform could complete. The JS now enters `is-closing`, waits for the panel transform `transitionend`, and uses a 350 ms cleanup fallback for reduced-motion or missing events.
- Reopening the drawer cancels pending close cleanup. Existing Escape handling, focus trap and focus restoration were retained.
- Added responsive `.cart-drawer.is-closing` rules in mobile, tablet and desktop styles so the panel/overlay can exit while pointer interaction is disabled.
- Added `test-phase-41-motion-interaction-audit.php`, a read-only 25-assertion source check for motion tokens, reduced-motion rules, responsive drawer close state and storefront/Admin asset separation.
- Existing storefront and Admin reduced-motion rules and existing motion tokens were confirmed in source; no redundant generic CSS abstraction was added.

### Validation limits

- The PHP test has **not** been executed or linted in XAMPP in this workflow. Its assertions are not a PASS result.
- Browser checks for normal/reduced motion, rapid open-close-open, Escape, overlay click, keyboard focus, theme and all breakpoints remain pending.
- The remaining contracts still require full source inventory and/or runtime evidence. This is a partial implementation checkpoint, not completion of all 110 contracts.

**Current status:** `IN PROGRESS`; `PASS = NO`; `LOCK = NO`. Backend Freeze preserved.
