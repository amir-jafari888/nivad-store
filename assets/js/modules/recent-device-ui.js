import {
  RECENT_DEVICE_STATES,
  getDeviceLabel,
  readPersistedDevice,
} from "./recent-device.js";

function initializeRecentDevice() {
  const roots = document.querySelectorAll("[data-recent-device]");

  if (!roots.length) {
    return;
  }

  roots.forEach((root) => {
    renderState(root, RECENT_DEVICE_STATES.EMPTY);

    restore(root);
  });
}

function restore(root) {
  try {
    renderState(root, "checking");

    const result = readPersistedDevice();

    if (result?.contract === "USER_STATE_CONTRACT_REQUIRED") {
      renderState(root, RECENT_DEVICE_STATES.EMPTY);

      return;
    }

    if (result?.state === RECENT_DEVICE_STATES.READY && result.device) {
      renderDevice(root, result.device);

      return;
    }

    renderState(root, RECENT_DEVICE_STATES.EMPTY);
  } catch (error) {
    renderState(root, RECENT_DEVICE_STATES.ERROR);
  }
}

function renderDevice(root, device) {
  const content = root.querySelector("[data-recent-device-content]");

  const empty = root.querySelector("[data-recent-device-empty]");

  const name = root.querySelector("[data-recent-device-name]");

  if (name) {
    name.textContent = getDeviceLabel(device);
  }

  if (content) {
    content.hidden = false;
  }

  if (empty) {
    empty.hidden = true;
  }

  root.dataset.recentDeviceState = RECENT_DEVICE_STATES.READY;
}

function renderState(root, state) {
  const content = root.querySelector("[data-recent-device-content]");

  const empty = root.querySelector("[data-recent-device-empty]");

  if (state === RECENT_DEVICE_STATES.READY) {
    return;
  }

  if (content) {
    content.hidden = true;
  }

  if (empty) {
    empty.hidden = false;
  }

  root.dataset.recentDeviceState = state;
}

export default initializeRecentDevice;

export { initializeRecentDevice, renderDevice, renderState };
