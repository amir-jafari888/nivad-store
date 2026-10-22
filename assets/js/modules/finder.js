import {
  FINDER_BUDGETS,
  FINDER_INTENTS,
  createFinderRequest,
  getFinderConfidence,
  getProductPrice,
  rankFinderProducts,
} from "./finder-engine.js";

const finder = () => {
  const page = document.querySelector("[data-finder-page]");

  if (!page) {
    return;
  }

  const form = page.querySelector("[data-finder-form]");

  const results = page.querySelector("[data-finder-results]");

  const count = page.querySelector("[data-finder-count]");

  const confidence = page.querySelector("[data-finder-confidence]");

  const status = page.querySelector("[data-finder-status]");

  const reset = page.querySelector("[data-finder-reset]");

  if (!form || !results || !count || !confidence || !status) {
    return;
  }

  const apiBase = page.dataset.apiBase || "/api/index.php";

  let products = [];
  let loading = false;

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (value) => {
    const price = Number(value);

    return Number.isFinite(price)
      ? `${price.toLocaleString("fa-IR")} تومان`
      : "قیمت نامشخص";
  };

  const setStatus = (message, state = "idle") => {
    status.dataset.state = state;
    status.textContent = message;
  };

  const requestProducts = async () => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", "/storefront/products");

    const response = await fetch(url.toString(), {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "دریافت محصولات ناموفق بود.");
    }

    return Array.isArray(payload?.data?.items) ? payload.data.items : [];
  };

  const getFormRequest = () => {
    const formData = new FormData(form);

    return createFinderRequest({
      intent: formData.get("intent"),
      budget: formData.get("budget"),
      query: formData.get("query"),
    });
  };

  const renderProducts = (rankedItems) => {
    results.replaceChildren();

    count.textContent = `${rankedItems.length.toLocaleString("fa-IR")} پیشنهاد`;

    if (rankedItems.length === 0) {
      confidence.textContent = "نتیجه کافی پیدا نشد";

      setStatus("با تغییر نیاز یا بودجه، دوباره امتحان کن.", "empty");

      const empty = document.createElement("div");

      empty.className = "finder-state finder-state--empty";

      empty.textContent =
        "فعلاً محصولی که با انتخاب‌هایت هم‌خوان باشد پیدا نشد.";

      results.appendChild(empty);

      return;
    }

    const confidenceMeta = getFinderConfidence(rankedItems);

    confidence.textContent = confidenceMeta.label;

    setStatus(
      "پیشنهادها بر اساس نیاز و بودجه انتخابی مرتب شده‌اند.",
      "success",
    );

    rankedItems.forEach(({ product, score, reasons }) => {
      const article = document.createElement("article");

      article.className = "finder-card";

      const title = escapeHTML(product?.name || "محصول بدون نام");

      const description = escapeHTML(
        product?.short_description || product?.description || "",
      );

      const slug = escapeHTML(product?.slug || "");

      const normalizedReasons = Array.isArray(reasons)
        ? reasons.map((reason) => String(reason ?? "").trim()).filter(Boolean)
        : [];

      const reasonText = normalizedReasons.length
        ? normalizedReasons.map(escapeHTML).join(" · ")
        : "بر اساس انتخاب‌های تو پیشنهاد شده است.";

      const price = formatPrice(getProductPrice(product));

      const scoreLabel = Math.min(100, Math.round(score));

      article.innerHTML = `
          <div class="finder-card__topline">
            <span class="finder-card__badge">
              MATCH ${scoreLabel}%
            </span>

            <span class="finder-card__confidence">
              ${escapeHTML(confidenceMeta.label)}
            </span>
          </div>

          <div class="finder-card__body">
            <span class="finder-card__eyebrow">
              NIVAD RECOMMENDATION
            </span>

            <h3 class="finder-card__title">
              ${title}
            </h3>

            <p class="finder-card__description">
              ${description}
            </p>

            <p class="finder-card__reason">
              <strong>
                چرا این محصول؟
              </strong>

              ${reasonText}
            </p>
          </div>

          <div class="finder-card__footer">
            <strong class="finder-card__price">
              ${price}
            </strong>

            ${
              slug
                ? `
                  <a
                    class="finder-card__link"
                    href="/products/${slug}">
                    مشاهده محصول
                  </a>
                `
                : ""
            }
          </div>
        `;

      results.appendChild(article);
    });
  };

  const setLoading = (state) => {
    loading = state;

    form.querySelectorAll("button, input, select").forEach((control) => {
      control.disabled = state;
    });

    page.setAttribute("aria-busy", String(state));
  };

  const run = async () => {
    if (loading) {
      return;
    }

    setLoading(true);

    setStatus("در حال بررسی نیازت و پیدا کردن گزینه‌های مناسب...", "loading");

    try {
      if (products.length === 0) {
        products = await requestProducts();
      }

      const request = getFormRequest();

      const rankedItems = rankFinderProducts(products, request);

      renderProducts(rankedItems);
    } catch (error) {
      results.replaceChildren();

      count.textContent = "۰ پیشنهاد";

      confidence.textContent = "نامشخص";

      setStatus(error?.message || "دریافت پیشنهادها ناموفق بود.", "error");

      const errorState = document.createElement("div");

      errorState.className = "finder-state finder-state--error";

      errorState.textContent =
        "دریافت محصولات انجام نشد. اتصال را بررسی کن و دوباره تلاش کن.";

      results.appendChild(errorState);
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    form.reset();

    results.replaceChildren();

    count.textContent = "۰ پیشنهاد";

    confidence.textContent = "هنوز بررسی نشده";

    setStatus("نیازت را انتخاب کن تا پیشنهاد مناسب ساخته شود.", "idle");

    page.querySelectorAll("[data-finder-intent]").forEach((item) => {
      const active = item.dataset.finderIntent === FINDER_INTENTS.DAILY;

      item.classList.toggle("is-active", active);

      item.setAttribute("aria-pressed", String(active));
    });
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    run();
  });

  reset?.addEventListener("click", resetForm);

  page.querySelectorAll("[data-finder-intent]").forEach((button) => {
    button.addEventListener("click", () => {
      const input = form.querySelector('[name="intent"]');

      if (!input) {
        return;
      }

      input.value = button.dataset.finderIntent || "";

      page.querySelectorAll("[data-finder-intent]").forEach((item) => {
        const active = item === button;

        item.classList.toggle("is-active", active);

        item.setAttribute("aria-pressed", String(active));
      });
    });
  });

  form.querySelector('[name="budget"]')?.addEventListener("change", () => {
    const select = form.querySelector('[name="budget"]');

    if (!select || !Object.values(FINDER_BUDGETS).includes(select.value)) {
      if (select) {
        select.value = FINDER_BUDGETS.ANY;
      }
    }
  });
};

export default finder;
