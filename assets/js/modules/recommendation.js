export default function recommendation() {
  const roots = document.querySelectorAll("[data-recommendation]");

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const compatibilityLabel = (state) => {
    switch (state) {
      case "compatible":
        return "سازگار";
      case "partially_compatible":
        return "سازگاری کامل تأیید نشده";
      case "not_compatible":
        return "ناسازگار";
      case "no_proven_problem":
        return "مشکل مشخصی ثبت نشده";
      default:
        return "نامشخص";
    }
  };

  const getAlternatives = (data) => {
    if (Array.isArray(data?.alternatives)) {
      return data.alternatives;
    }

    if (Array.isArray(data?.items)) {
      return data.items;
    }

    return [];
  };

  const findAlternative = (data, productId) =>
    getAlternatives(data).find(
      (item) => Number(item?.id ?? item?.product?.id ?? 0) === productId,
    ) || null;

  const renderConfidence = (confidence) => {
    if (confidence === null || confidence === undefined || confidence === "") {
      return `
        <span class="recommendation-trust__value">در دسترس نیست</span>
        <small class="recommendation-trust__note">
          امتیاز یا درصد ساختگی نمایش داده نمی‌شود.
        </small>
      `;
    }

    return `
      <span class="recommendation-trust__value">${escapeHtml(confidence)}</span>
      <small class="recommendation-trust__note">
        این مقدار مستقیماً از قرارداد واقعی پیشنهاد دریافت شده است.
      </small>
    `;
  };

  const renderContent = (target, data, productId, finderContext = null) => {
    const alternatives = getAlternatives(data);
    const item = findAlternative(data, productId);
    const problem = data?.problem || {};
    const compatibility = item?.compatibility || {};
    const reason =
      item?.reason?.text ||
      problem?.message ||
      finderContext?.reason ||
      "این پیشنهاد بر اساس داده‌های موجود سیستم انتخاب شده است.";
    const state =
      compatibility?.state ||
      (problem?.state === "not_compatible" ? "not_compatible" : "unknown");
    const confidence = data?.ranking?.confidence ?? null;
    const alternativeName =
      item?.name ||
      item?.product?.name ||
      finderContext?.name ||
      "پیشنهاد انتخاب‌شده";
    const finderScore = finderContext?.score;

    target.innerHTML = `
      <div class="recommendation-trust">
        <div class="recommendation-trust__item">
          <span class="recommendation-trust__label">دلیل پیشنهاد</span>
          <p class="recommendation-trust__reason">${escapeHtml(reason)}</p>
        </div>
        <div class="recommendation-trust__item">
          <span class="recommendation-trust__label">سازگاری</span>
          <strong class="recommendation-trust__value">${escapeHtml(compatibilityLabel(state))}</strong>
          ${
            compatibility?.notes?.length
              ? `<small class="recommendation-trust__note">${escapeHtml(compatibility.notes.join(" · "))}</small>`
              : ""
          }
        </div>
        <div class="recommendation-trust__item">
          <span class="recommendation-trust__label">اطمینان پیشنهاد</span>
          ${renderConfidence(confidence)}
        </div>
        <div class="recommendation-trust__item">
          <span class="recommendation-trust__label">انتخاب فعلی</span>
          <strong class="recommendation-trust__value">${escapeHtml(alternativeName)}</strong>
          ${
            Number.isFinite(Number(finderScore))
              ? `<small class="recommendation-trust__note">قدرت تطبیق Finder: ${escapeHtml(Math.min(100, Math.round(Number(finderScore))))}%</small>`
              : ""
          }
        </div>
      </div>
      ${
        alternatives.length === 0
          ? `<p class="recommendation-trust__boundary">برای این انتخاب، داده‌ی جداگانه‌ای برای اطمینان‌سنجی بیشتر ثبت نشده است.</p>`
          : ""
      }
    `;

    target.hidden = false;
  };

  const createLoader = (target) => {
    target.hidden = false;
    target.textContent = "در حال بررسی دلیل پیشنهاد و سازگاری...";
  };

  const createError = (target) => {
    target.hidden = false;
    target.innerHTML = `
      <p class="recommendation-trust__error">
        بررسی دلیل پیشنهاد انجام نشد. دوباره تلاش کن.
      </p>
      <button type="button" class="recommendation-trust__retry" data-recommendation-retry>
        تلاش دوباره
      </button>
    `;
  };

  const load = async ({ target, apiBase, productId, finderContext = null }) => {
    if (!target || !apiBase || productId < 1) {
      if (target) {
        target.hidden = false;
        target.textContent = "اطلاعات لازم برای بررسی پیشنهاد در دسترس نیست.";
      }

      return;
    }

    target.setAttribute("aria-live", "polite");
    createLoader(target);

    try {
      const url = new URL(apiBase, window.location.origin);

      url.searchParams.set("path", "/storefront/products/alternative");

      url.searchParams.set("product_id", String(productId));

      const response = await fetch(url.toString(), {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        credentials: "same-origin",
      });

      const payload = await response.json().catch(() => ({}));

      if (!response.ok || payload?.success !== true) {
        throw new Error(payload?.message || `HTTP ${response.status}`);
      }

      renderContent(target, payload.data || {}, productId, finderContext);
    } catch (error) {
      createError(target);
    }
  };

  roots.forEach((root) => {
    const target = root.querySelector("[data-recommendation-content]");

    const state = root.querySelector("[data-recommendation-state]");

    const productId = Number(root.dataset.recommendationProductId || 0);

    const apiBase = root.dataset.apiBase || "";

    if (!target) {
      return;
    }

    root.addEventListener("click", (event) => {
      if (!event.target.closest("[data-recommendation-retry]")) {
        return;
      }

      event.preventDefault();

      load({
        target,
        apiBase,
        productId,
      });
    });

    if (state) {
      state.textContent =
        "برای مشاهده دلیل، سازگاری و اطمینان پیشنهاد بررسی را آغاز کنید.";
    }
  });

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-recommendation-trigger]");

    if (!trigger) {
      return;
    }

    const card = trigger.closest("[data-recommendation-card]");

    const target = card?.querySelector("[data-recommendation-content]");

    if (!card || !target) {
      return;
    }

    event.preventDefault();

    const finderContext = {
      name: card.dataset.recommendationName || "",
      reason: card.dataset.recommendationReason || "",
      score: Number(card.dataset.recommendationScore || NaN),
    };

    target.hidden = false;

    trigger.setAttribute("aria-expanded", "true");

    if (target.dataset.loaded === "true") {
      return;
    }

    target.dataset.loaded = "true";

    target.setAttribute("aria-live", "polite");

    target.innerHTML = `
        <div class="recommendation-trust">
          <div class="recommendation-trust__item">
            <span class="recommendation-trust__label">دلیل پیشنهاد</span>
            <p class="recommendation-trust__reason">${escapeHtml(
              finderContext.reason ||
                "بر اساس نیاز و بودجه انتخابی پیشنهاد شده است.",
            )}</p>
          </div>

          <div class="recommendation-trust__item">
            <span class="recommendation-trust__label">سازگاری</span>
            <strong class="recommendation-trust__value">نامشخص</strong>
            <small class="recommendation-trust__note">
              برای این Finder Contract، داده‌ی سازگاری دستگاهی در این پاسخ ثبت نشده است.
            </small>
          </div>

          <div class="recommendation-trust__item">
            <span class="recommendation-trust__label">اطمینان پیشنهاد</span>
            <strong class="recommendation-trust__value">در دسترس نیست</strong>
            <small class="recommendation-trust__note">
              امتیاز Finder به‌عنوان اطمینان سازگاری نمایش داده نمی‌شود.
            </small>
          </div>

          <div class="recommendation-trust__item">
            <span class="recommendation-trust__label">قدرت تطبیق Finder</span>
            <strong class="recommendation-trust__value">${
              Number.isFinite(finderContext.score)
                ? `${Math.min(100, Math.round(finderContext.score))}%`
                : "نامشخص"
            }</strong>
          </div>
        </div>
      `;
  });
}
