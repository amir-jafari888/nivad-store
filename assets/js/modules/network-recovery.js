const REQUEST_TIMEOUT_MS = 15000;
const MAX_SAFE_RETRIES = 1;
const RETRYABLE_STATUS = new Set([502, 503, 504]);
const SESSION_SCOPED_PATH = /\/(account|orders|checkout|cart|wishlist)(\/|$)/i;
const NETWORK_EVENT = "nivad:network-state";

const emit = (name, detail = {}) => {
  document.dispatchEvent(new CustomEvent(name, { detail }));
};

const requestDetails = (input, init = {}) => {
  const url = input instanceof Request ? input.url : String(input);
  const method = String(init.method || (input instanceof Request ? input.method : "GET")).toUpperCase();
  let parsed;
  try {
    parsed = new URL(url, window.location.href);
  } catch {
    parsed = new URL(window.location.href);
  }
  return { url: parsed, method, externalSignal: init.signal || (input instanceof Request ? input.signal : null) };
};

const apiPathFromUrl = (url) => {
  try {
    return new URLSearchParams(url.search).get("path") || url.pathname;
  } catch {
    return url.pathname;
  }
};

const dispatchResponseState = (response, details) => {
  if (!response.ok) {
    const category = response.status >= 500 ? "server" : response.status >= 400 ? "client" : "unknown";
    emit("nivad:api-failure", { status: response.status, category, path: apiPathFromUrl(details.url) });
  }

  if (response.status === 401 || response.status === 419) {
    const apiPath = apiPathFromUrl(details.url);
    const isLoginEndpoint = /\/auth\/(login|register|forgot-password|reset-password)(\/|$)/i.test(apiPath);
    if (response.status === 419 || (!isLoginEndpoint && SESSION_SCOPED_PATH.test(apiPath))) {
      emit("nivad:session-expired", { status: response.status, path: apiPath });
      showNotice("session", "نشست کاربری ممکن است منقضی شده باشد. برای ادامه، وضعیت ورود را بررسی کنید.", {
        actionLabel: "ورود به حساب",
        actionHref: `${String(window.NIVAD_BASE_URL || "").replace(/\/$/, "")}/pages/auth/login.php`,
      });
    }
    return;
  }

  if (RETRYABLE_STATUS.has(response.status) || response.status >= 500) {
    emit("nivad:server-unavailable", { status: response.status, path: apiPathFromUrl(details.url) });
    showNotice("server", "سرور موقتاً پاسخ‌گو نیست. چند لحظه دیگر دوباره تلاش کنید.");
  }
};

let notice;
let noticeTimer = 0;
let originalFetch;

const removeNotice = () => {
  if (noticeTimer) window.clearTimeout(noticeTimer);
  noticeTimer = 0;
  notice?.remove();
  notice = null;
};

const showNotice = (type, message, action = {}) => {
  if (!document.body) return;
  if (!notice) {
    notice = document.createElement("aside");
    notice.className = "network-recovery-notice";
    notice.setAttribute("role", "status");
    notice.setAttribute("aria-live", "polite");
    notice.setAttribute("aria-atomic", "true");
    notice.dataset.networkNotice = "true";
    document.body.prepend(notice);
  }

  notice.dataset.state = type;
  notice.replaceChildren();

  const text = document.createElement("p");
  text.className = "network-recovery-notice__message";
  text.textContent = message;
  notice.append(text);

  if (action.actionHref && action.actionLabel) {
    const link = document.createElement("a");
    link.className = "network-recovery-notice__action";
    link.href = action.actionHref;
    link.textContent = action.actionLabel;
    notice.append(link);
  }

  const dismiss = document.createElement("button");
  dismiss.className = "network-recovery-notice__dismiss";
  dismiss.type = "button";
  dismiss.textContent = "بستن پیام";
  dismiss.setAttribute("aria-label", "بستن پیام وضعیت اتصال");
  dismiss.addEventListener("click", removeNotice, { once: true });
  notice.append(dismiss);

  if (type === "online") {
    noticeTimer = window.setTimeout(removeNotice, 4000);
  }
};

