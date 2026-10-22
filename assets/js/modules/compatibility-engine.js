const COMPATIBILITY_TYPES = Object.freeze({
  COMPATIBLE: "compatible",
  PARTIALLY_COMPATIBLE: "partially_compatible",
  NOT_COMPATIBLE: "not_compatible",
});

const COMPATIBILITY_TYPE_SET = new Set(Object.values(COMPATIBILITY_TYPES));

const normalizeString = (value) => {
  if (value === null || value === undefined) {
    return "";
  }

  return String(value).trim();
};

const normalizeNullableString = (value) => {
  const normalized = normalizeString(value);

  return normalized || null;
};

const normalizeDevice = (device) => {
  if (!device || typeof device !== "object") {
    return null;
  }

  const id = Number(device.id);

  if (!Number.isInteger(id) || id <= 0) {
    return null;
  }

  return {
    id,
    brandName: normalizeString(device.brandName ?? device.brand_name),
    name: normalizeString(device.name),
    model: normalizeNullableString(device.model),
    deviceType: normalizeNullableString(
      device.deviceType ?? device.device_type,
    ),
    slug: normalizeNullableString(device.slug),
    description: normalizeNullableString(device.description),
    image: normalizeNullableString(device.image),
  };
};

const normalizeProduct = (product) => {
  if (!product || typeof product !== "object") {
    return null;
  }

  const id = Number(product.id ?? product.product_id);

  return {
    id: Number.isInteger(id) && id > 0 ? id : null,

    name: normalizeString(product.name ?? product.product_name),

    brandName: normalizeString(
      product.brandName ?? product.brand_name ?? product.brand,
    ),

    slug: normalizeNullableString(product.slug ?? product.product_slug),

    price: product.price ?? null,

    compatibilityType: normalizeString(
      product.compatibilityType ?? product.compatibility_type,
    ),

    note: normalizeNullableString(product.note),

    image: normalizeNullableString(product.image),

    sortOrder: Number.isFinite(Number(product.sortOrder ?? product.sort_order))
      ? Number(product.sortOrder ?? product.sort_order)
      : 0,

    confidence: normalizeConfidence(product.confidence),
  };
};

const normalizeConfidence = (value) => {
  if (!value || typeof value !== "object") {
    return {
      level: "unknown",
      label: "اطمینان نامشخص",
      score: null,
      reason: null,
    };
  }

  const rawScore = Number(value.score);

  const score =
    Number.isFinite(rawScore) && rawScore >= 0 && rawScore <= 100
      ? rawScore
      : null;

  const level = normalizeString(value.level) || "unknown";

  const label =
    normalizeString(value.label) ||
    (level === "high"
      ? "اطمینان بالا"
      : level === "medium"
        ? "اطمینان متوسط"
        : level === "low"
          ? "اطمینان محدود"
          : "اطمینان نامشخص");

  return {
    level,
    label,
    score,
    reason: normalizeNullableString(value.reason),
  };
};

const normalizeCompatibilityType = (value) => {
  const type = normalizeString(value);

  return COMPATIBILITY_TYPE_SET.has(type) ? type : null;
};

const getCompatibilityMeta = (value) => {
  const type = normalizeCompatibilityType(value);

  if (type === COMPATIBILITY_TYPES.COMPATIBLE) {
    return {
      key: type,
      label: "سازگار",
      tone: "success",
      description: "این محصول برای دستگاه انتخاب‌شده سازگار ثبت شده است.",
    };
  }

  if (type === COMPATIBILITY_TYPES.PARTIALLY_COMPATIBLE) {
    return {
      key: type,
      label: "سازگاری مشروط",
      tone: "warning",
      description:
        "این محصول سازگاری کامل ندارد؛ توضیحات سازگاری را بررسی کنید.",
    };
  }

  if (type === COMPATIBILITY_TYPES.NOT_COMPATIBLE) {
    return {
      key: type,
      label: "ناسازگار",
      tone: "danger",
      description: "این محصول برای دستگاه انتخاب‌شده ناسازگار ثبت شده است.",
    };
  }

  return {
    key: null,
    label: "نامشخص",
    tone: "neutral",
    description: "نوع سازگاری برای این محصول ثبت نشده است.",
  };
};

const normalizeResult = (payload) => {
  const data =
    payload &&
    typeof payload === "object" &&
    payload.data &&
    typeof payload.data === "object"
      ? payload.data
      : payload;

  if (!data || typeof data !== "object") {
    return {
      device: null,
      items: [],
      type: null,
      count: 0,
      compatibleCount: 0,
      partialCount: 0,
      incompatibleCount: 0,
    };
  }

  const device = normalizeDevice(data.device);

  const rawItems = Array.isArray(data.items) ? data.items : [];

  const items = rawItems.map(normalizeProduct).filter(Boolean);

  const compatibleCount = items.filter(
    (item) => item.compatibilityType === COMPATIBILITY_TYPES.COMPATIBLE,
  ).length;

  const partialCount = items.filter(
    (item) =>
      item.compatibilityType === COMPATIBILITY_TYPES.PARTIALLY_COMPATIBLE,
  ).length;

  const incompatibleCount = items.filter(
    (item) => item.compatibilityType === COMPATIBILITY_TYPES.NOT_COMPATIBLE,
  ).length;

  const types = items
    .map((item) => normalizeCompatibilityType(item.compatibilityType))
    .filter(Boolean);

  const type = types.length === 1 ? types[0] : null;

  return {
    device,
    items,
    type,
    count: items.length,
    compatibleCount,
    partialCount,
    incompatibleCount,
  };
};

const createRequest = (deviceId, type = null) => {
  const id = Number(deviceId);

  if (!Number.isInteger(id) || id <= 0) {
    return null;
  }

  const request = {
    deviceId: id,
  };

  const normalizedType = normalizeCompatibilityType(type);

  if (normalizedType) {
    request.type = normalizedType;
  }

  return request;
};

const isKnownType = (value) => normalizeCompatibilityType(value) !== null;

const isCompatible = (value) =>
  normalizeCompatibilityType(value) === COMPATIBILITY_TYPES.COMPATIBLE;

const isPartiallyCompatible = (value) =>
  normalizeCompatibilityType(value) ===
  COMPATIBILITY_TYPES.PARTIALLY_COMPATIBLE;

const isNotCompatible = (value) =>
  normalizeCompatibilityType(value) === COMPATIBILITY_TYPES.NOT_COMPATIBLE;
export {
  COMPATIBILITY_TYPES,
  COMPATIBILITY_TYPE_SET,
  normalizeDevice,
  normalizeProduct,
  normalizeCompatibilityType,
  normalizeConfidence,
  getCompatibilityMeta,
  normalizeResult,
  createRequest,
  isKnownType,
  isCompatible,
  isPartiallyCompatible,
  isNotCompatible,
};
