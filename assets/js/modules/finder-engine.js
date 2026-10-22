export const FINDER_INTENTS = Object.freeze({
  CHARGING: "charging",
  GAMING: "gaming",
  TRAVEL: "travel",
  WORK: "work",
  CAR: "car",
  DESK: "desk",
  CONTENT: "content",
  DAILY: "daily",
});

export const FINDER_BUDGETS = Object.freeze({
  LOW: "low",
  MID: "mid",
  HIGH: "high",
  ANY: "any",
});

const INTENT_TERMS = Object.freeze({
  charging: ["شارژ", "کابل", "شارژر", "pd", "usb", "type-c"],
  gaming: ["گیم", "گیمینگ", "gaming", "hdmi", "مانیتور", "کنسول"],
  travel: ["سفر", "مسافرت", "پاوربانک", "شارژر", "کابل"],
  work: ["کار", "اداری", "لپ تاپ", "مانیتور", "هاب", "usb"],
  car: ["خودرو", "ماشین", "car", "شارژر", "کابل"],
  desk: ["میز", "دسک", "desk", "هاب", "مانیتور", "کابل"],
  content: ["تولید محتوا", "محتوا", "دوربین", "میکروفون", "hdmi", "usb"],
  daily: ["روزمره", "روزانه", "کابل", "شارژر", "usb"],
});

const BUDGET_RANGES = Object.freeze({
  low: [0, 1000000],
  mid: [1000000, 5000000],
  high: [5000000, Number.POSITIVE_INFINITY],
  any: [0, Number.POSITIVE_INFINITY],
});

const toText = (value) =>
  String(value ?? "")
    .trim()
    .toLowerCase();

const productText = (product) =>
  [
    product?.name,
    product?.short_description,
    product?.description,
    product?.slug,
  ]
    .map(toText)
    .filter(Boolean)
    .join(" ");

export const normalizeFinderRequest = (request = {}) => {
  const intent = Object.values(FINDER_INTENTS).includes(request.intent)
    ? request.intent
    : null;

  const budget = Object.values(FINDER_BUDGETS).includes(request.budget)
    ? request.budget
    : FINDER_BUDGETS.ANY;

  return Object.freeze({
    intent,
    budget,
    query: toText(request.query),
  });
};

export const createFinderRequest = ({ intent, budget, query } = {}) =>
  normalizeFinderRequest({
    intent,
    budget,
    query,
  });

export const getProductPrice = (product) => {
  const price = Number(
    product?.variant?.price ??
      product?.sale_price ??
      product?.final_price ??
      product?.price,
  );

  return Number.isFinite(price) ? price : null;
};

export const matchesFinderBudget = (product, budget) => {
  const price = getProductPrice(product);

  const [minimum, maximum] = BUDGET_RANGES[budget] ?? BUDGET_RANGES.any;

  if (price === null) {
    return budget === FINDER_BUDGETS.ANY;
  }

  return price >= minimum && price <= maximum;
};

export const scoreFinderProduct = (product, request) => {
  const normalized = normalizeFinderRequest(request);

  const text = productText(product);

  let score = 0;

  const reasons = [];

  if (normalized.intent) {
    const terms = INTENT_TERMS[normalized.intent] ?? [];

    const matches = terms.filter((term) => text.includes(toText(term)));

    score += Math.min(matches.length * 14, 56);

    if (matches.length > 0) {
      reasons.push(`مطابق با نیاز ${normalized.intent}`);
    }
  }

  if (normalized.query) {
    const queryTerms = normalized.query.split(/\s+/).filter(Boolean);

    const queryMatches = queryTerms.filter((term) => text.includes(term));

    score += Math.min(queryMatches.length * 12, 36);

    if (queryMatches.length > 0) {
      reasons.push("با جستجوی تو هم‌خوانی دارد");
    }
  }

  if (matchesFinderBudget(product, normalized.budget)) {
    if (normalized.budget !== FINDER_BUDGETS.ANY) {
      score += 24;

      reasons.push("در محدوده بودجه تو قرار می‌گیرد");
    }
  } else if (normalized.budget !== FINDER_BUDGETS.ANY) {
    score -= 24;
  }

  if (product?.is_featured) {
    score += 8;
  }

  return {
    product,
    score: Math.max(0, score),
    reasons,
  };
};

export const rankFinderProducts = (products = [], request = {}) =>
  products
    .filter((product) => product && typeof product === "object")
    .map((product, index) => ({
      ...scoreFinderProduct(product, request),
      originalIndex: index,
    }))
    .filter((item) => item.score > 0 || !normalizeFinderRequest(request).intent)
    .sort((a, b) => b.score - a.score || a.originalIndex - b.originalIndex)
    .slice(0, 12);

export const getFinderConfidence = (rankedItems = []) => {
  const topScore = Number(rankedItems[0]?.score ?? 0);

  if (topScore >= 70) {
    return {
      level: "high",
      label: "اعتماد بالا",
    };
  }

  if (topScore >= 40) {
    return {
      level: "medium",
      label: "اعتماد متوسط",
    };
  }

  return {
    level: "low",
    label: "اعتماد محدود",
  };
};
