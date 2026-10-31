# Admin Phase 5 — Structured CMS and Visual Section Studio

Status: implementation in progress; database and runtime gates remain external.

## Contract and architecture

- Pages are allowlisted by `AdminContentRegistry`; the current contract covers the landing page and the eight existing public content pages.
- A page contains ordered sections. A section stores structured JSON content and a separately validated design-token payload.
- Raw HTML, scripts, event attributes, arbitrary CSS and arbitrary page paths are not accepted from the editor.
- Existing homepage components remain the renderers for native interactive modules (catalog, discovery, categories, compatibility, setup and smart bundle). CMS values override their supported copy and outer visual tokens; product/catalog data continues to come from existing Storefront APIs.
- Content pages switch from legacy PHP markup only when all registered sections for that page have been published. This prevents partially published pages.
- Draft changes do not change storefront output. Publishing copies draft JSON to the published snapshot.
- The existing `admin_content_revisions` table stores immutable per-section snapshots. Restore creates a new draft revision; it does not overwrite the published version.
- The existing `admin_media_assets` table and `AdminMediaStorageService` are reused. Files remain outside the web root and are served publicly only when referenced by a published visible section.
- Scheduled timestamps are normalized to UTC. The CLI worker must be invoked by Windows Task Scheduler or cron; the application does not start a background process itself.

## Batches — 8 × 10 sub-steps

### Batch 01 — Architecture and contracts
1. [x] Audit current public PHP pages and homepage partial order.
2. [x] Define allowlisted CMS page registry.
3. [x] Define supported section types.
4. [x] Define structured content JSON contract.
5. [x] Define validated design-token contract.
6. [x] Reuse the existing admin permission catalog.
7. [x] Reuse existing media and revision foundation tables.
8. [x] Define draft/published separation.
9. [x] Define soft-delete and restore behavior.
10. [x] Add the additive CMS migration.

### Batch 02 — Database and persistence
1. [x] Create page table.
2. [x] Create section table.
3. [x] Add page/section ordering indexes.
4. [x] Add draft and published content snapshots.
5. [x] Add schedule fields.
6. [x] Seed landing page sections.
7. [x] Seed public content page records.
8. [x] Seed public page sections from existing copy.
9. [x] Reuse section revision table.
10. [ ] Apply migration and verify schema on XAMPP (user-run DB gate).

### Batch 03 — CRUD, ordering and lifecycle
1. [x] Read page and section records.
2. [x] Create structured section.
3. [x] Update title and content.
4. [x] Update visual settings.
5. [x] Soft-delete section.
6. [x] Restore archived section.
7. [x] Reorder sections.
8. [x] Save section revisions.
9. [x] Restore selected revision to draft.
10. [ ] Execute CRUD/revision integration tests against XAMPP.

### Batch 04 — Media library
1. [x] Reuse private image upload validator.
2. [x] Persist media metadata in existing media table.
3. [x] List active media assets.
4. [x] Update alt text.
5. [x] Block deletion while referenced by draft/published/history.
6. [x] Soft-delete media.
7. [x] Add authenticated admin image delivery.
8. [x] Add public delivery restricted to published references.
9. [ ] Configure private storage path on target Apache/XAMPP.
10. [ ] Verify upload, replace, delivery and deletion on XAMPP.

### Batch 05 — Visual editor and preview
1. [x] Build page/section navigator.
2. [x] Build content fields.
3. [x] Build image picker and upload UI.
4. [x] Build item/card editor.
5. [x] Build solid background controls.
6. [x] Build gradient controls.
7. [x] Build spacing, radius and alignment controls.
8. [x] Build actual native component preview frame.
9. [x] Update preview without a page refresh.
10. [ ] Browser-verify preview fidelity across desktop/tablet/mobile.

### Batch 06 — Publishing and version history
1. [x] Save draft without changing published storefront data.
2. [x] Publish a section snapshot.
3. [x] Schedule a future publication time.
4. [x] Add due-publication repository operation.
5. [x] Add CLI publication worker.
6. [x] Add per-section revision history API.
7. [x] Restore historical revision as draft.
8. [x] Preserve previous snapshots.
9. [ ] Configure Task Scheduler/cron on deployment host.
10. [ ] Verify UTC behavior and scheduled publication against MySQL.

### Batch 07 — Storefront integration
1. [x] Add CMS-aware landing page renderer.
2. [x] Preserve legacy sections until each draft is published.
3. [x] Keep existing interactive catalog and discovery modules.
4. [x] Bind editable copy to native landing modules.
5. [x] Bind visual design tokens to native section wrappers.
6. [x] Add generic structured section renderer.
7. [x] Integrate public content-page fallback.
8. [x] Require complete page publication before switching content pages.
9. [ ] Verify every existing public page route after migration.
10. [ ] Verify live catalog, discovery, setup and smart-bundle behavior after publish.

### Batch 08 — Security, regression and handoff
1. [x] Add CSRF-protected mutation routes.
2. [x] Add explicit route permission contracts through feature prefixes.
3. [x] Escape text at render time.
4. [x] Restrict links to safe relative/HTTPS URLs.
5. [x] Validate media references against active assets.
6. [x] Keep private media outside document root.
7. [x] Restrict public media access to published visible content.
8. [ ] Run PHP/JS syntax and contract CI.
9. [ ] Run XAMPP database, media and browser regression.
10. [ ] Deliver migration SQL and operational configuration; request user DB execution.

## Required database order

1. Apply the already-existing Admin Panel foundation migration that creates `admin_content_revisions` and `admin_media_assets`.
2. Apply the Phase 3 and Phase 4 migrations if not already applied.
3. Apply `database/migrations/20261030_admin_cms_phase5.sql`.

No migration is executed by this implementation. Back up the target database first.

## Runtime configuration

Set `NIVAD_PRIVATE_STORAGE_PATH` to an absolute directory outside the Apache document root. The media uploader refuses to run if the path is missing, relative, or resolves inside the web root.

Run `php bin/admin-content-publish-due.php` from Task Scheduler/cron at the desired interval. The worker uses UTC database time and writes no output to a public endpoint.
