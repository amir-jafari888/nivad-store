# NivAD — Phase 33: Media Experience

Status: IMPLEMENTED — LOCK=NO until CI and browser/XAMPP media regression evidence is complete.

## Batch 01 — Media Experience

### 33.01 — Product Images
Use the existing Storefront Product API `product.media` collection as the source for product media. No new backend media contract is invented.

Evidence:
- `assets/js/modules/product-detail.js` calls `renderGallery(product.media)`.
- `assets/js/modules/product-media.js` enhances the rendered main image and gallery images.

### 33.02 — Gallery
Keep the existing primary-media selection, main-image rendering, thumbnail gallery, and click-to-change behavior.

Evidence:
- Existing primary selection remains `is_primary || media[0]`.
- Existing gallery buttons remain intact.
- Media state enhancement is layered on top without replacing the product-detail renderer.

### 33.03 — Thumbnail
Thumbnails remain native buttons, keep real `thumbnail_url`/`url` sources, are keyboard reachable, and retain visible focus treatment.

Evidence:
- Existing `button.type = "button"`.
- Existing `thumbnail_url || url` source.
- Existing `:focus-visible` styling.

### 33.04 — Lazy Loading
Gallery thumbnails use lazy loading; the media module also enforces `loading="lazy"` for dynamically rendered thumbnails.

### 33.05 — Placeholder
Missing media and media without a usable `src` receive a deterministic local SVG placeholder. This avoids inventing an external placeholder URL.

### 33.06 — Broken Image
Image load errors are caught and replaced with the same safe placeholder. The failure state is exposed through `data-media-state` and `data-media-placeholder`.

### 33.07 — Avatar
No real product-avatar backend contract was proven, so no avatar endpoint or field is invented. The media fallback remains dependency-safe for future avatar/image consumers.

Status: DEPENDENCY UNKNOWN for a dedicated backend avatar contract.

### 33.08 — Responsive Images
The media module supports optional backend-provided `data-media-srcset` / `data-media-sizes` attributes and always applies responsive `sizes` defaults. No unproven image-resizing URL convention is invented.

### 33.09 — Loading State
Media loading is exposed through `data-media-state="loading"`, with breakpoint-specific visual treatment for main and thumbnail media.

### 33.10 — Failure State
Media failure is exposed through `data-media-state="failure"` and accessible fallback alt text, with mobile/tablet/desktop styling.

## Implementation files

- `assets/js/modules/product-media.js`
- `assets/js/main.js`
- `assets/css/mobile/product-media-mobile.css`
- `assets/css/tablet/product-media-tablet.css`
- `assets/css/desktop/product-media-desktop.css`
- `test-phase-31-content-support.php`
- `.github/workflows/phase-33-media-experience.yml`

## Regression gate

Required before LOCK=YES:

1. JavaScript syntax checks.
2. PHP syntax check.
3. Canonical read-only contract suite.
4. Browser/XAMPP product-detail regression at mobile, tablet, and desktop widths.
5. Verify primary media, thumbnail selection, lazy loading, placeholder, broken-image fallback, loading/failure states, and responsive behavior.
6. Verify no existing product-detail/cart/wishlist/compatibility behavior regressed.
