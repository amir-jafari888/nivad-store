const collection = () => {
  const root = document.querySelector("[data-collection-page]");

  if (!root) {
    return;
  }

  const apiBase = root.dataset.apiBase;
  const slug = root.dataset.collectionSlug || "";
  const title = root.querySelector("[data-collection-title]");
  const description = root.querySelector("[data-collection-description]");
  const products = root.querySelector("[data-collection-products]");
  const count = root.querySelector("[data-collection-count]");
  const state = root.querySelector("[data-collection-state]");
  const loading = root.querySelector("[data-collection-loading]");
  const empty = root.querySelector("[data-collection-empty]");
  const error = root.querySelector("[data-collection-error]");
  const retry = root.querySelector("[data-collection-retry]");

  if (!apiBase || !slug || !products || !state) {
    return;
  }

  let isLoading = false;
  let requestToken = 0;

  const setState = (value) => {
    loading?.toggleAttribute("hidden", value !== "loading");
    empty?.toggleAttribute("hidden", value !== "empty");
    error?.toggleAttribute("hidden", value !== "error");
    retry?.toggleAttribute("hidden", value !== "error");
    products.toggleAttribute("aria-busy", value === "loading");
    root.dataset.state = value;
    state.dataset.state = value;
  };

  const request = async (token) => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set(
      "path",
      `/collections/slug/${encodeURIComponent(slug)}`,
    );

    const response = await fetch(url.toString(), {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json();

    if (token !== requestToken) {
      return null;
    }

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "دریافت اطلاعات مجموعه ناموفق بود.");
    }

    return payload?.data ?? {};
  };

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (value, currency = "IRR") => {
    const numericValue = Number(value);

    if (!Number.isFinite(numericValue)) {
      return "قیمت نامشخص";
    }

    return `${numericValue.toLocaleString("fa-IR")} ${
      currency === "IRR" ? "ریال" : escapeHTML(currency)
    }`;
  };

  const renderProducts = (items) => {
    products.replaceChildren();

    if (!Array.isArray(items) || items.length === 0) {
      count.textContent = "۰ محصول";
      setState("empty");
      return;
    }

    products.innerHTML = items
      .map((item) => {
        const variant = item.variant || {};
        const productSlug = encodeURIComponent(item.slug || "");
        const productUrl = `${
          window.NIVAD_BASE_URL || ""
        }/products/${productSlug}`;

        return `
          <article class="collection-product">
            <a class="collection-product__link" href="${productUrl}">
              <div class="collection-product__media">
                <span aria-hidden="true">
                  ${escapeHTML(String(item.name || "").slice(0, 1))}
                </span>
              </div>
              <div class="collection-product__body">
                <span class="collection-product__brand">
                  ${escapeHTML(item.brand || "")}
                </span>
                <h3>
                  ${escapeHTML(item.name || "محصول")}
                </h3>
                <p>
                  ${escapeHTML(
                    item.short_description || item.shortDescription || "",
                  )}
                </p>
                <strong>
                  ${formatPrice(variant.price ?? item.price, variant.currency)}
                </strong>
              </div>
            </a>
          </article>
        `;
      })
      .join("");

    count.textContent = `${items.length.toLocaleString("fa-IR")} محصول`;

    setState("ready");
  };

  const load = async () => {
    if (isLoading) {
      return;
    }

    const token = ++requestToken;

    isLoading = true;
    setState("loading");

    try {
      const data = await request(token);

      if (!data || token !== requestToken) {
        return;
      }

      const collectionData = data.collection || {};
      const items = Array.isArray(data.items) ? data.items : [];

      if (title) {
        title.textContent = collectionData.name || "مجموعه محصولات";
      }

      if (description) {
        description.textContent =
          collectionData.description ||
          "محصولات منتخب این مجموعه را مشاهده کنید.";
      }

      renderProducts(items);
    } catch (requestError) {
      if (token !== requestToken) {
        return;
      }

      console.error("Collection request failed:", requestError);

      products.replaceChildren();
      count.textContent = "۰ محصول";
      setState("error");
    } finally {
      if (token === requestToken) {
        isLoading = false;
      }
    }
  };

  retry?.addEventListener("click", load);

  load();
};

export default collection;
