const account = (() => {
  const baseUrl = window.NIVAD_BASE_URL || "";
  const apiUrl = (path) => `${baseUrl}/api/index.php?path=${encodeURIComponent(path)}`;
  const escapeHtml = (value) => String(value ?? "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
  const message = (node, text, error = false) => {
    if (!node) return;
    node.textContent = text;
    node.setAttribute("role", error ? "alert" : "status");
    node.setAttribute("aria-live", "polite");
  };
  const csrf = async () => {
    const response = await fetch(apiUrl("/auth/csrf"), { credentials: "same-origin", headers: { Accept: "application/json" } });
    const payload = await response.json().catch(() => null);
    if (!response.ok || !payload?.data?.token) throw new Error(payload?.message || "دریافت توکن امنیتی انجام نشد.");
    return String(payload.data.token);
  };
  const request = async (path, options = {}) => {
    const method = String(options.method || "GET").toUpperCase();
    const headers = { Accept: "application/json", ...(options.headers || {}) };
    let body = options.body;
    if (!["GET", "HEAD"].includes(method)) {
      headers["Content-Type"] = "application/json";
      headers["X-CSRF-TOKEN"] = await csrf();
      if (body === undefined) body = "{}";
    }
    let response;
    try { response = await fetch(apiUrl(path), { ...options, method, headers, body, credentials: "same-origin" }); }
    catch { throw new Error("ارتباط با سرور برقرار نشد. اتصال را بررسی کنید."); }
    const payload = await response.json().catch(() => null);
    if (!payload || typeof payload !== "object") throw new Error("پاسخ سرور قابل خواندن نیست.");
    if (!response.ok || payload.success === false) {
      const error = new Error(payload.message || (response.status === 401 ? "برای ادامه دوباره وارد حساب شوید." : "درخواست انجام نشد."));
      error.status = response.status; error.payload = payload; throw error;
    }
    return payload;
  };
  const fieldErrors = (form, errors) => {
    form.querySelectorAll("[data-field-error]").forEach((node) => node.remove());
    form.querySelectorAll('[aria-invalid="true"]').forEach((node) => node.removeAttribute("aria-invalid"));
    if (!errors || typeof errors !== "object") return;
    Object.entries(errors).forEach(([name, value]) => {
      const field = form.elements.namedItem(name);
      if (!field || !("setAttribute" in field)) return;
      const id = field.id || name;
      field.setAttribute("aria-invalid", "true");
      const node = document.createElement("small");
      node.dataset.fieldError = name;
      node.id = `${id}-error`;
      node.textContent = Array.isArray(value) ? value.join(" ") : String(value);
      field.setAttribute("aria-describedby", node.id);
      field.insertAdjacentElement("afterend", node);
    });
  };
  const busy = (form, value) => {
    form.dataset.busy = value ? "true" : "false";
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
      button.disabled = value;
      if (value) button.setAttribute("aria-busy", "true"); else button.removeAttribute("aria-busy");
    });
  };
  const initProfile = () => {
    const form = document.querySelector("[data-account-profile-form]");
    if (!form) return;
    const feedback = form.querySelector("[data-account-profile-message]");
    request("/account/profile").then((payload) => {
      const user = payload?.data?.user;
      if (!user) throw new Error("اطلاعات پروفایل در پاسخ موجود نیست.");
      ["first_name", "last_name", "email", "phone"].forEach((key) => { if (form.elements.namedItem(key)) form.elements[key].value = user[key] ?? ""; });
    }).catch((error) => message(feedback, error.message, true));
    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!form.reportValidity() || form.dataset.busy === "true") return;
      busy(form, true);
      try {
        const payload = await request("/account/profile", { method: "PATCH", body: JSON.stringify({ first_name: form.elements.first_name.value.trim(), last_name: form.elements.last_name.value.trim() }) });
        if (!payload?.data?.user) throw new Error(payload.message || "ذخیره پروفایل تأیید نشد.");
        form.elements.first_name.value = payload.data.user.first_name ?? "";
        form.elements.last_name.value = payload.data.user.last_name ?? "";
        message(feedback, payload.message || "تغییرات ذخیره شد.");
      } catch (error) { fieldErrors(form, error.payload?.errors || error.payload?.data?.errors); message(feedback, error.message, true); }
      finally { busy(form, false); }
    });
  };
  const initAddresses = () => {
    const list = document.querySelector("[data-account-addresses]");
    const form = document.querySelector("[data-account-address-form]");
    if (!list || !form) return;
    const feedback = form.querySelector("[data-account-address-message]");
    const cancel = form.querySelector("[data-account-address-cancel]");
    let editingId = null; let cache = [];
    const readForm = () => ({
      title: form.elements.title.value.trim(), recipient_name: form.elements.recipient_name.value.trim(),
      phone: form.elements.phone.value.trim(), province: form.elements.province.value.trim(),
      city: form.elements.city.value.trim(), address: form.elements.address.value.trim(),
      postal_code: form.elements.postal_code.value.trim(), building_number: form.elements.building_number.value.trim() || null,
      unit: form.elements.unit.value.trim() || null, is_default: form.elements.is_default.checked,
    });
    const reset = () => { form.reset(); form.elements.id.value = ""; editingId = null; };
    const load = async () => {
      list.setAttribute("aria-busy", "true"); list.textContent = "در حال دریافت آدرس‌ها...";
      try {
        const payload = await request("/account/addresses");
        cache = Array.isArray(payload?.data?.addresses) ? payload.data.addresses : [];
        list.innerHTML = cache.length ? cache.map((item) => `<article class="account-address" data-address-id="${escapeHtml(item.id)}">
          <h2>${escapeHtml(item.title)}</h2><p>${escapeHtml(item.recipient_name)} — ${escapeHtml(item.phone)}</p>
          <p>${escapeHtml(item.province)}، ${escapeHtml(item.city)}</p><p>${escapeHtml(item.address)}</p>
          <p>${escapeHtml(item.postal_code || "")}</p>${item.is_default ? "<strong class='account-status'>آدرس پیش‌فرض</strong>" : ""}
          <div class="account-address__actions"><button type="button" data-address-edit="${escapeHtml(item.id)}">ویرایش</button>
          ${item.is_default ? "" : `<button type="button" data-address-default="${escapeHtml(item.id)}">انتخاب پیش‌فرض</button>`}
          <button type="button" data-address-delete="${escapeHtml(item.id)}">حذف</button></div></article>`).join("") : "<p>هنوز آدرسی ثبت نکرده‌اید.</p>";
      } catch (error) { list.textContent = error.message; }
      finally { list.removeAttribute("aria-busy"); }
    };
    list.addEventListener("click", async (event) => {
      const target = event.target instanceof Element ? event.target : null;
      const edit = target?.closest("[data-address-edit]"), makeDefault = target?.closest("[data-address-default]"), remove = target?.closest("[data-address-delete]");
      const button = edit || makeDefault || remove;
      if (!button || button.disabled) return;
      const id = Number(button.dataset.addressEdit || button.dataset.addressDefault || button.dataset.addressDelete);
      if (!Number.isSafeInteger(id) || id <= 0) return;
      if (edit) {
        const item = cache.find((row) => Number(row.id) === id);
        if (!item) return message(feedback, "آدرس پیدا نشد.", true);
        editingId = id; form.elements.id.value = String(id);
        Object.keys(readForm()).forEach((key) => {
          if (!form.elements.namedItem(key)) return;
          if (key === "is_default") form.elements[key].checked = Boolean(item[key]);
          else form.elements[key].value = item[key] ?? "";
        });
        form.scrollIntoView({ behavior: "smooth", block: "start" }); return;
      }
      if (remove && !window.confirm("از حذف این آدرس مطمئن هستید؟")) return;
      button.disabled = true;
      try {
        const payload = makeDefault ? await request(`/account/addresses/${id}/default`, { method: "POST" }) : await request(`/account/addresses/${id}`, { method: "DELETE" });
        message(feedback, payload.message || "عملیات آدرس انجام شد."); await load();
      } catch (error) { message(feedback, error.message, true); }
      finally { button.disabled = false; }
    });
    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!form.reportValidity() || form.dataset.busy === "true") return;
      busy(form, true);
      try {
        const payload = await request(editingId === null ? "/account/addresses" : `/account/addresses/${editingId}`, { method: editingId === null ? "POST" : "PUT", body: JSON.stringify(readForm()) });
        if (!payload?.data?.address) throw new Error(payload.message || "ذخیره آدرس تأیید نشد.");
        message(feedback, payload.message || "آدرس ذخیره شد."); reset(); await load();
      } catch (error) { fieldErrors(form, error.payload?.errors || error.payload?.data?.errors); message(feedback, error.message, true); }
      finally { busy(form, false); }
    });
    cancel?.addEventListener("click", () => { reset(); message(feedback, "ویرایش لغو شد."); });
    load();
  };
  const initOrders = () => {
    const list = document.querySelector("[data-account-orders]"); if (!list) return;
    list.setAttribute("aria-busy", "true");
    request("/orders").then((payload) => {
      const items = Array.isArray(payload?.data?.orders) ? payload.data.orders : [];
      list.innerHTML = items.length ? items.map((item) => `<article class="account-order"><h2>سفارش #${escapeHtml(item.id)}</h2><p>وضعیت: ${escapeHtml(item.status || "نامشخص")}</p><p>مبلغ: ${escapeHtml(item.total ?? item.subtotal ?? "نامشخص")}</p><a href="show.php?id=${encodeURIComponent(item.id)}">مشاهده جزئیات</a></article>`).join("") : "<p>هنوز سفارشی ثبت نشده است.</p>";
    }).catch((error) => { list.textContent = error.message; }).finally(() => list.removeAttribute("aria-busy"));
  };
  const initOrderDetail = () => {
    const root = document.querySelector("[data-account-order-detail]"); if (!root) return;
    const id = new URLSearchParams(location.search).get("id");
    if (!id || !/^\d+$/.test(id) || Number(id) < 1) { root.textContent = "شناسه سفارش نامعتبر است."; return; }
    root.setAttribute("aria-busy", "true");
    request(`/orders/${encodeURIComponent(id)}`).then((payload) => {
      const data = payload?.data; if (!data) throw new Error("سفارش پیدا نشد.");
      const order = data.order || data, items = Array.isArray(data.items) ? data.items : [];
      root.innerHTML = `<h1>سفارش #${escapeHtml(order.id || id)}</h1><p>وضعیت: ${escapeHtml(order.status || "نامشخص")}</p><p>مبلغ: ${escapeHtml(order.total ?? order.subtotal ?? "نامشخص")}</p><h2>اقلام سفارش</h2>
        ${items.length ? items.map((item) => `<article class="account-order__item"><span>${escapeHtml(item.name || item.product_name || item.variantId || item.variant_id || "محصول")}</span><span>تعداد: ${escapeHtml(item.quantity ?? 0)}</span></article>`).join("") : "<p>جزئیات اقلام در پاسخ موجود نیست.</p>"}<a href="index.php">بازگشت به سفارش‌ها</a>`;
    }).catch((error) => { root.textContent = error.message; }).finally(() => root.removeAttribute("aria-busy"));
  };
  const initReturns = () => {
    const list = document.querySelector("[data-account-returns]");
    const form = document.querySelector("[data-account-return-form]");
    if (!list && !form) return;
    const feedback = form?.querySelector("[data-account-return-message]");
    const load = async () => {
      if (!list) return;
      list.setAttribute("aria-busy", "true");
      list.textContent = "در حال دریافت درخواست‌ها...";
      try {
        const payload = await request("/returns");
        const items = Array.isArray(payload?.data?.returns) ? payload.data.returns : [];
        list.innerHTML = items.length ? items.map((item) => `<article class="account-return"><h2>مرجوعی #${escapeHtml(item.id)}</h2><p>وضعیت: ${escapeHtml(item.status || "نامشخص")}</p><a href="show.php?id=${encodeURIComponent(item.id)}">مشاهده جزئیات</a></article>`).join("") : "<p>درخواستی برای مرجوعی ثبت نشده است.</p>";
      } catch (error) { list.textContent = error.message; }
      finally { list.removeAttribute("aria-busy"); }
    };
    form?.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!form.reportValidity() || form.dataset.busy === "true") return;
      const orderId = Number(form.elements.orderId.value);
      const orderItemId = Number(form.elements.orderItemId.value);
      const quantity = Number(form.elements.quantity.value);
      if (![orderId, orderItemId, quantity].every((value) => Number.isSafeInteger(value) && value > 0)) {
        message(feedback, "شناسه سفارش، شناسه قلم و تعداد باید معتبر باشند.", true); return;
      }
      busy(form, true);
      try {
        const payload = await request("/returns", { method: "POST", body: JSON.stringify({
          orderId, returnNumber: form.elements.returnNumber.value.trim(),
          items: [{ orderItemId, quantity }],
          reason: form.elements.reason.value.trim() || null,
          customerNote: form.elements.customerNote.value.trim() || null,
        }) });
        if (!payload?.data) throw new Error(payload?.message || "ثبت درخواست مرجوعی تأیید نشد.");
        message(feedback, payload.message || "درخواست مرجوعی ثبت شد.");
        form.reset();
        await load();
      } catch (error) { message(feedback, error.message, true); }
      finally { busy(form, false); }
    });
    load();
  };

  const initReturnDetail = () => {
    const root = document.querySelector("[data-account-return-detail]"); if (!root) return;
    const id = new URLSearchParams(location.search).get("id");
    if (!id || !/^\d+$/.test(id) || Number(id) < 1) { root.textContent = "شناسه مرجوعی نامعتبر است."; return; }
    root.setAttribute("aria-busy", "true");
    request(`/returns/${encodeURIComponent(id)}`).then((payload) => {
      const data = payload?.data; if (!data?.return) throw new Error("درخواست مرجوعی پیدا نشد.");
      const item = data.return, items = Array.isArray(data.items) ? data.items : [], history = Array.isArray(data.history) ? data.history : [];
      root.innerHTML = `<h1>مرجوعی #${escapeHtml(item.id || id)}</h1><p>وضعیت: ${escapeHtml(item.status || "نامشخص")}</p><h2>اقلام</h2>
        ${items.length ? items.map((row) => `<p>${escapeHtml(row.product_name || row.name || row.variant_id || "محصول")} — تعداد: ${escapeHtml(row.quantity ?? 0)}</p>`).join("") : "<p>اقلامی در پاسخ وجود ندارد.</p>"}<h2>تاریخچه وضعیت</h2>
        ${history.length ? history.map((row) => `<p>${escapeHtml(row.status || "وضعیت")} — ${escapeHtml(row.created_at || row.createdAt || "")}</p>`).join("") : "<p>تاریخچه‌ای در پاسخ موجود نیست.</p>"}<a href="index.php">بازگشت به مرجوعی‌ها</a>`;
    }).catch((error) => { root.textContent = error.message; }).finally(() => root.removeAttribute("aria-busy"));
  };
  const initReviews = () => {
    const list = document.querySelector("[data-account-reviews]");
    const form = document.querySelector("[data-account-review-form]");
    if (!list && !form) return;
    const feedback = form?.querySelector("[data-review-message]");

    const load = async () => {
      if (!list) return;
      list.setAttribute("aria-busy", "true");
      list.textContent = "در حال دریافت دیدگاه‌ها...";
      try {
        const payload = await request("/reviews/mine");
        const items = payload?.data?.items;
        if (!Array.isArray(items)) throw new Error("ساختار پاسخ دیدگاه‌ها معتبر نیست.");
        list.replaceChildren();
        if (!items.length) {
          list.textContent = "هنوز دیدگاهی ثبت نکرده‌اید.";
          return;
        }
        items.forEach((item) => {
          const article = document.createElement("article");
          article.className = "account-review";
          const title = document.createElement("h2");
          title.textContent = item.title || "دیدگاه بدون عنوان";
          const body = document.createElement("p");
          body.textContent = item.body || "";
          const rating = document.createElement("p");
          rating.textContent = `امتیاز: ${Number(item.rating) || 0} از ۵`;
          const status = document.createElement("p");
          status.textContent = item.status ? `وضعیت: ${String(item.status)}` : "وضعیت بررسی در پاسخ موجود نیست.";
          article.append(title, body, rating, status);
          list.append(article);
        });
      } catch (error) {
        list.replaceChildren();
        const text = document.createElement("p");
        text.textContent = error.message || "دریافت دیدگاه‌ها انجام نشد.";
        const retry = document.createElement("button");
        retry.type = "button";
        retry.textContent = "تلاش دوباره";
        retry.addEventListener("click", load, { once: true });
        list.append(text, retry);
        list.setAttribute("role", "alert");
      } finally {
        list.removeAttribute("aria-busy");
      }
    };

    form?.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!form.reportValidity() || form.dataset.busy === "true") return;
      const productId = Number(form.elements.productId.value);
      const rating = Number(form.elements.rating.value);
      if (!Number.isSafeInteger(productId) || productId <= 0 || !Number.isInteger(rating) || rating < 1 || rating > 5) {
        message(feedback, "شناسه محصول و امتیاز باید معتبر باشند.", true);
        return;
      }
      busy(form, true);
      try {
        const payload = await request("/reviews", {
          method: "POST",
          body: JSON.stringify({
            productId,
            rating,
            title: form.elements.title.value.trim() || null,
            body: form.elements.body.value.trim(),
          }),
        });
        if (!payload?.success || !payload?.data?.review) {
          throw new Error(payload?.message || "ثبت دیدگاه تأیید نشد.");
        }
        message(feedback, payload.message || "دیدگاه ثبت شد و در انتظار بررسی است.");
        form.reset();
        await load();
      } catch (error) {
        fieldErrors(form, error.payload?.errors || error.payload?.data?.errors);
        message(feedback, error.message || "ثبت دیدگاه انجام نشد.", true);
      } finally {
        busy(form, false);
      }
    });

    load();
  };
  const init = () => { initProfile(); initAddresses(); initOrders(); initOrderDetail(); initReturns(); initReturnDetail(); initReviews(); };
  return { init };
})();
export default account;
