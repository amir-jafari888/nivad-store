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

  if (priceContext.hasDrop && priceContext.currentPrice !== null) {
    root.dataset.priceDrop = "true";
  }

  if (stockContext.state === STOCK_WATCH_STATES.OUT_OF_STOCK) {
    root.dataset.backInStock = "available";
  }

  if (
    priceContext.state === PRICE_WATCH_STATES.UNKNOWN &&
    stockContext.state === STOCK_WATCH_STATES.UNKNOWN
  ) {
    renderStatus(status, "", "unknown");
  }
}

export default initializePriceStockWatch;
