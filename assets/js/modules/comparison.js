const comparison = () => {
  const root = document.querySelector("[data-comparison-page]");

  const comparisonStorageKey = "digital-store-comparison";

  const readSelection = () => {
    try {
      const raw = JSON.parse(
        localStorage.getItem(comparisonStorageKey) || "[]",
      );

      return Array.isArray(raw)
        ? raw
            .map(Number)
            .filter((id) => Number.isInteger(id) && id > 0)
            .slice(0, 4)
        : [];
    } catch {
      return [];
    }
  };

  const writeSelection = (ids) => {
    const unique = [
      ...new Set(
        ids.map(Number).filter((id) => Number.isInteger(id) && id > 0),
      ),
    ].slice(0, 4);

    localStorage.setItem(comparisonStorageKey, JSON.stringify(unique));

    window.dispatchEvent(
      new CustomEvent("comparison:selection-changed", {
        detail: {
          ids: unique,
        },
      }),
    );

    return unique;
  };

  const toggleProduct = (id) => {
    const numericId = Number(id);

    if (!Number.isInteger(numericId) || numericId < 1) {
      return;
    }

    const ids = readSelection();

    const next = ids.includes(numericId)
      ? ids.filter((item) => item !== numericId)
      : ids.length >= 4
        ? ids
        : [...ids, numericId];

    writeSelection(next);
  };

  const productCompareButton = (id) => {
    const button = document.createElement("button");

    button.type = "button";

    button.className = "comparison-trigger";

    button.dataset.compareProduct = String(id);

    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      toggleProduct(id);

      updateTriggerStates();
    });

    return button;
  };

  const updateTriggerStates = () => {
    const selected = new Set(readSelection());

    document.querySelectorAll("[data-compare-product]").forEach((button) => {
      const id = Number(button.dataset.compareProduct || 0);

      const active = selected.has(id);

      button.setAttribute("aria-pressed", String(active));

      button.textContent = active ? "حذف از مقایسه" : "مقایسه";
    });
  };

  const mountTriggers = () => {
    document
      .querySelectorAll(
        "[data-catalog-list] .catalog-card, [data-product-detail]",
      )
      .forEach((card) => {
        if (card.querySelector("[data-compare-product]")) {
          return;
        }

        const id = Number(
          card.dataset.productId ||
            card.querySelector("[data-product-id]")?.textContent ||
            0,
        );

        if (!Number.isInteger(id) || id < 1) {
          return;
        }

        const button = productCompareButton(id);

        button.dataset.compareProduct = String(id);

        const actions =
          card.querySelector(".product-detail-actions") ||
          card.querySelector(".catalog-card__body") ||
          card;

        actions.appendChild(button);
      });

    updateTriggerStates();
  };

  if (!root) {
    const observer = new MutationObserver(mountTriggers);

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

    mountTriggers();

    return;
  }

  const apiBase =
    root.dataset.apiBase || `${window.NIVAD_BASE_URL || ""}/api/index.php`;

  const productBase = root.dataset.productBase || "/products";

  const state = root.querySelector("[data-comparison-state]");

  const empty = root.querySelector("[data-comparison-empty]");

  const content = root.querySelector("[data-comparison-content]");

  const products = root.querySelector("[data-comparison-products]");

  const count = root.querySelector("[data-comparison-count]");

  const verdict = root.querySelector("[data-comparison-verdict]");

  const tableHead = root.querySelector("[data-comparison-table-head]");

  const tableBody = root.querySelector("[data-comparison-table-body]");

  const differenceSummary = root.querySelector(
    "[data-comparison-difference-summary]",
  );

  const compatibilityGrid = root.querySelector(
    "[data-comparison-compatibility-grid]",
  );

  const compatibilityForm = root.querySelector(
    "[data-comparison-compatibility-form]",
  );

  const deviceInput = root.querySelector("[data-comparison-device-id]");

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (value, currency = "IRR") => {
    if (!Number.isFinite(Number(value))) {
      return "قیمت نامشخص";
    }

    return `${Number(value).toLocaleString("fa-IR")} ${
      currency === "IRR" ? "تومان" : escapeHTML(currency)
    }`;
  };

  const request = async (deviceId = null) => {
    const ids = readSelection();

    if (!ids.length) {
      return null;
    }

    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", "/storefront/products/comparison");

    url.searchParams.set("product_ids", ids.join(","));

    if (deviceId) {
      url.searchParams.set("device_id", String(deviceId));
    }

    const response = await fetch(url.toString(), {
      headers: {
        Accept: "application/json",
      },
      credentials: "same-origin",
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "دریافت مقایسه ناموفق بود.");
    }

    return payload?.data || {};
  };

  const setState = (message, type = "loading") => {
    if (!state) {
      return;
    }

    state.hidden = false;

    state.dataset.state = type;

    state.textContent = message;
  };

  const renderProducts = (items) => {
    products.innerHTML = items
      .map((item) => {
        const variant = item.variant || {};

        const media =
          (item.media || []).find((value) => value.is_primary) ||
          (item.media || [])[0];

        const image = media?.thumbnail_url || media?.url || "";

        const stock = item.availability?.status === "in_stock";

        return `
              <article
                class="comparison-product-card">

                <div
                  class="comparison-product-card__media">

                  ${
                    image
                      ? `
                        <img
                          src="${escapeHTML(image)}"
                          alt="${escapeHTML(media?.alt || item.name)}"
                          loading="lazy">
                      `
                      : `
                        <span>
                          ${escapeHTML(item.name.slice(0, 1))}
                        </span>
                      `
                  }

                </div>

                <div
                  class="comparison-product-card__body">

                  <span
                    class="comparison-product-card__status">
                    ${stock ? "موجود" : "ناموجود"}
                  </span>

                  <h2>
                    <a
                      href="${productBase.replace(
                        /\/$/,
                        "",
                      )}/${encodeURIComponent(item.slug)}">
                      ${escapeHTML(item.name)}
                    </a>
                  </h2>

                  <p>
                    ${escapeHTML(item.short_description || "")}
                  </p>

                  <strong>
                    ${formatPrice(variant.price, variant.currency)}
                  </strong>

                  <div
                    class="comparison-product-card__actions">

                    <button
                      type="button"
                      data-cart="${item.id}"
                      data-product-variant-id="${Number(variant.id || 0)}"
                      data-product-name="${escapeHTML(item.name)}"
                      data-product-price="${Number(variant.price || 0)}"
                      data-product-spec="${escapeHTML(variant.name || "")}">
                      افزودن به سبد
                    </button>

                    <button
                      type="button"
                      data-remove-comparison="${item.id}">
                      حذف
                    </button>

                  </div>

                </div>

              </article>
            `;
      })
      .join("");
  };

  const renderVerdict = (data) => {
    const priceLeader = data?.comparison?.price_leader_product_id;

    const winner = data?.comparison?.overall_winner_product_id;

    const item = (data.items || []).find(
      (product) => Number(product.id) === Number(winner || priceLeader),
    );

    const priceText =
      priceLeader && item
        ? `
          <p>
            کمترین قیمت ثبت‌شده مربوط به
            <strong>
              ${escapeHTML(item.name)}
            </strong>
            است.
          </p>
        `
        : `
          <p>
            برای اعلام برنده کلی،
            معیار کافی و قابل اثبات وجود ندارد.
          </p>
        `;

    verdict.innerHTML = `
      ${priceText}

      <p>
        مزیت قیمتی به‌تنهایی
        به‌عنوان «بهترین محصول»
        معرفی نمی‌شود.
      </p>
    `;
  };

  const renderTable = (data) => {
    const items = data.items || [];

    tableHead.innerHTML = `
      <tr>
        <th scope="col">
          مشخصه
        </th>

        ${items
          .map(
            (item) => `
              <th scope="col">
                ${escapeHTML(item.name)}
              </th>
            `,
          )
          .join("")}

      </tr>
    `;

    tableBody.innerHTML = (data.specifications || [])
      .map((spec) => {
        const cells = items
          .map((item) => {
            const value =
              spec.values?.[String(item.id)] ?? spec.values?.[item.id] ?? null;

            const text =
              value === null || value === ""
                ? "—"
                : typeof value === "boolean"
                  ? value
                    ? "بله"
                    : "خیر"
                  : String(value);

            return `
                      <td
                        data-different="${spec.different ? "true" : "false"}">
                        ${escapeHTML(text)}
                        ${spec.unit ? ` ${escapeHTML(spec.unit)}` : ""}
                      </td>
                    `;
          })
          .join("");

        return `
              <tr>

                <th scope="row">

                  <span>
                    ${escapeHTML(spec.group_name)}
                  </span>

                  ${escapeHTML(spec.name)}

                </th>

                ${cells}

              </tr>
            `;
      })
      .join("");

    differenceSummary.textContent = `${Number(
      data.comparison?.different_specification_count || 0,
    ).toLocaleString("fa-IR")} مشخصه متفاوت`;
  };

  const renderCompatibility = (items) => {
    compatibilityGrid.innerHTML = items
      .map((item) => {
        const state = item.compatibility?.state || "unknown";

        const labels = {
          compatible: "سازگار",

          partially_compatible: "سازگاری نسبی",

          not_compatible: "ناسازگار",

          unknown: "نامشخص",
        };

        return `
              <article
                class="comparison-compatibility-card">

                <strong>
                  ${escapeHTML(item.name)}
                </strong>

                <span
                  data-state="${escapeHTML(state)}">
                  ${labels[state] || labels.unknown}
                </span>

                ${
                  item.compatibility?.note
                    ? `
                      <p>
                        ${escapeHTML(item.compatibility.note)}
                      </p>
                    `
                    : ""
                }

              </article>
            `;
      })
      .join("");
  };

  const render = (data) => {
    const items = Array.isArray(data?.items) ? data.items : [];

    if (!items.length) {
      content.hidden = true;
      empty.hidden = false;

      setState("", "empty");

      state.hidden = true;

      return;
    }

    state.hidden = true;
    empty.hidden = true;
    content.hidden = false;

    count.textContent = `${items.length.toLocaleString("fa-IR")} محصول`;

    renderProducts(items);
    renderVerdict(data);
    renderTable(data);
    renderCompatibility(items);
  };

  const load = async (deviceId = null) => {
    const ids = readSelection();

    if (!ids.length) {
      render(null);
      return;
    }

    setState("در حال دریافت و مقایسه اطلاعات واقعی محصولات...");

    try {
      const data = await request(deviceId);

      render(data);
    } catch (error) {
      setState(error.message || "خطا در دریافت مقایسه.", "error");
    }
  };

  products?.addEventListener("click", (event) => {
    const button = event.target.closest("[data-remove-comparison]");

    if (!button) {
      return;
    }

    writeSelection(
      readSelection().filter(
        (id) => id !== Number(button.dataset.removeComparison),
      ),
    );

    load(deviceInput?.value || null);
  });

  root
    .querySelector("[data-comparison-clear]")
    ?.addEventListener("click", () => {
      writeSelection([]);

      load();
    });

  compatibilityForm?.addEventListener("submit", (event) => {
    event.preventDefault();

    const id = Number(deviceInput?.value || 0);

    load(Number.isInteger(id) && id > 0 ? id : null);
  });

  window.addEventListener("comparison:selection-changed", () =>
    load(deviceInput?.value || null),
  );

  load();
};

export default comparison;
