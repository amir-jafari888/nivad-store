const initClientErrorMonitoring = () => {
  "use strict";
  if (window.__nivadErrorMonitoringBound) return;
  window.__nivadErrorMonitoringBound = true;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const apiMeta = document.querySelector('meta[name="api-url"], meta[name="admin-api-url"]');
  const apiUrl = apiMeta?.content || "/api/index.php";
  if (!csrf) return;

  const endpoint = new URL(apiUrl, window.location.href);
  endpoint.searchParams.set("path", "/incident/client");
  let lastSentAt = 0;

  const send = payload => {
    const now = Date.now();
    if (now - lastSentAt < 1500) return;
    lastSentAt = now;

    fetch(endpoint.toString(), {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify({
        event_type: payload.event_type,
        error_type: payload.error_type || "Error",
        page: window.location.pathname,
        file: payload.file || "",
        line: payload.line || null,
        column: payload.column || null,
      }),
    }).catch(() => {});
  };

  window.addEventListener("error", event => {
    if (event.target && event.target !== window) {
      const target = event.target;
      send({
        event_type: "resource_error",
        error_type: target.tagName || "Resource",
        file: target.currentSrc || target.src || target.href || "",
      });
      return;
    }
    send({
      event_type: "runtime_error",
      error_type: event.error?.name || "Error",
      file: event.filename || "",
      line: event.lineno || null,
      column: event.colno || null,
    });
  }, true);

  window.addEventListener("unhandledrejection", event => {
    const reason = event.reason;
    send({
      event_type: "unhandled_rejection",
      error_type: reason && typeof reason === "object" && typeof reason.name === "string"
        ? reason.name
        : "UnhandledRejection",
    });
  });
};

export default initClientErrorMonitoring;