const isSafeToRetry = (details, attempt, externalSignal) =>
  attempt < MAX_SAFE_RETRIES &&
  (details.method === "GET" || details.method === "HEAD") &&
  !externalSignal?.aborted;

const fetchWithTimeout = async (input, init, details, attempt) => {
  const controller = new AbortController();
  const externalSignal = details.externalSignal;
  let timedOut = false;
  const abortFromCaller = () => controller.abort(externalSignal?.reason);

  if (externalSignal?.aborted) {
    controller.abort(externalSignal.reason);
  } else {
    externalSignal?.addEventListener("abort", abortFromCaller, { once: true });
  }

  const timeoutId = window.setTimeout(() => {
    timedOut = true;
    controller.abort(new DOMException("Request timed out", "TimeoutError"));
  }, REQUEST_TIMEOUT_MS);

  try {
    const response = await originalFetch(input, { ...init, signal: controller.signal });
    dispatchResponseState(response, details);
    if (response.ok && notice?.dataset.state === "server") {
      showNotice("online", "ارتباط با سرور برقرار شد. می‌توانید دوباره تلاش کنید.");
    }

    if (RETRYABLE_STATUS.has(response.status) && isSafeToRetry(details, attempt, externalSignal)) {
      await new Promise((resolve) => window.setTimeout(resolve, 350));
      return fetchWithTimeout(input, init, details, attempt + 1);
    }
    return response;
  } catch (error) {
    if (externalSignal?.aborted) throw error;

    if (timedOut && isSafeToRetry(details, attempt, externalSignal)) {
      await new Promise((resolve) => window.setTimeout(resolve, 350));
      return fetchWithTimeout(input, init, details, attempt + 1);
    }

    if (timedOut) {
      const timeoutError = new DOMException("The request exceeded the configured timeout.", "TimeoutError");
      emit("nivad:request-timeout", { method: details.method, url: details.url.href });
      showNotice("timeout", "درخواست بیش از حد طول کشید. اتصال را بررسی کنید و دوباره تلاش کنید.");
      throw timeoutError;
    }

    if (isSafeToRetry(details, attempt, externalSignal)) {
      await new Promise((resolve) => window.setTimeout(resolve, 350));
      return fetchWithTimeout(input, init, details, attempt + 1);
    }

    emit("nivad:network-error", { method: details.method, url: details.url.href, offline: navigator.onLine === false });
    if (navigator.onLine === false) {
      showNotice("offline", "اتصال اینترنت قطع است. پس از بازگشت شبکه، دوباره تلاش کنید.");
    } else {
      showNotice("connection", "ارتباط با سرور برقرار نشد. اتصال را بررسی کنید و دوباره تلاش کنید.");
    }
    throw error;
  } finally {
    window.clearTimeout(timeoutId);
    externalSignal?.removeEventListener("abort", abortFromCaller);
  }
};

const installFetchResilience = () => {
  if (window.__NIVAD_FETCH_RESILIENCE__) return;
  originalFetch = window.fetch.bind(window);
  window.__NIVAD_FETCH_RESILIENCE__ = true;

  window.fetch = (input, init = {}) => {
    const details = requestDetails(input, init);
    return fetchWithTimeout(input, init, details, 0);
  };
};

const initNetworkRecovery = () => {
  if (window.__NIVAD_NETWORK_RECOVERY__) return;
  window.__NIVAD_NETWORK_RECOVERY__ = true;

  const updateNetworkState = () => {
    const online = navigator.onLine !== false;
    emit(NETWORK_EVENT, { online });
    if (!online) {
      showNotice("offline", "اتصال اینترنت قطع است. بعضی قابلیت‌ها تا بازگشت شبکه در دسترس نیستند.");
      return;
    }
    if (notice?.dataset.state === "offline" || notice?.dataset.state === "connection") {
      showNotice("online", "اتصال شبکه برقرار شد. می‌توانید دوباره تلاش کنید.");
    }
  };

  window.addEventListener("offline", updateNetworkState);
  window.addEventListener("online", updateNetworkState);
  installFetchResilience();
  updateNetworkState();
};

export default initNetworkRecovery;
