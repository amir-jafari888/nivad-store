import header from "./modules/header.js";
import search from "./modules/search.js";
import wishlist from "./modules/wishlist.js";
import cart from "./modules/cart.js";
import cartDrawer from "./modules/cart-drawer.js";
import backToTopButton from "./modules/backtoup.js";

header();
search();
wishlist();
cart();
cartDrawer();
backToTopButton();

const loadDeferredModules = async () => {
  const modules = await Promise.all([
    import("./modules/hero.js"),
    import("./modules/discovery.js"),
    import("./modules/setup-builder.js"),
    import("./modules/compatibility.js"),
    import("./modules/new-product.js"),
    import("./modules/storefront.js"),
    import("./modules/catalog.js"),
    import("./modules/product-detail.js"),
    import("./modules/product-media.js"),
    import("./modules/customer-shop.js"),
    import("./modules/account.js"),
    import("./modules/customer-commerce.js"),
    import("./modules/auth.js"),
    import("./modules/finder.js"),
    import("./modules/home-commerce.js"),
    import("./modules/collection.js"),
    import("./modules/complete-your-setup.js"),
    import("./modules/comparison.js"),
    import("./modules/alternative.js"),
    import("./modules/budget-optimizer.js"),
    import("./modules/smart-bundle.js"),
    import("./modules/price-stock-watch-ui.js"),
    import("./modules/recent-device-ui.js"),
    import("./modules/checkout.js"),
    import("./modules/payment.js"),
    import("./modules/notification.js"),
  ]);

  const initializers = modules
    .map((module) => module.default)
    .filter((initializer) => typeof initializer === "function");

  initializers.forEach((initializer) => initializer());
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
