const adminDataExperience = () => {
  "use strict";

  const normalize = (value) => String(value ?? "").normalize("NFKC").trim().toLocaleLowerCase();

  const make = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  const csvCell = (value) => {
    const text = String(value ?? "");
    return /[",\r\n]/.test(text) ? '"' + text.replaceAll('"', '""') + '"' : text;
  };

  const enhanceTable = (table, index) => {
    if (table.dataset.adminDataReady === "true" || !table.tHead || !table.tBodies.length) return;
    table.dataset.adminDataReady = "true";
    table.classList.add("admin-data-table");

    const body = table.tBodies[0];
    const headerCells = Array.from(table.tHead.rows[0]?.cells ?? []);
    if (!headerCells.length) return;

    const wrapper = make("section", "admin-data-experience");
    wrapper.setAttribute("aria-label", "ابزارهای جدول");
    const toolbar = make("div", "admin-data-toolbar");
    const searchLabel = make("label", "admin-data-field", "جست‌وجو در نتایج");
    const search = make("input", "admin-data-search");
    search.type = "search";
    search.placeholder = "عبارت موردنظر را وارد کنید";
    search.autocomplete = "off";
    search.setAttribute("aria-label", "جست‌وجو در ردیف‌های بارگذاری‌شده");
    searchLabel.append(search);

    const statusColumn = headerCells.findIndex((cell) => /status|وضعیت/i.test(cell.textContent ?? ""));
    let statusFilter = null;
    if (statusColumn >= 0) {
      const filterLabel = make("label", "admin-data-field", "فیلتر وضعیت");
      statusFilter = make("select", "admin-data-filter");
      statusFilter.setAttribute("aria-label", "فیلتر بر اساس وضعیت");
      filterLabel.append(statusFilter);
      toolbar.append(searchLabel, filterLabel);
    } else {
      toolbar.append(searchLabel);
    }

    const pageSizeLabel = make("label", "admin-data-field", "تعداد ردیف در صفحه");
    const pageSize = make("select", "admin-data-page-size");
    pageSize.setAttribute("aria-label", "تعداد ردیف در صفحه");
    [[10, "۱۰"], [25, "۲۵"], [50, "۵۰"], [100, "۱۰۰"]].forEach(([value, label]) => {
      const option = make("option", "", label);
      option.value = String(value);
      if (value === 10) option.selected = true;
      pageSize.append(option);
    });
    pageSizeLabel.append(pageSize);
    toolbar.append(pageSizeLabel);

    const bulk = make("div", "admin-data-bulk");
    const selectAllLabel = make("label", "admin-data-select-all");
    const selectAll = make("input");
    selectAll.type = "checkbox";
    selectAll.setAttribute("aria-label", "انتخاب ردیف‌های قابل مشاهده");
    selectAllLabel.append(selectAll, document.createTextNode(" انتخاب ردیف‌های قابل مشاهده"));
    const selectedCount = make("span", "admin-data-selected-count", "۰ ردیف انتخاب شده");
    const exportButton = make("button", "admin-data-button", "خروجی CSV از انتخاب‌ها");
    exportButton.type = "button";
    const clearButton = make("button", "admin-data-button admin-data-button-secondary", "پاک‌کردن انتخاب");
    clearButton.type = "button";
    bulk.append(selectAllLabel, selectedCount, exportButton, clearButton);

    const tableWrap = make("div", "admin-data-table-wrap");
    const status = make("p", "admin-data-status", "در حال آماده‌سازی جدول…");
    status.setAttribute("role", "status");
    status.setAttribute("aria-live", "polite");

    const parent = table.parentElement;
    if (!parent) return;
    parent.insertBefore(wrapper, table);
    wrapper.append(toolbar, bulk, tableWrap, status);
    tableWrap.append(table);

    let page = 1;
    let realRows = [];
    let visibleRows = [];
    const selected = new Set();

    const rowText = (row) => Array.from(row.cells).map((cell) => cell.textContent ?? "").join(" ");
    const isPlaceholder = (row) => row.cells.length === 1 && row.cells[0].hasAttribute("colspan");

    const refreshFilterOptions = () => {
      if (!statusFilter) return;
      const previousValue = statusFilter.value;
      const values = [...new Set(realRows.map((row) => normalize(row.cells[statusColumn]?.textContent)).filter(Boolean))].sort();
      statusFilter.replaceChildren();
      const all = make("option", "", "همه وضعیت‌ها");
      all.value = "";
      statusFilter.append(all);
      values.forEach((value) => {
        const original = realRows.find((row) => normalize(row.cells[statusColumn]?.textContent) === value)?.cells[statusColumn]?.textContent.trim() ?? value;
        const option = make("option", "", original);
        option.value = value;
        statusFilter.append(option);
      });
      if (values.includes(previousValue)) statusFilter.value = previousValue;
    };

    const refreshSelection = () => {
      const checkboxes = Array.from(body.querySelectorAll("[data-admin-row-select]"));
      checkboxes.forEach((checkbox) => {
        const row = checkbox.closest("tr");
        checkbox.checked = selected.has(row);
      });
      selectedCount.textContent = new Intl.NumberFormat("fa-IR").format(selected.size) + " ردیف انتخاب شده";
      selectAll.checked = checkboxes.length > 0 && checkboxes.every((checkbox) => checkbox.checked);
      selectAll.indeterminate = checkboxes.some((checkbox) => checkbox.checked) && !selectAll.checked;
    };

    const apply = () => {
      realRows = Array.from(body.rows).filter((row) => !isPlaceholder(row));
      for (const row of selected) {
        if (!realRows.includes(row)) selected.delete(row);
      }
      refreshFilterOptions();
      const query = normalize(search.value);
      const statusValue = statusFilter ? statusFilter.value : "";
      visibleRows = realRows.filter((row) => {
        const matchesQuery = !query || normalize(rowText(row)).includes(query);
        const matchesStatus = !statusValue || normalize(row.cells[statusColumn]?.textContent) === statusValue;
        return matchesQuery && matchesStatus;
      });

      const size = Number(pageSize.value) || 10;
      const pageCount = Math.max(1, Math.ceil(visibleRows.length / size));
      page = Math.min(page, pageCount);
      const start = (page - 1) * size;
      const pageRows = new Set(visibleRows.slice(start, start + size));

      realRows.forEach((row) => {
        row.hidden = !pageRows.has(row);
        let checkbox = row.querySelector("[data-admin-row-select]");
        if (!checkbox) {
          const cell = make("td", "admin-data-select-cell");
          checkbox = make("input");
          checkbox.type = "checkbox";
          checkbox.dataset.adminRowSelect = "";
          checkbox.setAttribute("aria-label", "انتخاب ردیف");
          checkbox.addEventListener("change", () => {
            if (checkbox.checked) selected.add(row);
            else selected.delete(row);
            refreshSelection();
          });
          cell.append(checkbox);
          row.insertBefore(cell, row.firstElementChild);
        }
      });

      Array.from(body.rows).filter(isPlaceholder).forEach((row) => row.hidden = true);
      const existingHeader = table.tHead.rows[0];
      if (existingHeader && !existingHeader.querySelector("[data-admin-select-heading]")) {
        const th = make("th", "admin-data-select-cell");
        th.scope = "col";
        th.dataset.adminSelectHeading = "";
        th.textContent = "انتخاب";
        existingHeader.insertBefore(th, existingHeader.firstElementChild);
      }

      status.textContent = visibleRows.length
        ? "نمایش " + new Intl.NumberFormat("fa-IR").format(Math.min(start + 1, visibleRows.length)) + " تا " +
          new Intl.NumberFormat("fa-IR").format(Math.min(start + size, visibleRows.length)) + " از " +
          new Intl.NumberFormat("fa-IR").format(visibleRows.length) + " ردیف بارگذاری‌شده. جست‌وجو و صفحه‌بندی فقط روی داده‌های دریافت‌شده اعمال می‌شود."
        : (realRows.length ? "نتیجه‌ای با این جست‌وجو یا فیلتر پیدا نشد." : "داده‌ای برای نمایش وجود ندارد.");
      renderPager(pageCount);
      refreshSelection();
    };

    const pager = make("nav", "admin-data-pagination");
    pager.setAttribute("aria-label", "صفحه‌بندی نتایج");
    const previous = make("button", "admin-data-button admin-data-page-previous", "قبلی");
    previous.type = "button";
    const next = make("button", "admin-data-button admin-data-page-next", "بعدی");
    next.type = "button";
    const pageLabel = make("span", "admin-data-page-label", "صفحه ۱");
    previous.addEventListener("click", () => { page = Math.max(1, page - 1); apply(); });
    next.addEventListener("click", () => { page += 1; apply(); });
    pager.append(previous, pageLabel, next);
    wrapper.append(pager);

    function renderPager(pageCount) {
      previous.disabled = page <= 1;
      next.disabled = page >= pageCount;
      pageLabel.textContent = "صفحه " + new Intl.NumberFormat("fa-IR").format(page) + " از " + new Intl.NumberFormat("fa-IR").format(pageCount);
    }

    search.addEventListener("input", () => { page = 1; apply(); });
    statusFilter?.addEventListener("change", () => { page = 1; apply(); });
    pageSize.addEventListener("change", () => { page = 1; apply(); });
    selectAll.addEventListener("change", () => {
      Array.from(body.rows).filter((row) => !row.hidden && !isPlaceholder(row)).forEach((row) => {
        if (selectAll.checked) selected.add(row);
        else selected.delete(row);
      });
      refreshSelection();
    });
    clearButton.addEventListener("click", () => { selected.clear(); refreshSelection(); });
    exportButton.addEventListener("click", () => {
      const rows = [...selected];
      if (!rows.length) {
        status.textContent = "برای خروجی گرفتن ابتدا حداقل یک ردیف انتخاب کنید.";
        return;
      }
      const headings = headerCells.map((cell) => cell.textContent.trim());
      const records = rows.map((row) => Array.from(row.cells).slice(1).map((cell) => cell.textContent.trim()));
      const csv = "\uFEFF" + [headings.map(csvCell).join(","), ...records.map((record) => record.map(csvCell).join(","))].join("\r\n");
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = make("a");
      link.href = url;
      link.download = "nivad-admin-selection-" + (index + 1) + ".csv";
      link.click();
      URL.revokeObjectURL(url);
      status.textContent = "خروجی CSV برای " + new Intl.NumberFormat("fa-IR").format(rows.length) + " ردیف آماده شد.";
    });

    const observer = new MutationObserver(() => {
      page = 1;
      apply();
    });
    observer.observe(body, { childList: true, subtree: true });
    apply();
  };

  const enhanceForms = () => {
    document.querySelectorAll("form:not([data-admin-form-ready])").forEach((form) => {
      form.dataset.adminFormReady = "true";
      form.addEventListener("submit", (event) => {
        if (event.defaultPrevented) return;
        if (form.dataset.submitting === "true") {
          event.preventDefault();
          return;
        }
        if (!form.noValidate && !form.checkValidity()) {
          form.reportValidity();
          return;
        }
        form.dataset.submitting = "true";
        const submitters = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitters.forEach((button) => {
          if (!button.dataset.originalLabel) button.dataset.originalLabel = button.textContent || button.value || "";
          button.disabled = true;
          if (button.tagName === "BUTTON") button.textContent = "در حال ارسال…";
          else button.value = "در حال ارسال…";
        });
      });
    });
  };

  const enforceDeclaredPermissions = () => {
    const root = document.documentElement;
    let permissions = [];
    try {
      const raw = root.dataset.adminPermissions;
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) permissions = parsed.filter((item) => typeof item === "string");
      }
    } catch (_) {
      permissions = [];
    }

    document.querySelectorAll("[data-admin-permission]").forEach((control) => {
      const required = control.dataset.adminPermission;
      if (!required || permissions.includes(required)) return;
      if ("disabled" in control) control.disabled = true;
      control.setAttribute("aria-disabled", "true");
      if (!control.hasAttribute("title")) {
        control.title = "مجوز این عملیات از قرارداد فعلی صفحه تأیید نشده است.";
      }
    });
  };

  const enhanceFeedback = () => {
    document.querySelectorAll(".message, #message, [data-admin-message]").forEach((node) => {
      if (!node.hasAttribute("role")) node.setAttribute("role", "status");
      if (!node.hasAttribute("aria-live")) node.setAttribute("aria-live", "polite");
      const announce = () => {
        const text = normalize(node.textContent);
        if (/خطا|ناموفق|مجاز نیست|error|failed/.test(text)) node.setAttribute("role", "alert");
        else if (node.getAttribute("role") === "alert") node.setAttribute("role", "status");
      };
      announce();
      if (node.dataset.adminFeedbackObserved !== "true") {
        node.dataset.adminFeedbackObserved = "true";
        new MutationObserver(announce).observe(node, { childList: true, characterData: true, subtree: true });
      }
    });
  };

  const mount = () => {
    document.querySelectorAll("table").forEach((table, index) => enhanceTable(table, index));
    enhanceForms();
    enhanceFeedback();
    enforceDeclaredPermissions();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mount, { once: true });
  } else {
    mount();
  }
};

export default adminDataExperience;
