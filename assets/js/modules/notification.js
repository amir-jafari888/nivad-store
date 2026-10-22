const NOTIFICATION_STATES = Object.freeze({
  IDLE: "idle",
  LOADING: "loading",
  SUCCESS: "success",
  EMPTY: "empty",
  ERROR: "error",
});

const NOTIFICATION_TYPES = Object.freeze({
  UNKNOWN: "unknown",
  PRICE_DROP: "price_drop",
  BACK_IN_STOCK: "back_in_stock",
});

const NOTIFICATION_ENDPOINTS = Object.freeze({
  INDEX: "/notifications",
  UNREAD: "/notifications/unread",
  COUNT: "/notifications/count",
  SHOW: (id) => `/notifications/${encodeURIComponent(id)}`,
  READ: (id) => `/notifications/${encodeURIComponent(id)}/read`,
  READ_ALL: "/notifications/read-all",
});

function normalizeNotification(value) {
  if (!value || typeof value !== "object") {
    return null;
  }

  return {
    id: value.id ?? null,
    type:
      typeof value.type === "string" ? value.type : NOTIFICATION_TYPES.UNKNOWN,
    title: typeof value.title === "string" ? value.title : "",
    message: typeof value.message === "string" ? value.message : "",
    read: Boolean(value.read),
    createdAt: value.created_at ?? value.createdAt ?? null,
    raw: value,
  };
}

async function requestJson(url, options = {}) {
  const response = await fetch(url, {
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      ...(options.headers || {}),
    },
    ...options,
  });

  let payload = null;

  try {
    payload = await response.json();
  } catch {
    payload = null;
  }

  if (!response.ok) {
    const error = new Error(payload?.message || "Notification request failed.");

    error.status = response.status;
    error.payload = payload;

    throw error;
  }

  return payload;
}

async function getNotifications() {
  return requestJson(NOTIFICATION_ENDPOINTS.INDEX);
}

async function getUnreadNotifications() {
  return requestJson(NOTIFICATION_ENDPOINTS.UNREAD);
}

async function getNotificationCount() {
  return requestJson(NOTIFICATION_ENDPOINTS.COUNT);
}

async function getNotification(id) {
  if (id === null || id === undefined) {
    throw new TypeError("Notification id is required.");
  }

  return requestJson(NOTIFICATION_ENDPOINTS.SHOW(id));
}

async function markAsRead(id) {
  if (id === null || id === undefined) {
    throw new TypeError("Notification id is required.");
  }

  return requestJson(NOTIFICATION_ENDPOINTS.READ(id), {
    method: "PATCH",
  });
}

async function markAllAsRead() {
  return requestJson(NOTIFICATION_ENDPOINTS.READ_ALL, {
    method: "PATCH",
  });
}

export {
  NOTIFICATION_STATES,
  NOTIFICATION_TYPES,
  NOTIFICATION_ENDPOINTS,
  normalizeNotification,
  getNotifications,
  getUnreadNotifications,
  getNotificationCount,
  getNotification,
  markAsRead,
  markAllAsRead,
};
