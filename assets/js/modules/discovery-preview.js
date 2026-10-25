import Swiper from "../../../swiper/swiper-bundle.mjs";

const discoveryPreview = () => {
  const root = document.querySelector("[data-discovery-preview]");
  if (!root || root.dataset.previewInitialized === "true") return;
  root.dataset.previewInitialized = "true";

  const apiBase = root.dataset.apiBase || "/api/index.php";
  const filters = root.querySelector("[data-preview-filters]");
  const products = root.querySelector("[data-preview-products]");
  const wrapper = root.querySelector("[data-preview-products-wrapper]");
  const state = root.querySelector("[data-preview-state]");
  const count = root.querySelector("[data-discovery-preview-count]");
  const filterPrev = root.querySelector("[data-preview-filter-prev]");
  const filterNext = root.querySelector("[data-preview-filter-next]");
  const productPrev = root.querySelector("[data-preview-product-prev]");
  const productNext = root.querySelector("[data-preview-product-next]");
  const pagination = root.querySelector("[data-preview-pagination]");
  if (!filters || !products || !wrapper) return;

  let activeCategory = root.querySelector("[data-preview-category].is-active")?.dataset.previewCategory || "work";
  let requestController = null;
  let filterSwiper = null;
  let productSwiper = null;

  const number = (value) => Number(value || 0).toLocaleString("fa-IR");
  const price = (value) => {
    const numeric = Number(value);
    return Number.isFinite(numeric) && numeric > 0 ? `${numeric.toLocaleString("fa-IR")} تومان` : "قیمت نامشخص";
  };

  const setState = (kind, message, retry = false) => {
    if (!state) return;
    state.hidden = !message;
    state.dataset.state = kind;
    state.replaceChildren();
    if (!message) return;
    const text = document.createElement("p");
    text.textContent = message;
    state.appendChild(text);
    if (retry) {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "home-discovery__retry";
      button.textContent = "تلاش دوباره";
      button.addEventListener("click", () => loadProducts(activeCategory));
      state.appendChild(button);
    }
  };

  const createCard = (product) => {
    const slide = document.createElement("article");
    slide.className = "home-discovery__product swiper-slide";
    const media = document.createElement("div");
    media.className = "home-discovery__product-media";
    if (product.image) {
      const image = document.createElement("img");
      image.src = product.image;
      image.alt = product.name || "تصویر محصول";
      image.loading = "lazy";
      image.decoding = "async";
      image.onerror = () => {
        image.remove();
        const fallback = document.createElement("span");
        fallback.className = "home-discovery__image-fallback";
        fallback.textContent = product.brand || "NIVAD";
        media.appendChild(fallback);
      };
      media.appendChild(image);
    } else {
      const fallback = document.createElement("span");
      fallback.className = "home-discovery__image-fallback";
      fallback.textContent = product.brand || "NIVAD";
      media.appendChild(fallback);
    }

    const body = document.createElement("div");
    body.className = "home-discovery__product-body";
    const brand = document.createElement("span");
    brand.className = "home-discovery__product-brand";
    brand.textContent = product.brand || "NIVAD";
    const title = document.createElement("h5");
    title.textContent = product.name || "محصول";
    const description = document.createElement("p");
    description.textContent = product.short_description || product.description || "";
    const productPrice = document.createElement("strong");
    productPrice.textContent = price(product.sale_price ?? product.final_price ?? product.price);
    body.append(brand, title, description, productPrice);
    slide.append(media, body);

    const slug = product.slug || product.product_slug;
    if (slug) {
      const link = document.createElement("a");
      link.className = "home-discovery__product-link";
      link.href = `/products/${encodeURIComponent(slug)}`;
      link.setAttribute("aria-label", `مشاهده ${product.name || "محصول"}`);
      link.appendChild(slide);
      return link;
    }
    return slide;
  };

  const renderSkeletons = () => {
    wrapper.replaceChildren();
    for (let i = 0; i < 3; i += 1) {
      const card = document.createElement("div");
      card.className = "home-discovery__product home-discovery__skeleton swiper-slide";
      card.setAttribute("aria-hidden", "true");
      card.innerHTML = '<span class="home-discovery__skeleton-media"></span><span class="home-discovery__skeleton-line"></span><span class="home-discovery__skeleton-line home-discovery__skeleton-line--short"></span><span class="home-discovery__skeleton-price"></span>';
      wrapper.appendChild(card);
    }
    productSwiper?.update();
  };

  const updateNavigation = () => {
    if (!productSwiper) return;
    if (productPrev) productPrev.disabled = productSwiper.isBeginning;
    if (productNext) productNext.disabled = productSwiper.isEnd;
  };

  const loadProducts = async (category) => {
    activeCategory = category;
    requestController?.abort();
    requestController = new AbortController();
    const controller = requestController;
    setState("loading", "");
    if (count) count.textContent = "در حال بارگذاری…";
    products.classList.add("is-loading");
    renderSkeletons();

    try {
      const separator = apiBase.includes("?") ? "&" : "?";
      const endpoint = `${apiBase}${separator}path=${encodeURIComponent(`/discovery/${category}/products?limit=5&offset=0`)}&_=${Date.now()}`;
      const response = await fetch(endpoint, {
        credentials: "same-origin",
        headers: { Accept: "application/json" },
        signal: controller.signal,
      });
      if (!response.ok) throw new Error("در دریافت پیشنهادها مشکلی پیش آمد.");
      const result = await response.json();
      if (!result?.success) throw new Error(result?.message || "دریافت پیشنهادها ناموفق بود.");
      if (controller.signal.aborted) return;
      const items = Array.isArray(result?.data?.items) ? result.data.items : [];
      wrapper.replaceChildren();

      if (!items.length) {
        setState("empty", "برای این کاربرد فعلاً محصولی پیدا نشد. کاربرد دیگری را امتحان کن یا همه پیشنهادها را ببین.");
        if (count) count.textContent = "۰ محصول";
      } else {
        setState("success", "");
        items.forEach((item) => wrapper.appendChild(createCard(item)));
        if (count) count.textContent = `${number(items.length)} پیشنهاد`;
      }
      productSwiper?.update();
      productSwiper?.slideTo(0, 0);
      updateNavigation();
    } catch (error) {
      if (error.name === "AbortError") return;
      wrapper.replaceChildren();
      setState("error", error.message || "ارتباط با سرویس پیشنهادها برقرار نشد.", true);
      if (count) count.textContent = "بارگذاری ناموفق";
      productSwiper?.update();
      updateNavigation();
      console.error("Discovery preview request failed:", error);
    } finally {
      if (!controller.signal.aborted) products.classList.remove("is-loading");
    }
  };

  filterSwiper = new Swiper(filters, {
    slidesPerView: "auto",
    spaceBetween: 10,
    freeMode: true,
    grabCursor: true,
    watchOverflow: true,
    keyboard: { enabled: true, onlyInViewport: true },
    navigation: { nextEl: filterNext, prevEl: filterPrev },
  });

  productSwiper = new Swiper(products, {
    slidesPerView: 1,
    spaceBetween: 12,
    speed: 420,
    grabCursor: true,
    watchOverflow: true,
    keyboard: { enabled: true, onlyInViewport: true },
    navigation: { nextEl: productNext, prevEl: productPrev },
    pagination: pagination ? { el: pagination, clickable: true } : undefined,
    breakpoints: {
      601: { slidesPerView: 2, spaceBetween: 14 },
      1010: { slidesPerView: 3, spaceBetween: 16 },
    },
    on: {
      init: updateNavigation,
      slideChange: updateNavigation,
      resize: updateNavigation,
      update: updateNavigation,
      breakpoint: updateNavigation,
    },
  });

  root.querySelectorAll("[data-preview-category]").forEach((button) => {
    button.addEventListener("click", () => {
      const category = button.dataset.previewCategory;
      if (!category || category === activeCategory) return;
      root.querySelectorAll("[data-preview-category]").forEach((item) => {
        const active = item === button;
        item.classList.toggle("is-active", active);
        item.setAttribute("aria-pressed", active ? "true" : "false");
      });
      loadProducts(category);
    });
  });

  loadProducts(activeCategory);
};

export default discoveryPreview;
