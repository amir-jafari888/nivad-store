const STOCK_WATCH_STATES = Object.freeze({
  IDLE: "idle",
  IN_STOCK: "in_stock",
  OUT_OF_STOCK: "out_of_stock",
  SUBSCRIBED: "subscribed",
  UNSUBSCRIBED: "unsubscribed",
  LOADING: "loading",
  SUCCESS: "success",
  ERROR: "error",
  UNKNOWN: "unknown",
});

const STOCK_STATUS = Object.freeze({
  IN_STOCK: "in_stock",
  OUT_OF_STOCK: "out_of_stock",
  UNKNOWN: "unknown",
});

function normalizeStockStatus(value) {
  if (typeof value !== "string") {
    return STOCK_STATUS.UNKNOWN;
  }

  const normalized = value.trim().toLowerCase();

  if (
    normalized === "in_stock" ||
    normalized === "available" ||
    normalized === "in-stock"
  ) {
    return STOCK_STATUS.IN_STOCK;
  }

  if (
    normalized === "out_of_stock" ||
    normalized === "unavailable" ||
    normalized === "out-of-stock"
  ) {
    return STOCK_STATUS.OUT_OF_STOCK;
  }

  return STOCK_STATUS.UNKNOWN;
}

function isInStock(value) {
  return normalizeStockStatus(value) === STOCK_STATUS.IN_STOCK;
}

function isOutOfStock(value) {
  return normalizeStockStatus(value) === STOCK_STATUS.OUT_OF_STOCK;
}

function createStockWatchContext(product) {
  if (!product || typeof product !== "object") {
    return {
      productId: null,
      variantId: null,
      status: STOCK_STATUS.UNKNOWN,
      state: STOCK_WATCH_STATES.UNKNOWN,
    };
  }

  const status = normalizeStockStatus(
    product.stock_status ??
      product.stockStatus ??
      product.availability ??
      product.status,
  );

  return {
    productId: product.id ?? null,
    variantId: product.variant_id ?? product.variantId ?? null,
    status,
    state:
      status === STOCK_STATUS.IN_STOCK
        ? STOCK_WATCH_STATES.IN_STOCK
        : status === STOCK_STATUS.OUT_OF_STOCK
          ? STOCK_WATCH_STATES.OUT_OF_STOCK
          : STOCK_WATCH_STATES.UNKNOWN,
  };
}

export {
  STOCK_WATCH_STATES,
  STOCK_STATUS,
  normalizeStockStatus,
  isInStock,
  isOutOfStock,
  createStockWatchContext,
};
