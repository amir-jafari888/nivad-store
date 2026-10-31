import header from "./modules/header.js";
import theme from "./modules/theme.js";
import search from "./modules/search.js";
import wishlist from "./modules/wishlist.js";
import cart from "./modules/cart.js";
import cartDrawer from "./modules/cart-drawer.js";
import backToTopButton from "./modules/backtoup.js";
import skipNavigation from "./modules/skip-navigation.js";
import clientErrorMonitoring from "./modules/client-error-monitor.js";
import analytics from "./modules/analytics.js";

header();
theme();
search();
wishlist();
cart();
cartDrawer();
backToTopButton();
skipNavigation();
clientErrorMonitoring();
analytics();

const deferredModuleManifest = [
  { path: "./modules/hero.js", aliases: ["hero"] },
  { path: "./modules/discovery.js", aliases: ["discovery"] },
  { path: "./modules/discovery-preview.js", aliases: ["discovery-preview"] },
  { path: "./modules/setup-builder.js", aliases: ["setup-builder"] },
  { path: "./modules/compatibility.js", aliases: ["compatibility"] },
  { path: "./modules/new-product.js", aliases: ["new-product"] },
  {
    path: "./modules/storefront.js",
    selectors: ["[data-home-products]", "[data-categories-section]"],
  },
  { path: "./modules/catalog.js", aliases: ["catalog-page"] },
  { path: "./modules/product-detail.js", aliases: ["product-detail"] },
  { path: "./modules/product-analytics.js", aliases: ["product-detail"] },
  { path: "./modules/product-media.js", aliases: ["product-media"] },
  { path: "./modules/customer-shop.js", aliases: ["customer-shop"] },
  { path: "./modules/account.js", aliases: ["account"] },
  { path: "./modules/account-support.js", aliases: ["customer-support-page"] },
  { path: "./modules/customer-commerce.js", aliases: ["customer-commerce"] },
  { path: "./modules/auth.js", aliases: ["auth"] },
  { path: "./modules/finder.js", aliases: ["finder"] },
  {
    path: "./modules/home-commerce.js",
    selectors: ["[data-home-collections]", "[data-home-sliders]"],
  },
  { path: "./modules/collection.js", aliases: ["collection"] },
  { path: "./modules/complete-your-setup.js", aliases: ["complete-setup"] },
  { path: "./modules/comparison.js", aliases: ["comparison"] },
  { path: "./modules/alternative.js", aliases: ["alternative"] },
  { path: "./modules/budget-optimizer.js", aliases: ["budget-optimizer"] },
  { path: "./modules/smart-bundle.js", aliases: ["smart-bundle"] },
  { path: "./modules/price-stock-watch-ui.js", aliases: ["price-stock-watch"] },
  { path: "./modules/recent-device-ui.js", aliases: ["recent-device"] },
  { path: "./modules/checkout.js", aliases: ["checkout"] },
  { path: "./modules/payment.js", aliases: ["payment"] },
  { path: "./modules/notification.js", aliases: ["notification"] },
  { path: "./modules/performance.js", always: true },
  { path: "./modules/network-recovery.js", always: true },
];

const pageCapabilities = () => {
  const capabilities = new Set();

  document.querySelectorAll("*").forEach((element) => {
    for (const attribute of element.attributes) {
      if (!attribute.name.startsWith("data-")) continue;

      const name = attribute.name.slice(5);
      capabilities.add(name);
    }
  });

  return capabilities;
};

const shouldLoad = (entry, capabilities) => {
  if (entry.always) return true;
  if (entry.selectors?.some((selector) => document.querySelector(selector))) {
    return true;
  }

  return (entry.aliases || []).some((alias) =>
    [...capabilities].some(
      (capability) =>
        capability === alias || capability.startsWith(`${alias}-`),
    ),
  );
};

const loadDeferredModules = async () => {
  const capabilities = pageCapabilities();
  const candidates = deferredModuleManifest.filter((entry) =>
    shouldLoad(entry, capabilities),
  );

  if (!candidates.length) return;

  const modules = await Promise.all(
    candidates.map(({ path }) => import(path)),
  );

  modules
    .map((module) => module.default)
    .filter((initializer) => typeof initializer === "function")
    .forEach((initializer) => initializer());
};

const scheduleDeferredModules = () => {
  if ("requestIdleCallback" in window) {
    window.requestIdleCallback(loadDeferredModules, { timeout: 1200 });
    return;
  }

  window.setTimeout(loadDeferredModules, 150);
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", scheduleDeferredModules, {
    once: true,
  });
} else {
  scheduleDeferredModules();
}
