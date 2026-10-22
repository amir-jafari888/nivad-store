const smartBundle = () => {
  const root = document.querySelector("[data-smart-bundle]");
  if (!root) return;

  const state = root.querySelector("[data-smart-bundle-state]");
  const error = root.querySelector("[data-smart-bundle-error]");
  const content = root.querySelector("[data-smart-bundle-content]");
  const selectorWrap = root.querySelector("[data-smart-bundle-selector-wrap]");
  const selector = root.querySelector("[data-smart-bundle-product]");

  const baseUrl = String(window.NIVAD_BASE_URL || "").replace(/\/$/, "");

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const api = async (path) => {
    const response = await fetch(
      `${baseUrl}/api/index.php?path=${encodeURIComponent(path)}`,
      {
        method: "GET",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP_${response.status}`);
    }

    return response.json();
  };

  const setLoading = (message) => {
    state.hidden = false;
    state.textContent = message;
    error.hidden = true;
    content.hidden = true;
  };

  const setError = (message) => {
    state.hidden = true;
    content.hidden = true;
    error.hidden = false;
    error.textContent = message;
  };

  const formatMoney = (value, currency) => {
    const amount = Number(value || 0);

    return `${amount.toLocaleString("fa-IR")} ${escapeHtml(currency || "")}`;
  };

  const render = (bundle) => {
    if (!bundle || !Array.isArray(bundle.items) || !bundle.items.length) {
      state.hidden = false;
      state.textContent = "در حال حاضر ست مناسبی برای نمایش وجود ندارد.";
      content.hidden = true;
      return;
    }

    state.hidden = true;
    error.hidden = true;
    content.hidden = false;

    const items = bundle.items
      .map((item, index) => {
        const isPrimary = index === 0;

        return `
          <article class="smart-bundle__item${isPrimary ? " is-primary" : ""}">
            <div class="smart-bundle__item-meta">
              <span>${isPrimary ? "محصول اصلی" : "مکمل پیشنهادی"}</span>
              <span>${escapeHtml(item.relation_type || "")}</span>
            </div>

            <h3>${escapeHtml(item.name)}</h3>

            <p>
              ${escapeHtml(item.variant_name || "")}
            </p>

            <strong>
              ${formatMoney(item.price, item.currency)}
            </strong>

            ${
              Number(item.compare_at_price) > Number(item.price)
                ? `<del>${formatMoney(
                    item.compare_at_price,
                    item.currency,
                  )}</del>`
                : ""
            }

            ${
              !isPrimary
                ? `
                  <button
                    type="button"
                    class="smart-bundle__item-cart"
                    data-cart="${escapeHtml(item.slug)}"
                    data-product-variant-id="${Number(item.variant_id)}"
                    data-product-name="${escapeHtml(item.name)}"
                    data-product-price="${Number(item.price)}"
                    data-product-brand=""
                    data-product-spec="${escapeHtml(item.variant_name || "")}">
                    افزودن
                  </button>
                `
                : ""
            }
          </article>
        `;
      })
      .join("");

    content.innerHTML = `
      <div class="smart-bundle__items">
        ${items}
      </div>

      <aside class="smart-bundle__summary">
        <span class="smart-bundle__summary-label">
          ارزش اصلی
        </span>

        <strong>
          ${formatMoney(bundle.original_total, bundle.currency)}
        </strong>

        <span class="smart-bundle__summary-label">
          مجموع فعلی
        </span>

        <strong>
          ${formatMoney(bundle.bundle_total, bundle.currency)}
        </strong>

        <div class="smart-bundle__saving">
          <span>
            صرفه‌جویی
          </span>

          <small>
            ${escapeHtml(bundle.saving_percentage)}٪
          </small>
        </div>

        <strong>
          ${formatMoney(bundle.saving_amount, bundle.currency)}
        </strong>

        <button
          type="button"
          class="smart-bundle__cart"
          data-smart-bundle-add>
          افزودن مکمل‌ها به سبد
        </button>
      </aside>
    `;

    const addButton = content.querySelector("[data-smart-bundle-add]");

    addButton?.addEventListener("click", () => {
      content
        .querySelectorAll("[data-cart]")
        .forEach((button) => button.click());

      addButton.textContent = "مکمل‌ها به سبد اضافه شدند";

      addButton.disabled = true;
    });
  };

  const loadBundle = async (productId = "") => {
    setLoading("در حال ساخت ست پیشنهادی...");

    try {
      const path = productId
        ? `/storefront/smart-bundles&product_id=${encodeURIComponent(
            productId,
          )}`
        : "/storefront/smart-bundles";

      const response = await api(path);

      render(response?.data?.bundle || null);
    } catch (requestError) {
      console.error(requestError);
      setError("دریافت ست پیشنهادی با خطا مواجه شد.");
    }
  };

  const loadProducts = async () => {
    try {
      const response = await api("/storefront/products");
      const products = response?.data?.products;

      if (!Array.isArray(products) || !products.length) {
        return;
      }

      selector.innerHTML = `
        <option value="">
          محصول منتخب
        </option>
      `;

      products.forEach((product) => {
        const option = document.createElement("option");

        option.value = String(product.id);
        option.textContent = String(product.name || "");

        selector.appendChild(option);
      });

      selectorWrap.hidden = false;
    } catch (requestError) {
      console.warn("Smart bundle product selector unavailable.", requestError);
    }
  };

  selector?.addEventListener("change", () => {
    loadBundle(selector.value);
  });

  loadProducts();
  loadBundle();
};

export default smartBundle;
