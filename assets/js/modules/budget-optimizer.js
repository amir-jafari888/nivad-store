const budgetOptimizer = () => {
  const root = document.querySelector("[data-budget-optimizer]");

  if (!root) {
    return;
  }

  const form = root.querySelector("[data-budget-optimizer-form]");

  const deviceSelect = root.querySelector("[data-budget-optimizer-device]");

  const budgetInput = root.querySelector("[data-budget-optimizer-budget]");

  const submitButton = root.querySelector("[data-budget-optimizer-submit]");

  const stateElement = root.querySelector("[data-budget-optimizer-state]");

  const result = root.querySelector("[data-budget-optimizer-result]");

  const summary = root.querySelector("[data-budget-optimizer-summary]");

  const items = root.querySelector("[data-budget-optimizer-items]");

  const tradeoff = root.querySelector("[data-budget-optimizer-tradeoff]");

  const cartButton = root.querySelector("[data-budget-optimizer-cart]");

  const resetButton = root.querySelector("[data-budget-optimizer-reset]");

  const state = {
    result: null,
    selected: [],
    busy: false,
  };

  const apiUrl = "/storefront/budget-optimizer";

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const setState = (message, type = "info", busy = false) => {
    stateElement.textContent = String(message ?? "");

    stateElement.dataset.state = type;

    stateElement.hidden = !message;

    stateElement.setAttribute("aria-busy", busy ? "true" : "false");
  };

  const setBusy = (busy) => {
    state.busy = busy;

    submitButton.disabled = busy;
    deviceSelect.disabled = busy;
    budgetInput.disabled = busy;

    if (busy) {
      submitButton.textContent = "در حال محاسبه…";
    } else {
      submitButton.textContent = "محاسبه بهترین ترکیب";
    }
  };

  const loadDevices = async () => {
    try {
      const response = await fetch("/storefront/compatibility/devices", {
        headers: {
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        throw new Error("دریافت دستگاه‌ها انجام نشد.");
      }

      const payload = await response.json();

      const devices = Array.isArray(payload?.items)
        ? payload.items
        : Array.isArray(payload?.data?.items)
          ? payload.data.items
          : [];

      if (!devices.length) {
        setState("دستگاه قابل انتخابی وجود ندارد.", "empty");

        return;
      }

      for (const device of devices) {
        const option = document.createElement("option");

        option.value = String(device.id);

        option.textContent = String(device.name ?? "");

        deviceSelect.appendChild(option);
      }
    } catch (error) {
      setState(error?.message || "دریافت دستگاه‌ها انجام نشد.", "error");
    }
  };

  const formatMoney = (value, currency) => {
    const numeric = Number(value);

    if (!Number.isFinite(numeric)) {
      return "—";
    }

    return `${numeric.toLocaleString("fa-IR")} ${escapeHtml(currency || "")}`;
  };

  const renderResult = (payload) => {
    state.result = payload;
    state.selected = Array.isArray(payload.selected) ? payload.selected : [];

    result.hidden = false;

    const selected = state.selected;

    const currency = payload.currency || "";

    summary.innerHTML = `
      <div class="budget-optimizer__summary-item">
        <span>بودجه</span>
        <strong>
          ${formatMoney(payload.budget, currency)}
        </strong>
      </div>

      <div class="budget-optimizer__summary-item">
        <span>مجموع</span>
        <strong>
          ${formatMoney(payload.total, currency)}
        </strong>
      </div>

      <div class="budget-optimizer__summary-item">
        <span>باقی‌مانده</span>
        <strong>
          ${formatMoney(payload.remaining, currency)}
        </strong>
      </div>
    `;

    if (payload.state === "no_candidate") {
      items.innerHTML = `
        <div class="budget-optimizer__empty">
          ${escapeHtml(payload.message || "ترکیب مناسبی پیدا نشد.")}
        </div>
      `;

      cartButton.disabled = true;
    } else if (payload.state === "over_budget") {
      items.innerHTML = `
        <div class="budget-optimizer__empty">
          ${escapeHtml(
            payload.message || "بودجه برای ارزان‌ترین گزینه کافی نیست.",
          )}
        </div>
      `;

      cartButton.disabled = true;
    } else {
      items.innerHTML = selected
        .map(
          (item) => `
              <article
                class="budget-optimizer__item"
                data-product-id="${escapeHtml(item.product_id)}"
              >
                <div>
                  <h3>
                    ${escapeHtml(item.name)}
                  </h3>

                  <p>
                    ${escapeHtml(item.short_description || "")}
                  </p>

                  <span>
                    ${escapeHtml(item.compatibility_type)}
                  </span>
                </div>

                <strong>
                  ${formatMoney(item.price, item.currency)}
                </strong>
              </article>
            `,
        )
        .join("");

      cartButton.disabled = selected.length === 0;
    }

    if (payload.trade_off) {
      tradeoff.hidden = false;

      tradeoff.textContent = `برای گزینه جایگزین، حداقل ${formatMoney(
        payload.trade_off.required_extra,
        currency,
      )} بودجه بیشتر لازم است.`;
    } else {
      tradeoff.hidden = true;
      tradeoff.textContent = "";
    }
  };

  const optimize = async () => {
    const deviceId = Number(deviceSelect.value);

    const budget = String(budgetInput.value ?? "").trim();

    if (!Number.isInteger(deviceId) || deviceId < 1) {
      setState("ابتدا دستگاه را انتخاب کنید.", "error");

      deviceSelect.focus();

      return;
    }

    if (
      budget === "" ||
      !Number.isFinite(Number(budget)) ||
      Number(budget) < 0
    ) {
      setState("بودجه معتبر وارد کنید.", "error");

      budgetInput.focus();

      return;
    }

    setBusy(true);

    result.hidden = true;

    setState("در حال بررسی گزینه‌های سازگار…", "loading", true);

    try {
      const url = `${apiUrl}?device_id=${encodeURIComponent(
        deviceId,
      )}&budget=${encodeURIComponent(budget)}`;

      const response = await fetch(url, {
        headers: {
          Accept: "application/json",
        },
      });

      const payload = await response.json();

      if (!response.ok || !payload?.success) {
        throw new Error(payload?.message || "بهینه‌سازی بودجه انجام نشد.");
      }

      renderResult(payload);

      if (payload.state === "optimized") {
        setState("بهترین ترکیب قابل خرید در محدوده بودجه پیدا شد.", "success");
      } else {
        setState(payload.message || "نتیجه‌ای برای انتخاب پیدا نشد.", "empty");
      }
    } catch (error) {
      setState(error?.message || "خطا در ارتباط با سرویس بهینه‌ساز.", "error");
    } finally {
      setBusy(false);
      stateElement.setAttribute("aria-busy", "false");
    }
  };

  const addSelectedToCart = () => {
    if (
      !state.selected.length ||
      !window.digitalStoreCart ||
      typeof window.digitalStoreCart.add !== "function"
    ) {
      setState("ماژول سبد خرید در دسترس نیست.", "error");

      return;
    }

    let added = 0;

    for (const product of state.selected) {
      if (
        !product.available ||
        !product.product_id ||
        !product.name ||
        !Number.isFinite(Number(product.price))
      ) {
        continue;
      }

      window.digitalStoreCart.add(
        {
          id: String(product.product_id),
          variantId:
            Number(product.variant_id) > 0 ? Number(product.variant_id) : null,
          name: String(product.name),
          price: Number(product.price),
        },
        1,
      );

      added += 1;
    }

    if (added === 0) {
      setState("هیچ محصول قابل خریدی برای افزودن وجود ندارد.", "error");

      return;
    }

    setState("محصولات انتخاب‌شده به سبد اضافه شدند.", "success");
  };

  const reset = () => {
    form.reset();

    state.result = null;
    state.selected = [];

    result.hidden = true;

    items.innerHTML = "";
    summary.innerHTML = "";

    tradeoff.hidden = true;
    tradeoff.textContent = "";

    cartButton.disabled = true;

    setState("");
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!state.busy) {
      optimize();
    }
  });

  cartButton.addEventListener("click", addSelectedToCart);

  resetButton.addEventListener("click", reset);

  loadDevices();
};

export default budgetOptimizer;
