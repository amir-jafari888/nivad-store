const PLACEHOLDER =
  'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"%3E%3Crect width="800" height="600" fill="black"/%3E%3Cpath d="M180 440l130-150 100 100 80-90 130 140H180z" fill="gray"/%3E%3Ccircle cx="300" cy="230" r="55" fill="dimgray"/%3E%3C/svg%3E';

const toSrcSet = (value) => {
  if (Array.isArray(value)) {
    return value
      .map((item) => {
        if (typeof item === "string") {
          return item.trim();
        }

        if (!item || typeof item !== "object") {
          return "";
        }

        const url = String(item.url || item.src || "").trim();
        const descriptor = String(item.descriptor || item.width || "").trim();

        if (!url) {
          return "";
        }

        return descriptor ? `${url} ${descriptor}` : url;
      })
      .filter(Boolean)
      .join(", ");
  }

  return String(value || "").trim();
};

const setPlaceholder = (image, alt = "تصویر محصول در دسترس نیست") => {
  image.src = PLACEHOLDER;
  image.removeAttribute("srcset");
  image.removeAttribute("sizes");
  image.alt = alt;
  image.dataset.mediaState = "failure";
  image.dataset.mediaPlaceholder = "true";

  const frame = image.closest(
    ".product-detail-gallery__main, .product-detail-gallery__thumb",
  );

  if (frame) {
    frame.dataset.mediaState = "failure";
  }
};

const setMediaState = (image, state) => {
  image.dataset.mediaState = state;
  image.dataset.mediaPlaceholder = "false";

  const frame = image.closest(
    ".product-detail-gallery__main, .product-detail-gallery__thumb",
  );

  if (frame) {
    frame.dataset.mediaState = state;
  }
};

const applyResponsiveSource = (image) => {
  const srcset = toSrcSet(image.dataset.mediaSrcset);
  const sizes = String(image.dataset.mediaSizes || "").trim();

  if (srcset) {
    image.srcset = srcset;
  }

  if (sizes) {
    image.sizes = sizes;
  } else if (image.closest(".product-detail-gallery__thumb")) {
    image.sizes = "80px";
  } else {
    image.sizes = "(max-width: 600px) 100vw, (max-width: 1009px) 52vw, 60vw";
  }
};

const wireImage = (image, fallbackAlt) => {
  if (
    !(image instanceof HTMLImageElement) ||
    image.dataset.mediaEnhanced === "true"
  ) {
    return;
  }

  image.dataset.mediaEnhanced = "true";
  image.decoding = "async";
  applyResponsiveSource(image);

  image.addEventListener("load", () => {
    if (image.dataset.mediaPlaceholder === "true") {
      return;
    }

    setMediaState(image, "loaded");
  });

  image.addEventListener("error", () => {
    if (image.dataset.mediaPlaceholder === "true") {
      return;
    }

    setPlaceholder(image, fallbackAlt);
  });

  if (!image.getAttribute("src")) {
    setPlaceholder(image, fallbackAlt);
    return;
  }

  if (image.complete && image.naturalWidth > 0) {
    setMediaState(image, "loaded");
  } else {
    setMediaState(image, "loading");
  }
};

const enhanceGallery = (root) => {
  const mainImage = root.querySelector("[data-product-main-image]");
  const gallery = root.querySelector("[data-product-gallery]");
  const avatars = root.querySelectorAll("[data-media-avatar] img");

  if (mainImage) {
    mainImage.loading = "eager";
    wireImage(mainImage, mainImage.alt || "تصویر محصول");
  }

  gallery?.querySelectorAll("img").forEach((image) => {
    image.loading = "lazy";
    wireImage(image, image.alt || "تصویر محصول");
  });

  avatars.forEach((image) => {
    image.loading = "lazy";
    wireImage(image, image.alt || "تصویر کاربر");
  });
};

const productMedia = () => {
  const root = document.querySelector("[data-product-detail]");

  if (!root) {
    return;
  }

  enhanceGallery(root);

  const observer = new MutationObserver(() => enhanceGallery(root));
  const gallery = root.querySelector("[data-product-gallery]");

  if (gallery) {
    observer.observe(gallery, { childList: true, subtree: true });
  }

  document.addEventListener("product-detail:loaded", () => {
    window.setTimeout(() => enhanceGallery(root), 0);
  });
};

export default productMedia;
