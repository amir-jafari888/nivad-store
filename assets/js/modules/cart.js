const cart = () => {
  const storageKey = "digital-store-cart";
  const baseUrl = window.NIVAD_BASE_URL || "";

  const normalizeItem = (item) => {
    if (!item || typeof item !== "object") {
      return null;
    }

    const id = String(item.id ?? "").trim();
    const variantId = Number(item.variantId ?? item.variant_id ?? 0);

    const name = String(
      item.name ?? item.product?.name ?? item.variant?.name ?? "",
    ).trim();

    const brand = String(item.brand ?? item.product?.brand ?? "").trim();

    const spec = String(
      item.spec ?? item.variant?.name ?? item.product?.short_description ?? "",
    ).trim();

    const price = Number(item.price ?? item.unit_price ?? item.variant?.price);

    const quantity = Math.floor(Number(item.quantity));

    if (
      !id ||
      !name ||
      !Number.isFinite(price) ||
      price < 0 ||
      !Number.isFinite(quantity) ||
      quantity < 1
    ) {
      return null;
    }

    return {
      id,
      variantId: variantId > 0 ? variantId : null,
      name,
      brand,
      price,
      spec,
      quantity,
    };
  };

  const itemIdentity = (item) => {
    const variantId = Number(item?.variantId ?? item?.variant_id ?? 0);

    if (variantId > 0) {
      return `variant:${variantId}`;
    }

    return `product:${String(item?.id ?? "").trim()}`;
  };

  const normalizeCart = (items) => {
    if (!Array.isArray(items)) {
      return [];
    }

    const normalized = [];

    items.forEach((item) => {
      const valid = normalizeItem(item);

      if (!valid) {
        return;
      }

      const existing = normalized.find(
        (cartItem) => itemIdentity(cartItem) === itemIdentity(valid),
      );

      if (existing) {
        existing.quantity += valid.quantity;
        return;
      }

      normalized.push(valid);
    });

    return normalized;
  };

  const getCart = () => {
    try {
      const stored = localStorage.getItem(storageKey);

      if (!stored) {
        return [];
      }

      return normalizeCart(JSON.parse(stored));
    } catch {
      return [];
    }
  };

  const getCartCount = (items = getCart()) =>
    normalizeCart(items).reduce((total, item) => total + item.quantity, 0);

  const getCartTotal = (items = getCart()) =>
    normalizeCart(items).reduce(
      (total, item) => total + item.price * item.quantity,
      0,
    );

  const updateCount = (items = getCart()) => {
    const count = getCartCount(items);

    document.querySelectorAll(".cart-count").forEach((element) => {
      element.textContent = count.toLocaleString("fa-IR");

      element.setAttribute("aria-label", `${count} کالا`);
    });

    document.querySelectorAll("[data-cart-toggle]").forEach((button) => {
      button.classList.toggle("has-items", count > 0);

      button.setAttribute(
        "aria-label",
        count > 0 ? `سبد خرید، ${count} کالا` : "سبد خرید خالی است",
      );
    });
  };

  const saveCart = (items) => {
    const normalized = normalizeCart(items);

    try {
      localStorage.setItem(storageKey, JSON.stringify(normalized));
    } catch {
      return false;
    }

    updateCount(normalized);

    document.dispatchEvent(
      new CustomEvent("cart:updated", {
        detail: {
          items: normalized,
          count: getCartCount(normalized),
          total: getCartTotal(normalized),
        },
      }),
    );

    return true;
  };

  const apiRequest = async (path, options = {}) => {
    const method = options.method || "GET";

    const headers = {
      Accept: "application/json",
      ...(options.headers || {}),
    };

    if (method !== "GET" && method !== "HEAD") {
      headers["Content-Type"] = "application/json";

      const csrfResponse = await fetch(
        `${baseUrl}/api/index.php?path=${encodeURIComponent("/auth/csrf")}`,
        {
          credentials: "same-origin",
        },
      );

      const csrf = await csrfResponse.json().catch(() => ({}));

      const token = csrf?.data?.token || "";

      if (token) {
        headers["X-CSRF-TOKEN"] = token;
      }
    }

    const response = await fetch(
      `${baseUrl}/api/index.php?path=${encodeURIComponent(path)}`,
      {
        ...options,
        method,
        headers,
        credentials: "same-origin",
      },
    );

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      const error = new Error(
        payload?.message || "عملیات سبد خرید ناموفق بود.",
      );

      error.status = response.status;

      error.payload = payload;

      throw error;
    }

    return payload;
  };

  const serverItemToLocal = (item) => {
    const variant = item?.variant || {};

    const product = item?.product || {};

    return normalizeItem({
      id: product.slug || product.id || variant.product_id || variant.id,

      variantId: item.variant_id ?? item.variantId ?? variant.id,

      name: product.name || variant.name,

      brand: product.brand || "",

      spec: variant.name || product.short_description || "",

      price: variant.price ?? item.unit_price,

      quantity: item.quantity,
    });
  };

  const syncBackend = async () => {
    const localItems = getCart();

    try {
      const auth = await apiRequest("/auth/status");

      if (!auth?.data?.authenticated) {
        return;
      }

      const current = await apiRequest("/cart");

      let serverCart = current?.data?.cart || null;

      if (!serverCart) {
        if (!localItems.length) {
          return;
        }

        if (localItems.some((item) => !item.variantId)) {
          return;
        }

        const created = await apiRequest("/cart", {
          method: "POST",
          body: JSON.stringify({
            currency: "IRR",
            status: "active",
          }),
        });

        serverCart = created?.data?.cart || null;
      }

      if (!serverCart?.id) {
        return;
      }

      const itemsResult = await apiRequest(`/cart/${serverCart.id}/items`);

      const serverItems = itemsResult?.data?.items || [];

      const serverByVariant = new Map(
        serverItems.map((item) => [
          Number(item.variant_id ?? item.variantId ?? item.variant?.id),
          item,
        ]),
      );

      for (const localItem of localItems) {
        const variantId = Number(localItem.variantId || 0);

        if (variantId <= 0) {
          continue;
        }

        const serverItem = serverByVariant.get(variantId);

        if (serverItem) {
          await apiRequest(`/cart-items/${serverItem.id}`, {
            method: "PUT",
            body: JSON.stringify({
              quantity: Number(localItem.quantity),
            }),
          });

          continue;
        }

        await apiRequest("/cart-items", {
          method: "POST",
          body: JSON.stringify({
            cart_id: serverCart.id,
            variant_id: variantId,
            quantity: localItem.quantity,
            unit_price: localItem.price,
          }),
        });
      }

      for (const serverItem of serverItems) {
        const variantId = Number(
          serverItem.variant_id ??
            serverItem.variantId ??
            serverItem.variant?.id ??
            0,
        );

        const exists = localItems.some(
          (item) => Number(item.variantId) === variantId,
        );

        if (!exists) {
          await apiRequest(`/cart-items/${serverItem.id}`, {
            method: "DELETE",
          });
        }
      }

      const refreshed = await apiRequest("/cart");

      const refreshedItems = refreshed?.data?.items || [];

      if (refreshedItems.length) {
        const restored = normalizeCart(
          refreshedItems.map(serverItemToLocal).filter(Boolean),
        );

        if (restored.length) {
          saveCart(restored);
        }
      }
    } catch (error) {
      document.dispatchEvent(
        new CustomEvent("cart:sync-error", {
          detail: {
            error,
          },
        }),
      );
    }
  };

  const getProductFromButton = (button) => ({
    id: String(button.dataset.cart ?? "").trim(),

    variantId: Number(
      button.dataset.productVariantId ?? button.dataset.variantId ?? 0,
    ),

    name: String(button.dataset.productName ?? "").trim(),

    brand: String(button.dataset.productBrand ?? "").trim(),

    price: Number(button.dataset.productPrice),

    spec: String(button.dataset.productSpec ?? "").trim(),
  });

  const add = (product, quantity = 1) => {
    if (!product?.id) {
      return getCart();
    }

    const safeQuantity = Math.floor(Number(quantity));

    if (!Number.isFinite(safeQuantity) || safeQuantity < 1) {
      return getCart();
    }

    if (!product.name || !Number.isFinite(product.price) || product.price < 0) {
      return getCart();
    }

    const items = getCart();

    const productItem = normalizeItem(product);

    if (!productItem) {
      return items;
    }

    const existing = items.find(
      (item) => itemIdentity(item) === itemIdentity(productItem),
    );

    if (existing) {
      existing.quantity += safeQuantity;

      if (productItem.variantId) {
        existing.variantId = productItem.variantId;
      }

      existing.price = productItem.price;

      existing.name = productItem.name;

      existing.brand = productItem.brand;

      existing.spec = productItem.spec;
    } else {
      items.push({
        ...productItem,
        quantity: safeQuantity,
      });
    }

    saveCart(items);

    void syncBackend();

    return getCart();
  };

  const remove = (productId, variantId = 0) => {
    const id = String(productId ?? "").trim();

    const safeVariant = Number(variantId || 0);

    if (!id) {
      return getCart();
    }

    const items = getCart().filter((item) => {
      if (safeVariant > 0) {
        return !(Number(item.variantId) === safeVariant);
      }

      return item.id !== id;
    });

    saveCart(items);

    void syncBackend();

    return getCart();
  };

  const increase = (productId, quantity = 1, variantId = 0) => {
    const id = String(productId ?? "").trim();

    const amount = Math.floor(Number(quantity));

    if (!id || !Number.isFinite(amount) || amount < 1) {
      return getCart();
    }

    const item = getCart().find((cartItem) =>
      Number(variantId) > 0
        ? Number(cartItem.variantId) === Number(variantId)
        : cartItem.id === id,
    );

    if (!item) {
      return getCart();
    }

    item.quantity += amount;

    saveCart(getCart());

    void syncBackend();

    return getCart();
  };

  const decrease = (productId, quantity = 1, variantId = 0) => {
    const id = String(productId ?? "").trim();

    const amount = Math.floor(Number(quantity));

    if (!id || !Number.isFinite(amount) || amount < 1) {
      return getCart();
    }

    const items = getCart();

    const item = items.find((cartItem) =>
      Number(variantId) > 0
        ? Number(cartItem.variantId) === Number(variantId)
        : cartItem.id === id,
    );

    if (!item) {
      return items;
    }

    item.quantity -= amount;

    if (item.quantity <= 0) {
      const next = items.filter((cartItem) => cartItem !== item);

      saveCart(next);

      void syncBackend();

      return next;
    }

    saveCart(items);

    void syncBackend();

    return getCart();
  };

  const clear = () => {
    saveCart([]);

    void syncBackend();

    return [];
  };

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-cart]");

    if (!button) {
      return;
    }

    event.preventDefault();

    const product = getProductFromButton(button);

    if (!product.id || !product.name || !Number.isFinite(product.price)) {
      return;
    }

    add(product);

    button.classList.add("is-added");

    window.setTimeout(() => {
      button.classList.remove("is-added");
    }, 1000);
  });

  updateCount();

  window.digitalStoreCart = {
    getCart,
    getCartCount,
    getCartTotal,
    add,
    remove,
    increase,
    decrease,
    clear,
    syncBackend,
  };

  void syncBackend();
};

export default cart;
