# Phase 31 — Content & Support

Status: IMPLEMENTATION CANDIDATE — LOCK=NO until CI, local XAMPP/browser regression, and business/legal policy review pass.

Scope: content pages and the buying-guide entry point. No API, controller, service, repository, database, checkout, payment, or authentication contract is intentionally changed.

## Execution rules
- Eight batches, ten contracts per batch (80 total).
- Audit actual files before changing contracts; never invent policy, contact details, delivery estimates, fees, or legal promises.
- Read-only static tests only; no database mutations or live API calls.
- One canonical test file: `test-phase-31-content-support.php`. Legacy phase-specific test scripts are removed; the Phase 31 workflow is the single CI entry point for this phase.
- PASS from static checks does not prove browser behavior or business/legal approval.

## Batch 01 — Shared structure and routes (31.01–31.10)
31.01 inventory the seven existing content pages; 31.02 verify shared header/footer; 31.03 verify direct paths; 31.04 validate footer destinations; 31.05 remove placeholder guide links; 31.06 establish the standalone buying guide; 31.07 validate page titles/meta; 31.08 validate UTF-8 source; 31.09 preserve fa/RTL/UTF-8; 31.10 regression scope excludes unrelated commerce/auth contracts.

## Batch 02 — About (31.11–31.20)
31.11 preserve and audit file; 31.12 correct Persian copy; 31.13 align metadata; 31.14 describe only supported store purpose; 31.15 accurately describe product scope; 31.16 semantic heading hierarchy; 31.17 logical sections; 31.18 real internal links; 31.19 no unverified guarantees; 31.20 responsive/theme browser check.

## Batch 03 — Contact (31.21–31.30)
31.21 audit file; 31.22 only verified contact details; 31.23 distinguish real support channels; 31.24 validate link destinations; 31.25 determine whether a contact form contract exists; 31.26 align any form to a real receiver; 31.27 validate client/server if form exists; 31.28 handle duplicate/network/API failures if form exists; 31.29 success only after confirmed persistence; 31.30 accessibility/responsive checks. No invented form/API is added.

## Batch 04 — FAQ (31.31–31.40)
31.31 audit file; 31.32 improve question coverage; 31.33 align product discovery answer; 31.34 align order-tracking answer; 31.35 align returns answer with real process; 31.36 link to actual support page; 31.37 semantic details/summary; 31.38 keyboard/mobile interaction; 31.39 no unsupported promises; 31.40 FAQ link/content regression.

## Batch 05 — Privacy and Terms (31.41–31.50)
31.41 audit privacy file; 31.42 align collection statements with actual forms; 31.43 describe data purposes cautiously; 31.44 do not invent retention/sharing policy; 31.45 no unverified security guarantees; 31.46 audit terms file; 31.47 align with actual order flow; 31.48 align prices/inventory/payment statements; 31.49 mark business/legal review dependency; 31.50 validate metadata, headings, links and encoding.

## Batch 06 — Shipping and Returns (31.51–31.60)
31.51 audit shipping page; 31.52 no unverified delivery coverage; 31.53 no invented delivery time; 31.54 no invented fees; 31.55 align tracking guidance; 31.56 audit returns page; 31.57 require approved eligibility policy; 31.58 do not invent deadlines/exceptions; 31.59 do not invent refund outcomes; 31.60 link to the actual account returns workflow.

## Batch 07 — Buying Guide (31.61–31.70)
31.61 preserve homepage partial; 31.62 verify standalone route; 31.63 add standalone guide page; 31.64 link the “all guides” CTA; 31.65 charger compatibility; 31.66 USB-C versus Thunderbolt; 31.67 HDMI selection; 31.68 replace three placeholder links; 31.69 maintain extensible sections; 31.70 homepage/article regression.

## Batch 08 — Design system, accessibility, SEO and regression (31.71–31.80)
31.71 use setting.css tokens; 31.72 remove unnecessary fixed fallbacks; 31.73 dedicated responsive CSS; 31.74 theme/focus/contrast checks; 31.75 validate links; 31.76 metadata/language/headings; 31.77 keyboard/focus/accessibility; 31.78 PHP lint; 31.79 header/footer/homepage/buying guide/content regression; 31.80 publish evidence-based status and LOCK decision.

## Explicit dependencies / blockers
- Business owner/legal reviewer must approve final Privacy and Terms content.
- Business owner must confirm shipping and returns policy details before any exact deadlines, fees, exclusions, or refund claims are published.
- No verified contact form receiver/API was found in this audit; contact form implementation remains out of scope until a real contract exists.
- Browser, responsive, theme, and actual XAMPP regression remain to be run locally.
