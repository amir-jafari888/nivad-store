const adminDiscovery = () => {
  const root = document.querySelector("[data-admin-discovery-page]");
  if (!root || root.dataset.initialized === "true") return;
  root.dataset.initialized = "true";

  const tabs = Array.from(root.querySelectorAll("[data-discovery-tab]"));
  const panels = Array.from(root.querySelectorAll("[data-discovery-panel]"));
  const openTagsButton = root.querySelector("[data-discovery-open-tags]");
  const form = root.querySelector("[data-discovery-tag-form]");
  const list = root.querySelector("[data-discovery-tag-list]");
  const emptyState = root.querySelector("[data-discovery-empty]");
  const count = root.querySelector("[data-discovery-count]");
  const countLabel = root.querySelector("[data-discovery-count-label]");
  const feedback = root.querySelector("[data-discovery-feedback]");
  if (!tabs.length || !panels.length) return;

  const localPreviewTags = [];

  const toPersianNumber = (value) => String(value).replace(/\d/g, (digit) => "۰۱۲۳۴۵۶۷۸۹"[digit]);

  const setTab = (name, focus = false) => {
    tabs.forEach((tab) => {
      const active = tab.dataset.discoveryTab === name;
      tab.classList.toggle("is-active", active);
      tab.setAttribute("aria-selected", String(active));
      tab.tabIndex = active ? 0 : -1;
      if (active && focus) tab.focus();
    });
    panels.forEach((panel) => {
      panel.hidden = panel.dataset.discoveryPanel !== name;
    });
  };

  const showFeedback = (message, kind = "info") => {
    if (!feedback) return;
    feedback.hidden = false;
    feedback.dataset.state = kind;
    feedback.textContent = message;
  };

  const renderTags = () => {
    if (!list) return;
    list.replaceChildren();
    if (emptyState && localPreviewTags.length === 0) {
      list.appendChild(emptyState);
    } else if (emptyState) {
      emptyState.remove();
    }

    localPreviewTags.forEach((tag) => {
      const row = document.createElement("article");
      row.className = "admin-discovery-tag-item";

      const details = document.createElement("div");
      details.className = "admin-discovery-tag-item-main";
      const name = document.createElement("strong");
      name.textContent = tag.name;
      const slug = document.createElement("code");
      slug.dir = "ltr";
      slug.textContent = tag.slug;
      details.append(name, slug);

      const meta = document.createElement("div");
      meta.className = "admin-discovery-tag-item-meta";
      const status = document.createElement("span");
      status.className = "admin-discovery-preview-badge";
      status.textContent = tag.status === "active" ? "فعال · نمونه" : "غیرفعال · نمونه";
      const order = document.createElement("span");
      order.textContent = "ترتیب " + toPersianNumber(tag.sortOrder);
      meta.append(status, order);
      row.append(details, meta);

      if (tag.description) {
        const description = document.createElement("p");
        description.className = "admin-discovery-tag-description";
        description.textContent = tag.description;
        row.appendChild(description);
      }
      list.appendChild(row);
    });

    if (count) count.textContent = toPersianNumber(localPreviewTags.length);
    if (countLabel) countLabel.textContent = toPersianNumber(localPreviewTags.length) + " مورد";
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => setTab(tab.dataset.discoveryTab));
    tab.addEventListener("keydown", (event) => {
      if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
      event.preventDefault();
      let next = index;
      if (event.key === "Home") next = 0;
      else if (event.key === "End") next = tabs.length - 1;
      else if (event.key === "ArrowRight") next = (index + 1) % tabs.length;
      else next = (index - 1 + tabs.length) % tabs.length;
      setTab(tabs[next].dataset.discoveryTab, true);
    });
  });

  openTagsButton?.addEventListener("click", () => {
    setTab("tags");
    tabs.find((tab) => tab.dataset.discoveryTab === "tags")?.focus();
  });

  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const formData = new FormData(form);
    const name = String(formData.get("name") || "").trim();
    const slug = String(formData.get("slug") || "").trim();
    if (localPreviewTags.some((tag) => tag.slug === slug)) {
      showFeedback("این Slug در پیش‌نمایش تکراری است.", "error");
      form.querySelector('[name="slug"]')?.focus();
      return;
    }
    localPreviewTags.unshift({
      name,
      slug,
      description: String(formData.get("description") || "").trim(),
      status: String(formData.get("status") || "active"),
      sortOrder: Math.max(0, Number(formData.get("sortOrder") || 0)),
    });
    renderTags();
    form.reset();
    showFeedback("تگ به پیش‌نمایش محلی اضافه شد؛ در دیتابیس ذخیره نشده است.", "success");
  });

  form?.addEventListener("reset", () => {
    if (feedback) feedback.hidden = true;
  });

  renderTags();
  setTab("overview");
};

export default adminDiscovery;
