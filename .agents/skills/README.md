# Nivad — Agent Skills (UI/UX)

Project-local skills; tooling only. No application code is changed by using them unless implementation is explicitly requested.

## Taste Skill
- Path: `.agents/skills/taste-skill/`
- Upstream: https://github.com/Leonxlnx/taste-skill
- Pinned commit: `12af471f627f866411b4bd785b19646d3672784c`
- License: MIT, included as `LICENSE`
- Purpose: art direction, page composition, typography, imagery, motion, screenshot review.
- Requirements: Node.js 22+ and Chrome/Edge/Chromium for browser inspection.

## UI UX Pro Max
- Path: `.agents/skills/ui-ux-pro-max/`
- Upstream: https://github.com/nextlevelbuilder/ui-ux-pro-max-skill
- Pinned commit: `d14efff8ed1faa4187f004f79926f428c8074ec7`
- License: MIT, included as `LICENSE`
- Purpose: searchable UI patterns, palettes, typography, accessibility, UX, responsive and stack guidance.
- Requirements: Python 3.x; bundled scripts use the standard library.

## Quick start (PowerShell, from repository root)

```powershell
py -3 .agents/skills/ui-ux-pro-max/scripts/search.py "product detail responsive layout" --domain ux
py -3 .agents/skills/ui-ux-pro-max/scripts/search.py "ecommerce storefront catalog" --design-system -p "Nivad Store"
node .agents/skills/taste-skill/scripts/draw.mjs --help
```

Read the relevant `SKILL.md` first and confirm command flags in its docs. Do not run browser inspection until the local PHP/XAMPP app is running and the target route is known.

## Nivad guardrails
1. Inspect the real route/view, CSS, JS modules, contracts, and tests before claims or edits.
2. Existing design tokens are authoritative. Inspect the actual token source (including `assets/css/setting.css` if present on the checked-out branch); do not create competing token files or hard-code values when tokens apply.
3. Audit and implementation are separate. No product-file edits during an audit unless explicitly requested.
4. Preserve pages, sections, states, routes, API contracts, data attributes, event contracts, and JS module boundaries.
5. Never copy optional reset/base CSS into Nivad or replace the existing design system with a generated one.
6. Use app-screen guidance for admin workflows; do not apply marketing-page rules blindly to operational screens.
7. Report evidence, paths/line references, severity, and concrete fixes. Separate measurable defects from subjective polish.
8. If browser, XAMPP, keyboard, screen-reader, or viewport validation was not run, report BLOCKED/UNKNOWN, not PASS.
9. Do not automatically persist generated design-system files or overwrite existing files.
10. Project contracts, explicit user instructions, security rules, and approved decisions take precedence.

These snapshots are pinned. Do not update from an unpinned `main`; review upstream changes and compatibility in a separate branch. This addition does not install software, run app code, modify the database, or alter product files.
