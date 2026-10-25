import {
  getCompatibilityMeta,
  normalizeDevice,
  normalizeProduct,
  normalizeResult,
  createRequest,
} from "./compatibility-engine.js";

const compatibility = () => {
  const section = document.querySelector(".compatibility");

  if (!section) {
    return;
  }

  const brandSelect = section.querySelector("[data-compatibility-brand]");

  const modelSelect = section.querySelector("[data-compatibility-model]");

  const submit = section.querySelector("[data-compatibility-submit]");

  const result = section.querySelector("[data-compatibility-result]");

  const resultTitle = section.querySelector(
    "[data-compatibility-result-title]",
  );

  const resultCount = section.querySelector("[data-compatibility-count]");

  const productsContainer = result?.querySelector(".compatibility-products");

  if (
    !brandSelect ||
    !modelSelect ||
    !submit ||
    !result ||
    !resultTitle ||
    !resultCount ||
    !productsContainer
  ) {
    return;
  }

  const baseUrl = window.NIVAD_BASE_URL || "";

  let devices = [];
  let loading = false;
  let lastDeviceId = 0;

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (value) => {
    const number = Number(value);

    if (!Number.isFinite(number)) {
      return "قیمت نامشخص";
    }

    return `${number.toLocaleString("fa-IR")} تومان`;
  };

  const setResultState = (state, message = "") => {
    result.dataset.state = state;

    if (state === "loading") {
      productsContainer.innerHTML = `
        <div
          class="compatibility-state"
          role="status"
          aria-live="polite">

          <strong>
            در حال بررسی سازگاری...
          </strong>

          <span>
            لطفاً چند لحظه صبر کن.
          </span>

        </div>
      `;

      return;
    }

    if (state === "error") {
      productsContainer.innerHTML = `
        <div
          class="compatibility-state compatibility-state--error"
          role="alert">

          <strong>
            بررسی سازگاری انجام نشد.
          </strong>

          <p>
            ${escapeHTML(message || "دریافت اطلاعات ناموفق بود.")}
          </p>

          <button
            type="button"
            data-compatibility-retry>
            تلاش دوباره
          </button>

        </div>
      `;

      return;
    }

    if (state === "empty") {
      productsContainer.innerHTML = `
        <div
          class="compatibility-state"
          role="status">

          <strong>
            نتیجه‌ای برای این دستگاه پیدا نشد.
          </strong>

          <span>
            مدل دستگاه را تغییر بده یا دوباره بررسی کن.
          </span>

          <button
            type="button"
            data-compatibility-retry>
            بررسی دوباره
          </button>

        </div>
      `;
    }
  };

  const request = async (path, params = {}) => {
    const url = new URL(`${baseUrl}/api/index.php`, window.location.origin);

    url.searchParams.set("path", path);

    Object.entries(params).forEach(([key, value]) => {
      url.searchParams.set(key, String(value));
    });

    const response = await fetch(url.toString(), {
      credentials: "same-origin",

      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      const error = new Error(payload?.message || "دریافت اطلاعات ناموفق بود.");

      error.status = response.status;

      error.payload = payload;

      throw error;
    }

    return payload?.data ?? {};
  };

  const clearResult = () => {
    result.hidden = true;

    result.removeAttribute("data-state");

    productsContainer.replaceChildren();
  };

  const updateSubmitState = () => {
    submit.disabled = loading || !modelSelect.value;

    submit.setAttribute("aria-disabled", String(submit.disabled));
  };

  const loadDevices = async () => {
    const data = await request("/compatibility/devices");

    devices = (Array.isArray(data.items) ? data.items : [])
      .map(normalizeDevice)
      .filter(Boolean);

    const brands = [
      ...new Set(devices.map((device) => device.brandName).filter(Boolean)),
    ];

    brandSelect.replaceChildren();

    const firstOption = document.createElement("option");

    firstOption.value = "";

    firstOption.textContent = "انتخاب برند";

    brandSelect.appendChild(firstOption);

    brands.forEach((brand) => {
      const option = document.createElement("option");

      option.value = brand;

      option.textContent = brand;

      brandSelect.appendChild(option);
    });

    loadModels();
  };

  const loadModels = () => {
    const brand = brandSelect.value;

    modelSelect.replaceChildren();

    const firstOption = document.createElement("option");

    firstOption.value = "";

    firstOption.textContent = brand ? "انتخاب مدل" : "ابتدا برند را انتخاب کن";

    modelSelect.appendChild(firstOption);

    const filteredDevices = devices.filter(
      (device) => device.brandName === brand,
    );

    filteredDevices.forEach((device) => {
      const option = document.createElement("option");

      option.value = String(device.id);

      option.textContent = device.model || device.name || "مدل دستگاه";

      modelSelect.appendChild(option);
    });

    modelSelect.disabled = !brand || filteredDevices.length === 0;

    updateSubmitState();
  };

  const renderSummary = (resultData) => {
    const { compatibleCount, partialCount, incompatibleCount, count } =
      resultData;

    resultCount.textContent = `${count.toLocaleString("fa-IR")} نتیجه`;

    const existingSummary = result.querySelector(
      ".compatibility-result-summary",
    );

    existingSummary?.remove();

    const summary = document.createElement("div");

    summary.className = "compatibility-result-summary";

    summary.setAttribute("aria-label", "خلاصه سازگاری");

    const summaryItems = [
      ["compatible", "سازگار", compatibleCount],
      ["partially_compatible", "مشروط", partialCount],
      ["not_compatible", "ناسازگار", incompatibleCount],
    ];

    summary.innerHTML = summaryItems
      .filter(([, , value]) => value > 0)
      .map(
        ([type, label, value]) => `
            <span
              class="compatibility-summary-item compatibility-summary-item--${type}">

              <strong>
                ${Number(value).toLocaleString("fa-IR")}
              </strong>

              <span>
                ${label}
              </span>

            </span>
          `,
      )
      .join("");

    const header = result.querySelector(".compatibility-result-header");

    header?.insertAdjacentElement("afterend", summary);
  };

  const renderProduct = (product) => {
    const normalized = normalizeProduct(product);

    if (!normalized) {
      return "";
    }

    const meta = getCompatibilityMeta(normalized.compatibilityType);

    const confidence = normalized.confidence || {
      level: "unknown",
      label: "اطمینان نامشخص",
      score: null,
      reason: null,
    };

    const confidenceMarkup = `
  <div
    class="compatibility-product-confidence"
    data-confidence-level="${escapeHTML(confidence.level)}"
  >
    <span>
      ${escapeHTML(confidence.label)}
    </span>

    ${
      confidence.score !== null
        ? `
          <strong>
            ${escapeHTML(`${Math.round(confidence.score)}٪`)}
          </strong>
        `
        : ""
    }

    ${
      confidence.reason
        ? `
          <p>
            ${escapeHTML(confidence.reason)}
          </p>
        `
        : ""
    }
  </div>
`;

    const note = normalized.note
      ? `
          <p
            class="compatibility-product-note">
            ${escapeHTML(normalized.note)}
          </p>
        `
      : `
          <p
            class="compatibility-product-note">
            ${escapeHTML(meta.description)}
          </p>
        `;

    const initial = escapeHTML((normalized.name || "محصول").slice(0, 1));

    const image = normalized.image
      ? `
          <img
            src="${escapeHTML(normalized.image)}"
            alt=""
            loading="lazy">
        `
      : `
          <span
            aria-hidden="true">
            ${initial}
          </span>
        `;

    return `
      <article
        class="compatibility-product"
        data-compatibility-type="${escapeHTML(meta.key || "unknown")}">

        <div
          class="compatibility-product-image"
          aria-hidden="true">

          ${image}

        </div>

        <div
          class="compatibility-product-info">

          ${
            normalized.brandName
              ? `
                <span>
                  ${escapeHTML(normalized.brandName)}
                </span>
              `
              : ""
          }

          <h3>
            ${escapeHTML(normalized.name || "محصول")}
          </h3>

          <strong>
            ${escapeHTML(formatPrice(normalized.price))}
          </strong>

          <span
            class="compatibility-product-badge compatibility-product-badge--${escapeHTML(
              meta.tone,
            )}">

            ${escapeHTML(meta.label)}

          </span>

          ${note}

        </div>

      </article>
    `;
  };

  const renderProducts = (items) => {
    if (!items.length) {
      setResultState("empty");

      return;
    }

    productsContainer.innerHTML = items
      .map((item) => {
        const name = escapeHTML(item?.name || "محصول");

        const brand = escapeHTML(item?.brandName || "");

        const price = escapeHTML(formatPrice(item?.price));

        const type = String(item?.compatibilityType || "");

        const note = escapeHTML(item?.note || "");

        let typeLabel = "";

        if (type === "compatible") {
          typeLabel = "سازگار";
        } else if (type === "partially_compatible") {
          typeLabel = "سازگاری جزئی";
        } else if (type === "not_compatible") {
          typeLabel = "ناسازگار";
        }

        return `
        <article
          class="compatibility-product">

          <div
            class="compatibility-product-image"
            aria-hidden="true">

            <span>
              ${escapeHTML(String(item?.name || "محصول").slice(0, 1))}
            </span>

          </div>

          <div
            class="compatibility-product-info">

            ${
              brand
                ? `
                  <span>
                    ${brand}
                  </span>
                `
                : ""
            }

            <h3>
              ${name}
            </h3>

            <strong>
              ${price}
            </strong>

            ${
              typeLabel
                ? `
                  <small class="compatibility-product-type">
                    ${escapeHTML(typeLabel)}
                  </small>
                `
                : ""
            }

            ${
              note
                ? `
                  <p class="compatibility-product-note">
                    ${note}
                  </p>
                `
                : ""
            }

          </div>

        </article>
      `;
      })
      .join("");

    result.dataset.state = "success";
  };

  const loadProducts = async () => {
    const requestData = createRequest(modelSelect.value);

    if (!requestData || loading) {
      return;
    }

    loading = true;

    lastDeviceId = requestData.deviceId;

    result.hidden = false;

    setResultState("loading");

    updateSubmitState();

    try {
      const data = await request(
        `/compatibility/devices/${requestData.deviceId}/products`,
      );

      const resultData = normalizeResult(data);

      if (resultData.device) {
        const deviceLabel = [
          resultData.device.brandName,

          resultData.device.model || resultData.device.name,
        ]
          .filter(Boolean)
          .join(" ");

        resultTitle.textContent = deviceLabel || "نتیجه سازگاری";
      } else {
        resultTitle.textContent = "نتیجه سازگاری";
      }

      renderSummary(resultData);

      renderProducts(resultData.items);
    } catch (error) {
      setResultState(
        "error",
        error?.message || "دریافت محصولات سازگار ناموفق بود.",
      );
    } finally {
      loading = false;

      updateSubmitState();
    }
  };

  brandSelect.addEventListener("change", () => {
    loadModels();
    clearResult();
  });

  modelSelect.addEventListener("change", () => {
    updateSubmitState();
    clearResult();
  });

  submit.addEventListener("click", loadProducts);

  result.addEventListener("click", (event) => {
    const retry = event.target.closest("[data-compatibility-retry]");

    if (!retry) {
      return;
    }

    if (lastDeviceId) {
      modelSelect.value = String(lastDeviceId);
    }

    loadProducts();
  });

  loadDevices().catch((error) => {
    result.hidden = false;

    setResultState("error", error?.message || "دریافت دستگاه‌ها ناموفق بود.");
  });

  updateSubmitState();
};

export default compatibility;
