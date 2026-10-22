const PRICE_WATCH_STATES = Object.freeze({
  IDLE: "idle",
  SUBSCRIBED: "subscribed",
  UNSUBSCRIBED: "unsubscribed",
  LOADING: "loading",
  SUCCESS: "success",
  ERROR: "error",
  UNKNOWN: "unknown",
});

function normalizePrice(value) {
  if (value === null || value === undefined || value === "") {
    return null;
  }

  const number = Number(value);

  return Number.isFinite(number) ? number : null;
}

function hasPriceDrop(previousPrice, currentPrice) {
  const previous = normalizePrice(previousPrice);
  const current = normalizePrice(currentPrice);

  if (previous === null || current === null) {
    return false;
  }

  return current < previous;
}

function createPriceDropContext(product) {
  if (!product || typeof product !== "object") {
    return {
      productId: null,
      variantId: null,
      previousPrice: null,
      currentPrice: null,
      hasDrop: false,
      state: PRICE_WATCH_STATES.UNKNOWN,
    };
  }

  const previousPrice = normalizePrice(
    product.previous_price ??
      product.previousPrice ??
      product.compare_at_price ??
      product.compareAtPrice,
  );

  const currentPrice = normalizePrice(
    product.price ?? product.current_price ?? product.currentPrice,
  );

  return {
    productId: product.id ?? null,
    variantId: product.variant_id ?? product.variantId ?? null,
    previousPrice,
    currentPrice,
    hasDrop: hasPriceDrop(previousPrice, currentPrice),
    state: PRICE_WATCH_STATES.UNKNOWN,
  };
}

function assertPriceDropContract(context) {
  if (!context || typeof context !== "object") {
    return false;
  }

  return context.productId !== null && context.currentPrice !== null;
}

export {
  PRICE_WATCH_STATES,
  normalizePrice,
  hasPriceDrop,
  createPriceDropContext,
  assertPriceDropContract,
};
