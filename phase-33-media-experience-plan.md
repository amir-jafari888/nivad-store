# NivAD — Phase 33: Media Experience

Status: IMPLEMENTATION COMPLETE — LOCK=NO until CI and browser/XAMPP media regression evidence is complete.

## Batch 01 — Media Experience — 10 contracts

- **33.01 Product Images:** Existing Storefront `product.media` is consumed by Product Detail; no new backend media contract is invented.
- **33.02 Gallery:** Existing primary-media selection, main-image rendering, thumbnail gallery, and selection behavior are preserved.
- **33.03 Thumbnail:** Existing native thumbnail buttons, real `thumbnail_url || url`, lazy loading, and focus treatment are preserved.
- **33.04 Lazy Loading:** Gallery thumbnails are lazy-loaded and the media module enforces it for dynamically rendered images.
- **33.05 Placeholder:** Missing media receives a deterministic local SVG placeholder.
- **33.06 Broken Image:** `error` events replace failed media with the safe placeholder and expose a failure state.
- **33.07 Avatar:** No dedicated backend avatar contract was proven; the fallback is dependency-safe for future `[data-media-avatar]` consumers without inventing an endpoint or field.
- **33.08 Responsive Images:** Optional `data-media-srcset`/`data-media-sizes` inputs are supported; responsive `sizes` defaults are always applied without assuming an image-resizing URL convention.
- **33.09 Loading State:** Media loading is exposed with `data-media-state="loading"` and breakpoint-specific UI treatment.
- **33.10 Failure State:** Media failure is exposed with `data-media-state="failure"` and accessible fallback text across mobile/tablet/desktop.

## Implementation

- `assets/js/modules/product-media.js`
- `assets/js/main.js`
- `assets/css/mobile/product-media-mobile.css`
- `assets/css/tablet/product-media-tablet.css`
- `assets/css/desktop/product-media-desktop.css`
- `test-phase-31-content-support.php`
- `.github/workflows/phase-33-media-experience.yml`

## Lock gate

LOCK remains **NO** until:
1. GitHub Actions syntax + canonical contract suite passes.
2. Product Detail is checked in XAMPP/browser at mobile, tablet, and desktop widths.
3. Primary media, thumbnail selection, lazy loading, placeholder, broken-image fallback, loading/failure states, and responsive behavior are manually verified.
4. Existing product-detail/cart/wishlist/compatibility behavior remains intact.
