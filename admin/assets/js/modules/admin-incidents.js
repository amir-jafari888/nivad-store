const initAdminIncidents = () => {
  "use strict";
  const root = document.querySelector("[data-admin-incidents]");
  if (!root || root.dataset.bound === "true") return;
  root.dataset.bound = "true";

  const apiUrl = root.dataset.apiUrl;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const feedback = root.querySelector("[data-feedback]");
  const rows = root.querySelector("[data-incidents]");
  const analysis = root.querySelector("[data-analysis]");
  const jobs = root.querySelector("[data-jobs]");

  const endpoint = (path, params = {}) => {
    const url = new URL(apiUrl, window.location.href);
    url.searchParams.set("path", "/admin/capabilities" + path);
    Object.entries(params).forEach(([key, value]) => {
      if (value !== "" && value !== null && value !== undefined) url.searchParams.set(key, String(value));
    });
    return url.toString();
  };

  const request = async (path, options = {}, params = {}) => {
    const headers = { Accept: "application/json", ...(options.headers || {}) };
    if (options.method && options.method !== "GET") {
      headers["X-CSRF-TOKEN"] = csrf;
      headers["Content-Type"] = "application/json";
    }
    const response = await fetch(endpoint(path, params), { credentials: "same-origin", ...options, headers });
    const payload = await response.json();
    if (!response.ok || payload.success !== true) throw new Error(payload.message || "درخواست ناموفق بود.");
    return payload.data || {};
  };

  const cell = (row, value) => {
    const td = document.createElement("td");
    td.textContent = value === null || value === undefined ? "—" : String(value);
    row.append(td);
    return td;
  };

  const loadTimeline = async (incidentId, target) => {
    target.replaceChildren();
    const data = await request("/incidents/" + incidentId + "/timeline");
    if (!data.items?.length) {
      target.textContent = "برای این رخداد هنوز جزئیات Timeline ثبت نشده است.";
      return;
    }
    data.items.forEach(item => {
      const article = document.createElement("article");
      const title = document.createElement("strong");
      title.textContent = item.occurred_at + " · " + item.event_type + " · " + item.severity;
      const detail = document.createElement("p");
      detail.textContent = item.source +
        (item.http_status ? " · HTTP " + item.http_status : "") +
        (item.request_method ? " · " + item.request_method : "") +
        (item.route_path ? " · " + item.route_path : "") +
        (item.request_id ? " · Request ID " + item.request_id : "") +
        (item.file_basename ? " · " + item.file_basename + ":" + (item.line_number || 0) : "");
      article.append(title, detail);
      target.append(article);
    });
  };

  const load = async () => {
    feedback.textContent = "در حال دریافت اطلاعات…";
    try {
      const status = root.querySelector("[data-filter-status]").value;
      const severity = root.querySelector("[data-filter-severity]").value;
      const data = await request("/incidents", {}, { status });
      rows.replaceChildren();

      (data.items || []).filter(item => !severity || item.severity === severity).forEach(item => {
        const tr = document.createElement("tr");
        cell(tr, item.title);
        cell(tr, item.source);
        cell(tr, item.severity);
        cell(tr, item.status);
        cell(tr, item.occurrence_count);
        cell(tr, item.first_seen_at);
        cell(tr, item.last_seen_at);

        const actionCell = document.createElement("td");
        const details = document.createElement("details");
        const summary = document.createElement("summary");
        summary.textContent = "Timeline";
        const timeline = document.createElement("div");
        summary.addEventListener("click", async () => {
          if (details.dataset.loaded === "true") return;
          try {
            await loadTimeline(item.id, timeline);
            details.dataset.loaded = "true";
          } catch (error) {
            timeline.textContent = error.message;
          }
        });
        details.append(summary, timeline);

        const statusSelect = document.createElement("select");
        [["open","باز"],["acknowledged","در حال بررسی"],["resolved","حل‌شده"],["ignored","نادیده‌گرفته‌شده"]].forEach(([value,label]) => {
          const option = document.createElement("option");
          option.value = value; option.textContent = label; option.selected = item.status === value;
          statusSelect.append(option);
        });
        const save = document.createElement("button");
        save.type = "button"; save.textContent = "ثبت وضعیت";
        save.addEventListener("click", async () => {
          try {
            await request("/incidents/" + item.id, {
              method: "PATCH",
              body: JSON.stringify({
                status: statusSelect.value,
                assigned_admin_id: item.assigned_admin_id ? Number(item.assigned_admin_id) : null
              })
            });
            await load();
          } catch (error) { feedback.textContent = error.message; }
        });
        actionCell.append(details, statusSelect, save);
        tr.append(actionCell);
        rows.append(tr);
      });

      const hours = Number(root.querySelector("[data-filter-hours]").value || 24);
      const report = await request("/incidents/analysis", {}, { hours });
      analysis.replaceChildren();
      [...(report.by_severity || []), ...(report.by_source || [])].forEach(item => {
        const p = document.createElement("p");
        p.textContent = (item.source || item.severity) + " · " + item.severity +
          " · رویدادها: " + item.event_count + " · رخدادهای یکتا: " + item.incident_count;
        analysis.append(p);
      });

      const jobData = await request("/jobs/health");
      jobs.replaceChildren();
      const note = document.createElement("p");
      note.textContent = jobData.generic_queue_found
        ? "صف عمومی شناسایی شد."
        : "صف عمومی در کد فعلی شناسایی نشد؛ فقط صف‌های موجود پایش می‌شوند.";
      jobs.append(note);
      (jobData.monitored_queues || []).forEach(queue => {
        const p = document.createElement("p");
        p.textContent = queue.label + " · " + queue.state + " · وضعیت‌ها: " +
          JSON.stringify(queue.statuses) + " · قدیمی‌تر از ۳۰ دقیقه: " +
          (queue.stale_count === null ? "نامشخص" : queue.stale_count);
        jobs.append(p);
      });
      feedback.textContent = "اطلاعات رخدادها به‌روزرسانی شد.";
    } catch (error) {
      feedback.textContent = error.message;
    }
  };

  root.querySelector("[data-refresh]")?.addEventListener("click", load);
  root.querySelectorAll("[data-filter-status], [data-filter-severity], [data-filter-hours]")
    .forEach(control => control.addEventListener("change", load));
  load();
};

export default initAdminIncidents;
