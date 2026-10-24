const customerShop = () => {
  const root = document.querySelector("[data-customer-page]");

  if (!root) return;

  const page = root.dataset.customerPage;
  const apiBase =
    root.dataset.apiBase || `${window.NIVAD_BASE_URL || ""}/api/index.php`;
  const loginUrl = root.dataset.loginUrl || "../auth/login.php";

  const state = root.querySelector("[data-customer-state]");

  const list = root.querySelector("[data-customer-list]");

  const summary = root.querySelector("[data-customer-summary]");

  const action = root.querySelector("[data-customer-action]");

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
      currency === "IRR" ? "تومان" : currency
    }`;
  };

  const request = async (path, options = {}) => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", path);

    const config = {
      credentials: "same-origin",

      headers: {
        Accept: "application/json",

        ...(options.body instanceof URLSearchParams
          ? {
              "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
            }
          : {}),

        ...(options.headers || {}),
      },

      ...options,
    };

    let response;

    try {
      response = await fetch(url.toString(), config);
    } catch {
      throw new Error("ارتباط با سرور برقرار نشد. اتصال را بررسی کنید.");
    }

    const payload = await response.json().catch(() => null);

    if (!payload || typeof payload !== "object") {
      throw new Error("پاسخ سرور قابل پردازش نیست.");
    }

    if (!response.ok || payload?.success === false) {
      const error = new Error(payload?.message || "عملیات ناموفق بود.");

      error.status = response.status;

      error.payload = payload;

      throw error;
    }

    return payload?.data ?? {};
  };

  const csrf = async () => {
    const data = await request("/auth/csrf");
    const token = data?.token;

    if (typeof token !== "string" || token.length === 0) {
      throw new Error("دریافت توکن امنیتی انجام نشد. صفحه را تازه‌سازی کنید.");
    }

    return token;
  };

  const formBody = async (values) => {
    const body = new URLSearchParams();

    body.set("csrf_token", await csrf());

    Object.entries(values).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        body.set(key, String(value));
      }
    });

    return body;
  };

  const authStatus = async () => request("/auth/status");

  const showLogin = () => {
    if (state) {
      state.innerHTML = "برای ادامه باید وارد حساب شوید.";
    }

    if (action) {
      action.innerHTML = `
        <a
          class="customer-shop__button"
          href="${escapeHTML(loginUrl)}">
          ورود / ثبت‌نام
        </a>
      `;
    }

    if (list) {
      list.innerHTML = "";
    }
  };

  const setState = (text) => {
    if (state) {
      state.textContent = text;
    }
  };

  const cartItemMarkup = (item) => {
    const product = item.product || {};

    const variant = item.variant || {};

    const price = item.unit_price ?? variant.price;

    return `
      <article class="customer-shop__item">

        <div>
          <strong>
            ${escapeHTML(product.name || variant.name || "محصول")}
          </strong>

          <small>
            ${escapeHTML(variant.sku || "")}
          </small>
        </div>

        <div class="customer-shop__item-price">
          ${formatPrice(price, variant.currency)}
        </div>

        <div class="customer-shop__quantity">

          <button
            type="button"
            data-cart-quantity="${item.id}"
            data-delta="1">
            +
          </button>

          <span>
            ${Number(item.quantity).toLocaleString("fa-IR")}
          </span>

          <button
            type="button"
            data-cart-quantity="${item.id}"
            data-delta="-1">
            −
          </button>

        </div>

        <button
          type="button"
          class="customer-shop__remove"
          data-cart-remove="${item.id}">
          حذف
        </button>

      </article>
    `;
  };

  const loadCart = async () => request("/cart");

  const syncLocalCart = async () => {
    const local = window.digitalStoreCart?.getCart?.() || [];

    const current = await loadCart();

    let cart = current.cart;

    if (!local.length) {
      return;
    }

    if (!cart) {
      const body = await formBody({
        currency: "IRR",
        status: "active",
      });

      const created = await request("/cart", {
        method: "POST",
        body,
      });

      cart = created.cart;
    }

    if (!cart?.id) {
      return;
    }

    const existing = new Set(
      (current.items || [])
        .map((item) =>
          Number(item.variant_id ?? item.variantId ?? item.variant?.id ?? 0),
        )
        .filter((id) => id > 0),
    );

    for (const localItem of local) {
      const localVariantId = Number(localItem.variantId || 0);

      if (localVariantId <= 0) {
        continue;
      }

      if (existing.has(localVariantId)) {
        continue;
      }

      let productData = null;

      try {
        productData = await request(
          `/storefront/products/detail?slug=${encodeURIComponent(
            localItem.id,
          )}`,
        );
      } catch {
        try {
          const catalog = await request("/storefront/products");

          const product = (catalog.items || []).find(
            (item) =>
              String(item.id) === String(localItem.id) ||
              String(item.slug) === String(localItem.id),
          );

          if (product?.slug) {
            productData = await request(
              `/storefront/products/detail?slug=${encodeURIComponent(
                product.slug,
              )}`,
            );
          }
        } catch {
          productData = null;
        }
      }

      if (!productData) {
        continue;
      }

      const variants = Array.isArray(productData.variants)
        ? productData.variants
        : [];

      const variant =
        variants.find((item) => Number(item.id) === localVariantId) ||
        variants.find((item) => item.is_default) ||
        variants[0];

      if (!variant) {
        continue;
      }

      try {
        const body = await formBody({
          cart_id: cart.id,
          variant_id: variant.id,
          quantity: localItem.quantity,
          unit_price: variant.price,
        });

        await request("/cart-items", {
          method: "POST",
          body,
        });
      } catch (error) {
        if (error.status !== 409) {
          throw error;
        }
      }
    }
  };

  const renderCart = (data) => {
    const items = Array.isArray(data.items) ? data.items : [];

    const total = items.reduce(
      (sum, item) =>
        sum +
        Number(item.unit_price || item.variant?.price || 0) *
          Number(item.quantity || 0),
      0,
    );

    if (summary) {
      summary.textContent = `${items.length.toLocaleString(
        "fa-IR",
      )} قلم — ${formatPrice(total, data.cart?.currency || "IRR")}`;
    }

    if (!list) {
      return;
    }

    list.innerHTML = items.length
      ? items.map(cartItemMarkup).join("")
      : `
        <div
          class="customer-shop__empty"
          role="status">
          سبد خرید شما خالی است.
        </div>
      `;

    if (action) {
      action.innerHTML = items.length
        ? `
            <a
              class="customer-shop__button"
              href="../shop/checkout.php">
              ادامه فرایند خرید
            </a>
          `
        : "";
    }
  };

  const handleCartAction = async (event) => {
    const remove = event.target.closest("[data-cart-remove]");

    const quantity = event.target.closest("[data-cart-quantity]");

    if (!remove && !quantity) {
      return;
    }

    event.preventDefault();

    try {
      if (remove) {
        const body = await formBody({});

        await request(
          `/cart-items/${encodeURIComponent(remove.dataset.cartRemove)}`,
          {
            method: "DELETE",
            body,
          },
        );
      } else {
        const itemId = quantity.dataset.cartQuantity;

        const current = await loadCart();

        const item = (current.items || []).find(
          (entry) => String(entry.id) === String(itemId),
        );

        if (!item) {
          return;
        }

        const next = Number(item.quantity) + Number(quantity.dataset.delta);

        if (next <= 0) {
          const body = await formBody({});

          await request(`/cart-items/${encodeURIComponent(itemId)}`, {
            method: "DELETE",
            body,
          });
        } else {
          const body = await formBody({
            quantity: next,
            unit_price: item.unit_price,
          });

          await request(`/cart-items/${encodeURIComponent(itemId)}`, {
            method: "PUT",
            body,
          });
        }
      }

      await syncLocalCart();

      const refreshed = await loadCart();

      renderCart(refreshed);

      document.dispatchEvent(
        new CustomEvent("cart:updated", {
          detail: {
            source: "customer-shop",
            server: refreshed,
          },
        }),
      );
    } catch (error) {
      setState(error.message || "به‌روزرسانی سبد خرید ناموفق بود.");
    }
  };

  const syncLocalWishlist = async () => {
    if (window.digitalStoreWishlist?.sync) {
      await window.digitalStoreWishlist.sync();
    }
  };

  const renderWishlist = (data) => {
    const wishlists = Array.isArray(data?.wishlists) ? data.wishlists : [];

    const defaultList =
      wishlists.find((item) => item.isDefault) || wishlists[0] || null;

    const items = defaultList
      ? Array.isArray(data?.items?.[String(defaultList.id)])
        ? data.items[String(defaultList.id)]
        : []
      : [];

    if (summary) {
      summary.textContent = `${items.length.toLocaleString("fa-IR")} مورد`;
    }

    if (!list) {
      return;
    }

    if (!items.length) {
      list.innerHTML = `
      <div
        class="customer-shop__empty"
        role="status">
        هنوز محصولی به علاقه‌مندی‌ها
        اضافه نشده است.
      </div>
    `;

      return;
    }

    list.innerHTML = items
      .map((item) => {
        const product = item.product || {};

        const variant = item.variant || {};

        const productId = product.slug || product.id || "";

        return `
        <article
          class="customer-shop__item"
          data-wishlist-item-id="${escapeHTML(item.id)}"
          data-wishlist-variant-id="${escapeHTML(item.variant_id)}">

          <div
            class="customer-shop__item-content">

            <strong>
              ${escapeHTML(product.name || variant.name || "محصول")}
            </strong>

            <small>
              ${escapeHTML(variant.sku || "")}
            </small>

            ${
              product.short_description
                ? `
                  <p>
                    ${escapeHTML(product.short_description)}
                  </p>
                `
                : ""
            }

          </div>

          <div
            class="customer-shop__item-price">
            ${formatPrice(variant.price, variant.currency)}
          </div>

          <div
            class="customer-shop__item-actions">

            ${
              productId
                ? `
                  <a
                    class="customer-shop__link"
                    href="${escapeHTML(
                      `${window.NIVAD_BASE_URL || ""}/pages/store/products/show.php?slug=${encodeURIComponent(
                        productId,
                      )}`,
                    )}">
                    مشاهده محصول
                  </a>
                `
                : ""
            }

            <button
              type="button"
              class="customer-shop__remove"
              data-wishlist-remove="${escapeHTML(item.id)}"
              aria-label="حذف محصول از علاقه‌مندی‌ها">
              حذف
            </button>

          </div>

        </article>
      `;
      })
      .join("");
  };

  const handleWishlistAction = async (event) => {
    const button = event.target.closest("[data-wishlist-remove]");

    if (!button) {
      return;
    }

    event.preventDefault();

    const itemId = Number(button.dataset.wishlistRemove);

    if (!Number.isInteger(itemId) || itemId <= 0) {
      return;
    }

    button.disabled = true;

    try {
      const body = await formBody({});

      await request(`/wishlist-items/${itemId}`, {
        method: "DELETE",
        body,
      });

      /*
       * Update Local state using the real
       * Variant identity when available.
       */
      const api = window.digitalStoreWishlist;

      if (api?.getWishlist && api?.getVariants) {
        const items = api.getWishlist();

        const variants = api.getVariants();

        const target = list?.querySelector(
          `[data-wishlist-item-id="${itemId}"]`,
        );

        const variantId = Number(target?.dataset.wishlistVariantId || 0);

        if (variantId > 0) {
          for (const id of items) {
            if (Number(variants[id]) === variantId) {
              delete variants[id];

              const next = items.filter((item) => item !== id);

              api.saveLocal(next, variants, false);

              break;
            }
          }
        }
      }

      renderWishlist(await request("/wishlists"));
    } catch (error) {
      setState(error.message || "حذف علاقه‌مندی ناموفق بود.");

      button.disabled = false;
    }
  };

  const renderCheckout = (data) => {
    const items = Array.isArray(data.items) ? data.items : [];

    const address = data.address || {};

    if (summary) {
      summary.textContent = formatPrice(data.subtotal, data.currency);
    }

    if (!list) return;

    list.innerHTML = `
        <section
          class="customer-shop__panel">

          <h2>
            آدرس ارسال
          </h2>

          <p>
            ${escapeHTML(address.recipient_name || "")}
          </p>

          <p>
            ${escapeHTML(address.province || "")}
           ،
            ${escapeHTML(address.city || "")}
          </p>

          <p>
            ${escapeHTML(address.address || "")}
          </p>

          <small>
            کد پستی:
            ${escapeHTML(address.postal_code || "")}
          </small>

        </section>

        <section
          class="customer-shop__panel">

          <h2>
            اقلام سفارش
          </h2>

          ${items
            .map(
              (item) => `
                <div
                  class="customer-shop__checkout-item">

                  <span>
                    Variant #${item.variantId ?? item.variant_id}
                  </span>

                  <span>
                    ${Number(item.quantity || 0).toLocaleString("fa-IR")}
                  </span>

                </div>
              `,
            )
            .join("")}

        </section>

        <button
          type="button"
          class="customer-shop__button"
          data-order-submit
          data-address-id="${address.id || ""}">

          ثبت سفارش

        </button>
      `;
  };

  const submitOrder = async (event) => {
    const button = event.target.closest("[data-order-submit]");

    if (!button) return;

    button.disabled = true;

    setState("در حال ثبت سفارش...");

    try {
      const body = await formBody({
        addressId: button.dataset.addressId || "",
      });

      const data = await request("/orders", {
        method: "POST",
        body,
      });

      const order = data?.id ? data : data?.order;

      window.location.href = `../account/orders/show.php?id=${encodeURIComponent(
        order?.id || "",
      )}`;
    } catch (error) {
      button.disabled = false;

      setState(error.message || "ثبت سفارش ناموفق بود.");
    }
  };

  const renderOrders = (orders) => {
    if (!list) return;

    list.innerHTML = orders.length
      ? orders
          .map(
            (order) => `
                  <article
                    class="customer-shop__item">

                    <div>
                      <strong>
                        سفارش #${escapeHTML(order.id)}
                      </strong>

                      <small>
                        ${escapeHTML(order.status || "")}
                      </small>
                    </div>

                    <strong>
                      ${formatPrice(
                        order.total || order.subtotal,
                        order.currency,
                      )}
                    </strong>

                    <a
                      class="customer-shop__link"
                      href="show.php?id=${encodeURIComponent(order.id)}">
                      مشاهده
                    </a>

                  </article>
                `,
          )
          .join("")
      : `
              <div
                class="customer-shop__empty">
                هنوز سفارشی ثبت نشده است.
              </div>
            `;
  };

  const renderOrder = (data) => {
    const order = data.order || {};

    const items = data.items || [];

    if (summary) {
      summary.textContent = `سفارش #${order.id || ""}`;
    }

    if (!list) return;

    list.innerHTML = `
        <section
          class="customer-shop__panel">

          <h2>
            وضعیت سفارش
          </h2>

          <strong>
            ${escapeHTML(order.status || "")}
          </strong>

        </section>

        <section
          class="customer-shop__panel">

          <h2>
            اقلام
          </h2>

          ${items
            .map(
              (item) => `
                <div
                  class="customer-shop__checkout-item">

                  <span>
                    Variant #${item.variantId ?? item.variant_id}
                  </span>

                  <span>
                    ${Number(item.quantity || 0).toLocaleString("fa-IR")}
                  </span>

                </div>
              `,
            )
            .join("")}

        </section>
      `;
  };

  const run = async () => {
    setState("در حال دریافت اطلاعات...");

    try {
      const status = await authStatus();

      if (!status.authenticated) {
        showLogin();
        return;
      }

      if (page === "cart") {
        await syncLocalCart();

        renderCart(await loadCart());
      } else if (page === "wishlist") {
        await syncLocalWishlist();

        renderWishlist(await request("/wishlists"));
      } else if (page === "checkout") {
        renderCheckout(await request("/checkout"));
      } else if (page === "orders") {
        const data = await request("/orders");

        renderOrders(Array.isArray(data.orders) ? data.orders : []);
      } else if (page === "order") {
        const id = new URLSearchParams(window.location.search).get("id");

        if (!id) {
          throw new Error("شناسه سفارش ارسال نشده است.");
        }

        renderOrder(await request(`/orders/${encodeURIComponent(id)}`));
      }

      setState("");
    } catch (error) {
      setState(error.message || "دریافت اطلاعات ناموفق بود.");
    }
  };

  list?.addEventListener("click", handleCartAction);

  list?.addEventListener("click", handleWishlistAction);

  list?.addEventListener("click", submitOrder);

  run();
};

export default customerShop;
