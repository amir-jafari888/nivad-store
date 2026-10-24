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
    read: Boolean(value.isRead ?? value.is_read ?? value.read),
    createdAt: value.created_at ?? value.createdAt ?? null,
    raw: value,
  };
}

const notificationApiUrl = (path) => {
  const baseUrl = String(window.NIVAD_BASE_URL || "").replace(/\/$/, "");
  const url = new URL(`${baseUrl}/api/index.php`, window.location.origin);
  url.searchParams.set("path", path);
  return url.toString();
};

async function requestJson(path, options = {}) {
  const method = String(options.method || "GET").toUpperCase();
  const headers = {
    Accept: "application/json",
    ...(options.headers || {}),
  };

  let body = options.body;

  if (!["GET", "HEAD"].includes(method)) {
    const csrfResponse = await fetch(notificationApiUrl("/auth/csrf"), {
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    });
    const csrfPayload = await csrfResponse.json().catch(() => null);
    const token = csrfPayload?.data?.token;

    if (!csrfResponse.ok || typeof token !== "string" || token.length === 0) {
      throw new Error(csrfPayload?.message || "دریافت توکن امنیتی انجام نشد. صفحه را تازه‌سازی کنید.");
    }

    headers["X-CSRF-TOKEN"] = token;
    headers["Content-Type"] = "application/json";
    if (body === undefined) body = "{}";
  }

  let response;

  try {
    response = await fetch(notificationApiUrl(path), {
      ...options,
      method,
      body,
      credentials: "same-origin",
      headers,
    });
  } catch {
    throw new Error("ارتباط با سرور برقرار نشد. اتصال را بررسی کنید.");
  }

  const payload = await response.json().catch(() => null);

  if (!payload || typeof payload !== "object") {
    throw new Error("پاسخ سرور قابل پردازش نیست.");
  }

  if (!response.ok || payload.success === false) {
    const error = new Error(payload.message || "عملیات اعلان انجام نشد.");
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

const initializeNotifications = () => {
  const root = document.querySelector("[data-account-notifications]");
  if (!root || root.dataset.notificationsInitialized === "true") return;
  root.dataset.notificationsInitialized = "true";

  const list = root.querySelector("[data-notifications-list]");
  const readAllButton = root.querySelector("[data-notifications-read-all]");
  if (!list) return;

  let busy = false;
  const setMessage = (text, isError = false) => {
    list.setAttribute("role", isError ? "alert" : "status");
    list.setAttribute("aria-live", "polite");
    list.textContent = text;
  };

  const render = (items) => {
    list.replaceChildren();
    if (!items.length) {
      setMessage("اعلانی برای نمایش وجود ندارد.");
      return;
    }

    const fragment = document.createDocumentFragment();
    items.forEach((raw) => {
      const item = normalizeNotification(raw);
      if (!item) return;

      const article = document.createElement("article");
      article.className = "account-notification";
      article.dataset.notificationId = String(item.id ?? "");

      const title = document.createElement("h2");
      title.textContent = item.title || "اعلان";
      const body = document.createElement("p");
      body.textContent = item.message || "جزئیات بیشتری برای این اعلان موجود نیست.";
      const time = document.createElement("time");
      if (item.createdAt) {
        const parsed = new Date(item.createdAt);
        if (!Number.isNaN(parsed.getTime())) time.dateTime = parsed.toISOString();
        time.textContent = String(item.createdAt);
      }
      const status = document.createElement("p");
      status.className = "account-notification__status";
      status.textContent = item.read ? "خوانده‌شده" : "خوانده‌نشده";
      article.append(title, body);
      if (item.createdAt) article.append(time);
      article.append(status);

      if (!item.read && item.id !== null && item.id !== undefined) {
        const button = document.createElement("button");
        button.type = "button";
        button.dataset.notificationRead = String(item.id);
        button.textContent = "علامت‌گذاری به‌عنوان خوانده‌شده";
        button.addEventListener("click", async () => {
          if (busy || button.disabled) return;
          busy = true;
          button.disabled = true;
          try {
            await markAsRead(item.id);
            await load();
          } catch (error) {
            setMessage(error.message || "به‌روزرسانی اعلان انجام نشد.", true);
          } finally {
            busy = false;
            button.disabled = false;
          }
        });
        article.append(button);
      }
      fragment.append(article);
    });

    if (!fragment.childNodes.length) {
      setMessage("اعلانی برای نمایش وجود ندارد.");
      return;
    }
    list.replaceChildren(fragment);
    list.removeAttribute("role");
  };

  const load = async () => {
    list.setAttribute("aria-busy", "true");
    setMessage("در حال دریافت اعلان‌ها...");
    if (readAllButton) readAllButton.disabled = true;
    try {
      const payload = await getNotifications();
      const items = payload?.data?.items ?? payload?.items;
      if (!Array.isArray(items)) throw new Error("ساختار پاسخ اعلان‌ها معتبر نیست.");
      render(items);
    } catch (error) {
      list.replaceChildren();
      const message = document.createElement("p");
      message.textContent = error.message || "دریافت اعلان‌ها انجام نشد.";
      const retry = document.createElement("button");
      retry.type = "button";
      retry.textContent = "تلاش دوباره";
      retry.addEventListener("click", load, { once: true });
      list.append(message, retry);
      list.setAttribute("role", "alert");
      list.setAttribute("aria-live", "polite");
    } finally {
      list.removeAttribute("aria-busy");
      if (readAllButton) readAllButton.disabled = false;
    }
  };

  readAllButton?.addEventListener("click", async () => {
    if (busy || readAllButton.disabled) return;
    busy = true;
    readAllButton.disabled = true;
    try {
      await markAllAsRead();
      await load();
    } catch (error) {
      setMessage(error.message || "علامت‌گذاری اعلان‌ها انجام نشد.", true);
    } finally {
      busy = false;
      readAllButton.disabled = false;
    }
  });

  load();
};

export default initializeNotifications;
