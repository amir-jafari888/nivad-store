export default function alternative() {
  const root = document.querySelector("[data-alternative]");

  if (!root) {
    return;
  }

  const apiBase = root.dataset.apiBase || "";

  const productId = Number(root.dataset.productId || 0);

  const deviceId = Number(root.dataset.deviceId || 0);

  const state = {
    isLoading: false,
    error: null,
    data: null,
  };

  const stateNode = root.querySelector("[data-alternative-state]");

  const contentNode = root.querySelector("[data-alternative-content]");

  const listNode = root.querySelector("[data-alternative-list]");

  const retryButton = root.querySelector("[data-alternative-retry]");

  const setState = (message) => {
    if (!stateNode) {
      return;
    }

    stateNode.textContent = message;
  };

  const setRootState = (value) => {
    root.dataset.state = value;
  };

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const productUrl = (slug) => {
    const base = root.dataset.productBase || "/products";

    return base + "/" + encodeURIComponent(String(slug || ""));
  };

  const formatPrice = (value, currency) => {
    if (value === null || value === undefined || value === "") {
      return "";
    }

    const numeric = Number(value);

    if (!Number.isFinite(numeric)) {
      return escapeHtml(value);
    }

    const formatted = numeric.toLocaleString("fa-IR");

    return currency ? `${formatted} ${escapeHtml(currency)}` : formatted;
  };

  const normalizeResponse = (payload) => {
    const data =
      payload &&
      typeof payload === "object" &&
      payload.data &&
      typeof payload.data === "object"
        ? payload.data
        : null;

    if (!data) {
      return {
        currentProduct: null,
        problem: {
          state: "unknown",
          reason_code: "invalid_response",
          message: "اطلاعات جایگزین‌ها قابل تشخیص نیست.",
        },
        alternatives: [],
        count: 0,
        ranking: null,
      };
    }

    return {
      currentProduct: data.current_product || null,

      problem:
        data.problem && typeof data.problem === "object"
          ? data.problem
          : {
              state: "unknown",
              reason_code: "unknown_context",
              message: "اطلاعات کافی برای تشخیص جایگزین وجود ندارد.",
            },

      alternatives: Array.isArray(data.alternatives) ? data.alternatives : [],

      count: Number(data.count || 0),

      ranking:
        data.ranking && typeof data.ranking === "object" ? data.ranking : null,
    };
  };

  const renderProblem = (problem) => {
    if (!problem || typeof problem !== "object") {
      return;
    }

    const message = String(problem.message || "").trim();

    if (message) {
      setState(message);
    }
  };

  const render = () => {
    if (!listNode) {
      return;
    }

    const alternatives = Array.isArray(state.data?.alternatives)
      ? state.data.alternatives
      : [];

    if (!alternatives.length) {
      if (contentNode) {
        contentNode.hidden = true;
      }

      renderProblem(state.data?.problem);

      if (!state.data?.problem?.message) {
        setState("برای این محصول جایگزین مناسبی پیدا نشد.");
      }

      setRootState("empty");

      if (retryButton) {
        retryButton.hidden = false;
      }

      return;
    }

    if (contentNode) {
      contentNode.hidden = false;
    }

    if (retryButton) {
      retryButton.hidden = true;
    }

    listNode.innerHTML = alternatives
      .map((item) => {
        const product = item || {};

        const media = product.media || null;

        const variant = product.variant || null;

        const availability = variant?.availability || null;

        const reason = product.reason || null;

        const image = media?.thumbnail_url || media?.url || "";

        const href = productUrl(product.slug);

        const reasonText =
          reason?.text || "بر اساس سازگاری ثبت‌شده پیشنهاد شده است.";

        const available = availability?.status === "in_stock";

        const availabilityText = available ? "موجود" : "ناموجود";

        const price = formatPrice(variant?.price, variant?.currency);

        const sharedSpecifications = Number(
          reason?.shared_specification_count ?? 0,
        );

        const lowerPrice = reason?.lower_price === true;

        return `
          <article
            class="product-alternative-card"
            data-alternative-item
          >
            <a
              class="product-alternative-card__media"
              href="${escapeHtml(href)}"
              aria-label="${escapeHtml(product.name || "محصول")}"
            >
              ${
                image
                  ? `
                    <img
                      src="${escapeHtml(image)}"
                      alt="${escapeHtml(media?.alt || product.name || "محصول")}"
                      loading="lazy"
                      decoding="async"
                    >
                  `
                  : `
                    <span
                      aria-hidden="true"
                    >
                      ${escapeHtml(String(product.name || "محصول").slice(0, 1))}
                    </span>
                  `
              }
            </a>

            <div
              class="product-alternative-card__body"
            >
              <span
                class="product-alternative-card__rank"
              >
                پیشنهاد ${escapeHtml(product.rank || "")}
              </span>

              <h3>
                <a
                  href="${escapeHtml(href)}"
                >
                  ${escapeHtml(product.name || "محصول")}
                </a>
              </h3>

              <p
                class="product-alternative-card__reason"
              >
                <strong>
                  چرا این محصول؟
                </strong>

                ${escapeHtml(reasonText)}
              </p>

              ${
                sharedSpecifications > 0
                  ? `
                    <span
                      class="product-alternative-card__signal"
                    >
                      ${escapeHtml(sharedSpecifications)}
                      مشخصه مشترک
                    </span>
                  `
                  : ""
              }

              ${
                lowerPrice
                  ? `
                    <span
                      class="product-alternative-card__signal"
                    >
                      قیمت پایین‌تر از محصول فعلی
                    </span>
                  `
                  : ""
              }

              <p
                class="product-alternative-card__availability"
              >
                ${escapeHtml(availabilityText)}
              </p>

              ${
                price
                  ? `
                    <strong
                      class="product-alternative-card__price"
                    >
                      ${price}
                    </strong>
                  `
                  : ""
              }

              <div
                class="product-alternative-card__actions"
              >
                <a
                  class="product-alternative-card__view"
                  href="${escapeHtml(href)}"
                >
                  مشاهده محصول
                </a>
              </div>
            </div>
          </article>
        `;
      })
      .join("");

    setRootState("success");
  };

  const load = async () => {
    if (!Number.isSafeInteger(productId) || productId < 1 || !apiBase) {
      setState("اطلاعات لازم برای پیشنهاد جایگزین در دسترس نیست.");

      setRootState("unknown");

      return;
    }

    if (state.isLoading) {
      return;
    }

    state.isLoading = true;
    state.error = null;

    setRootState("loading");

    setState("در حال بررسی گزینه‌های جایگزین...");

    if (contentNode) {
      contentNode.hidden = true;
    }

    if (retryButton) {
      retryButton.hidden = true;
    }

    try {
      const params = new URLSearchParams();

      params.set("product_id", String(productId));

      if (deviceId > 0) {
        params.set("device_id", String(deviceId));
      }

      const response = await fetch(
        `${apiBase}/storefront/products/alternative?${params.toString()}`,
        {
          method: "GET",
          headers: {
            Accept: "application/json",
          },
          credentials: "same-origin",
        },
      );

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const payload = await response.json();

      if (!payload || payload.success !== true) {
        throw new Error("Invalid alternative response");
      }

      state.data = normalizeResponse(payload);

      render();
    } catch (error) {
      state.error = error;

      if (contentNode) {
        contentNode.hidden = true;
      }

      setRootState("error");

      setState("دریافت پیشنهادهای جایگزین ناموفق بود.");

      if (retryButton) {
        retryButton.hidden = false;
      }
    } finally {
      state.isLoading = false;
    }
  };

  retryButton?.addEventListener("click", (event) => {
    event.preventDefault();

    load();
  });

  load();
}
