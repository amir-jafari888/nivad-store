import { clearCommerceContext, readCommerceContext, updateCommerceContext } from "./commerce-context.js";

const setupBuilder = () => {
const API_BASE = `${window.NIVAD_BASE_URL || ""}/api/index.php?path=`;
const initialSetupContext = readCommerceContext().setup;

const STEPS = Object.freeze([
  "device",
  "needs",
  "products",
  "setup",
  "budget",
  "final",
]);

const NEEDS = Object.freeze([
  {
    id: "essential",
    label: "ضروری",
    description: "فقط اقلام ضروری برای این ترکیب.",
  },
  {
    id: "recommended",
    label: "پیشنهادی",
    description: "ترکیب متعادل با پیشنهادهای مرتبط.",
  },
  {
    id: "complete",
    label: "ترکیب کامل",
    description: "ترکیب کامل‌تر با اقلام اختیاری.",
  },
]);

const state = {
  currentStep: "device",

  devices: [],
  device: null,

  needs: [],

  candidates: [],
  selectedProducts: [],

  selectedAccessories: [],

  budget: null,

  compatibility: new Map(),

  isLoadingDevices: false,
  isLoadingProducts: false,
  isSubmitting: false,

  error: null,
};

const root = document.querySelector("#setup-builder");

if (!root) {
  return;
}

const deviceOptions = root.querySelector("#setup-device-options");

const deviceLoading = root.querySelector("#setup-device-loading");

const deviceEmpty = root.querySelector("#setup-device-empty");

const deviceError = root.querySelector("#setup-device-error");

const deviceRetry = root.querySelector("#setup-device-retry");

const needsOptions = root.querySelector("#setup-needs-options");

const needsEmpty = root.querySelector("#setup-needs-empty");

const productOptions = root.querySelector("#setup-product-options");

const productsChecking = root.querySelector("#setup-products-checking");

const productsError = root.querySelector("#setup-products-error");

const productsRetry = root.querySelector("#setup-products-retry");

const composition = root.querySelector("#setup-composition");

const compositionState = root.querySelector("#setup-composition-state");

const budgetInput = root.querySelector("#setup-budget-input");

const budgetSummary = root.querySelector("#setup-budget-summary");

const finalSummary = root.querySelector("#setup-final-summary");

const finalError = root.querySelector("#setup-final-error");

const addToCartButton = root.querySelector("#setup-add-to-cart");

const backButton = root.querySelector("#setup-back");

const nextButton = root.querySelector("#setup-next");

const globalError = root.querySelector("#setup-global-error");

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function numberOf(value) {
  const number = Number(value);

  return Number.isFinite(number) ? number : null;
}

function priceOf(item) {
  if (!item) {
    return null;
  }

  return numberOf(item.price ?? item.variant?.price ?? item.variant_price);
}

function variantIdOf(item) {
  const value =
    item?.variant_id ?? item?.variantId ?? item?.variant?.id ?? null;

  const id = Number(value);

  return Number.isInteger(id) && id > 0 ? id : null;
}

function formatPrice(value) {
  const price = numberOf(value);

  if (price === null || price < 0) {
    return "نامشخص";
  }

  return `${price.toLocaleString("fa-IR")} تومان`;
}

function show(element, visible) {
  if (!element) {
    return;
  }

  element.hidden = !visible;
}

function setText(element, value) {
  if (!element) {
    return;
  }

  element.textContent = String(value ?? "");
}

async function request(path) {
  const response = await fetch(`${API_BASE}${encodeURIComponent(path)}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
    credentials: "same-origin",
  });

  let payload = null;

  try {
    payload = await response.json();
  } catch {
    payload = null;
  }

  if (!response.ok) {
    throw new Error(payload?.message || "درخواست با خطا مواجه شد.");
  }

  if (!payload || payload.success !== true) {
    throw new Error(payload?.message || "پاسخ معتبر از سرور دریافت نشد.");
  }

  return payload;
}

function getItems(payload) {
  if (Array.isArray(payload?.data)) {
    return payload.data;
  }

  if (Array.isArray(payload?.data?.items)) {
    return payload.data.items;
  }

  return [];
}

function persistSetupContext() {
  updateCommerceContext({ setup: {
    step: state.currentStep,
    deviceId: Number(state.device?.id || 0),
    needs: state.needs,
    selectedProductIds: state.selectedProducts.map((product) => Number(product.id || 0)),
    selectedVariantIds: state.selectedProducts.map((product) => Number(product.variantId || 0)),
    budget: state.budget,
  } });
}

function setStep(step) {
  if (!STEPS.includes(step)) {
    return;
  }

  state.currentStep = step;
  persistSetupContext();

  root.querySelectorAll("[data-setup-panel]").forEach((panel) => {
    panel.hidden = panel.dataset.setupPanel !== step;
  });

  root.querySelectorAll("[data-setup-step]").forEach((button) => {
    const isCurrent = button.dataset.setupStep === step;

    button.setAttribute("aria-current", isCurrent ? "step" : "false");

    button.disabled = !canNavigateTo(button.dataset.setupStep);
  });

  updateNavigation();

  if (step === "needs") {
    renderNeeds();
  }

  if (step === "setup") {
    renderComposition();
  }

  if (step === "budget") {
    renderBudget();
  }

  if (step === "final") {
    renderFinal();
  }
}

function canNavigateTo(step) {
  const index = STEPS.indexOf(step);

  const currentIndex = STEPS.indexOf(state.currentStep);

  if (index < 0) {
    return false;
  }

  if (index === 0) {
    return true;
  }

  if (!state.device) {
    return false;
  }

  if (index >= 2 && state.needs.length === 0) {
    return false;
  }

  if (index >= 3 && state.selectedProducts.length === 0) {
    return false;
  }

  return index <= currentIndex + 1;
}

function updateNavigation() {
  const currentIndex = STEPS.indexOf(state.currentStep);

  if (backButton) {
    backButton.hidden = currentIndex <= 0;
  }

  if (nextButton) {
    nextButton.hidden = state.currentStep === "final";

    if (state.currentStep === "device") {
      nextButton.disabled = !state.device;
    } else if (state.currentStep === "needs") {
      nextButton.disabled = state.needs.length === 0;
    } else if (state.currentStep === "products") {
      nextButton.disabled = state.selectedProducts.length === 0;
    } else if (state.currentStep === "setup") {
      nextButton.disabled = !isSetupComplete();
    } else {
      nextButton.disabled = false;
    }
  }
}

function renderDevices() {
  if (!deviceOptions) {
    return;
  }

  deviceOptions.innerHTML = "";

  state.devices.forEach((device) => {
    const button = document.createElement("button");

    button.type = "button";
    button.className = "setup-builder__device";

    button.dataset.deviceId = String(device.id);

    button.setAttribute(
      "aria-pressed",
      state.device?.id === device.id ? "true" : "false",
    );

    button.innerHTML = `
      <span class="setup-builder__device-name">
        ${escapeHtml(device.name || "")}
      </span>
    `;

    button.addEventListener("click", () => selectDevice(device));

    deviceOptions.appendChild(button);
  });
}

function renderNeeds() {
  if (!needsOptions) {
    return;
  }

  needsOptions.innerHTML = "";

  NEEDS.forEach((need) => {
    const button = document.createElement("button");

    const selected = state.needs.includes(need.id);

    button.type = "button";
    button.className = "setup-builder__need";

    button.setAttribute("aria-pressed", selected ? "true" : "false");

    button.innerHTML = `
      <strong>
        ${escapeHtml(need.label)}
      </strong>

      <span>
        ${escapeHtml(need.description)}
      </span>
    `;

    button.addEventListener("click", () => toggleNeed(need.id));

    needsOptions.appendChild(button);
  });

  show(needsEmpty, NEEDS.length === 0);

  updateNavigation();
}

function toggleNeed(needId) {
  if (state.needs.includes(needId)) {
    state.needs = state.needs.filter((id) => id !== needId);
  } else {
    state.needs.push(needId);
  }

  state.selectedProducts = [];
  state.compatibility.clear();

  renderNeeds();
  renderProducts();

  updateNavigation();
  persistSetupContext();
}

async function selectDevice(device, { restore = false } = {}) {
  if (!device || state.isLoadingProducts) {
    return;
  }

  const changed = state.device?.id !== device.id;

  state.device = {
    id: Number(device.id),
    name: String(device.name ?? ""),
    slug: String(device.slug ?? ""),
  };

  if (changed) {
    state.needs = [];
    state.candidates = [];
    state.selectedProducts = [];
    state.selectedAccessories = [];
    state.compatibility.clear();
    state.budget = null;

    if (budgetInput) {
      budgetInput.value = "";
    }
  }

  renderDevices();
  renderNeeds();
  renderProducts();
  renderComposition();

  updateNavigation();
  persistSetupContext();

  if (changed) {
    await loadProducts({ restore });
  }
}

async function loadDevices() {
  show(deviceError, false);
  show(deviceEmpty, false);
  show(deviceRetry, false);
  show(deviceLoading, true);

  state.isLoadingDevices = true;

  try {
    const payload = await request("/setup/devices");

    state.devices = getItems(payload);

    show(deviceLoading, false);

    if (state.devices.length === 0) {
      show(deviceEmpty, true);
      show(deviceRetry, true);
      return;
    }

    renderDevices();

    const savedDevice = state.devices.find((item) => Number(item.id) === Number(initialSetupContext.deviceId));
    if (initialSetupContext.deviceId && !savedDevice) clearCommerceContext("setup");
    await selectDevice(savedDevice || state.devices[0], { restore: Boolean(savedDevice) });
    if (savedDevice) {
      const savedStep = STEPS.includes(initialSetupContext.step) ? initialSetupContext.step : "device";
      setStep(canNavigateTo(savedStep) ? savedStep : "device");
    }
  } catch (error) {
    state.error = error;

    show(deviceLoading, false);
    show(deviceError, true);
    show(deviceRetry, true);

    setText(deviceError, error?.message || "دریافت دستگاه‌ها انجام نشد.");
  } finally {
    state.isLoadingDevices = false;
  }
}

function normalizeProduct(product) {
  if (!product) {
    return null;
  }

  const id = Number(product.product_id ?? product.id);

  if (!Number.isInteger(id) || id < 1) {
    return null;
  }

  return {
    id,

    name: String(product.name ?? ""),

    slug: String(product.slug ?? ""),

    shortDescription: String(product.short_description ?? ""),

    relationType: String(product.relation_type ?? "unknown"),

    variantId: variantIdOf(product),

    variantName: String(product.variant_name ?? ""),

    price: priceOf(product),

    compareAtPrice: numberOf(product.compare_at_price),

    currency: String(product.currency ?? ""),
  };
}

async function loadProducts({ restore = false } = {}) {
  if (!state.device?.id) {
    return;
  }

  state.isLoadingProducts = true;

  show(productsError, false);
  show(productsRetry, false);
  show(productsChecking, true);

  try {
    const payload = await request(
      `/setup/devices/${encodeURIComponent(state.device.id)}/products`,
    );

    state.candidates = getItems(payload).map(normalizeProduct).filter(Boolean);

    state.selectedProducts = [];
    if (restore) {
      const savedProductIds = Array.isArray(initialSetupContext.selectedProductIds) ? initialSetupContext.selectedProductIds : [];
      const savedVariantIds = Array.isArray(initialSetupContext.selectedVariantIds) ? initialSetupContext.selectedVariantIds : [];
      state.selectedProducts = savedProductIds.map((productId, index) => {
        const variantId = Number(savedVariantIds[index] || 0);
        return state.candidates.find((item) => Number(item.id) === Number(productId) && variantId > 0 && Number(item.variantId) === variantId && item.price !== null) || null;
      }).filter(Boolean);
      state.needs = Array.isArray(initialSetupContext.needs) ? initialSetupContext.needs.filter((id) => NEEDS.some((need) => need.id === id)) : [];
      const savedBudget = numberOf(initialSetupContext.budget);
      state.budget = savedBudget !== null && savedBudget >= 0 ? savedBudget : null;
      if (budgetInput) budgetInput.value = state.budget === null ? "" : String(state.budget);
      renderNeeds();
      renderComposition();
    }

    show(productsChecking, false);

    renderProducts();
    updateNavigation();
  } catch (error) {
    state.error = error;

    show(productsChecking, false);

    show(productsError, true);

    show(productsRetry, true);

    setText(productsError, error?.message || "دریافت محصولات انجام نشد.");
  } finally {
    state.isLoadingProducts = false;
    updateNavigation();
  }
}

function getCompatibilityState(product) {
  /*
   * Phase 16 deliberately does not fabricate
   * compatibility certainty.
   *
   * Setup Product relation is real.
   * Compatibility Device mapping is not proven here.
   */

  if (product.relationType === "recommended") {
    return {
      key: "checking",
      label: "در حال بررسی",
    };
  }

  if (product.relationType === "compatible") {
    return {
      key: "checking",
      label: "در حال بررسی",
    };
  }

  if (product.relationType === "optional") {
    return {
      key: "unknown",
      label: "سازگاری نامشخص",
    };
  }

  return {
    key: "unknown",
    label: "سازگاری نامشخص",
  };
}

function renderProducts() {
  if (!productOptions) {
    return;
  }

  productOptions.innerHTML = "";

  state.candidates.forEach((product) => {
    const selected = state.selectedProducts.some(
      (item) => item.id === product.id,
    );

    const compatibility = getCompatibilityState(product);

    const button = document.createElement("button");

    button.type = "button";

    button.className = "setup-builder__product";

    button.setAttribute("aria-pressed", selected ? "true" : "false");

    button.dataset.productId = String(product.id);

    button.innerHTML = `
        <span>
          ${escapeHtml(product.name)}
        </span>

        <small>
          ${escapeHtml(compatibility.label)}
        </small>

        <strong>
          ${product.price !== null ? formatPrice(product.price) : "قیمت نامشخص"}
        </strong>
      `;

    button.addEventListener("click", () => toggleProduct(product));

    productOptions.appendChild(button);
  });

  updateNavigation();
}

function toggleProduct(product) {
  const exists = state.selectedProducts.some((item) => item.id === product.id);

  if (exists) {
    state.selectedProducts = state.selectedProducts.filter(
      (item) => item.id !== product.id,
    );
  } else {
    state.selectedProducts.push(product);
  }

  renderProducts();
  renderComposition();
  updateNavigation();
  persistSetupContext();
}

function removeProduct(productId) {
  state.selectedProducts = state.selectedProducts.filter(
    (item) => item.id !== productId,
  );

  renderProducts();
  renderComposition();
  updateNavigation();
  persistSetupContext();
}

function replaceProduct(productId, replacement) {
  state.selectedProducts = state.selectedProducts.map((item) =>
    item.id === productId ? replacement : item,
  );

  renderProducts();
  renderComposition();
  updateNavigation();
  persistSetupContext();
}

function isSetupComplete() {
  if (!state.device) {
    return false;
  }

  if (state.selectedProducts.length === 0) {
    return false;
  }

  return state.selectedProducts.every(
    (product) => product.variantId !== null && product.price !== null,
  );
}

function calculateTotal() {
  const productTotal = state.selectedProducts.reduce(
    (total, product) => total + (product.price ?? 0),
    0,
  );

  const accessoryTotal = state.selectedAccessories.reduce(
    (total, accessory) => total + (priceOf(accessory) ?? 0),
    0,
  );

  return productTotal + accessoryTotal;
}

function renderComposition() {
  if (!composition) {
    return;
  }

  const items = [
    ...state.selectedProducts.map((product) => ({
      type: "product",
      id: product.id,
      name: product.name,
      price: product.price,
    })),

    ...state.selectedAccessories.map((accessory) => ({
      type: "accessory",
      id: accessory.id,
      name: accessory.name,
      price: priceOf(accessory),
    })),
  ];

  if (items.length === 0) {
    composition.innerHTML = "";

    show(compositionState, true);

    setText(compositionState, "هنوز محصولی به ترکیب اضافه نشده است.");

    return;
  }

  show(compositionState, false);

  composition.innerHTML = items
    .map(
      (item) => `
          <article
            class="setup-builder__composition-item">

            <div>
              <strong>
                ${escapeHtml(item.name)}
              </strong>

              <span>
                ${item.price !== null ? formatPrice(item.price) : "قیمت نامشخص"}
              </span>
            </div>

            ${
              item.type === "product"
                ? `
                  <button
                    type="button"
                    data-remove-product="${item.id}">
                    حذف
                  </button>
                `
                : ""
            }
          </article>
        `,
    )
    .join("");

  composition.querySelectorAll("[data-remove-product]").forEach((button) => {
    button.addEventListener("click", () =>
      removeProduct(Number(button.dataset.removeProduct)),
    );
  });
}

function renderBudget() {
  if (!budgetSummary) {
    return;
  }

  const total = calculateTotal();

  if (state.budget === null) {
    budgetSummary.innerHTML = `
      <div>
        <span>مجموع فعلی</span>
        <strong>
          ${formatPrice(total)}
        </strong>
      </div>

      <p>
        بودجه‌ای وارد نشده است.
      </p>
    `;

    return;
  }

  const remaining = state.budget - total;

  const status = remaining >= 0 ? "در محدوده بودجه" : "بیشتر از بودجه";

  budgetSummary.innerHTML = `
    <div>
      <span>بودجه</span>
      <strong>
        ${formatPrice(state.budget)}
      </strong>
    </div>

    <div>
      <span>مجموع فعلی</span>
      <strong>
        ${formatPrice(total)}
      </strong>
    </div>

    <div>
      <span>مانده</span>
      <strong>
        ${formatPrice(Math.max(0, remaining))}
      </strong>
    </div>

    <div>
      <span>وضعیت</span>
      <strong>
        ${escapeHtml(status)}
      </strong>
    </div>
  `;
}

function renderFinal() {
  if (!finalSummary) {
    return;
  }

  const total = calculateTotal();

  finalSummary.innerHTML = `
    <div>
      <span>دستگاه</span>
      <strong>
        ${escapeHtml(state.device?.name || "نامشخص")}
      </strong>
    </div>

    <div>
      <span>محصولات</span>
      <strong>
        ${state.selectedProducts.length}
      </strong>
    </div>

    <div>
      <span>مجموع</span>
      <strong>
        ${formatPrice(total)}
      </strong>
    </div>
  `;

  const valid = isSetupComplete();

  if (addToCartButton) {
    addToCartButton.disabled = state.isSubmitting || !valid;
  }

  if (!valid) {
    show(finalError, true);

    setText(
      finalError,
      "برای خرید، همه محصولات انتخاب‌شده باید Variant و قیمت معتبر داشته باشند.",
    );
  } else {
    show(finalError, false);
  }
}

function nextStep() {
  const index = STEPS.indexOf(state.currentStep);

  if (index < 0 || index >= STEPS.length - 1) {
    return;
  }

  if (state.currentStep === "device" && !state.device) {
    return;
  }

  if (state.currentStep === "needs" && state.needs.length === 0) {
    return;
  }

  if (state.currentStep === "products" && state.selectedProducts.length === 0) {
    return;
  }

  if (state.currentStep === "setup" && !isSetupComplete()) {
    return;
  }

  setStep(STEPS[index + 1]);
}

function previousStep() {
  const index = STEPS.indexOf(state.currentStep);

  if (index <= 0) {
    return;
  }

  setStep(STEPS[index - 1]);
}

async function addSetupToCart() {
  if (!isSetupComplete() || state.isSubmitting) {
    return;
  }

  const cart = window.digitalStoreCart;

  if (!cart || typeof cart.add !== "function") {
    show(finalError, true);

    setText(finalError, "ماژول سبد خرید در دسترس نیست.");

    return;
  }

  state.isSubmitting = true;

  renderFinal();

  try {
    for (const product of state.selectedProducts) {
      cart.add({
        id: String(product.id),
        variantId: product.variantId,
        name: product.name,
        price: product.price,
      });
    }

    show(finalError, false);
  } catch (error) {
    show(finalError, true);

    setText(finalError, error?.message || "افزودن ستاپ به سبد انجام نشد.");
  } finally {
    state.isSubmitting = false;

    renderFinal();
  }
}

budgetInput?.addEventListener("input", () => {
  const value = numberOf(budgetInput.value);

  state.budget = value !== null && value >= 0 ? value : null;
  persistSetupContext();

  renderBudget();
});

nextButton?.addEventListener("click", nextStep);

backButton?.addEventListener("click", previousStep);

addToCartButton?.addEventListener("click", addSetupToCart);

deviceRetry?.addEventListener("click", loadDevices);

productsRetry?.addEventListener("click", loadProducts);

root.querySelectorAll("[data-setup-step]").forEach((button) => {
  button.addEventListener("click", () => setStep(button.dataset.setupStep));
});

setStep("device");

loadDevices();

};

export default setupBuilder;
