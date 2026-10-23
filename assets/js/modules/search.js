import { updateCommerceContext } from "./commerce-context.js";

const search = () => {
  const form = document.querySelector("[data-search-form]");
  const input = document.querySelector("[data-search-input]");

  if (!form || !input) return;

  const baseUrl = window.NIVAD_BASE_URL || "";

  const recentSearchKey = "nivad-recent-searches";
  const maxRecentSearches = 6;

  const normalize = (value) =>
    String(value ?? "")
      .trim()
      .toLowerCase()
      .replace(/ي/g, "ی")
      .replace(/ك/g, "ک");

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  let requestToken = 0;

  let suggestionState = "idle";

  const setSuggestionState = (state) => {
    suggestionState = state;

    const box = createBox();

    box.dataset.searchState = state;
  };

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
      // Local storage can be unavailable; search still works.
    }
  };

  const request = async (query) => {
    const url = new URL(`${baseUrl}/api/index.php`, window.location.origin);

    url.searchParams.set("path", "/search/suggestions");

    url.searchParams.set("q", query);

    const response = await fetch(url.toString(), {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "Search suggestion request failed.");
    }

    if (!payload || typeof payload !== "object" || Array.isArray(payload)) {
      throw new Error("Search suggestion response is invalid.");
    }

    return payload?.data ?? [];
  };

  let suggestionBox = form.querySelector(".search-suggestions");

  let activeIndex = -1;

  const createBox = () => {
    if (suggestionBox) {
      return suggestionBox;
    }

    suggestionBox = document.createElement("div");

    suggestionBox.className = "search-suggestions";

    suggestionBox.id = "search-suggestions";

    suggestionBox.id = "search-suggestions";

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

  const normalizeRemoteSuggestions = (items) => {
    if (!Array.isArray(items)) {
      return [];
    }

    return items
      .map((item) => ({
        type: item.type || item.kind || "محصول",

        title: item.title || item.name || item.label || "",

        meta:
          item.meta ||
          item.description ||
          item.brand ||
          item.metadata?.brand?.name ||
          "",

        value: item.value || item.name || item.title || "",
      }))
      .filter((item) => item.value !== "");
  };

  const renderLoading = () => {
    setSuggestionState("loading");

    const box = createBox();

    box.innerHTML = `
    <div class="search-empty" role="status" aria-live="polite" aria-atomic="true">
      <strong>در حال جستجو...</strong>
      <small>پیشنهادهای مرتبط در حال دریافت هستند.</small>
    </div>
  `;

    open();
  };

  const renderError = () => {
    setSuggestionState("error");

    const box = createBox();

    box.innerHTML = `
    <div class="search-empty" role="alert" aria-live="assertive" aria-atomic="true">
      <strong>دریافت پیشنهادها ناموفق بود.</strong>
      <small>می‌توانید جستجو را مستقیم ارسال کنید.</small>
    </div>
  `;

    open();
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

  const render = (items, titleText = "نتایج پیشنهادی") => {
    setSuggestionState(items.length ? "ready" : "empty");

    const box = createBox();

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

      <div
        class="search-suggestions__list"
        id="search-suggestions-list"
        role="listbox"
        aria-label="پیشنهادهای جستجو"></div>
      <div class="search-suggestions__feedback" role="status" aria-live="polite" aria-atomic="true"></div>
    `;

    const list = box.querySelector(".search-suggestions__list");
    const feedback = box.querySelector(".search-suggestions__feedback");

    if (!items.length) {
      feedback.innerHTML = `
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
            <div
              id="search-option-${index}"
              class="search-suggestion"
              role="option"
              aria-selected="false"
              tabindex="-1"
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
            </div>
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

  const selectItem = (item) => {
    if (!item) return;

    input.value = item.dataset.searchValue || "";

    saveRecentSearch(input.value);

    close();

    input.focus();
  };

  createBox();

  input.setAttribute("autocomplete", "off");

  input.setAttribute("role", "combobox");
  input.setAttribute("aria-autocomplete", "list");

  input.setAttribute("aria-controls", "search-suggestions");

  input.setAttribute("aria-expanded", "false");

  input.addEventListener("focus", refresh);

  input.addEventListener("input", refresh);

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

    updateCommerceContext({ search: { query: value, returnPath: `${baseUrl}/search?q=${encodeURIComponent(value)}` } });

    close();

    window.location.href = `${baseUrl}/search?q=${encodeURIComponent(value)}`;
  });

  document.addEventListener("click", (event) => {
    if (!form.contains(event.target)) {
      close();
    }
  });
};

export default search;
