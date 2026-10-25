import { PRICE_WATCH_STATES, createPriceDropContext } from "./price-watch.js";

import { STOCK_WATCH_STATES, createStockWatchContext } from "./stock-watch.js";

const SELECTORS = Object.freeze({
  root: "[data-price-stock-watch]",
  priceWatch: "[data-price-drop-watch]",
  stockWatch: "[data-back-in-stock-watch]",
  status: "[data-watch-status]",
});

function getRoot() {
  return document.querySelector(SELECTORS.root);
}

function getProductContext(root) {
  if (!root) {
    return null;
  }

  return {
    id: root.dataset.productId ?? null,
    variantId: root.dataset.variantId ?? null,
    price: root.dataset.currentPrice ?? null,
    previousPrice: root.dataset.previousPrice ?? null,
    stockStatus: root.dataset.stockStatus ?? null,
  };
}

function renderStatus(element, message, state) {
  if (!element) {
    return;
  }

  element.textContent = message;
  element.dataset.state = state;
  element.hidden = !message;
}

function initializePriceStockWatch() {
  const root = getRoot();

  if (!root) {
    return;
  }

  const product = getProductContext(root);

  const priceContext = createPriceDropContext({
    id: product.id,
    variantId: product.variantId,
    price: product.price,
    previousPrice: product.previousPrice,
  });

  const stockContext = createStockWatchContext({
    id: product.id,
    variantId: product.variantId,
    stockStatus: product.stockStatus,
  });

  const status = root.querySelector(SELECTORS.status);

  const applyWatchState = (context) => {
    const price = createPriceDropContext({
      id: context.id,
      variantId: context.variantId,
      price: context.price,
      previousPrice: context.previousPrice,
    });
    const stock = createStockWatchContext({
      id: context.id,
      variantId: context.variantId,
      stockStatus: context.stockStatus,
    });

    delete root.dataset.priceDrop;
    delete root.dataset.backInStock;

    if (price.hasDrop && price.currentPrice !== null) {
      root.dataset.priceDrop = "true";
    }

    if (stock.state === STOCK_WATCH_STATES.OUT_OF_STOCK) {
      root.dataset.backInStock = "available";
    }

    if (
      price.state === PRICE_WATCH_STATES.UNKNOWN &&
      stock.state === STOCK_WATCH_STATES.UNKNOWN
    ) {
      renderStatus(status, "", "unknown");
    }
  };

  applyWatchState(product);

  document.addEventListener("product-detail:variant-changed", (event) => {
    const detail = event.detail || {};
    if (String(detail.productId ?? "") !== String(root.dataset.productId ?? "")) {
      return;
    }

    const context = {
      id: detail.productId,
      variantId: detail.variantId,
      price: detail.price,
      previousPrice: detail.compareAtPrice,
      stockStatus: detail.stockStatus || "unknown",
    };

    root.dataset.variantId = String(context.variantId ?? "");
    root.dataset.currentPrice = context.price == null ? "" : String(context.price);
    root.dataset.previousPrice =
      context.previousPrice == null ? "" : String(context.previousPrice);
    root.dataset.stockStatus = context.stockStatus;

    applyWatchState(context);
  });
}

export default initializePriceStockWatch;
