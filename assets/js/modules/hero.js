const hero = async () => {
  const heroSlider = document.querySelector("[data-hero-slider]");
  const controls = document.querySelector("[data-hero-controls]");
  const pagination = document.querySelector("[data-hero-pagination]");
  const previousButton = document.querySelector("[data-hero-prev]");
  const nextButton = document.querySelector("[data-hero-next]");

  if (!heroSlider || !controls || !pagination || !previousButton || !nextButton) return;

  const { default: Swiper } = await import("../../../swiper/swiper-bundle.mjs");
  const slides = [...heroSlider.querySelectorAll(".swiper-slide")];
  if (!slides.length) return;

  pagination.innerHTML = "";

  const paginationItems = slides.map((slide, index) => {
    const topic = slide.querySelector(".hero-eyebrow")?.textContent?.trim() || `اسلاید ${index + 1}`;
    const paginationItem = document.createElement("button");
    paginationItem.type = "button";
    paginationItem.className = "hero-pagination__item";
    paginationItem.dataset.heroPaginationIndex = String(index);
    paginationItem.setAttribute("aria-label", `رفتن به اسلاید ${index + 1}: ${topic}`);
    paginationItem.innerHTML = `<span class="hero-pagination__number">${String(index + 1).padStart(2, "0")}</span><span class="hero-pagination__label">${topic}</span>`;
    pagination.appendChild(paginationItem);
    return paginationItem;
  });

  const getVisibleIndexes = (activeIndex) => {
    const lastIndex = slides.length - 1;
    if (slides.length <= 3) return Array.from({ length: slides.length }, (_, index) => index);
    if (activeIndex === 0) return [0, 1, 2];
    if (activeIndex === lastIndex) return [lastIndex - 2, lastIndex - 1, lastIndex];
    return [activeIndex - 1, activeIndex, activeIndex + 1];
  };

  const updatePagination = (activeIndex) => {
    const visibleIndexes = getVisibleIndexes(activeIndex);
    paginationItems.forEach((paginationItem, index) => {
      const isActive = index === activeIndex;
      paginationItem.hidden = !visibleIndexes.includes(index);
      paginationItem.classList.toggle("is-active", isActive);
      if (isActive) paginationItem.setAttribute("aria-current", "true");
      else paginationItem.removeAttribute("aria-current");
    });
  };

  const swiper = new Swiper(heroSlider, {
    slidesPerView: 1,
    spaceBetween: 0,
    speed: 650,
    grabCursor: true,
    keyboard: { enabled: true, onlyInViewport: true },
    a11y: {
      enabled: true,
      prevSlideMessage: "اسلاید قبلی",
      nextSlideMessage: "اسلاید بعدی",
      firstSlideMessage: "این اولین اسلاید است",
      lastSlideMessage: "این آخرین اسلاید است",
    },
    on: {
      init(instance) { updatePagination(instance.activeIndex); },
      slideChange(instance) { updatePagination(instance.activeIndex); },
    },
  });

  previousButton.addEventListener("click", () => swiper.slidePrev());
  nextButton.addEventListener("click", () => swiper.slideNext());
  paginationItems.forEach((paginationItem, index) => {
    paginationItem.addEventListener("click", () => swiper.slideTo(index));
  });
};

export default hero;
