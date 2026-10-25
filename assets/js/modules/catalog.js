const catalog = () => {
  const root = document.querySelector("[data-catalog-page]");

  if (!root) {
    return;
  }

  const apiBase = root.dataset.apiBase;
  const productBase = root.dataset.productBase || "/products";
  const pageType = root.dataset.catalogPage;
  const list = root.querySelector("[data-catalog-list]");
  const state = root.querySelector("[data-catalog-state]");
  const title = root.querySelector("[data-catalog-title]");
  const description = root.querySelector("[data-catalog-description]");
  const filters = root.querySelector("[data-catalog-filters]");

  if (!apiBase || !list || !state) {
    return;
  }

  const filterOptions = {
    categories: [],
    brands: [],
    product_types: [],
    attributes: [],
    price: {
      min: null,
      max: null,
    },
  };

  let isLoading = false;
  let requestToken = 0;
  let currentPage = 1;

  /*
   * =========================================================
   * PAGINATION STATE
   * =========================================================
   */

  const getPageFromUrl = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const value = Number.parseInt(urlParams.get("page") || "1", 10);

    return Number.isInteger(value) && value > 0 ? value : 1;
  };

  const setPageInUrl = (page, { replace = false } = {}) => {
    const numericPage = Number(page);

    const normalizedPage =
      Number.isInteger(numericPage) && numericPage > 0 ? numericPage : 1;

    const url = new URL(window.location.href);

    if (normalizedPage === 1) {
      url.searchParams.delete("page");
    } else {
      url.searchParams.set("page", String(normalizedPage));
    }

    if (replace) {
      window.history.replaceState({}, "", url.toString());
    } else {
      window.history.pushState({}, "", url.toString());
    }

    currentPage = normalizedPage;
  };

  const resetPageInUrl = () => {
    setPageInUrl(1, {
      replace: true,
    });
  };

  /*
   * =========================================================
   * REQUEST
   * =========================================================
   */

  const request = async (path, params = {}) => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", path);

    Object.entries(params).forEach(([key, value]) => {
      if (value !== null && value !== undefined && String(value) !== "") {
        url.searchParams.set(key, value);
      }
    });

    const response = await fetch(url.toString(), {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json();

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "خطا در دریافت اطلاعات.");
    }

    return payload?.data ?? {};
  };

  /*
   * =========================================================
   * HELPERS
   * =========================================================
   */

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
      currency === "IRR" ? "ریال" : escapeHTML(currency)
    }`;
  };

  /*
   * =========================================================
   * STATE
   * =========================================================
   */

  const setState = (value, message = "") => {
    root.dataset.state = value;
    state.dataset.state = value;

    list.toggleAttribute("aria-busy", value === "loading");

    if (value === "loading") {
      state.textContent = message || "در حال دریافت اطلاعات...";
      return;
    }

    if (value === "empty") {
      state.textContent = message || "محصولی برای نمایش پیدا نشد.";
      return;
    }

    if (value === "error") {
      state.textContent = message || "دریافت اطلاعات ناموفق بود.";
      return;
    }

    state.textContent = message;
  };

  /*
   * =========================================================
   * PRODUCTS
   * =========================================================
   */

  const renderProducts = (items) => {
    const countElement = root.querySelector("[data-catalog-count]");

    if (countElement) {
      countElement.textContent = Number(items.length).toLocaleString("fa-IR");
    }

    if (!items.length) {
      list.innerHTML = `
      <div class="catalog-state page-category__empty">
        محصولی برای نمایش در این دسته‌بندی پیدا نشد.
      </div>
    `;

      return;
    }

    list.innerHTML = items
      .map((item) => {
        const variant = item.variant || {};
        const mediaItems = Array.isArray(item.media)
          ? item.media.filter((media) => media && media.url)
          : [];
        const primaryMedia =
          mediaItems.find((media) => media.is_primary) || mediaItems[0] || null;
        const mediaUrl = primaryMedia?.thumbnail_url || primaryMedia?.url || "";
        const mediaMarkup = mediaUrl
          ? `<img class="catalog-card__image" src="${escapeHTML(mediaUrl)}" alt="${escapeHTML(primaryMedia?.alt || item.name || item.title || "")}" loading="lazy" decoding="async">`
          : `<span class="catalog-card__placeholder">${escapeHTML(String(item.name || item.title || "").slice(0, 1))}</span>`;

        const slug = encodeURIComponent(item.slug || "");

        const productUrl = `${productBase.replace(/\/$/, "")}/${slug}`;

        const name = String(item.name || item.title || "").trim();

        const description = String(
          item.short_description ||
            item.shortDescription ||
            item.description ||
            "",
        ).trim();

        const variantName = String(
          variant.name || variant.title || variant.label || "",
        ).trim();

        const priceValue = variant.price ?? item.price ?? null;

        const compareAtValue =
          variant.compare_at_price ??
          variant.compareAtPrice ??
          item.compare_at_price ??
          item.compareAtPrice ??
          null;

        const currency = variant.currency || item.currency || "IRR";

        const price = formatPrice(priceValue, currency);

        const comparePrice =
          compareAtValue !== null &&
          compareAtValue !== undefined &&
          String(compareAtValue) !== ""
            ? formatPrice(compareAtValue, currency)
            : "";

        const hasCompareAtPrice =
          comparePrice !== "" && Number(compareAtValue) > Number(priceValue);

        const isFeatured =
          item.is_featured === true ||
          item.is_featured === 1 ||
          item.isFeatured === true ||
          item.isFeatured === 1;

        const featuredBadge = isFeatured
          ? `
          <span
            class="catalog-card__badge"
            aria-label="محصول ویژه">
            ویژه
          </span>
        `
          : "";

        const variantMeta = variantName
          ? `
          <span class="catalog-card__variant">
            ${escapeHTML(variantName)}
          </span>
        `
          : "";

        return `
       <article
  class="catalog-card"
  data-product-id="${item.id}">
          <a
            class="catalog-card__link"
            href="${productUrl}"
            aria-label="مشاهده ${escapeHTML(name)}">

            <div class="catalog-card__media">

              ${featuredBadge}

              ${mediaMarkup}
            </div>

            <div class="catalog-card__body">

              <div class="catalog-card__identity">
                <strong class="catalog-card__title">
                  ${escapeHTML(name)}
                </strong>

                ${variantMeta}

                ${
                  description
                    ? `
                      <p class="catalog-card__description">
                        ${escapeHTML(description)}
                      </p>
                    `
                    : ""
                }
              </div>

              <div class="catalog-card__pricing">

                ${
                  hasCompareAtPrice
                    ? `
                      <del class="catalog-card__compare-price">
                        ${escapeHTML(comparePrice)}
                      </del>
                    `
                    : ""
                }

                <span class="catalog-card__price">
                  ${escapeHTML(price)}
                </span>

              </div>

            </div>
          </a>
        </article>
      `;
      })
      .join("");
  };

  /*
   * =========================================================
   * FILTER VALUE HELPERS
   * =========================================================
   */

  const getFilterValue = (selector) => {
    return filters?.querySelector(selector)?.value || "";
  };

  const getChecked = (selector) => {
    return filters?.querySelector(selector)?.checked === true;
  };

  const getAttributeValueIds = () => {
    if (!filters) {
      return [];
    }

    return Array.from(
      filters.querySelectorAll("[data-filter-attribute-value]:checked"),
    )
      .map((input) => input.value)
      .filter((value) => value !== "");
  };

  /*
   * =========================================================
   * FILTERS
   * =========================================================
   */

  const getFilters = () => {
    const urlParams = new URLSearchParams(window.location.search);

    const params = {};

    const query = urlParams.get("q") || "";

    const categoryId = getFilterValue("[data-filter-category]");

    const brandId = getFilterValue("[data-filter-brand]");

    const productTypeId = getFilterValue("[data-filter-product-type]");

    const sort = getFilterValue("[data-filter-sort]");

    const minPrice = getFilterValue("[data-filter-min-price]");

    const maxPrice = getFilterValue("[data-filter-max-price]");

    const attributeValueIds = getAttributeValueIds();

    if (query !== "") {
      params.q = query;
    }

    if (categoryId !== "") {
      params.category_id = categoryId;
    }

    if (brandId !== "") {
      params.brand_id = brandId;
    }

    if (productTypeId !== "") {
      params.product_type_id = productTypeId;
    }

    if (["relevance", "newest", "oldest", "featured"].includes(sort)) {
      params.sort = sort;
    }

    if (minPrice !== "") {
      params.min_price = minPrice;
    }

    if (maxPrice !== "") {
      params.max_price = maxPrice;
    }

    if (attributeValueIds.length) {
      params.attribute_value_ids = attributeValueIds.join(",");
    }

    if (getChecked("[data-filter-stock]")) {
      params.in_stock = "1";
    }

    const page = getPageFromUrl();

    params.page = page;

    return params;
  };

  const syncFilterUrl = (params) => {
    const url = new URL(window.location.href);

    const managedKeys = [
      "category_id",
      "brand_id",
      "product_type_id",
      "sort",
      "min_price",
      "max_price",
      "attribute_value_ids",
      "in_stock",
    ];

    managedKeys.forEach((key) => {
      url.searchParams.delete(key);
    });

    Object.entries(params).forEach(([key, value]) => {
      if (key !== "q" && !managedKeys.includes(key)) {
        return;
      }

      if (value !== null && value !== undefined && String(value) !== "") {
        url.searchParams.set(key, value);
      }
    });

    window.history.replaceState({}, "", url.toString());
  };

  const hydrateFiltersFromUrl = () => {
    if (!filters) {
      return;
    }

    const params = new URLSearchParams(window.location.search);

    const setValue = (selector, value) => {
      if (value === null || value === "") {
        return;
      }

      filters.querySelectorAll(selector).forEach((input) => {
        input.value = value;
      });
    };

    setValue("[data-filter-category]", params.get("category_id"));

    setValue("[data-filter-brand]", params.get("brand_id"));

    setValue("[data-filter-product-type]", params.get("product_type_id"));

    setValue("[data-filter-sort]", params.get("sort"));

    setValue("[data-filter-min-price]", params.get("min_price"));

    setValue("[data-filter-max-price]", params.get("max_price"));

    const stock = params.get("in_stock");

    if (stock !== null) {
      filters.querySelectorAll("[data-filter-stock]").forEach((input) => {
        input.checked = stock === "1";
      });
    }

    const attributeValueIds = new Set(
      (params.get("attribute_value_ids") || "")
        .split(",")
        .map((value) => value.trim())
        .filter(Boolean),
    );

    filters
      .querySelectorAll("[data-filter-attribute-value]")
      .forEach((input) => {
        input.checked = attributeValueIds.has(input.value);
      });

    syncFilterOptionStates();
    renderActiveFilters();
    updateAllFilterGroupMeta();
  };

  /*
   * =========================================================
   * ACTIVE FILTERS
   * =========================================================
   */

  const getActiveFilterItems = () => {
    const active = [];

    const category = filters?.querySelector("[data-filter-category]");

    const brand = filters?.querySelector("[data-filter-brand]");

    const productType = filters?.querySelector("[data-filter-product-type]");

    const sort = filters?.querySelector("[data-filter-sort]");

    const minPrice = filters?.querySelector("[data-filter-min-price]");

    const maxPrice = filters?.querySelector("[data-filter-max-price]");

    const stock = filters?.querySelector("[data-filter-stock]");

    if (category?.value) {
      active.push({
        key: "category_id",
        value: category.value,
        label: category.selectedOptions[0]?.textContent?.trim() || "دسته‌بندی",
      });
    }

    if (brand?.value) {
      active.push({
        key: "brand_id",
        value: brand.value,
        label: brand.selectedOptions[0]?.textContent?.trim() || "برند",
      });
    }

    if (productType?.value) {
      active.push({
        key: "product_type_id",
        value: productType.value,
        label:
          productType.selectedOptions[0]?.textContent?.trim() || "نوع محصول",
      });
    }

    if (sort?.value && sort.value !== "relevance") {
      active.push({
        key: "sort",
        value: sort.value,
        label: sort.selectedOptions[0]?.textContent?.trim() || "مرتب‌سازی",
      });
    }

    if (minPrice?.value) {
      active.push({
        key: "min_price",
        value: minPrice.value,
        label: `حداقل ${Number(minPrice.value).toLocaleString("fa-IR")} ریال`,
      });
    }

    if (maxPrice?.value) {
      active.push({
        key: "max_price",
        value: maxPrice.value,
        label: `حداکثر ${Number(maxPrice.value).toLocaleString("fa-IR")} ریال`,
      });
    }

    if (stock?.checked) {
      active.push({
        key: "in_stock",
        value: "1",
        label: "فقط موجود",
      });
    }

    getAttributeValueIds().forEach((value) => {
      const input = filters?.querySelector(
        `[data-filter-attribute-value][value="${CSS.escape(value)}"]`,
      );

      active.push({
        key: "attribute_value_ids",
        value,
        label:
          input?.dataset.filterLabel ||
          input?.closest("label")?.textContent?.trim() ||
          "ویژگی",
      });
    });

    return active;
  };

  const renderActiveFilters = () => {
    if (!filters) {
      return;
    }

    const containers = filters.querySelectorAll("[data-filter-active]");

    if (!containers.length) {
      return;
    }

    const active = getActiveFilterItems();

    containers.forEach((container) => {
      if (!active.length) {
        container.innerHTML = "";
        container.hidden = true;
        return;
      }

      container.hidden = false;

      container.innerHTML = active
        .map(
          (item) => `
            <button
              type="button"
              class="catalog-filter-chip"
              data-filter-remove
              data-filter-key="${escapeHTML(item.key)}"
              data-filter-value="${escapeHTML(item.value)}">
              <span>
                ${escapeHTML(item.label)}
              </span>

              <span aria-hidden="true">
                ×
              </span>
            </button>
          `,
        )
        .join("");
    });
  };

  /*
   * =========================================================
   * SELECT OPTIONS
   * =========================================================
   */

  const renderSelectOptions = (
    selector,
    items,
    placeholder,
    valueKey = "id",
    labelKey = "name",
  ) => {
    const selects = filters?.querySelectorAll(selector);

    if (!selects?.length) {
      return;
    }

    selects.forEach((select) => {
      const currentValue = select.value;

      select.innerHTML = `
        <option value="">
          ${escapeHTML(placeholder)}
        </option>

        ${
          Array.isArray(items)
            ? items
                .map((item) => {
                  const value = item?.[valueKey];

                  const label = item?.[labelKey];

                  if (
                    value === null ||
                    value === undefined ||
                    label === null ||
                    label === undefined
                  ) {
                    return "";
                  }

                  return `
                    <option
                      value="${escapeHTML(value)}">
                      ${escapeHTML(label)}
                    </option>
                  `;
                })
                .join("")
            : ""
        }
      `;

      if (
        currentValue &&
        Array.from(select.options).some(
          (option) => option.value === currentValue,
        )
      ) {
        select.value = currentValue;
      }
    });
  };

  /*
   * =========================================================
   * FILTER GROUP HELPERS
   * =========================================================
   */

  const getGroupKey = (group) => {
    return group?.dataset.filterGroup || group?.dataset.group || "";
  };

  const getGroupInputs = (group) => {
    if (!group) {
      return [];
    }

    return Array.from(group.querySelectorAll("select, input"));
  };

  const getGroupSelectedCount = (group) => {
    if (!group) {
      return 0;
    }

    const key = getGroupKey(group);

    if (key === "attributes" || key === "attribute") {
      return group.querySelectorAll("[data-filter-attribute-value]:checked")
        .length;
    }

    if (key === "stock") {
      return group.querySelector("[data-filter-stock]:checked") ? 1 : 0;
    }

    if (key === "price") {
      const min = group.querySelector("[data-filter-min-price]");
      const max = group.querySelector("[data-filter-max-price]");

      return Number(Boolean(min?.value)) + Number(Boolean(max?.value));
    }

    const select = group.querySelector("select");

    if (select?.value) {
      return 1;
    }

    const checked = group.querySelector("input:checked");

    return checked ? 1 : 0;
  };

  const updateFilterGroupMeta = (group) => {
    if (!group) {
      return;
    }

    const selectedCount = getGroupSelectedCount(group);

    const countElement = group.querySelector("[data-filter-group-count]");

    const clearButton = group.querySelector("[data-filter-group-clear]");

    if (countElement) {
      countElement.textContent =
        selectedCount > 0 ? selectedCount.toLocaleString("fa-IR") : "";
    }

    if (clearButton) {
      clearButton.hidden = selectedCount === 0;
    }

    group.dataset.selectedCount = String(selectedCount);
  };

  const updateAllFilterGroupMeta = () => {
    if (!filters) {
      return;
    }

    filters
      .querySelectorAll("[data-filter-group]")
      .forEach(updateFilterGroupMeta);
  };

  const syncFilterOptionStates = () => {
    if (!filters) {
      return;
    }

    filters.querySelectorAll("[data-filter-option-label]").forEach((label) => {
      const input = label.querySelector("input");

      if (!input) {
        return;
      }

      label.classList.toggle("is-selected", input.checked);
    });
  };

  /*
   * =========================================================
   * FILTER GROUP RENDERING
   * =========================================================
   */

  const renderFilterGroup = (
    container,
    titleText,
    selected = 0,
    open = false,
  ) => {
    if (!container) {
      return;
    }

    const selectedCount = Number(selected) || 0;

    container.classList.toggle("is-open", open);

    const titleElement = container.querySelector("[data-filter-group-title]");

    const countElement = container.querySelector("[data-filter-group-count]");

    const body = container.querySelector("[data-filter-group-body]");

    const toggle = container.querySelector("[data-filter-group-toggle]");

    if (titleElement && titleText) {
      titleElement.textContent = titleText;
    }

    if (countElement) {
      countElement.textContent =
        selectedCount > 0 ? selectedCount.toLocaleString("fa-IR") : "";
    }

    if (body) {
      body.hidden = !open;
    }

    if (toggle) {
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    }
  };

  const renderFilterOptions = (container, items, type, selectedValues = []) => {
    if (!container) {
      return;
    }

    const normalizedSelected = new Set(
      selectedValues.map((value) => String(value)),
    );

    const inputName = `${type}-filter-${containerIndex}`;

    container.innerHTML = Array.isArray(items)
      ? items
          .map((item) => {
            const value = String(item?.id ?? item?.value ?? "");
            const label = String(
              item?.name ?? item?.label ?? item?.value ?? "",
            );

            if (!value || !label) {
              return "";
            }

            const checked = normalizedSelected.has(value);

            return `
              <label
                class="catalog-filter-option"
                data-filter-option-label
                data-filter-searchable="${escapeHTML(label)}">

                <input
                  type="checkbox"
                  name="${escapeHTML(inputName)}[]"
                  value="${escapeHTML(value)}"
                  ${checked ? "checked" : ""}
                  data-filter-value="${escapeHTML(value)}">

                <span>
                  ${escapeHTML(label)}
                </span>

              </label>
            `;
          })
          .join("")
      : "";
  };

  /*
   * =========================================================
   * ATTRIBUTE OPTIONS
   * =========================================================
   */

  const renderAttributeOptions = () => {
    const containers = filters?.querySelectorAll("[data-filter-attributes]");

    if (!containers?.length) {
      return;
    }

    containers.forEach((container) => {
      const attributeId = String(container.dataset.filterAttributeId || "");

      const attribute = filterOptions.attributes.find(
        (item) => String(item?.id ?? "") === attributeId,
      );

      if (!attribute) {
        return;
      }

      const selectedValues = getAttributeValueIds();

      const values = Array.isArray(attribute?.values) ? attribute.values : [];

      const options = values
        .map((value) => {
          const id = String(value?.id ?? value?.value ?? "");

          const label = String(value?.name ?? value?.value ?? "");

          const checked = selectedValues.includes(id);

          if (!id || !label) {
            return "";
          }

          return `
            <label
              class="catalog-filter-option"
              data-filter-option-label
              data-filter-searchable="${escapeHTML(label)}">

              <input
                type="checkbox"
                value="${escapeHTML(id)}"
                ${checked ? "checked" : ""}
                data-filter-attribute-value
                data-filter-label="${escapeHTML(label)}">

              <span>
                ${escapeHTML(label)}
              </span>

            </label>
          `;
        })
        .join("");

      container.innerHTML = options;
    });
  };

  const updateAttributeGroupMeta = (container) => {
    if (!container) {
      return;
    }

    const groups = container.querySelectorAll("[data-filter-group]");

    if (groups.length) {
      groups.forEach((group) => {
        const count = group.querySelectorAll(
          "[data-filter-attribute-value]:checked",
        ).length;

        const countElement = group.querySelector("[data-filter-group-count]");

        const clearButton = group.querySelector("[data-filter-group-clear]");

        if (countElement) {
          countElement.textContent =
            count > 0 ? count.toLocaleString("fa-IR") : "";
        }

        if (clearButton) {
          clearButton.hidden = count === 0;
        }
      });

      return;
    }

    const count = container.querySelectorAll(
      "[data-filter-attribute-value]:checked",
    ).length;

    const countElement = container.querySelector("[data-filter-group-count]");

    const clearButton = container.querySelector("[data-filter-group-clear]");

    if (countElement) {
      countElement.textContent = count > 0 ? count.toLocaleString("fa-IR") : "";
    }

    if (clearButton) {
      clearButton.hidden = count === 0;
    }
  };

  /*
   * =========================================================
   * FILTER OPTIONS UI
   * =========================================================
   */

  const renderFilterOptionsUI = () => {
    if (!filters) {
      return;
    }

    renderSelectOptions(
      "[data-filter-category]",
      filterOptions.categories,
      "همه دسته‌بندی‌ها",
    );

    renderSelectOptions(
      "[data-filter-brand]",
      filterOptions.brands,
      "همه برندها",
    );

    renderSelectOptions(
      "[data-filter-product-type]",
      filterOptions.product_types,
      "همه انواع محصولات",
    );

    const minPrices = filters.querySelectorAll("[data-filter-min-price]");

    const maxPrices = filters.querySelectorAll("[data-filter-max-price]");

    minPrices.forEach((input) => {
      if (
        filterOptions.price.min !== null &&
        input.dataset.filterDefault === undefined
      ) {
        input.dataset.filterMin = String(filterOptions.price.min);
      }
    });

    maxPrices.forEach((input) => {
      if (
        filterOptions.price.max !== null &&
        input.dataset.filterDefault === undefined
      ) {
        input.dataset.filterMax = String(filterOptions.price.max);
      }
    });

    renderAttributeOptions();

    hydrateFiltersFromUrl();

    renderActiveFilters();

    syncFilterOptionStates();

    updateAllFilterGroupMeta();
  };

  /*
   * =========================================================
   * LOAD FILTERS
   * =========================================================
   */

  const loadFilters = async () => {
    if (!filters) {
      return;
    }

    try {
      const data = await request("/search/filters");

      filterOptions.categories = Array.isArray(data.categories)
        ? data.categories
        : [];

      filterOptions.brands = Array.isArray(data.brands) ? data.brands : [];

      filterOptions.product_types = Array.isArray(data.product_types)
        ? data.product_types
        : [];

      filterOptions.attributes = Array.isArray(data.attributes)
        ? data.attributes
        : [];

      filterOptions.price = {
        min: data.price?.min ?? null,
        max: data.price?.max ?? null,
      };

      root._catalogFilterOptions = filterOptions;

      renderFilterOptionsUI();

      root.dispatchEvent(
        new CustomEvent("catalog:filters-loaded", {
          detail: filterOptions,
        }),
      );
    } catch (error) {
      root.dispatchEvent(
        new CustomEvent("catalog:filters-error", {
          detail: error,
        }),
      );
    }
  };

  /*
   * =========================================================
   * RESET FILTERS
   * =========================================================
   */

  const resetFilters = () => {
    if (!filters) {
      return;
    }

    filters.querySelectorAll("select").forEach((select) => {
      select.value =
        select.dataset.filterDefault ||
        (select.matches("[data-filter-sort]") ? "relevance" : "");
    });

    filters
      .querySelectorAll("[data-filter-min-price], [data-filter-max-price]")
      .forEach((input) => {
        input.value = "";
      });

    filters
      .querySelectorAll("[data-filter-stock], [data-filter-attribute-value]")
      .forEach((input) => {
        input.checked = false;
      });

    filters.querySelectorAll("[data-filter-search]").forEach((input) => {
      input.value = "";

      const group = input.closest("[data-filter-group]");

      if (group) {
        group
          .querySelectorAll("[data-filter-option-label]")
          .forEach((option) => {
            option.hidden = false;
          });
      }
    });

    syncFilterOptionStates();

    renderActiveFilters();

    updateAllFilterGroupMeta();

    resetPageInUrl();

    load(1);
  };

  /*
   * =========================================================
   * REMOVE ACTIVE FILTER
   * =========================================================
   */

  const removeActiveFilter = (key, value) => {
    if (!filters) {
      return;
    }

    if (key === "attribute_value_ids") {
      const input = Array.from(
        filters.querySelectorAll("[data-filter-attribute-value]"),
      ).find((item) => item.value === value);

      if (input) {
        input.checked = false;
      }
    } else if (key === "in_stock") {
      const inputs = filters.querySelectorAll("[data-filter-stock]");

      inputs.forEach((input) => {
        input.checked = false;
      });
    } else {
      let selector = "";

      if (key === "category_id") {
        selector = "[data-filter-category]";
      } else if (key === "brand_id") {
        selector = "[data-filter-brand]";
      } else if (key === "product_type_id") {
        selector = "[data-filter-product-type]";
      } else if (key === "sort") {
        selector = "[data-filter-sort]";
      } else if (key === "min_price") {
        selector = "[data-filter-min-price]";
      } else if (key === "max_price") {
        selector = "[data-filter-max-price]";
      }

      if (selector) {
        filters.querySelectorAll(selector).forEach((input) => {
          if (input.matches("select")) {
            input.value =
              input.dataset.filterDefault ||
              (input.matches("[data-filter-sort]") ? "relevance" : "");
          } else {
            input.value = "";
          }
        });
      }
    }

    syncFilterOptionStates();

    renderActiveFilters();

    updateAllFilterGroupMeta();

    resetPageInUrl();

    load(1);
  };

  /*
   * =========================================================
   * PAGINATION
   * =========================================================
   */

  const renderPagination = (data) => {
    const pagination = root.querySelector("[data-catalog-pagination]");

    if (!pagination) {
      return;
    }

    const totalPages = Number.parseInt(
      data?.total_pages ?? data?.pagination?.total_pages ?? "0",
      10,
    );

    const page = Number.parseInt(
      data?.page ?? data?.pagination?.page ?? currentPage ?? "1",
      10,
    );

    if (!Number.isInteger(totalPages) || totalPages <= 1) {
      pagination.innerHTML = "";
      pagination.hidden = true;
      return;
    }

    const safePage =
      Number.isInteger(page) && page > 0 ? Math.min(page, totalPages) : 1;

    currentPage = safePage;

    if (safePage !== getPageFromUrl()) {
      setPageInUrl(safePage, {
        replace: true,
      });
    }

    const buttons = [];

    buttons.push(`
      <button
        type="button"
        class="catalog-pagination__button catalog-pagination__button--arrow"
        data-catalog-page-number="${safePage - 1}"
        aria-label="صفحه قبلی"
        ${safePage <= 1 ? "disabled" : ""}>
        قبلی
      </button>
    `);

    const startPage = Math.max(1, safePage - 2);

    const endPage = Math.min(totalPages, safePage + 2);

    if (startPage > 1) {
      buttons.push(`
        <button
          type="button"
          class="catalog-pagination__button"
          data-catalog-page-number="1"
          aria-label="صفحه 1">
          1
        </button>
      `);

      if (startPage > 2) {
        buttons.push(`
          <span
            class="catalog-pagination__ellipsis"
            aria-hidden="true">
            …
          </span>
        `);
      }
    }

    for (let pageNumber = startPage; pageNumber <= endPage; pageNumber += 1) {
      const isCurrent = pageNumber === safePage;

      buttons.push(`
        <button
          type="button"
          class="catalog-pagination__button${isCurrent ? " is-active" : ""}"
          data-catalog-page-number="${pageNumber}"
          ${isCurrent ? 'aria-current="page"' : ""}
          aria-label="صفحه ${pageNumber}">
          ${pageNumber.toLocaleString("fa-IR")}
        </button>
      `);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        buttons.push(`
          <span
            class="catalog-pagination__ellipsis"
            aria-hidden="true">
            …
          </span>
        `);
      }

      buttons.push(`
        <button
          type="button"
          class="catalog-pagination__button"
          data-catalog-page-number="${totalPages}"
          aria-label="صفحه ${totalPages}">
          ${totalPages.toLocaleString("fa-IR")}
        </button>
      `);
    }

    buttons.push(`
      <button
        type="button"
        class="catalog-pagination__button catalog-pagination__button--arrow"
        data-catalog-page-number="${safePage + 1}"
        aria-label="صفحه بعدی"
        ${safePage >= totalPages ? "disabled" : ""}>
        بعدی
      </button>
    `);

    pagination.innerHTML = buttons.join("");

    pagination.hidden = false;
  };

  const bindPaginationEvents = () => {
    const pagination = root.querySelector("[data-catalog-pagination]");

    if (!pagination) {
      return;
    }

    pagination.addEventListener("click", (event) => {
      const button = event.target.closest("[data-catalog-page-number]");

      if (!button || button.disabled) {
        return;
      }

      const page = Number.parseInt(button.dataset.catalogPageNumber || "1", 10);

      if (!Number.isInteger(page) || page < 1 || page === currentPage) {
        return;
      }

      setPageInUrl(page);

      load(page);
    });
  };

  /*
   * =========================================================
   * RESULT COUNT
   * =========================================================
   */

  const updateResultCount = (data) => {
    const count = root.querySelector("[data-catalog-count]");

    if (!count) {
      return;
    }

    const total =
      data.total ??
      data.count ??
      data.pagination?.total ??
      (Array.isArray(data.items) ? data.items.length : 0);

    count.textContent = `${Number(total).toLocaleString("fa-IR")} محصول`;
  };

  /*
   * =========================================================
   * FILTER GROUP EVENTS
   * =========================================================
   */

  const bindFilterGroupEvents = () => {
    if (!filters) {
      return;
    }

    filters.addEventListener("click", (event) => {
      const toggle = event.target.closest("[data-filter-group-toggle]");

      if (toggle) {
        const group = toggle.closest("[data-filter-group]");

        if (!group) {
          return;
        }

        const body = group.querySelector("[data-filter-group-body]");

        if (!body) {
          return;
        }

        const isOpen = group.classList.toggle("is-open");

        body.hidden = !isOpen;

        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");

        return;
      }

      const clearButton = event.target.closest("[data-filter-group-clear]");

      if (!clearButton) {
        return;
      }

      const group = clearButton.closest("[data-filter-group]");

      const groupKey =
        clearButton.dataset.filterGroupClear || getGroupKey(group);

      if (!groupKey) {
        return;
      }

      if (groupKey === "brand") {
        filters.querySelectorAll("[data-filter-brand]").forEach((input) => {
          input.value = "";
        });
      }

      if (groupKey === "category") {
        filters.querySelectorAll("[data-filter-category]").forEach((input) => {
          input.value = "";
        });
      }

      if (groupKey === "product_type") {
        filters
          .querySelectorAll("[data-filter-product-type]")
          .forEach((input) => {
            input.value = "";
          });
      }

      if (groupKey === "price") {
        filters
          .querySelectorAll("[data-filter-min-price], [data-filter-max-price]")
          .forEach((input) => {
            input.value = "";
          });
      }

      if (groupKey === "attributes" || groupKey === "attribute") {
        if (group?.classList.contains("catalog-filter-group--attribute")) {
          group
            .querySelectorAll("[data-filter-attribute-value]")
            .forEach((input) => {
              input.checked = false;
            });
        } else {
          filters
            .querySelectorAll("[data-filter-attribute-value]")
            .forEach((input) => {
              input.checked = false;
            });
        }
      }

      if (groupKey === "stock") {
        filters.querySelectorAll("[data-filter-stock]").forEach((input) => {
          input.checked = false;
        });
      }

      if (groupKey === "sort") {
        filters.querySelectorAll("[data-filter-sort]").forEach((input) => {
          input.value = input.dataset.filterDefault || "relevance";
        });
      }

      syncFilterOptionStates();

      renderActiveFilters();

      updateAllFilterGroupMeta();

      resetPageInUrl();

      load(1);
    });

    filters.addEventListener("input", (event) => {
      const searchInput = event.target.closest("[data-filter-search]");

      if (!searchInput) {
        return;
      }

      const query = searchInput.value.trim().toLowerCase();

      const group = searchInput.closest("[data-filter-group]");

      if (!group) {
        return;
      }

      group.querySelectorAll("[data-filter-option-label]").forEach((option) => {
        const searchableText = (
          option.dataset.filterSearchable ||
          option.textContent ||
          ""
        ).toLowerCase();

        option.hidden = query !== "" && !searchableText.includes(query);
      });
    });

    filters.addEventListener("change", (event) => {
      const target = event.target;

      if (
        target.matches("[data-filter-attribute-value], [data-filter-stock]")
      ) {
        syncFilterOptionStates();

        renderActiveFilters();

        updateAllFilterGroupMeta();

        return;
      }

      if (
        target.matches("select") ||
        target.matches("[data-filter-min-price], [data-filter-max-price]")
      ) {
        renderActiveFilters();

        updateAllFilterGroupMeta();
      }
    });
  };

  /*
   * =========================================================
   * LOAD CATALOG
   * =========================================================
   */

  const load = async (requestedPage = getPageFromUrl()) => {
    const currentToken = ++requestToken;

    isLoading = true;

    setState("loading");

    list.innerHTML = "";

    try {
      let data;

      const params = getFilters();

      syncFilterUrl(params);

      const numericPage = Number(requestedPage);

      const page =
        Number.isInteger(numericPage) && numericPage > 0 ? numericPage : 1;

      currentPage = page;

      const urlParams = new URLSearchParams(window.location.search);

      const slug = root.dataset.slug || urlParams.get("slug") || "";

      if (pageType === "product") {
        data = await request("/storefront/products/detail", {
          slug,
        });

        if (currentToken !== requestToken) {
          return;
        }

        if (title) {
          title.textContent = data.name || "جزئیات محصول";
        }

        if (description) {
          description.textContent =
            data.description ||
            data.short_description ||
            data.shortDescription ||
            "";
        }

        renderProducts([data]);

        renderPagination({
          total_pages: 0,
        });
      } else if (pageType === "category") {
        data = await request("/storefront/categories/detail", {
          slug,
          ...params,
        });

        if (currentToken !== requestToken) {
          return;
        }

        if (title) {
          title.textContent = data.category?.name || "دسته‌بندی";
        }

        if (description) {
          description.textContent = data.category?.description || "";
        }

        renderProducts(data.items || []);

        updateResultCount(data);

        renderPagination({
          total_pages: 0,
        });
      } else if (pageType === "brand") {
        data = await request("/storefront/brands/detail", {
          slug,
          ...params,
        });

        if (currentToken !== requestToken) {
          return;
        }

        if (title) {
          title.textContent = data.brand?.name || "برند";
        }

        if (description) {
          description.textContent = data.brand?.description || "";
        }

        renderProducts(data.items || []);

        updateResultCount(data);

        renderPagination({
          total_pages: 0,
        });
      } else if (pageType === "search") {
        params.q = urlParams.get("q") || "";

        params.page = page;

        data = await request("/search", params);

        if (currentToken !== requestToken) {
          return;
        }

        renderProducts(data.items || []);

        updateResultCount(data);

        renderPagination(data);

        if (title) {
          title.textContent = params.q
            ? `نتایج جستجو برای «${params.q}»`
            : "نتایج جستجو";
        }
      } else {
        data = await request("/storefront/products", params);

        if (currentToken !== requestToken) {
          return;
        }

        renderProducts(data.items || []);

        updateResultCount(data);

        renderPagination({
          total_pages: 0,
        });
      }

      const renderedItems = Array.isArray(data?.items)
        ? data.items
        : pageType === "product"
          ? [data]
          : [];

      if (renderedItems.length === 0) {
        const emptyMessage =
          pageType === "search"
            ? "برای عبارت جستجو نتیجه‌ای پیدا نشد."
            : "محصولی برای نمایش پیدا نشد.";

        setState("empty", emptyMessage);
      } else {
        setState("ready", "");
      }

      renderActiveFilters();

      syncFilterOptionStates();

      updateAllFilterGroupMeta();
    } catch (error) {
      if (currentToken !== requestToken) {
        return;
      }

      console.error("Catalog request failed:", error);

      list.innerHTML = "";

      setState("error", error.message || "دریافت اطلاعات ناموفق بود.");
    } finally {
      if (currentToken === requestToken) {
        isLoading = false;
      }
    }
  };

  /*
   * =========================================================
   * EXISTING FILTER ACTIONS
   * =========================================================
   */

  filters
    ?.querySelector("[data-filter-apply]")
    ?.addEventListener("click", () => {
      resetPageInUrl();

      load(1);
    });

  filters
    ?.querySelector("[data-filter-reset]")
    ?.addEventListener("click", resetFilters);

  filters?.addEventListener("click", (event) => {
    const button = event.target.closest("[data-filter-remove]");

    if (!button) {
      return;
    }

    removeActiveFilter(
      button.dataset.filterKey || "",
      button.dataset.filterValue || "",
    );
  });

  /*
   * =========================================================
   * BROWSER HISTORY / URL NAVIGATION
   * =========================================================
   */

  window.addEventListener("popstate", () => {
    const page = getPageFromUrl();

    if (page === currentPage) {
      return;
    }

    load(page);
  });

  /*
   * =========================================================
   * INIT
   * =========================================================
   */

  window.addEventListener("popstate", () => {
    hydrateFiltersFromUrl();
    load(getPageFromUrl());
  });

  const initializeCatalog = async () => {
    bindFilterGroupEvents();
    bindPaginationEvents();

    await loadFilters();

    load();
  };

  initializeCatalog();
};

export default catalog;
