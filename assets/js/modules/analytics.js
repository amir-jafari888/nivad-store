const analytics = () => {
  "use strict";

  if (window.NivadAnalytics) return;

  const consentKey = "nivad-analytics-consent";
  const visitorKey = "nivad-analytics-visitor";
  const seenSearches = new Set();
  const seenImpressions = new WeakSet();
  const seenComparisonViews = new Set();
  let previousComparison = [];

  const consent = () => {
    try { return window.localStorage.getItem(consentKey) || "unknown"; }
    catch (_) { return "unknown"; }
  };

  const visitorId = () => {
    if (consent() !== "accepted") return "";
    try {
      let value = window.localStorage.getItem(visitorKey) || "";
      if (!/^[A-Za-z0-9_-]{16,128}$/.test(value)) {
        value = typeof crypto !== "undefined" && typeof crypto.randomUUID === "function"
          ? crypto.randomUUID().replaceAll("-", "")
          : Array.from(crypto.getRandomValues(new Uint8Array(24)), byte => byte.toString(16).padStart(2, "0")).join("");
        window.localStorage.setItem(visitorKey, value);
      }
      return value;
    } catch (_) {
      return "";
    }
  };

  const apiUrl = () => {
    const base = document.querySelector('meta[name="api-url"]')?.content || "";
    if (!base) return null;
    const url = new URL(base, window.location.origin);
    url.searchParams.set("path", "/analytics/product-event");
    return url.toString();
  };

  const track = (eventName, data = {}) => {
    if (consent() !== "accepted") return false;
    const endpoint = apiUrl();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
    const visitor = visitorId();
    if (!endpoint || !csrf || !visitor) return false;

    const payload = {
      event_name: eventName,
      visitor_id: visitor,
      entity_type: data.entity_type || null,
      entity_id: data.entity_id === undefined ? null : String(data.entity_id),
      product_id: data.product_id ? Number(data.product_id) : null,
      variant_id: data.variant_id ? Number(data.variant_id) : null,
      search_term: typeof data.search_term === "string" ? data.search_term.slice(0, 200) : null,
      source: typeof data.source === "string" ? data.source.slice(0, 60) : "storefront",
      step: typeof data.step === "string" ? data.step : null,
      result_count: Number.isFinite(Number(data.result_count)) ? Number(data.result_count) : null,
      client_event_id: typeof crypto !== "undefined" && typeof crypto.randomUUID === "function"
        ? crypto.randomUUID().replaceAll("-", "")
        : String(Date.now()) + Math.random().toString(36).slice(2, 12),
    };

    fetch(endpoint, {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify(payload),
    }).catch(() => {});
    return true;
  };

  window.NivadAnalytics = { track, visitorId };

  const observeImpression = (element, eventName, productId, source) => {
    if (!element || seenImpressions.has(element) || !productId || !("IntersectionObserver" in window)) return;
    seenImpressions.add(element);
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.35) return;
        track(eventName, { product_id: productId, source });
        observer.unobserve(element);
      });
    }, { threshold: [0.35] });
    observer.observe(element);
  };

  const scanImpressions = () => {
    document.querySelectorAll("[data-complement-product-id]").forEach(element => {
      observeImpression(element, "related_product_impression", element.dataset.complementProductId, "related-products");
    });
    document.querySelectorAll(".page-discovery__product[data-product-id]").forEach(element => {
      observeImpression(element, "discovery_impression", element.dataset.productId, "discovery");
    });
    document.querySelectorAll("[data-discovery-tags]").forEach(element => {
      if (seenImpressions.has(element) || !("IntersectionObserver" in window)) return;
      seenImpressions.add(element);
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (!entry.isIntersecting || entry.intersectionRatio < 0.35) return;
          track("discovery_option_impression", {
            entity_type: "discovery",
            entity_id: element.dataset.discovery || "unknown",
            source: "discovery-options",
          });
          observer.unobserve(element);
        });
      }, { threshold: [0.35] });
      observer.observe(element);
    });
  };

  const scanSearchImpression = () => {
    const form = document.querySelector("[data-search-form]");
    const input = form?.querySelector("[data-search-input]");
    const box = form?.querySelector(".search-suggestions");
    if (!input || !box || !box.classList.contains("is-visible")) return;
    const query = input.value.trim();
    const count = box.querySelectorAll(".search-suggestion").length;
    if (!query || count < 1) return;
    const key = query.toLocaleLowerCase();
    if (seenSearches.has(key)) return;
    seenSearches.add(key);
    track("search_impression", { search_term: query, result_count: count, source: "search-suggestions" });
  };

  document.addEventListener("submit", event => {
    const form = event.target instanceof Element ? event.target.closest("[data-search-form]") : null;
    if (!form) return;
    const query = form.querySelector("[data-search-input]")?.value || "";
    if (query.trim()) track("search_click", { search_term: query, source: "search-submit" });
  }, true);

  document.addEventListener("click", event => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;

    const suggestion = target.closest(".search-suggestion");
    if (suggestion) {
      const query = document.querySelector("[data-search-input]")?.value || "";
      if (query.trim()) track("search_click", { search_term: query, source: "search-suggestion" });
    }

    const related = target.closest("[data-complement-product-id] a");
    if (related) {
      const card = related.closest("[data-complement-product-id]");
      track("related_product_click", { product_id: card?.dataset.complementProductId, source: "related-products" });
    }

    const discoveryOption = target.closest("[data-discovery-tags]");
    if (discoveryOption) {
      track("discovery_select", {
        entity_type: "discovery",
        entity_id: discoveryOption.dataset.discovery || "",
        source: "discovery-options",
      });
    }
  }, true);

  document.addEventListener("change", event => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;
    if (target.matches('input[name="checkoutAddress"]')) {
      track("checkout_address_selected", { entity_type: "checkout", source: "checkout", step: "address" });
    } else if (target.matches('input[name="shippingMethod"]')) {
      track("checkout_shipping_selected", { entity_type: "checkout", source: "checkout", step: "shipping" });
    }
  }, true);

  window.addEventListener("comparison:selection-changed", event => {
    const next = Array.isArray(event.detail?.ids) ? event.detail.ids.map(Number) : [];
    const before = new Set(previousComparison);
    const after = new Set(next);
    next.filter(id => !before.has(id)).forEach(id => track("comparison_add", { product_id: id, source: "comparison" }));
    previousComparison.filter(id => !after.has(id)).forEach(id => track("comparison_remove", { product_id: id, source: "comparison" }));
    previousComparison = next;
  });

  const scanComparisonView = () => {
    const page = document.querySelector("[data-comparison-page]");
    const content = page?.querySelector("[data-comparison-content]");
    if (!page || !content || content.hidden) return;
    let ids = [];
    try {
      const parsed = JSON.parse(window.localStorage.getItem("digital-store-comparison") || "[]");
      ids = Array.isArray(parsed) ? parsed.map(Number).filter(id => Number.isInteger(id) && id > 0).slice(0, 4) : [];
    } catch (_) {}
    if (!ids.length) return;
    const key = ids.join(",");
    if (seenComparisonViews.has(key)) return;
    seenComparisonViews.add(key);
    track("comparison_view", { entity_type: "comparison", entity_id: key, source: "comparison" });
  };

  const checkout = document.querySelector("[data-customer-checkout]");
  if (checkout) track("checkout_started", { entity_type: "checkout", source: "checkout", step: "start" });

  const observer = new MutationObserver(() => {
    scanImpressions();
    scanSearchImpression();
    scanComparisonView();
  });
  if (document.body) observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ["class", "hidden"] });

  scanImpressions();
  scanSearchImpression();
  scanComparisonView();

  let currentIds = [];
  try {
    const parsed = JSON.parse(window.localStorage.getItem("digital-store-comparison") || "[]");
    currentIds = Array.isArray(parsed) ? parsed.map(Number).filter(id => Number.isInteger(id) && id > 0) : [];
  } catch (_) {}
  previousComparison = currentIds;
};

export default analytics;
