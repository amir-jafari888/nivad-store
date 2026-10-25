const storefront = () => {
  const categoriesSection = document.querySelector("[data-categories-section]");
  const categoriesContainer =
    categoriesSection?.querySelector(".categories-list");
  const productsRoot = document.querySelector("[data-home-products]");
  const productsContainer = productsRoot?.querySelector("[data-home-products-grid]") || document.querySelector(".product-grid");

  if (!categoriesContainer && !productsContainer) {
    return;
  }

  const api = async (path, options = {}) => {
    const apiBase = categoriesSection?.dataset.apiBase || productsRoot?.dataset.apiBase || "/api/index.php";
    const apiUrl = new URL(apiBase, window.location.origin);

    apiUrl.searchParams.set("path", path);
    apiUrl.searchParams.set("_", Date.now().toString());

    const response = await fetch(apiUrl.toString(), {
      credentials: "same-origin",
      ...options,
    });

    if (!response.ok) {
      throw new Error(`Storefront API failed: ${response.status}`);
    }

    const result = await response.json();

    if (!result?.success) {
      throw new Error(result?.message || "Storefront API request failed.");
    }

    return result.data;
  };

  const renderCategories = (items) => {
    if (!categoriesContainer) return;

    const categoryBase =
      categoriesSection?.dataset.categoryBase || "/categories";

    categoriesContainer.replaceChildren();

    if (!items.length) {
      const state = document.createElement("p");
      state.className = "categories-list__state";
      state.textContent = "در حال حاضر دسته‌بندی فعالی ثبت نشده است.";
      categoriesContainer.appendChild(state);
      return;
    }

    items.forEach((category) => {
      if (!category || !category.slug || !category.name) return;

      const link = document.createElement("a");
      link.href =
        `${categoryBase.replace(/\/$/, "")}/` +
        encodeURIComponent(category.slug);
      link.className = "category-card";

      link.innerHTML = `
        <span class="category-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24">
            <rect x="5" y="5" width="14" height="14" rx="2"></rect>
            <path d="M8 9h8M8 12h6M8 15h4"></path>
          </svg>
        </span>
        <span class="category-name"></span>
      `;

      const categoryName = link.querySelector(".category-name");
      if (categoryName) categoryName.textContent = category.name;

      categoriesContainer.appendChild(link);
    });
  };

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (price, currency = "IRR") => {
    if (price === null || price === undefined) return "قیمت نامشخص";

    const numericPrice = Number(price);
    if (!Number.isFinite(numericPrice)) return "قیمت نامشخص";

    const formatted = new Intl.NumberFormat("fa-IR").format(numericPrice);
    return currency === "IRR"
      ? `${formatted} تومان`
      : `${formatted} ${currency}`;
  };

  const renderProducts = (items) => {
    if (!productsContainer) return;

    productsContainer.replaceChildren();

    if (!items.length) {
      const state = document.createElement("p");
      state.className = "product-grid-state";
      state.textContent = "در حال حاضر محصول فعالی برای نمایش وجود ندارد.";
      productsContainer.appendChild(state);
      return;
    }

    const productBase = productsRoot?.dataset.productBase || "/products";

    items.forEach((product) => {
      if (!product || !product.slug || !product.name) return;

      const article = document.createElement("article");
      article.className = "product-card";

      const variant = product.variant ?? null;
      const mediaItems = Array.isArray(product.media)
        ? product.media.filter((item) => item && item.url)
        : [];
      const primaryMedia =
        mediaItems.find((item) => item.is_primary) || mediaItems[0] || null;
      const detailUrl =
        `${productBase.replace(/\/$/, "")}/` +
        encodeURIComponent(product.slug);

      article.innerHTML = `
        <div class="product-card-image">
          ${product.is_featured ? '<span class="product-badge">ویژه</span>' : ""}

          <button
            class="product-wishlist"
            type="button"
            aria-label="افزودن محصول به علاقه‌مندی‌ها"
            aria-pressed="false"
            data-wishlist="${product.id}"
            data-wishlist-variant-id="${variant?.id ?? ""}">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"></path>
            </svg>
          </button>

          <a href="${detailUrl}" class="product-image-link" aria-label="مشاهده ${String(product.name).replace(/"/g, "&quot;")}">
            <div class="product-image-placeholder" aria-hidden="true">
              <span>Product</span>
            </div>
          </a>
        </div>

        <div class="product-card-body">
          <span class="product-brand">محصول</span>

          <h3 class="product-name">
            <a href="${detailUrl}"></a>
          </h3>

          <p class="product-spec"></p>

          <div class="product-card-footer">
            <strong class="product-price numeric"></strong>

            <button
              class="product-cart"
              type="button"
              aria-label="افزودن محصول به سبد خرید"
              data-cart="${product.slug}"
              data-product-name="${String(product.name).replace(/"/g, "&quot;")}"
              data-product-price="${variant?.price ?? ""}"
              data-product-brand="${product.brand_id ?? ""}"
              data-product-spec="${String(product.short_description || "").replace(/"/g, "&quot;")}"
              data-variant-id="${variant?.id ?? ""}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"></path>
                <circle cx="10" cy="20" r="1"></circle>
                <circle cx="18" cy="20" r="1"></circle>
              </svg>
            </button>
          </div>
        </div>
      `;

      const imageLink = article.querySelector(".product-image-link");
      const mediaUrl = primaryMedia?.thumbnail_url || primaryMedia?.url || "";

      if (imageLink && mediaUrl) {
        const image = document.createElement("img");
        image.className = "product-image-placeholder product-card-image__media";
        image.src = mediaUrl;
        image.alt = String(primaryMedia?.alt || product.name || "");
        image.loading = "lazy";
        image.decoding = "async";
        imageLink.querySelector(".product-image-placeholder")?.replaceWith(image);
      }

      const productName = article.querySelector(".product-name a");
      const productSpec = article.querySelector(".product-spec");
      const productPrice = article.querySelector(".product-price");

      if (productName) productName.textContent = product.name;
      if (productSpec) productSpec.textContent = product.short_description || "";
      if (productPrice) {
        productPrice.textContent = variant
          ? formatPrice(variant.price, variant.currency)
          : "قیمت نامشخص";
      }

      productsContainer.appendChild(article);
    });
  };

  const renderError = (container, message) => {
    if (!container) return;
    container.replaceChildren();

    const state = document.createElement("p");
    state.className = "storefront-load-error";
    state.setAttribute("role", "status");
    state.textContent = message;
    container.appendChild(state);
  };

  const load = async () => {
    const requests = [];

    if (categoriesContainer) {
      requests.push(
        api("/storefront/categories")
          .then((data) => {
            renderCategories(Array.isArray(data?.items) ? data.items : []);
          })
          .catch((error) => {
            console.error(error);
            renderError(categoriesContainer, "دریافت دسته‌بندی‌ها با خطا مواجه شد.");
          }),
      );
    }

    if (productsContainer) {
      requests.push(
        api("/storefront/products")
          .then((data) => {
            renderProducts(Array.isArray(data?.items) ? data.items : []);
          })
          .catch((error) => {
            console.error(error);
            renderError(productsContainer, "دریافت محصولات با خطا مواجه شد.");
          }),
      );
    }

    await Promise.all(requests);
  };

  load().catch((error) => {
    console.error(error);
  });
};

export default storefront;
