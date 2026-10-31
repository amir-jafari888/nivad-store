const productAnalytics = () => {
  "use strict";
  const root = document.querySelector("[data-product-detail]");
  if (!root || root.dataset.analyticsBound === "true") return;
  root.dataset.analyticsBound = "true";

  const productId = Number(root.dataset.productId || 0);
  const apiBase = root.dataset.apiBase || "";
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  if (!productId || !apiBase || !csrf) return;

  const consentKey = "nivad-analytics-consent";
  let consent = "unknown";
  let loaded = false;
  let viewSent = false;
  let lastVariantId = 0;
  try { consent = window.localStorage.getItem(consentKey) || "unknown"; } catch (_) {}

  const send = (eventName, variantId = null) => {
    if (consent !== "accepted") return;
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
        variant_id: variantId || null,
        visitor_id: window.NivadAnalytics?.visitorId?.() || "",
        source: "product-detail"
      })
    }).catch(() => {});
  };

  const showConsent = () => {
    if (consent !== "unknown" || root.querySelector("[data-product-analytics-consent]")) return;
    const panel = document.createElement("aside");
    panel.className = "product-analytics-consent";
    panel.dataset.productAnalyticsConsent = "";
    panel.setAttribute("aria-label", "تنظیمات تحلیل رفتار محصول");
    const text = document.createElement("p");
    text.textContent = "برای بهبود تجربه فروشگاه، رویدادهای تحلیلی این صفحه با شناسه مستعار مانند بازدید و افزودن به سبد ثبت شوند؟";
    const accept = document.createElement("button");
    accept.type = "button";
    accept.textContent = "موافقت";
    const reject = document.createElement("button");
    reject.type = "button";
    reject.textContent = "عدم موافقت";
    const choose = value => {
      consent = value;
      panel.remove();
      if (window.NivadAnalytics?.setConsent) {
        window.NivadAnalytics.setConsent(value);
      } else {
        try { window.localStorage.setItem(consentKey, value); } catch (_) {}
        window.dispatchEvent(new CustomEvent("nivad:analytics-consent", { detail: { consent: value } }));
      }
      if (value === "accepted" && loaded && !viewSent) {
        viewSent = true;
        send("product_view", lastVariantId || null);
      }
    };
    accept.addEventListener("click", () => choose("accepted"));
    reject.addEventListener("click", () => choose("rejected"));
    panel.append(text, accept, reject);
    root.append(panel);
  };

  if (consent === "unknown") showConsent();

  document.addEventListener("product-detail:loaded", event => {
    if (loaded) return;
    loaded = true;
    lastVariantId = Number(event.detail?.variantId || 0);
    if (consent === "accepted" && !viewSent) {
      viewSent = true;
      send("product_view", lastVariantId || null);
    }
  });

  document.addEventListener("product-detail:variant-changed", event => {
    if (!loaded || consent !== "accepted") return;
    const nextId = Number(event.detail?.variantId || 0);
    if (!nextId || nextId === lastVariantId) return;
    lastVariantId = nextId;
    send("variant_select", nextId);
  });

  document.addEventListener("click", event => {
    if (consent !== "accepted") return;
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;
    if (target.closest("[data-product-add-cart]")) send("add_to_cart", lastVariantId || null);
    if (target.closest("[data-product-wishlist]")) send("wishlist_add", lastVariantId || null);
  });
};

export default productAnalytics;
