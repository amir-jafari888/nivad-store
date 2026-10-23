# NIVAD — Phase 36: Admin Design System

## Source of truth
- `front-roadmap.txt` — Phase 36 title and scope.
- `uiuxrules.md` — Admin UX, Responsive Admin, RTL, Persian Typography and WCAG 2.2 AA requirements.
- Existing storefront CSS import architecture was inspected in `assets/css/main.css`, `assets/css/mobile/mobile.css`, `assets/css/tablet/tablet.css`, and `assets/css/desktop/desktop.css`.

## Admin asset isolation implemented on branch
All Admin design assets now live under `admin/assets/`; Admin pages no longer load the shared storefront design tokens or storefront Admin stylesheet/script paths.

- `admin/assets/css/setting.css`: Admin-owned semantic tokens and Light/Dark values.
- `admin/assets/css/main.css`: imports Admin tokens and the three viewport-specific entry files using the existing project's CSS import convention.
- `admin/assets/css/mobile/mobile.css`, `tablet/tablet.css`, `desktop/desktop.css`: import section-level files for their own breakpoint.
- Each breakpoint has separate layout, component, form, table, and authentication stylesheets. Responsive styling is authored in the corresponding device files; there are no `@media` blocks inside those section files.
- `admin/assets/js/main.js`: imports and initializes Admin modules.
- `admin/assets/js/modules/theme.js`: wraps theme behavior in one function and exports that function as the default export; includes localStorage persistence and ARIA toggle state.
- `admin/includes/auth.php` injects the isolated Admin CSS/JS into authenticated pages. Standalone login, report, return and refund pages load `/admin/assets/css/main.css` and `/admin/assets/js/main.js` directly.
- Admin-only tokens were removed from storefront `assets/css/setting.css`.
- Removed the previous shared-path Admin stylesheet/script after updating consumers.

## Contract mapping
- 36.01–36.10: Admin Foundation and isolated design tokens.
- 36.11–36.20: shared Admin layout/components/forms/tables.
- 36.21–36.30: Light/Dark theme, dedicated mobile/tablet/desktop styles, RTL-compatible logical properties.
- 36.31–36.40: accessible focus states, ARIA theme toggle, asset-link integrity and static regression.

This mapping is an execution breakdown, not literal numbered entries from `front-roadmap.txt`.

## Verification status
- Asset separation and file architecture: IMPLEMENTED on branch.
- Static test: updated, NOT EXECUTED in PHP/XAMPP.
- Browser visual review, keyboard walkthrough, real contrast measurement, PHP runtime checks and full Admin regression: NOT VERIFIED.
- PASS: NOT GRANTED.
- LOCK: NO.

## Risks / follow-up
- Legacy inline styles can override the new design system where selectors are more specific.
- Responsive CSS import qualifiers intentionally follow the existing project convention; individual device stylesheets contain no `@media` rules.
- Run `test-phase-36-admin-design-system.php` from the project root in PHP/XAMPP, fix failures, then perform visual and keyboard regression before requesting PASS/LOCK.
