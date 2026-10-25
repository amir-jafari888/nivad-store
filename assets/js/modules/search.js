import { updateCommerceContext } from "./commerce-context.js";

const search = () => {
  const form = document.querySelector("[data-search-form]");
  const input = document.querySelector("[data-search-input]");

  if (!form || !input) return;

  const recentSearchKey = "nivad-recent-searches";
  const maxRecentSearches = 6;
  const debounceDelay = 180;

  const normalize = (value) =>
    String(value ?? "")
      .trim()
      .toLowerCase()
      .replace(/ي/g, "ی")
      .replace(/ى/g, "ی")
      .replace(/ك/g, "ک");

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const resolveBaseUrl = () => {
    try {
      const actionUrl = new URL(
        form.getAttribute("action") || "/search",
        window.location.href,
      );

      const searchPath = actionUrl.pathname.replace(/\/+$/, "");

      const suffix = "/search";

      if (searchPath.endsWith(suffix)) {
        return searchPath.slice(0, -suffix.length);
      }

      return "";
    } catch {
      return "";
    }
  };

  const searchActionUrl = new URL(
    form.getAttribute("action") || "/search",
    window.location.href,
  );

  const searchPath = searchActionUrl.pathname.replace(/\/+$/, "");

  const baseUrl = searchPath.endsWith("/search")
    ? searchPath.slice(0, -"/search".length)
    : (document.documentElement.dataset.basePath || "").replace(/\/+$/, "");

  let requestToken = 0;
  let debounceTimer = null;
  let suggestionBox = null;
  let activeIndex = -1;

  const readRecentSearches = () => {
    try {
      const stored = window.localStorage.getItem(recentSearchKey);
      const parsed = JSON.parse(stored || "[]");

      return Array.isArray(parsed)
        ? parsed.filter(
            (item) => typeof item === "string" && item.trim() !== "",
          )
        : [];
    } catch {
      return [];
    }
  };

  const saveRecentSearch = (value) => {
    const normalizedValue = String(value ?? "").trim();

    if (!normalizedValue) {
      return;
    }

    const next = [
      normalizedValue,
      ...readRecentSearches().filter(
        (item) => normalize(item) !== normalize(normalizedValue),
      ),
    ].slice(0, maxRecentSearches);

    try {
      window.localStorage.setItem(recentSearchKey, JSON.stringify(next));
    } catch {
      // Search remains functional if localStorage is unavailable.
    }
  };

  const createBox = () => {
    if (suggestionBox) {
      return suggestionBox;
    }

    suggestionBox = document.createElement("div");

    suggestionBox.className = "search-suggestions";
    suggestionBox.id = "search-suggestions";
    suggestionBox.setAttribute("role", "listbox");
    suggestionBox.setAttribute("aria-label", "پیشنهادهای جستجو");

    form.appendChild(suggestionBox);

    return suggestionBox;
  };

  const getOptions = () => [
    ...(suggestionBox?.querySelectorAll(".search-suggestion") || []),
  ];

  const updateActiveOption = () => {
    const options = getOptions();

    options.forEach((option, index) => {
      const active = index === activeIndex;

      option.classList.toggle("is-active", active);

      option.setAttribute("aria-selected", String(active));
    });

    if (activeIndex >= 0 && options[activeIndex]) {
      input.setAttribute("aria-activedescendant", options[activeIndex].id);
    } else {
      input.removeAttribute("aria-activedescendant");
    }
  };

  const close = () => {
    if (!suggestionBox) return;

    suggestionBox.classList.remove("is-visible");

    input.setAttribute("aria-expanded", "false");

    input.removeAttribute("aria-activedescendant");

    activeIndex = -1;
  };

  const open = () => {
    createBox().classList.add("is-visible");

    input.setAttribute("aria-expanded", "true");
  };

  const request = async (query) => {
    const apiUrl = new URL(`${baseUrl}/api/index.php`, window.location.origin);

    apiUrl.searchParams.set("path", "/search/suggestions");
    apiUrl.searchParams.set("q", query);

    const response = await fetch(apiUrl.toString(), {
      method: "GET",
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok || !payload || payload.success === false) {
      throw new Error(
        payload?.message || `Search suggestions failed (${response.status})`,
      );
    }

    return payload.data ?? [];
  };

  const normalizeRemoteSuggestions = (items) => {
    if (!Array.isArray(items)) {
      return [];
    }

    return items
      .map((item) => {
        const brand = item?.metadata?.brand?.name || "";

        const category = item?.metadata?.category?.name || "";

        const meta = brand || category || "محصول";

        return {
          type: "محصول",
          title: item?.title || item?.name || item?.label || "",
          meta,
          value: item?.value || item?.name || item?.title || "",
        };
      })
      .filter((item) => String(item.value).trim() !== "");
  };

  const iconFor = (type) => {
    if (type === "برند") {
      return `
        <svg
          class="icon"
          viewBox="0 0 24 24"
          aria-hidden="true">
          <path
            d="M5 5h14v14H5z"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7" />
          <path
            d="M8 9h8M8 12h5M8 15h7"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            stroke-linecap="round" />
        </svg>
      `;
    }

    return `
      <svg
        class="icon"
        viewBox="0 0 24 24"
        aria-hidden="true">
        <path
          d="M6 8h12l1 12H5L6 8Z"
          fill="none"
          stroke="currentColor"
          stroke-width="1.7"
          stroke-linejoin="round" />
        <path
          d="M9 8a3 3 0 0 1 6 0"
          fill="none"
          stroke="currentColor"
          stroke-width="1.7"
          stroke-linecap="round" />
      </svg>
    `;
  };

  const setState = (state) => {
    const box = createBox();
    box.dataset.searchState = state;
  };

  const renderLoading = () => {
    setState("loading");

    const box = createBox();

    box.innerHTML = `
      <div class="search-empty">
        <strong>در حال جستجو...</strong>
        <small>
          پیشنهادهای مرتبط در حال دریافت هستند.
        </small>
      </div>
    `;

    open();
  };

  const renderError = () => {
    setState("error");

    const box = createBox();

    box.innerHTML = `
      <div class="search-empty">
        <strong>
          دریافت پیشنهادها ناموفق بود.
        </strong>
        <small>
          می‌توانید جستجو را مستقیم ارسال کنید.
        </small>
      </div>
    `;

    open();
  };

  const render = (items, titleText = "نتایج پیشنهادی") => {
    const box = createBox();

    setState(items.length ? "ready" : "empty");

    activeIndex = -1;

    box.innerHTML = `
      <div class="search-suggestions__head">
        <div class="search-suggestions__heading">
          <span class="search-suggestions__eyebrow">
            جستجوی سریع
          </span>

          <strong>
            ${escapeHTML(titleText)}
          </strong>
        </div>

        <span class="search-suggestions__count">
          ${items.length} مورد
        </span>
      </div>

      <div class="search-suggestions__list"></div>
    `;

    const list = box.querySelector(".search-suggestions__list");

    if (!items.length) {
      list.innerHTML = `
        <div class="search-empty">
          <strong>
            نتیجه‌ای پیدا نشد
          </strong>

          <small>
            نام محصول، برند یا مدل دیگری را امتحان کنید.
          </small>
        </div>
      `;
    } else {
      list.innerHTML = items
        .map(
          (item, index) => `
            <button
              id="search-option-${index}"
              class="search-suggestion"
              type="button"
              role="option"
              aria-selected="false"
              data-search-value="${escapeHTML(item.value)}">

              <span class="search-suggestion-icon">
                ${iconFor(item.type)}
              </span>

              <span class="search-suggestion-content">
                <strong>
                  ${escapeHTML(item.title)}
                </strong>

                <small>
                  ${escapeHTML(item.meta)}
                </small>
              </span>

              <span class="search-suggestion-type">
                ${escapeHTML(item.type)}
              </span>
            </button>
          `,
        )
        .join("");
    }

    open();
    updateActiveOption();
  };

  const refresh = async () => {
    const value = input.value.trim();

    const currentToken = ++requestToken;

    if (!value) {
      const recent = readRecentSearches().map((item) => ({
        type: "جستجوی اخیر",
        title: item,
        meta: "جستجوی قبلی شما",
        value: item,
      }));

      if (recent.length) {
        render(recent, "جستجوهای اخیر");
      } else {
        close();
      }

      return;
    }

    renderLoading();

    try {
      const remote = normalizeRemoteSuggestions(await request(value));

      if (currentToken !== requestToken) {
        return;
      }

      render(remote.slice(0, 6));
    } catch {
      if (currentToken !== requestToken) {
        return;
      }

      renderError();
    }
  };

  const scheduleRefresh = () => {
    window.clearTimeout(debounceTimer);

    debounceTimer = window.setTimeout(refresh, debounceDelay);
  };

  const selectItem = (item) => {
    if (!item) return;

    const value = item.dataset.searchValue || "";

    if (!value.trim()) return;

    input.value = value;
    saveRecentSearch(value);
    close();

    // Complete the search when a suggestion is selected.
    if (typeof form.requestSubmit === "function") {
      form.requestSubmit();
      return;
    }

    form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
  };

  createBox();

  input.setAttribute("autocomplete", "off");

  input.setAttribute("aria-autocomplete", "list");

  input.setAttribute("aria-controls", "search-suggestions");

  input.setAttribute("aria-expanded", "false");

  input.addEventListener("focus", () => {
    if (input.value.trim()) {
      scheduleRefresh();
      return;
    }

    refresh();
  });

  input.addEventListener("input", scheduleRefresh);

  input.addEventListener("keydown", (event) => {
    const options = getOptions();

    if (event.key === "Escape") {
      close();
      return;
    }

    if (event.key === "ArrowDown") {
      if (!options.length) return;

      event.preventDefault();

      activeIndex = (activeIndex + 1) % options.length;

      updateActiveOption();

      return;
    }

    if (event.key === "ArrowUp") {
      if (!options.length) return;

      event.preventDefault();

      activeIndex = activeIndex <= 0 ? options.length - 1 : activeIndex - 1;

      updateActiveOption();

      return;
    }

    if (event.key === "Enter" && activeIndex >= 0 && options[activeIndex]) {
      event.preventDefault();

      selectItem(options[activeIndex]);
    }
  });

  suggestionBox.addEventListener("click", (event) => {
    const button = event.target.closest(".search-suggestion");

    if (button) {
      selectItem(button);
    }
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const value = input.value.trim();

    if (!value) {
      input.focus();
      refresh();
      return;
    }

    saveRecentSearch(value);

    const targetUrl = new URL(
      form.getAttribute("action") || "/search",
      window.location.href,
    );

    targetUrl.searchParams.set("q", value);

    updateCommerceContext({
      search: {
        query: value,
        returnPath: targetUrl.toString(),
      },
    });

    close();

    window.location.href = targetUrl.toString();
  });

  document.addEventListener("click", (event) => {
    if (!form.contains(event.target)) {
      close();
    }
  });
};

export default search;
