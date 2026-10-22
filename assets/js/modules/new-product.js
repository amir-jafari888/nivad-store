const newProduct = () => {
  const section = document.querySelector(".new-product");

  if (!section) return;

  const list = section.querySelector(".new-product-list");

  const cards = [...section.querySelectorAll(".new-product-card")];

  if (!list || !cards.length) return;

  const normalize = (value) => {
    return String(value ?? "")
      .trim()
      .toLowerCase()
      .replace(/ي/g, "ی")
      .replace(/ك/g, "ک");
  };

  const getCardData = (card) => {
    return {
      element: card,
      id: String(card.dataset.productId || card.dataset.id || "").trim(),

      name: String(
        card.dataset.productName ||
          card.querySelector(".new-product-name")?.textContent ||
          "",
      ).trim(),

      brand: String(
        card.dataset.productBrand ||
          card.querySelector(".new-product-brand")?.textContent ||
          "",
      ).trim(),

      category: normalize(
        card.dataset.category || card.dataset.productCategory || "",
      ),
    };
  };

  const productData = cards.map(getCardData);

  const getCurrentIndex = () => {
    const scrollLeft = Math.abs(list.scrollLeft);

    let closestIndex = 0;
    let closestDistance = Infinity;

    productData.forEach((item, index) => {
      const distance = Math.abs(item.element.offsetLeft - scrollLeft);

      if (distance < closestDistance) {
        closestDistance = distance;
        closestIndex = index;
      }
    });

    return closestIndex;
  };

  const scrollToCard = (index) => {
    if (!productData[index]) return;

    const card = productData[index].element;

    list.scrollTo({
      left: card.offsetLeft,
      behavior: "smooth",
    });
  };

  const getDirection = () => {
    return getComputedStyle(list).direction === "rtl" ? "rtl" : "ltr";
  };

  const moveNext = () => {
    const current = getCurrentIndex();

    const next = Math.min(current + 1, productData.length - 1);

    scrollToCard(next);
  };

  const movePrevious = () => {
    const current = getCurrentIndex();

    const previous = Math.max(current - 1, 0);

    scrollToCard(previous);
  };

  const nextButton = section.querySelector("[data-new-product-next]");

  const previousButton = section.querySelector("[data-new-product-prev]");

  nextButton?.addEventListener("click", (event) => {
    event.preventDefault();

    if (getDirection() === "rtl") {
      movePrevious();
    } else {
      moveNext();
    }
  });

  previousButton?.addEventListener("click", (event) => {
    event.preventDefault();

    if (getDirection() === "rtl") {
      moveNext();
    } else {
      movePrevious();
    }
  });

  cards.forEach((card) => {
    card.addEventListener("keydown", (event) => {
      if (event.key === "ArrowRight") {
        event.preventDefault();

        if (getDirection() === "rtl") {
          movePrevious();
        } else {
          moveNext();
        }
      }

      if (event.key === "ArrowLeft") {
        event.preventDefault();

        if (getDirection() === "rtl") {
          moveNext();
        } else {
          movePrevious();
        }
      }
    });
  });

  let scrollTimer = null;

  list.addEventListener(
    "scroll",
    () => {
      window.clearTimeout(scrollTimer);

      scrollTimer = window.setTimeout(() => {
        document.dispatchEvent(
          new CustomEvent("new-products:scroll", {
            detail: {
              index: getCurrentIndex(),
            },
          }),
        );
      }, 100);
    },
    {
      passive: true,
    },
  );

  window.digitalStoreNewProducts = {
    next: moveNext,
    previous: movePrevious,

    goTo: (index) => {
      const safeIndex = Math.max(
        0,
        Math.min(Number(index) || 0, productData.length - 1),
      );

      scrollToCard(safeIndex);
    },

    getProducts: () => {
      return productData.map((item) => item.element);
    },

    getCurrentIndex,
  };
};

export default newProduct;
