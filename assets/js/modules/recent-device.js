const RECENT_DEVICE_STATES = Object.freeze({
  EMPTY: "empty",
  READY: "ready",
  INVALID: "invalid",
  ERROR: "error",
});

const RECENT_DEVICE_FIELDS = Object.freeze([
  "id",
  "name",
  "brand",
  "model",
  "slug",
]);

function normalizeDevice(device) {
  if (!device || typeof device !== "object") {
    return null;
  }

  const id = device.id ?? device.device_id ?? null;

  if (id === null || id === undefined || String(id).trim() === "") {
    return null;
  }

  return {
    id: String(id),
    name: String(device.name ?? device.model ?? device.title ?? "").trim(),
    brand: String(device.brand ?? device.brand_name ?? "").trim(),
    model: String(device.model ?? device.model_name ?? "").trim(),
    slug: String(device.slug ?? "").trim(),
  };
}

function isValidDevice(device) {
  return Boolean(normalizeDevice(device));
}

function createDeviceState(device) {
  const normalized = normalizeDevice(device);

  if (!normalized) {
    return {
      state: RECENT_DEVICE_STATES.EMPTY,
      device: null,
    };
  }

  return {
    state: RECENT_DEVICE_STATES.READY,
    device: normalized,
  };
}

function getDeviceLabel(device) {
  const normalized = normalizeDevice(device);

  if (!normalized) {
    return "";
  }

  const parts = [normalized.brand, normalized.model].filter(Boolean);

  if (parts.length > 0) {
    return parts.join(" ");
  }

  return normalized.name;
}

/*
 * IMPORTANT:
 * No storage key or persistence mechanism is invented here.
 *
 * Phase 24 requires the real project User State contract
 * before persistence can be connected.
 */
function readPersistedDevice() {
  return {
    state: RECENT_DEVICE_STATES.EMPTY,
    device: null,
    contract: "USER_STATE_CONTRACT_REQUIRED",
  };
}

function saveSelectedDevice() {
  return {
    state: RECENT_DEVICE_STATES.ERROR,
    device: null,
    contract: "USER_STATE_CONTRACT_REQUIRED",
  };
}

function clearSelectedDevice() {
  return {
    state: RECENT_DEVICE_STATES.ERROR,
    device: null,
    contract: "USER_STATE_CONTRACT_REQUIRED",
  };
}

export {
  RECENT_DEVICE_STATES,
  RECENT_DEVICE_FIELDS,
  normalizeDevice,
  isValidDevice,
  createDeviceState,
  getDeviceLabel,
  readPersistedDevice,
  saveSelectedDevice,
  clearSelectedDevice,
};

export default {
  RECENT_DEVICE_STATES,
  RECENT_DEVICE_FIELDS,
  normalizeDevice,
  isValidDevice,
  createDeviceState,
  getDeviceLabel,
  readPersistedDevice,
  saveSelectedDevice,
  clearSelectedDevice,
};
