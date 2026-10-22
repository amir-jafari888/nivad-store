const STORAGE_KEY = "nivad-commerce-context";
const VERSION = 1;
const SETUP_STEPS = new Set(["device", "needs", "products", "setup", "budget", "final"]);
const SETUP_NEEDS = new Set(["essential", "recommended", "complete"]);

const positiveId = (value) => {
  const number = Number(value);
  return Number.isSafeInteger(number) && number > 0 ? number : 0;
};
const cleanText = (value, max = 180) => typeof value === "string" ? value.trim().slice(0, max) : "";
const safePath = (value) => {
  const path = cleanText(value, 500);
  return path.startsWith("/") && !path.startsWith("//") ? path : "";
};
const emptyContext = () => ({
  version: VERSION, updatedAt: 0,
  search: { query: "", returnPath: "" }, finder: { intent: "", budget: "" },
  product: { id: 0, slug: "", variantId: 0 }, compatibility: { deviceId: 0 },
  setup: { step: "device", deviceId: 0, needs: [], selectedProductIds: [], selectedVariantIds: [], budget: null },
});
const normalize = (value) => {
  const result = emptyContext();
  if (!value || typeof value !== "object" || value.version !== VERSION) return result;
  result.updatedAt = Number.isFinite(Number(value.updatedAt)) ? Number(value.updatedAt) : 0;
  if (value.search && typeof value.search === "object") {
    result.search.query = cleanText(value.search.query, 160);
    result.search.returnPath = safePath(value.search.returnPath);
  }
  if (value.finder && typeof value.finder === "object") {
    result.finder.intent = cleanText(value.finder.intent, 32);
    result.finder.budget = cleanText(value.finder.budget, 16);
  }
  if (value.product && typeof value.product === "object") {
    result.product.id = positiveId(value.product.id);
    result.product.slug = cleanText(value.product.slug, 180);
    result.product.variantId = positiveId(value.product.variantId);
  }
  if (value.compatibility && typeof value.compatibility === "object") result.compatibility.deviceId = positiveId(value.compatibility.deviceId);
  if (value.setup && typeof value.setup === "object") {
    result.setup.step = SETUP_STEPS.has(value.setup.step) ? value.setup.step : "device";
    result.setup.deviceId = positiveId(value.setup.deviceId);
    result.setup.needs = Array.isArray(value.setup.needs) ? [...new Set(value.setup.needs.filter((item) => SETUP_NEEDS.has(item)))] : [];
    result.setup.selectedProductIds = Array.isArray(value.setup.selectedProductIds) ? [...new Set(value.setup.selectedProductIds.map(positiveId).filter(Boolean))].slice(0, 30) : [];
    result.setup.selectedVariantIds = Array.isArray(value.setup.selectedVariantIds) ? [...new Set(value.setup.selectedVariantIds.map(positiveId).filter(Boolean))].slice(0, 30) : [];
    const budget = value.setup.budget;
    result.setup.budget = budget === null || budget === "" || budget === undefined ? null : Number.isFinite(Number(budget)) && Number(budget) >= 0 ? Number(budget) : null;
  }
  return result;
};
export const readCommerceContext = () => {
  try { return normalize(JSON.parse(window.sessionStorage.getItem(STORAGE_KEY) || "null")); }
  catch { return emptyContext(); }
};
export const updateCommerceContext = (patch = {}) => {
  const current = readCommerceContext();
  const next = { ...current };
  for (const section of ["search", "finder", "product", "compatibility", "setup"]) {
    if (patch[section] && typeof patch[section] === "object" && !Array.isArray(patch[section])) next[section] = { ...current[section], ...patch[section] };
  }
  next.version = VERSION;
  next.updatedAt = Date.now();
  const normalized = normalize(next);
  try { window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(normalized)); return true; }
  catch { return false; }
};
export const clearCommerceContext = (section = "") => {
  if (!section) {
    try { window.sessionStorage.removeItem(STORAGE_KEY); return true; }
    catch { return false; }
  }
  if (!["search", "finder", "product", "compatibility", "setup"].includes(section)) return false;
  const current = readCommerceContext();
  current[section] = emptyContext()[section];
  current.updatedAt = Date.now();
  try { window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(current)); return true; }
  catch { return false; }
};
export default { readCommerceContext, updateCommerceContext, clearCommerceContext };
