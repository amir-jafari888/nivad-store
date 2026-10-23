const wishlist = () => {
  const storageKey = "digital-store-wishlist";
  const variantStorageKey = "digital-store-wishlist-variants";
  let localMutationVersion = 0;
  let syncPromise = null;
  let syncRequestedAgain = false;

  const baseUrl = window.NIVAD_BASE_URL || "";

  const readJSON = (key, fallback) => {
    try {
      const value = JSON.parse(
        localStorage.getItem(key) || JSON.stringify(fallback),
      );

      return value;
    } catch {
      return fallback;
    }
  };

  const getWishlist = () => {
    const value = readJSON(storageKey, []);

    if (!Array.isArray(value)) {
      return [];
    }

    return [
      ...new Set(value.map((item) => String(item).trim()).filter(Boolean)),
    ];
  };

  const getVariants = () => {
    const value = readJSON(variantStorageKey, {});

    if (!value || typeof value !== "object" || Array.isArray(value)) {
      return {};
    }

    return value;
  };

  const saveLocal = (items, variants, shouldSync = true) => {
    try {
      localStorage.setItem(storageKey, JSON.stringify(items));
      localStorage.setItem(variantStorageKey, JSON.stringify(variants));
    } catch (error) {
      window.dispatchEvent(
        new CustomEvent("wishlist:storage-error", {
          detail: { error },
        }),
      );

      return false;
    }

    localMutationVersion += 1;
    updateUI();

    window.dispatchEvent(
      new CustomEvent("wishlist:updated", {
        detail: {
          items,
          variants,
        },
      }),
    );

    if (shouldSync) {
      void sync();
    }

    return true;
  };

  const updateUI = () => {
    const items = getWishlist();

    document.querySelectorAll(".wishlist-count").forEach((element) => {
      element.textContent = String(items.length);

      element.setAttribute("aria-label", `${items.length} علاقه‌مندی`);
    });

    document.querySelectorAll("[data-wishlist]").forEach((button) => {
      const id = String(button.dataset.wishlist || "").trim();

      const active = items.includes(id);

      button.classList.toggle("is-active", active);

      button.setAttribute("aria-pressed", String(active));
    });
  };

  const request = async (path, options = {}) => {
    const method = String(options.method || "GET").toUpperCase();

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

      const csrfData = await csrfResponse.json().catch(() => ({}));

      const token = csrfData?.data?.token || "";

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

    const payload = await response.json().catch(() => null);

    if (!response.ok || payload?.success === false) {
      const error = new Error(
        payload?.message || "عملیات Wishlist ناموفق بود.",
      );

      error.status = response.status;
      error.payload = payload;

      throw error;
    }

    if (!payload || typeof payload !== "object" || Array.isArray(payload)) {
      const error = new Error("پاسخ معتبر از سرور Wishlist دریافت نشد.");
      error.status = response.status;
      error.payload = payload;
      throw error;
    }

    return payload;
  };

  const serverItemToLocal = (item) => {
    const product = item?.product || {};

    const variant = item?.variant || {};

    const id = String(
      product.slug || product.id || variant.product_id || variant.id || "",
    ).trim();

    const variantId = Number(item?.variant_id || variant.id || 0);

    if (!id || variantId <= 0) {
      return null;
    }

    return {
      id,
      variantId,
    };
  };

  const writeServerItemsToLocal = (serverItems) => {
    const items = [];
    const variants = {};

    for (const serverItem of serverItems) {
      const local = serverItemToLocal(serverItem);

      if (!local) {
        continue;
      }

      if (items.includes(local.id)) {
        continue;
      }

      items.push(local.id);
      variants[local.id] = local.variantId;
    }

    try {
      localStorage.setItem(storageKey, JSON.stringify(items));
      localStorage.setItem(variantStorageKey, JSON.stringify(variants));
      return true;
    } catch (error) {
      window.dispatchEvent(
        new CustomEvent("wishlist:storage-error", {
          detail: { error },
        }),
      );

      return false;
    }
  };

  const getDefaultWishlist = async () => {
    const result = await request("/wishlists");

    const wishlists = Array.isArray(result?.data?.wishlists)
      ? result.data.wishlists
      : [];

    let wishlist =
      wishlists.find((item) => item.isDefault) || wishlists[0] || null;

    if (!wishlist) {
      const created = await request("/wishlists", {
        method: "POST",
        body: JSON.stringify({
          name: "علاقه‌مندی‌ها",
          is_default: true,
        }),
      });

      wishlist = created?.data?.wishlist || null;
    }

    return wishlist;
  };

  const syncOnce = async () => {
    try {
      const auth = await request("/auth/status");

      if (!auth?.data?.authenticated) {
        return;
      }

      const wishlist = await getDefaultWishlist();

      if (!wishlist?.id) {
        return;
      }

      const result = await request(`/wishlists/${wishlist.id}/items`);

      const serverItems = Array.isArray(result?.data?.items)
        ? result.data.items
        : [];

      const localItems = getWishlist();
      const syncVersion = localMutationVersion;
      const localVariants = getVariants();

      const validLocalItems = localItems.filter(
        (id) => Number(localVariants[id] || 0) > 0,
      );

      /*
       * Empty Local:
       * recover from authenticated Server.
       */
      if (localItems.length === 0 && serverItems.length > 0) {
        if (!writeServerItemsToLocal(serverItems)) {
          return;
        }

        updateUI();

        window.dispatchEvent(new CustomEvent("wishlist:updated"));

        return;
      }

      /*
       * Local items without variant identity
       * are NOT allowed to trigger destructive
       * server cleanup.
       */
      if (validLocalItems.length !== localItems.length) {
        return;
      }

      const serverByVariant = new Map(
        serverItems.map((item) => [Number(item.variant_id), item]),
      );

      /*
       * Local → Server
       */
      for (const id of validLocalItems) {
        const variantId = Number(localVariants[id]);

        if (variantId <= 0 || serverByVariant.has(variantId)) {
          continue;
        }

        try {
          await request("/wishlist-items", {
            method: "POST",
            body: JSON.stringify({
              wishlist_id: wishlist.id,
              variant_id: variantId,
            }),
          });
        } catch (error) {
          if (error.status !== 409) {
            throw error;
          }
        }
      }

      /*
       * Server → Local cleanup
       * only when Local has a complete
       * variant identity contract.
       */
      if (syncVersion !== localMutationVersion) {
        return;
      }

      for (const serverItem of serverItems) {
        const serverVariantId = Number(serverItem.variant_id);

        const exists = validLocalItems.some(
          (id) => Number(localVariants[id]) === serverVariantId,
        );

        if (!exists) {
          try {
            await request(`/wishlist-items/${serverItem.id}`, {
              method: "DELETE",
            });
          } catch (error) {
            if (error.status !== 404) {
              throw error;
            }
          }
        }
      }

      const refreshed = await request(`/wishlists/${wishlist.id}/items`);

      const refreshedItems = Array.isArray(refreshed?.data?.items)
        ? refreshed.data.items
        : [];

      if (syncVersion !== localMutationVersion) {
        return;
      }

      if (refreshedItems.length > 0) {
        if (!writeServerItemsToLocal(refreshedItems)) {
          return;
        }

        updateUI();

        window.dispatchEvent(new CustomEvent("wishlist:updated"));
      }
    } catch (error) {
      window.dispatchEvent(
        new CustomEvent("wishlist:sync-error", {
          detail: error,
        }),
      );

      /*
       * Local Wishlist remains usable.
       */
    }
  };

  const sync = () => {
    if (syncPromise) {
      syncRequestedAgain = true;
      return syncPromise;
    }

    syncPromise = (async () => {
      do {
        syncRequestedAgain = false;
        await syncOnce();
      } while (syncRequestedAgain);
    })().finally(() => {
      syncPromise = null;
    });

    return syncPromise;
  };

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-wishlist]");

    if (!button) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    const id = String(button.dataset.wishlist || "").trim();

    if (!id) {
      return;
    }

    const items = getWishlist();
    const variants = getVariants();

    const index = items.indexOf(id);

    if (index === -1) {
      const variantId = Number(button.dataset.wishlistVariantId || 0);

      if (variantId <= 0) {
        return;
      }

      items.push(id);
      variants[id] = variantId;
    } else {
      items.splice(index, 1);
      delete variants[id];
    }

    saveLocal(items, variants, true);
  });

  window.addEventListener("storage", (event) => {
    if (event.key === storageKey || event.key === variantStorageKey) {
      updateUI();
    }
  });

  window.digitalStoreWishlist = {
    getWishlist,
    getVariants,
    saveLocal,
    sync,
  };

  updateUI();

  void sync();
};

export default wishlist;
