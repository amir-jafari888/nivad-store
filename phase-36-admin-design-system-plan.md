# NIVAD — Phase 36: Admin Design System

## Source of truth
- `front-roadmap.txt` — Phase 36 title and scope.
- `uiuxrules.md` — Admin UX, Dashboard UX, Responsive Admin, RTL, Persian Typography and WCAG 2.2 AA requirements.
- Actual Admin PHP templates and `assets/css/setting.css`.

## Scope
Build an Admin-specific design system aligned with the existing Nivad brand tokens, supporting Light, Dark, Responsive and Accessibility without changing backend contracts or business logic.

## Implemented on branch
- Extended `assets/css/setting.css` with centralized Admin semantic tokens for colors, typography, spacing, controls, shadows, radius, transition, focus and z-index.
- Added `assets/css/admin/admin.css` for shared Admin surfaces, typography, controls, tables, forms, feedback panels, responsive behavior, RTL-compatible logical properties, keyboard focus and reduced motion.
- Added `assets/js/admin-theme.js` for an accessible Light/Dark toggle with localStorage persistence and ARIA state.
- Updated `admin/includes/auth.php` to inject shared assets after legacy inline page styles, preserving existing templates.
- Added direct shared-asset loading to standalone Admin login, report, return and refund pages that do not use the shared Admin auth include.
- Added `test-phase-36-admin-design-system.php` as a read-only static contract/regression test.

## Contract mapping
- 36.01–36.10: Admin Foundation and centralized tokens.
- 36.11–36.20: shared layout/component styling across existing Admin templates.
- 36.21–36.30: Light/Dark theme, three responsive breakpoints and RTL-safe layout rules.
- 36.31–36.40: semantic controls, keyboard focus, ARIA toggle state, reduced motion and static integration regression.

This mapping is an execution breakdown, not literal numbered entries from `front-roadmap.txt`.

## Verification status
- Implementation: IN PROGRESS / initial shared-system rollout committed to this branch.
- Static test: CREATED, NOT EXECUTED in PHP/XAMPP.
- Browser visual review, keyboard walkthrough, real contrast measurement and PHP runtime checks: NOT VERIFIED.
- PASS: NOT GRANTED.
- LOCK: NO.

## Risks / follow-up
- Existing inline page styles may still override the shared system when selectors are more specific; visual review is required.
- Some legacy pages may contain bespoke components requiring page-specific token adoption after regression.
- The standalone static test must be run from the repository root in PHP/XAMPP and all failures resolved before claiming PASS.
