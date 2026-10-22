const completeYourSetup = () => {
  const root = document.querySelector("[data-product-detail]");

  if (!root) {
    return;
  }

  const section = root.querySelector("[data-complete-setup]");

  const smartSection = root.querySelector("[data-product-smart-section]");

  if (!section || !smartSection) {
    return;
  }

  const productId = Number(root.dataset.productId || 0);

  if (!productId) {
    return;
  }

  const apiBase =
    root.dataset.apiBase || `${window.NIVAD_BASE_URL || ""}/api/index.php`;

  const state = {
    status: "idle",
    items: [],
    selected: new Map(),
    budget: null,
    productId,
    selectedVariantId: null,
  };

  const stateElement = section.querySelector("[data-complete-setup-state]");

  const content = section.querySelector("[data-complete-setup-content]");

  const candidates = section.querySelector("[data-complete-setup-candidates]");

  const budget = section.querySelector("[data-complete-setup-budget]");

  const budgetState = section.querySelector(
    "[data-complete-setup-budget-state]",
  );

  const summary = section.querySelector("[data-complete-setup-summary]");

  const cartButton = section.querySelector("[data-complete-setup-cart]");

  const resetButton = section.querySelector("[data-complete-setup-reset]");

  const message = section.querySelector("[data-complete-setup-message]");

  const error = section.querySelector("[data-complete-setup-error]");

  const retry = section.querySelector("[data-complete-setup-retry]");

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (value, currency = "IRR") => {
    const number = Number(value);

    if (!Number.isFinite(number)) {
      return "قیمت نامشخص";
    }

    return `${number.toLocaleString("fa-IR")} ${
      currency === "IRR" ? "تومان" : escapeHTML(currency)
    }`;
  };

  const relationLabel = (relationType) => {
    if (relationType === "recommended") {
      return "پیشنهاد ویژه";
    }

    if (relationType === "optional") {
      return "اختیاری";
    }

    return "سازگار";
  };

  const compatibilityLabel = (stateValue) => {
    if (stateValue === "compatible") {
      return "سازگاری تأییدشده";
    }

    if (stateValue === "partially_compatible") {
      return "سازگاری جزئی";
    }

    if (stateValue === "not_compatible") {
      return "ناسازگار با محصول انتخاب‌شده";
    }

    return "وضعیت سازگاری نامشخص";
  };

  const compatibilityCertaintyLabel = (value) => {
    if (value === "confirmed") {
      return "اطمینان: تأییدشده";
    }

    if (value === "partial") {
      return "اطمینان: جزئی";
    }

    if (value === "blocked") {
      return "اطمینان: مسدود";
    }

    return "اطمینان: نامشخص";
  };

  const setState = (value, text) => {
    state.status = value;

    if (stateElement) {
      stateElement.textContent = text;
      stateElement.dataset.state = value;
    }
  };

  const showError = (text) => {
    if (!error) {
      return;
    }

    error.textContent = text || "دریافت پیشنهادهای تکمیل خرید ناموفق بود.";

    error.hidden = false;

    setState("error", "دریافت پیشنهادهای تکمیل خرید ناموفق بود.");

    if (retry) {
      retry.hidden = false;
    }
  };

  const hideError = () => {
    if (error) {
      error.hidden = true;
      error.textContent = "";
    }

    if (retry) {
      retry.hidden = true;
    }
  };

  const request = async (path) => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", path);

    const response = await fetch(url.toString(), {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
      credentials: "same-origin",
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "درخواست تکمیل ستاپ ناموفق بود.");
    }

    return payload?.data || {};
  };

  const normalizeItem = (item) => {
    if (!item || typeof item !== "object") {
      return null;
    }

    const id = Number(item.id || 0);

    const variantId = Number(item?.variant?.id || 0);

    const price = Number(item?.variant?.price);

    if (id <= 0 || variantId <= 0 || !Number.isFinite(price) || price < 0) {
      return null;
    }

    return {
      id,
      name: String(item.name || "").trim(),

      slug: String(item.slug || "").trim(),

      shortDescription: String(item.short_description || "").trim(),

      brandName: String(item?.brand?.name || "").trim(),

      variantId,

      variantName: String(item?.variant?.name || "").trim(),

      price,

      compareAtPrice:
        item?.variant?.compare_at_price !== null
          ? Number(item.variant.compare_at_price)
          : null,

      currency: String(item?.variant?.currency || "IRR").trim(),

      availability: String(item?.variant?.availability?.status || "").trim(),

      relationType: String(item.relation_type || "").trim(),

      setupDeviceId: Number(item.setup_device_id || 0),

      setupDeviceName: String(item.setup_device_name || "").trim(),

      compatibility: String(item.recommendation_state || "unknown").trim(),

      compatibilityNote: String(item.compatibility_note || "").trim(),

      compatibilityCertainty: String(
        item.compatibility_certainty || "unknown",
      ).trim(),
    };
  };

  const getSelectedItems = () => Array.from(state.selected.values());

  const getSelectedTotal = () =>
    getSelectedItems().reduce((total, item) => total + item.price, 0);

  const updateBudgetState = () => {
    if (!budgetState) {
      return;
    }

    const total = getSelectedTotal();

    if (state.budget === null) {
      budgetState.textContent = "";
      budgetState.dataset.state = "none";

      return;
    }

    const remaining = state.budget - total;

    if (remaining < 0) {
      budgetState.textContent = `بیش از بودجه: ${formatPrice(
        Math.abs(remaining),
      )}`;

      budgetState.dataset.state = "over";

      return;
    }

    budgetState.textContent = `بودجه باقی‌مانده: ${formatPrice(remaining)}`;

    budgetState.dataset.state = "within";
  };

  const updateSummary = () => {
    if (!summary) {
      return;
    }

    const selected = getSelectedItems();

    const total = getSelectedTotal();

    const valid = selected.every(
      (item) =>
        item.variantId > 0 &&
        Number.isFinite(item.price) &&
        item.availability !== "out_of_stock",
    );

    const complete = selected.length > 0 && valid;

    summary.innerHTML = `
      <div class="complete-setup__summary-row">
        <span>محصولات انتخاب‌شده</span>
        <strong>
          ${selected.length.toLocaleString("fa-IR")}
        </strong>
      </div>

      <div class="complete-setup__summary-row">
        <span>مجموع</span>
        <strong>
          ${formatPrice(total)}
        </strong>
      </div>

      <div class="complete-setup__summary-row">
        <span>وضعیت</span>
        <strong data-state="${complete ? "complete" : "incomplete"}">
          ${complete ? "ست تکمیل شده" : "هنوز محصولی انتخاب نشده"}
        </strong>
      </div>
    `;

    if (cartButton) {
      cartButton.disabled = !complete || state.status === "loading";
    }

    updateBudgetState();
  };

  const renderCandidates = () => {
    if (!candidates) {
      return;
    }

    candidates.innerHTML = "";

    if (!state.items.length) {
      candidates.innerHTML = `
        <div
          class="complete-setup__empty"
          role="status"
          aria-live="polite">
          برای این محصول پیشنهاد مکملی
          ثبت نشده است.
        </div>
      `;

      return;
    }

    state.items.forEach((item) => {
      const selected = state.selected.has(item.id);

      const disabled =
        item.availability === "out_of_stock" ||
        item.compatibility === "not_compatible";

      const article = document.createElement("article");

      article.className = "complete-setup__item";

      article.dataset.productId = String(item.id);

      article.dataset.state = selected ? "selected" : "available";

      article.innerHTML = `
          <div
            class="complete-setup__item-content">

            <span
              class="complete-setup__item-relation">
              ${escapeHTML(relationLabel(item.relationType))}
            </span>

            <h3>
              <a
                href="${
                  window.NIVAD_BASE_URL || ""
                }/products/${encodeURIComponent(item.slug)}">
                ${escapeHTML(item.name)}
              </a>
            </h3>

            ${
              item.brandName
                ? `
                  <span>
                    ${escapeHTML(item.brandName)}
                  </span>
                `
                : ""
            }

            ${
              item.shortDescription
                ? `
                  <p>
                    ${escapeHTML(item.shortDescription)}
                  </p>
                `
                : ""
            }

            <span
              class="complete-setup__item-compatibility"
              data-state="${item.compatibility}">
              ${escapeHTML(compatibilityLabel(item.compatibility))}
            </span>

            ${
              item.compatibilityNote
                ? `
                  <p>
                    ${escapeHTML(item.compatibilityNote)}
                  </p>
                `
                : ""
            }

            <strong
              class="complete-setup__item-price">
              ${escapeHTML(formatPrice(item.price, item.currency))}
            </strong>

            <button
              type="button"
              class="complete-setup__select"
              data-complete-setup-select
              data-product-id="${item.id}"
              aria-pressed="${selected}"
              ${disabled ? "disabled" : ""}>
              ${disabled ? "ناموجود" : selected ? "حذف از ست" : "افزودن به ست"}
            </button>

          </div>
        `;

      candidates.appendChild(article);
    });
  };

  const renderCompleteState = () => {
    if (!content) {
      return;
    }

    content.hidden = state.items.length === 0;

    if (state.items.length > 0) {
      smartSection.hidden = false;
    }
  };

  const toggleItem = (productId) => {
    const item = state.items.find(
      (candidate) => Number(candidate.id) === Number(productId),
    );

    if (!item) {
      return;
    }

    if (state.selected.has(item.id)) {
      state.selected.delete(item.id);
    } else {
      state.selected.set(item.id, item);
    }

    renderCandidates();
    updateSummary();
  };

  const load = async () => {
    hideError();

    setState("checking", "در حال بررسی سازگاری و پیشنهادهای تکمیل خرید...");

    if (content) {
      content.hidden = true;
    }

    try {
      const data = await request(
        `/storefront/products/complete-setup?product_id=${encodeURIComponent(
          state.productId,
        )}`,
      );

      const items = Array.isArray(data.items)
        ? data.items.map(normalizeItem).filter(Boolean)
        : [];

      state.items = items;

      state.selected.clear();

      if (!items.length) {
        setState("empty", "برای این محصول پیشنهاد مکملی ثبت نشده است.");

        if (content) {
          content.hidden = true;
        }

        return;
      }

      setState("ready", "پیشنهادهای تکمیل خرید آماده است.");

      renderCandidates();
      renderCompleteState();
      updateSummary();
    } catch (requestError) {
      showError(requestError?.message);
    }
  };

  const addToCart = () => {
    const cart = window.digitalStoreCart;

    if (!cart || typeof cart.add !== "function") {
      showError("ماژول سبد خرید در دسترس نیست.");

      return;
    }

    const selected = getSelectedItems();

    if (!selected.length) {
      return;
    }

    setState("adding", "در حال افزودن محصولات به سبد...");

    let added = 0;

    selected.forEach((item) => {
      if (
        item.variantId <= 0 ||
        !Number.isFinite(item.price) ||
        item.price < 0 ||
        item.availability === "out_of_stock" ||
        item.compatibility === "not_compatible"
      ) {
        return;
      }

      cart.add(
        {
          id: String(item.id),

          variantId: item.variantId,

          name: item.name,

          brand: item.brandName,

          price: item.price,

          spec: item.variantName,
        },
        1,
      );

      added += 1;
    });

    if (added === 0) {
      showError("هیچ محصول قابل خریدی انتخاب نشده است.");

      return;
    }

    if (message) {
      message.textContent = `${added.toLocaleString(
        "fa-IR",
      )} محصول به سبد خرید اضافه شد.`;

      message.dataset.state = "success";
    }

    setState("complete", "ست خرید با موفقیت تکمیل شد.");

    state.selected.clear();

    renderCandidates();
    updateSummary();
  };

  candidates?.addEventListener("click", (event) => {
    const button = event.target.closest("[data-complete-setup-select]");

    if (!button) {
      return;
    }

    toggleItem(Number(button.dataset.productId));
  });

  budget?.addEventListener("input", () => {
    const value = Number(budget.value);

    state.budget = Number.isFinite(value) && value > 0 ? value : null;

    updateBudgetState();
  });

  resetButton?.addEventListener("click", () => {
    state.selected.clear();

    if (message) {
      message.textContent = "";
    }

    renderCandidates();
    updateSummary();
  });

  cartButton?.addEventListener("click", addToCart);

  retry?.addEventListener("click", load);

  document.addEventListener("product-detail:variant-changed", (event) => {
    state.selectedVariantId = Number(event.detail?.variantId || 0);

    load();
  });

  document.addEventListener(
    "product-detail:complete-setup-context",
    (event) => {
      if (Number(event.detail?.productId) !== productId) {
        return;
      }

      state.selectedVariantId = Number(event.detail?.variantId || 0);

      load();
    },
  );

  load();
};

export default completeYourSetup;
