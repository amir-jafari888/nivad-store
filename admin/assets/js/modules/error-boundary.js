const adminErrorBoundary = () => {
  "use strict";
  if (window.__nivadAdminErrorBoundary) return;
  window.__nivadAdminErrorBoundary = true;
  const apiBase = document.querySelector('meta[name="admin-api-url"]')?.content || "/api/index.php";
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || "";
  let visible = false;
  const show = message => {
    if (visible) return;
    visible = true;
    const banner = document.createElement("section");
    banner.className = "admin-global-error";
    banner.setAttribute("role", "alert");
    banner.setAttribute("aria-live", "assertive");
    const text = document.createElement("p");
    text.textContent = message || "بخشی از رابط مدیریت با خطا مواجه شد.";
    const retry = document.createElement("button");
    retry.type = "button";
    retry.textContent = "بارگذاری مجدد صفحه";
    retry.addEventListener("click", () => location.reload());
    banner.append(text, retry);
    document.body.append(banner);
  };
  const report = (eventType, errorType, file, line, column) => {
    const token = csrf();
    if (!token) return;
    const url = new URL(apiBase, location.href);
    url.searchParams.set("path", "/incident/client");
    fetch(url, {
      method: "POST", credentials: "same-origin", keepalive: true,
      headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": token },
      body: JSON.stringify({
        event_type: eventType,
        error_type: /^[A-Za-z][A-Za-z0-9_]{0,79}$/.test(errorType || "") ? errorType : "Error",
        page: location.pathname,
        file: file || "",
        line: Number.isInteger(line) && line > 0 ? line : null,
        column: Number.isInteger(column) && column > 0 ? column : null
      })
    }).catch(() => {});
  };
  window.addEventListener("error", event => {
    if (event instanceof ErrorEvent) {
      report("runtime_error", event.error?.name || "Error", event.filename, event.lineno, event.colno);
    } else {
      const target = event.target;
      if (target && target !== window) report("resource_error", "ResourceError", target.src || target.href || "", null, null);
    }
    show("خطای سمت مرورگر رخ داد. اطلاعات فنی بدون متن حساس گزارش شد.");
  }, true);
  window.addEventListener("unhandledrejection", event => {
    const reason = event.reason;
    report("unhandled_rejection", reason?.name || "PromiseRejection", "", null, null);
    show("یک عملیات پس‌زمینه کامل نشد. می‌توانید صفحه را دوباره بارگذاری کنید.");
  });

  const nativeFetch = window.fetch.bind(window);
  window.fetch = async (input, init = {}) => {
    const method = String(init.method || (input instanceof Request ? input.method : "GET")).toUpperCase();
    if (method !== "GET" || init.__adminRetry === false) return nativeFetch(input, init);
    let response;
    try {
      response = await nativeFetch(input, init);
    } catch (error) {
      await new Promise(resolve => setTimeout(resolve, 300));
      return nativeFetch(input, init);
    }
    if ([502, 503, 504].includes(response.status)) {
      await new Promise(resolve => setTimeout(resolve, 300));
      const retry = await nativeFetch(input, init);
      if (retry.ok) return retry;
    }
    return response;
  };
};

export default adminErrorBoundary;
