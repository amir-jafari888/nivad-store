import Swiper from "../../../swiper/swiper-bundle.mjs";

const discovery = () => {
  const discoveryPage = document.querySelector("[data-discovery-page]");

  if (!discoveryPage) {
    return;
  }

  const optionsContainer = discoveryPage.querySelector(
    "[data-discovery-options]",
  );

  const resultsContainer = discoveryPage.querySelector(
    "[data-discovery-results]",
  );

  const apiBase = discoveryPage.dataset.apiBase || "/api/index.php";

  const errorContainer = discoveryPage.querySelector("[data-discovery-error]");

  const resultsWrapper = discoveryPage.querySelector(
    "[data-discovery-results-wrapper]",
  );

  const countElement = discoveryPage.querySelector("[data-discovery-count]");

  const loadMoreButton = discoveryPage.querySelector(
    "[data-discovery-load-more]",
  );

  const previousButton = discoveryPage.querySelector(
    ".discovery-navigation-prev",
  );

  const nextButton = discoveryPage.querySelector(".discovery-navigation-next");

  if (!resultsContainer || !resultsWrapper) {
    return;
  }

  let optionsSwiper = null;
  let resultsSwiper = null;

  let activeDiscovery = "charging";
  let currentOffset = 0;
  let hasMoreProducts = false;
  let isLoading = false;

  const api = async (path) => {
    const separator = path.includes("?") ? "&" : "?";

    const response = await fetch(
      `${apiBase}?path=${encodeURIComponent(path)}${separator}_=${Date.now()}`,
      {
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!response.ok) {
      throw new Error(
        `Discovery request failed with status ${response.status}.`,
      );
    }

    const result = await response.json();

    if (!result?.success) {
      throw new Error(result?.message || "Discovery request failed.");
    }

    return result.data;
  };

  const toPersianNumber = (value) =>
    String(value).replace(/\d/g, (digit) => "۰۱۲۳۴۵۶۷۸۹"[digit]);

  const formatPrice = (value) => {
    const numericValue = Number(value);

    if (!Number.isFinite(numericValue)) {
      return "قیمت نامشخص";
    }

    return `${numericValue.toLocaleString("fa-IR")} تومان`;
  };

  const updateCount = (count) => {
    if (!countElement) {
      return;
    }

    countElement.textContent = `${toPersianNumber(count)} محصول`;
  };

  const updateNavigation = () => {
    if (!resultsSwiper) {
      return;
    }

    if (previousButton) {
      previousButton.disabled = resultsSwiper.isBeginning;
    }

    if (nextButton) {
      nextButton.disabled = resultsSwiper.isEnd;
    }
  };

  const createProductElement = (product) => {
    const article = document.createElement("article");

    article.className = "page-discovery__product swiper-slide";
    article.dataset.productId = String(Number(product?.id || 0));

    const imageWrapper = document.createElement("div");

    imageWrapper.className = "page-discovery__product-media";

    if (product.image) {
      const image = document.createElement("img");

      image.src = product.image;
      image.alt = product.name || "";
      image.loading = "lazy";

      imageWrapper.appendChild(image);
    } else {
      const placeholder = document.createElement("span");

      placeholder.textContent = product.brand || "NIVAD";

      imageWrapper.appendChild(placeholder);
    }

    const information = document.createElement("div");

    information.className = "page-discovery__product-body";

    const brand = document.createElement("span");

    brand.textContent = product.brand || "NIVAD";

    const title = document.createElement("h3");

    title.textContent = product.name || "";

    const description = document.createElement("p");

    description.textContent =
      product.short_description || product.description || "";

    const price = document.createElement("strong");

    price.textContent = formatPrice(
      product.sale_price ?? product.final_price ?? product.price,
    );

    information.append(brand, title, description, price);

    article.append(imageWrapper, information);

    return article;
  };

  const renderProducts = (items) => {
    resultsWrapper.replaceChildren();

    if (!Array.isArray(items) || items.length === 0) {
      updateCount(0);

      const emptyState = document.createElement("div");

      emptyState.className = "page-discovery__empty";

      emptyState.textContent = "برای این نیاز محصولی پیدا نشد.";

      resultsWrapper.appendChild(emptyState);

      resultsSwiper?.update();
      updateNavigation();

      return;
    }

    items.forEach((product) => {
      resultsWrapper.appendChild(createProductElement(product));
    });

    updateCount(items.length);

    resultsSwiper?.update();
    resultsSwiper?.slideTo(0, 0);

    updateNavigation();
  };

  const initializeOptionsSwiper = () => {
    if (!optionsContainer || optionsSwiper) {
      return;
    }

    optionsSwiper = new Swiper(optionsContainer, {
      direction: "horizontal",
      slidesPerView: "auto",
      spaceBetween: 10,
      freeMode: true,
      grabCursor: true,
      watchOverflow: true,
      observer: true,
      observeParents: true,
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },
    });
  };

  const initializeResultsSwiper = () => {
    if (resultsSwiper) {
      return;
    }

    resultsSwiper = new Swiper(resultsContainer, {
      direction: "horizontal",

      slidesPerView: 1,

      spaceBetween: 12,

      speed: 420,

      grabCursor: true,

      watchOverflow: true,

      observer: true,

      observeParents: true,

      preventInteractionOnTransition: true,

      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },

      navigation: {
        nextEl: nextButton,
        prevEl: previousButton,
      },

      breakpoints: {
        601: {
          slidesPerView: 2,
          spaceBetween: 16,
        },

        1010: {
          slidesPerView: 3,
          spaceBetween: 20,
        },
      },

      on: {
        init: updateNavigation,
        slideChange: updateNavigation,
        resize: updateNavigation,
        update: updateNavigation,
        breakpoint: updateNavigation,
      },
    });
  };

  const setActiveOption = (selectedOption) => {
    const options = discoveryPage.querySelectorAll("[data-discovery-tags]");

    options.forEach((option) => {
      const isActive = option === selectedOption;

      option.classList.toggle("is-active", isActive);

      option.setAttribute("aria-selected", isActive ? "true" : "false");
    });

    activeDiscovery = selectedOption.dataset.discovery || "charging";
  };

  const loadDiscoveryProducts = async ({ reset = true } = {}) => {
    if (isLoading) {
      return;
    }

    isLoading = true;

    if (reset) {
      currentOffset = 0;
      hasMoreProducts = false;
      resultsContainer.classList.add("is-loading");
    }

    try {
      const data = await api(
        `/discovery/${encodeURIComponent(
          activeDiscovery,
        )}/products?limit=5&offset=${currentOffset}`,
      );

      const items = Array.isArray(data?.items) ? data.items : [];

      if (reset) {
        renderProducts(items);
      } else {
        items.forEach((product) => {
          resultsWrapper.appendChild(createProductElement(product));
        });

        updateCount(
          resultsWrapper.querySelectorAll(".page-discovery__product").length,
        );

        resultsSwiper?.update();
      }

      currentOffset += items.length;

      hasMoreProducts = Boolean(data?.has_more) || Boolean(data?.hasMore);

      if (loadMoreButton) {
        loadMoreButton.hidden = !hasMoreProducts;
      }

      if (errorContainer) {
        errorContainer.hidden = true;
        errorContainer.textContent = "";
      }

      updateNavigation();
    } catch (error) {
      if (error.name === "AbortError") {
        return;
      }

      resultsWrapper.replaceChildren();

      if (errorContainer) {
        errorContainer.hidden = false;
        errorContainer.replaceChildren();

        const message = document.createElement("p");
        message.textContent =
          error.message || "دریافت پیشنهادها ناموفق بود.";

        const retry = document.createElement("button");
        retry.type = "button";
        retry.className = "page-discovery__retry";
        retry.textContent = "تلاش دوباره";
        retry.addEventListener("click", () => loadDiscoveryProducts({ reset: true }));

        errorContainer.append(message, retry);
      }

      updateCount(0);
      resultsSwiper?.update();
      updateNavigation();
      console.error("Discovery products failed:", error);
    } finally {
      isLoading = false;

      resultsContainer.classList.remove("is-loading");
    }
  };

  const handleOptionClick = (event) => {
    const selectedOption = event.currentTarget;

    if (
      !selectedOption ||
      selectedOption.dataset.discovery === activeDiscovery
    ) {
      return;
    }

    setActiveOption(selectedOption);

    loadDiscoveryProducts({
      reset: true,
    });
  };

  const handleLoadMore = () => {
    if (!hasMoreProducts || isLoading) {
      return;
    }

    loadDiscoveryProducts({
      reset: false,
    });
  };

  const bindEvents = () => {
    const options = discoveryPage.querySelectorAll("[data-discovery-tags]");

    options.forEach((option) => {
      option.addEventListener("click", handleOptionClick);
    });

    loadMoreButton?.addEventListener("click", handleLoadMore);
  };

  initializeOptionsSwiper();
  initializeResultsSwiper();
  bindEvents();

  const initialOption = discoveryPage.querySelector(
    "[data-discovery-tags].is-active",
  );

  if (initialOption) {
    activeDiscovery = initialOption.dataset.discovery || "charging";
  }

  loadDiscoveryProducts();
};

export default discovery;
