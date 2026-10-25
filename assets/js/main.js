import header from "./modules/header.js";
import theme from "./modules/theme.js";
import search from "./modules/search.js";
import wishlist from "./modules/wishlist.js";
import cart from "./modules/cart.js";
import cartDrawer from "./modules/cart-drawer.js";
import backToTopButton from "./modules/backtoup.js";

header();
theme();
search();
wishlist();
cart();
cartDrawer();
backToTopButton();

const deferredModuleManifest = [
  { path: "./modules/hero.js", selectors: ["[data-hero]"] },
  { path: "./modules/discovery.js", selectors: ["[data-discovery]"] },
  { path: "./modules/setup-builder.js", selectors: ["[data-setup-builder]"] },
  { path: "./modules/compatibility.js", selectors: ["[data-compatibility]"] },
  { path: "./modules/new-product.js", selectors: ["[data-new-product]"] },
  {
    path: "./modules/storefront.js",
    selectors: ["[data-home-products]", "[data-categories-section]"],
  },
  { path: "./modules/catalog.js", selectors: ["[data-catalog-page]"] },
  { path: "./modules/product-detail.js", selectors: ["[data-product-detail]"] },
  { path: "./modules/product-media.js", selectors: ["[data-product-media]"] },
  { path: "./modules/customer-shop.js", selectors: ["[data-customer-shop]"] },
  { path: "./modules/account.js", selectors: ["[data-account]"] },
  { path: "./modules/customer-commerce.js", selectors: ["[data-customer-commerce]"] },
  { path: "./modules/auth.js", selectors: ["[data-auth]"] },
  { path: "./modules/finder.js", selectors: ["[data-finder]"] },
  { path: "./modules/home-commerce.js", selectors: ["[data-home-commerce]"] },
  { path: "./modules/collection.js", selectors: ["[data-collection]"] },
  { path: "./modules/complete-your-setup.js", selectors: ["[data-complete-setup]"] },
  { path: "./modules/comparison.js", selectors: ["[data-comparison]"] },
  { path: "./modules/alternative.js", selectors: ["[data-alternative]"] },
  { path: "./modules/budget-optimizer.js", selectors: ["[data-budget-optimizer]"] },
  { path: "./modules/smart-bundle.js", selectors: ["[data-smart-bundle]"] },
  { path: "./modules/price-stock-watch-ui.js", selectors: ["[data-price-stock-watch]"] },
  { path: "./modules/recent-device-ui.js", selectors: ["[data-recent-device]"] },
  { path: "./modules/checkout.js", selectors: ["[data-checkout]"] },
  { path: "./modules/payment.js", selectors: ["[data-payment]"] },
  { path: "./modules/notification.js", selectors: ["[data-notification]"] },
];

const shouldLoad = (selectors) =>
  selectors.some((selector) => document.querySelector(selector));

const loadDeferredModules = async () => {
  const candidates = deferredModuleManifest.filter((entry) =>
    shouldLoad(entry.selectors),
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
