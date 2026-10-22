const cartDrawer = () => {
  const drawer = document.querySelector("[data-cart-drawer]");

  const panel = drawer?.querySelector(".cart-drawer__panel");

  const itemsContainer = drawer?.querySelector("[data-cart-drawer-items]");

  const emptyState = drawer?.querySelector("[data-cart-drawer-empty]");

  const footer = drawer?.querySelector("[data-cart-drawer-footer]");

  const totalElement = drawer?.querySelector("[data-cart-drawer-total]");

  const closeButtons = drawer?.querySelectorAll("[data-cart-drawer-close]");

  const checkoutButton = drawer?.querySelector("[data-cart-drawer-checkout]");

  const cartToggle = document.querySelector("[data-cart-toggle]");

  if (
    !drawer ||
    !panel ||
    !itemsContainer ||
    !emptyState ||
    !footer ||
    !totalElement
  ) {
    return;
  }

  let lastFocusedElement = null;

  const getCartAPI = () => window.digitalStoreCart || null;

  const formatPrice = (price) => {
    const value = Number(price);

    if (!Number.isFinite(value) || value < 0) {
      return "۰ تومان";
    }

    return `${value.toLocaleString("fa-IR")} تومان`;
  };

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const renderItem = (item) => {
    const id = String(item.id ?? "").trim();

    const variantId = Number(item.variantId || 0);

    const name = String(item.name ?? "").trim();

    const brand = String(item.brand ?? "").trim();

    const spec = String(item.spec ?? "").trim();

    const quantity = Math.max(1, Math.floor(Number(item.quantity) || 1));

    const price = Math.max(0, Number(item.price) || 0);

    return `
        <article
          class="cart-drawer-item"
          data-cart-item="${escapeHTML(id)}"
          data-cart-item-variant="${escapeHTML(variantId)}">

          <div
            class="cart-drawer-item__image"
            aria-hidden="true">
            <span>
              ${escapeHTML(brand || "DIGITAL")}
            </span>
          </div>

          <div
            class="cart-drawer-item__content">

            <div
              class="cart-drawer-item__top">

              <div
                class="cart-drawer-item__info">

                <span
                  class="cart-drawer-item__brand">
                  ${escapeHTML(brand)}
                </span>

                <strong
                  class="cart-drawer-item__name">
                  ${escapeHTML(name)}
                </strong>

                ${
                  spec
                    ? `
                      <span
                        class="cart-drawer-item__spec">
                        ${escapeHTML(spec)}
                      </span>
                    `
                    : ""
                }
              </div>

              <button
                class="cart-drawer-item__remove"
                type="button"
                aria-label="حذف ${escapeHTML(name)}"
                data-cart-remove="${escapeHTML(id)}"
                data-cart-remove-variant="${escapeHTML(variantId)}">

                <svg
                  class="icon"
                  viewBox="0 0 24 24"
                  aria-hidden="true">

                  <path
                    d="M4 7h16M10 11v6M14 11v6M9 7l1-2h4l1 2M6 7l1 14h10l1-14"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round" />

                </svg>
              </button>
            </div>

            <div
              class="cart-drawer-item__bottom">

              <div
                class="cart-drawer-item__quantity"
                aria-label="تعداد ${escapeHTML(name)}">

                <button
                  type="button"
                  aria-label="افزایش تعداد ${escapeHTML(name)}"
                  data-cart-increase="${escapeHTML(id)}"
                  data-cart-increase-variant="${escapeHTML(variantId)}">
                  +
                </button>

                <span>
                  ${quantity.toLocaleString("fa-IR")}
                </span>

                <button
                  type="button"
                  aria-label="کاهش تعداد ${escapeHTML(name)}"
                  data-cart-decrease="${escapeHTML(id)}"
                  data-cart-decrease-variant="${escapeHTML(variantId)}">
                  −
                </button>

              </div>

              <strong
                class="cart-drawer-item__price">
                ${formatPrice(price * quantity)}
              </strong>

            </div>
          </div>
        </article>
      `;
  };

  const render = (items = null, total = null) => {
    const api = getCartAPI();

    if (!api) {
      return;
    }

    const cartItems = Array.isArray(items) ? items : api.getCart();

    const cartTotal =
      total !== null ? Number(total) : api.getCartTotal(cartItems);

    if (!cartItems.length) {
      itemsContainer.innerHTML = "";

      itemsContainer.hidden = true;

      emptyState.hidden = false;

      footer.hidden = true;

      totalElement.textContent = "۰ تومان";

      return;
    }

    itemsContainer.hidden = false;

    emptyState.hidden = true;

    footer.hidden = false;

    itemsContainer.innerHTML = cartItems.map(renderItem).join("");

    totalElement.textContent = formatPrice(cartTotal);
  };

  const getFocusableElements = () =>
    [
      ...panel.querySelectorAll(
        'button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])',
      ),
    ].filter((element) => element.offsetParent !== null);

  const open = () => {
    if (drawer.classList.contains("is-open")) {
      return;
    }

    lastFocusedElement = document.activeElement;

    render();

    drawer.classList.add("is-open");

    drawer.setAttribute("aria-hidden", "false");

    document.body.classList.add("is-cart-drawer-open");

    requestAnimationFrame(() => {
      panel.classList.add("is-visible");

      const closeButton = drawer.querySelector(".cart-drawer__close");

      closeButton?.focus();
    });
  };

  const close = () => {
    if (!drawer.classList.contains("is-open")) {
      return;
    }

    drawer.classList.remove("is-open");

    drawer.setAttribute("aria-hidden", "true");

    panel.classList.remove("is-visible");

    document.body.classList.remove("is-cart-drawer-open");

    if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
      lastFocusedElement.focus();
    }

    lastFocusedElement = null;
  };

  const toggle = () => {
    drawer.classList.contains("is-open") ? close() : open();
  };

  cartToggle?.addEventListener("click", (event) => {
    event.preventDefault();
    toggle();
  });

  closeButtons?.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      close();
    });
  });

  itemsContainer.addEventListener("click", (event) => {
    const api = getCartAPI();

    if (!api) {
      return;
    }

    const increase = event.target.closest("[data-cart-increase]");

    const decrease = event.target.closest("[data-cart-decrease]");

    const remove = event.target.closest("[data-cart-remove]");

    if (increase) {
      event.preventDefault();

      api.increase(
        increase.dataset.cartIncrease,
        1,
        Number(increase.dataset.cartIncreaseVariant || 0),
      );

      return;
    }

    if (decrease) {
      event.preventDefault();

      api.decrease(
        decrease.dataset.cartDecrease,
        1,
        Number(decrease.dataset.cartDecreaseVariant || 0),
      );

      return;
    }

    if (remove) {
      event.preventDefault();

      api.remove(
        remove.dataset.cartRemove,
        Number(remove.dataset.cartRemoveVariant || 0),
      );
    }
  });

  checkoutButton?.addEventListener("click", (event) => {
    event.preventDefault();

    const api = getCartAPI();

    if (!api) {
      return;
    }

    if (!api.getCart().length) {
      return;
    }

    const base = window.NIVAD_BASE_URL || "";

    window.location.href = `${base}/pages/shop/cart.php`;
  });

  document.addEventListener("cart:updated", (event) => {
    if (!drawer.classList.contains("is-open")) {
      return;
    }

    const detail = event.detail || {};

    render(detail.items, detail.total);
  });

  document.addEventListener("keydown", (event) => {
    if (!drawer.classList.contains("is-open")) {
      return;
    }

    if (event.key === "Escape") {
      event.preventDefault();
      close();
      return;
    }

    if (event.key !== "Tab") {
      return;
    }

    const focusable = getFocusableElements();

    if (!focusable.length) {
      return;
    }

    const first = focusable[0];

    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
      return;
    }

    if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  drawer.addEventListener("click", (event) => {
    if (event.target.classList.contains("cart-drawer__overlay")) {
      close();
    }
  });

  render();
};

export default cartDrawer;
