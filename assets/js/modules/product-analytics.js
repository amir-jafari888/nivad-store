const productAnalytics = () => {
  "use strict";
  const root = document.querySelector("[data-product-detail]");
  if (!root || root.dataset.analyticsBound === "true") return;
  root.dataset.analyticsBound = "true";

  const productId = Number(root.dataset.productId || 0);
  const apiBase = root.dataset.apiBase || "";
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  if (!productId || !apiBase || !csrf) return;

  let loaded = false;
  let lastVariantId = 0;
  const send = (eventName, variantId = null) => {
    const url = new URL(apiBase, window.location.origin);
    url.searchParams.set("path", "/analytics/product-event");
    fetch(url.toString(), {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf
      },
      body: JSON.stringify({
        event_name: eventName,
        product_id: productId,
        variant_id: variantId || null
      })
    }).catch(() => {});
  };

  document.addEventListener("product-detail:loaded", event => {
    if (loaded) return;
    loaded = true;
    lastVariantId = Number(event.detail?.variantId || 0);
    send("product_view", lastVariantId || null);
  });

  document.addEventListener("product-detail:variant-changed", event => {
    if (!loaded) return;
    const nextId = Number(event.detail?.variantId || 0);
    if (!nextId || nextId === lastVariantId) return;
    lastVariantId = nextId;
    send("variant_select", nextId);
  });

  document.addEventListener("click", event => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;
    if (target.closest("[data-product-add-cart]")) {
      send("add_to_cart", lastVariantId || null);
    }
    if (target.closest("[data-product-wishlist]")) {
      send("wishlist_add", lastVariantId || null);
    }
  });
};

export default productAnalytics;
