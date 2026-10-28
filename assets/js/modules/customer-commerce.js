const customerCommerce = () => {
  const baseUrl = window.NIVAD_BASE_URL || "";

  const csrf = async () => {
    let response;

    try {
      response = await fetch(
        `${baseUrl}/api/index.php?path=${encodeURIComponent("/auth/csrf")}`,
        {
          credentials: "same-origin",
          headers: { Accept: "application/json" },
        },
      );
    } catch {
      throw new Error("دریافت توکن امنیتی ممکن نشد؛ اتصال را بررسی کنید.");
    }

    const result = await response.json().catch(() => null);
    const token = result?.data?.token;

    if (!response.ok || typeof token !== "string" || token.length === 0) {
      throw new Error(result?.message || "دریافت توکن امنیتی انجام نشد. صفحه را تازه‌سازی کنید.");
    }

    return token;
  };

  const request = async (path, options = {}) => {
    const method = options.method || "GET";

    const headers = {
      Accept: "application/json",
      ...(options.headers || {}),
    };

    if (method !== "GET" && method !== "HEAD") {
      headers["Content-Type"] = options.body instanceof URLSearchParams ? "application/x-www-form-urlencoded;charset=UTF-8" : "application/json";

      const token = await csrf();
      headers["X-CSRF-TOKEN"] = token;
      if (options.body instanceof URLSearchParams) options.body.set("csrf_token", token);
    }

    let response;
    try {
      response = await fetch(`${baseUrl}/api/index.php?path=${encodeURIComponent(path)}`, { ...options, method, headers, credentials: "same-origin" });
    } catch { throw new Error("ارتباط با سرور برقرار نشد. اتصال را بررسی کنید."); }
    const payload = await response.json().catch(() => null);
    if (!payload || typeof payload !== "object") throw new Error("پاسخ سرور قابل خواندن نیست.");
    if (!response.ok || payload.success === false) {
      const error = new Error(payload.message || (response.status === 401 ? "برای ادامه دوباره وارد حساب شوید." : "درخواست انجام نشد."));
      error.status = response.status; error.payload = payload; throw error;
    }
    return payload;
  };

  const escape = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const initDashboard = () => {
    const wrapper = document.querySelector("[data-account-page]");
    if (!wrapper) return;
    const state = wrapper.querySelector("[data-account-state]");
    const security = wrapper.querySelector("[data-account-security]");
    const verification = wrapper.querySelector("[data-account-verification]");
    const sessionsNode = wrapper.querySelector("[data-account-sessions]");
    const passwordForm = wrapper.querySelector("[data-password-form]");
    const feedback = wrapper.querySelector("[data-account-message]");
    const show = (node, text, error = false) => {
      if (!node) return;
      node.textContent = text; node.setAttribute("role", error ? "alert" : "status"); node.setAttribute("aria-live", "polite");
    };
    const load = async () => {
      show(state, "در حال دریافت اطلاعات حساب...");
      try {
        const results = await Promise.allSettled([
          request("/account/security"),
          request("/account/verification-status"),
          request("/account/sessions"),
          request("/notifications/count"),
          request("/orders"),
        ]);
        const value = (index) => results[index].status === "fulfilled" ? results[index].value : null;
        const securityResult = value(0);
        const verificationResult = value(1);
        const sessionsResult = value(2);
        const notificationsResult = value(3);
        const ordersResult = value(4);
        const sessions = Array.isArray(sessionsResult?.data) ? sessionsResult.data : (Array.isArray(sessionsResult?.data?.sessions) ? sessionsResult.data.sessions : []);
        const orders = Array.isArray(ordersResult?.data?.orders) ? ordersResult.data.orders : [];

        if (security) {
          security.innerHTML = securityResult
            ? `<h2>امنیت حساب</h2><p>وضعیت حساب: ${escape(securityResult?.data?.account_status || "نامشخص")}</p><p>تأیید ایمیل: ${securityResult?.data?.email_verified ? "تأیید شده" : "تأیید نشده"}</p><p>نشست‌های دیگر: ${escape(securityResult?.data?.active_other_sessions ?? (sessionsResult ? sessions.length : "نامشخص"))}</p><p>آخرین ورود: ${escape(securityResult?.data?.last_login_at || "نامشخص")}</p>`
            : "<h2>امنیت حساب</h2><p role=\"alert\">دریافت اطلاعات امنیتی انجام نشد.</p>";
        }
        if (verification) {
          verification.innerHTML = verificationResult
            ? `<h2>تأیید حساب</h2><p>${verificationResult?.data?.fully_verified ? "ایمیل تأیید شده است." : "ایمیل هنوز تأیید نشده یا وضعیت آن نامشخص است."}</p>`
            : "<h2>تأیید حساب</h2><p role=\"alert\">دریافت وضعیت تأیید انجام نشد.</p>";
        }
        if (sessionsNode) {
          sessionsNode.innerHTML = sessionsResult
            ? (sessions.length ? sessions.map((item) => `<article class="account-session"><strong>${escape(item.user_agent || "دستگاه نامشخص")}</strong><p>IP: <span dir="ltr">${escape(item.ip_address || "نامشخص")}</span></p><p>آخرین فعالیت: ${escape(item.updated_at || "نامشخص")}</p><button type="button" data-revoke-session="${escape(item.id)}">بستن این نشست</button></article>`).join("") : "<p>نشست فعال دیگری وجود ندارد.</p>")
            : "<p role=\"alert\">دریافت نشست‌ها انجام نشد.</p>";
        }
        wrapper.querySelectorAll("[data-account-metric]").forEach((node) => {
          if (node.dataset.accountMetric === "notifications") node.textContent = notificationsResult ? String(notificationsResult?.data?.count ?? 0) : "—";
          if (node.dataset.accountMetric === "orders") node.textContent = ordersResult ? String(orders.length) : "—";
        });

        const failedCount = results.filter((result) => result.status === "rejected").length;
        const failed = failedCount > 0;
        show(
          state,
          failed
            ? `بخشی از اطلاعات حساب دریافت نشد (${failedCount} بخش). سایر بخش‌های دریافت‌شده همچنان قابل استفاده هستند.`
            : "اطلاعات حساب دریافت شد.",
          failed,
        );
        if (failed && state && !state.querySelector("[data-account-retry]")) {
          const retry = document.createElement("button");
          retry.type = "button";
          retry.dataset.accountRetry = "true";
          retry.textContent = "تلاش دوباره";
          retry.addEventListener("click", load, { once: true });
          state.append(retry);
        }
      } catch (error) {
        show(state, error.message || "دریافت اطلاعات حساب انجام نشد.", true);
        [security, verification, sessionsNode].forEach((node) => show(node, error.message || "دریافت اطلاعات ناموفق بود.", true));
      }
    };
    wrapper.addEventListener("click", async (event) => {
      const target = event.target instanceof Element ? event.target : null;
      const revoke = target?.closest("[data-revoke-session]"), others = target?.closest("[data-logout-other-sessions]");
      const button = revoke || others;
      if (!button || button.disabled) return;
      if (!window.confirm(revoke ? "این نشست بسته شود؟" : "تمام نشست‌های دیگر بسته شوند؟")) return;
      button.disabled = true;
      try {
        const result = revoke ? await request("/account/sessions/revoke", { method: "POST", body: new URLSearchParams({ session_id: String(Number(revoke.dataset.revokeSession)) }) }) : await request("/logout-other-sessions", { method: "POST", body: JSON.stringify({}) });
        show(feedback || state, result?.message || "عملیات نشست انجام شد."); await load();
      } catch (error) { show(feedback || state, error.message, true); }
      finally { button.disabled = false; }
    });
    passwordForm?.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!passwordForm.reportValidity() || passwordForm.dataset.busy === "true") return;
      if (passwordForm.elements.new_password.value !== passwordForm.elements.new_password_confirmation.value) {
        show(feedback, "تکرار رمز عبور جدید یکسان نیست.", true); passwordForm.elements.new_password_confirmation.focus(); return;
      }
      passwordForm.dataset.busy = "true";
      const submit = passwordForm.querySelector('button[type="submit"]');
      if (submit) { submit.disabled = true; submit.setAttribute("aria-busy", "true"); }
      try {
        const result = await request("/account/password", { method: "POST", body: new URLSearchParams({ current_password: passwordForm.elements.current_password.value, new_password: passwordForm.elements.new_password.value, new_password_confirmation: passwordForm.elements.new_password_confirmation.value }) });
        passwordForm.reset(); show(feedback, result?.message || "رمز عبور تغییر کرد.");
      } catch (error) { show(feedback, error.message, true); }
      finally { passwordForm.dataset.busy = "false"; if (submit) { submit.disabled = false; submit.removeAttribute("aria-busy"); } }
    });
    load();
  };

  const initNotifications = () => {
    const wrapper = document.querySelector("[data-account-notifications]");
    if (!wrapper) return;
    const list = wrapper.querySelector("[data-notifications-list]");
    if (!list) return;
    const load = async () => {
      list.setAttribute("aria-busy", "true");
      list.textContent = "در حال دریافت اعلان‌ها...";
      try {
        const result = await request("/notifications");
        const items = Array.isArray(result?.data?.items) ? result.data.items : [];
        list.innerHTML = items.length ? items.map((item) => `
          <article class="account-notification" data-notification-id="${escape(item.id)}">
            <strong>${escape(item.title || "اعلان")}</strong>
            <p>${escape(item.body || item.message || "")}</p>
            <time datetime="${escape(item.createdAt || item.created_at || "")}">${escape(item.createdAt || item.created_at || "")}</time>
            ${(item.isRead ?? item.is_read) ? "" : `<button type="button" data-notification-read="${escape(item.id)}">خوانده شد</button>`}
          </article>`).join("") : "<p>اعلانی وجود ندارد.</p>";
      } catch (error) { list.textContent = error.message || "دریافت اعلان‌ها ناموفق بود."; }
      finally { list.removeAttribute("aria-busy"); }
    };
    wrapper.addEventListener("click", async (event) => {
      const target = event.target instanceof Element ? event.target : null;
      const button = target?.closest("[data-notification-read]");
      if (!button || button.disabled) return;
      const id = Number(button.dataset.notificationRead);
      if (!Number.isSafeInteger(id) || id <= 0) return;
      button.disabled = true;
      try { await request(`/notifications/${id}/read`, { method: "PATCH", body: JSON.stringify({}) }); await load(); }
      catch (error) { button.disabled = false; list.textContent = error.message || "به‌روزرسانی اعلان ناموفق بود."; }
    });
    wrapper.querySelector("[data-notifications-read-all]")?.addEventListener("click", async (event) => {
      const button = event.currentTarget;
      if (button.disabled) return;
      button.disabled = true;
      try { await request("/notifications/read-all", { method: "PATCH", body: JSON.stringify({}) }); await load(); }
      catch (error) { list.textContent = error.message || "به‌روزرسانی اعلان‌ها ناموفق بود."; }
      finally { button.disabled = false; }
    });
    load();
  };

  const initReviews = () => {
    const wrapper = document.querySelector("[data-account-reviews]");
    const form = document.querySelector("[data-account-review-form]");
    if (!wrapper) return;
    const feedback = form?.querySelector("[data-review-message]");
    const load = async () => {
      wrapper.setAttribute("aria-busy", "true");
      wrapper.textContent = "در حال دریافت دیدگاه‌ها...";
      try {
        const result = await request("/reviews/mine");
        const items = Array.isArray(result?.data?.items) ? result.data.items : [];
        wrapper.innerHTML = items.length ? items.map((item) => `
          <article class="account-review"><h2>${escape(item.title || "دیدگاه")}</h2><p>${escape(item.body || "")}</p>
          <span>امتیاز: ${escape(item.rating ?? "—")}</span><span>وضعیت: ${escape(item.status || "در انتظار بررسی")}</span></article>`).join("") : "<p>هنوز دیدگاهی ثبت نکرده‌اید.</p>";
      } catch (error) { wrapper.textContent = error.message || "دریافت دیدگاه‌ها ناموفق بود."; }
      finally { wrapper.removeAttribute("aria-busy"); }
    };
    form?.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (!form.reportValidity() || form.dataset.busy === "true") return;
      const productId = Number(form.elements.productId.value), rating = Number(form.elements.rating.value);
      if (!Number.isSafeInteger(productId) || productId < 1 || !Number.isInteger(rating) || rating < 1 || rating > 5) {
        if (feedback) feedback.textContent = "شناسه محصول و امتیاز را بررسی کنید.";
        return;
      }
      form.dataset.busy = "true";
      const submit = form.querySelector('button[type="submit"]');
      if (submit) { submit.disabled = true; submit.setAttribute("aria-busy", "true"); }
      try {
        const result = await request("/reviews", { method: "POST", body: JSON.stringify({
          productId, rating, title: form.elements.title.value.trim(), body: form.elements.body.value.trim(),
        }) });
        if (!result?.data?.review) throw new Error(result?.message || "ثبت دیدگاه تأیید نشد.");
        form.reset();
        if (feedback) { feedback.textContent = result.message || "دیدگاه ثبت شد."; feedback.setAttribute("role", "status"); }
        await load();
      } catch (error) {
        if (feedback) { feedback.textContent = error.message || "ثبت دیدگاه ناموفق بود."; feedback.setAttribute("role", "alert"); }
      } finally {
        form.dataset.busy = "false";
        if (submit) { submit.disabled = false; submit.removeAttribute("aria-busy"); }
      }
    });
    load();
  };

  const initCheckout = () => {
    const wrapper = document.querySelector("[data-customer-checkout]");

    if (!wrapper) return;

    const load = async () => {
      const result = await request("/checkout");

      if (!result?.data) {
        wrapper.innerHTML = `
          <p>
            ${escape(result?.message || "اطلاعات Checkout قابل دریافت نیست.")}
          </p>
        `;
        return;
      }

      const data = result.data;

      wrapper.innerHTML = `
        <section>
          <h1>تکمیل سفارش</h1>

          <p>
            تعداد کالا:
            ${escape(data.totalQuantity ?? data.itemCount ?? "")}
          </p>

          <p>
            مبلغ:
            ${escape(data.subtotal ?? data.total ?? "")}
          </p>

          <p>
            آدرس:
            ${escape(data.address?.address || "")}
          </p>
          <form data-place-order>
            <input
              type="text"
              name="customerNote"
              placeholder="توضیحات سفارش"
            >

            <button type="submit">
              ثبت سفارش
            </button>
          </form>
        </section>
      `;

      const couponBox = document.createElement("div");

      couponBox.innerHTML = `
  <div class="checkout-coupon">
    <label>
      کد تخفیف
    </label>

    <input
      type="text"
      data-coupon-code
      placeholder="کد تخفیف">

    <button
      type="button"
      data-coupon-apply>
      اعمال
    </button>

    <p data-coupon-message></p>
  </div>
`;

      wrapper.querySelector("section")?.appendChild(couponBox);

      let appliedCoupon = null;

      couponBox
        .querySelector("[data-coupon-apply]")
        ?.addEventListener("click", async () => {
          const code = couponBox
            .querySelector("[data-coupon-code]")
            ?.value.trim();

          if (!code) return;

          const result = await request("/coupons/validate", {
            method: "POST",
            body: JSON.stringify({
              code,
              subtotal: data.subtotal ?? data.total ?? "0",
            }),
          });

          const message = couponBox.querySelector("[data-coupon-message]");

          if (message) {
            message.textContent = result?.message || "";
          }

          if (result?.success) {
            appliedCoupon = result.data;

            const discount = result.data?.discount;

            const discountText = document.createElement("p");

            discountText.textContent = `تخفیف: ${
              discount?.discount_amount || "0"
            }`;

            couponBox.appendChild(discountText);
          }
        });

      const form = wrapper.querySelector("[data-place-order]");

      form?.addEventListener("submit", async (event) => {
        event.preventDefault();

        const order = await request("/orders", {
          method: "POST",
          body: JSON.stringify({
            customerNote: form.elements.customerNote.value || null,

            couponId: appliedCoupon?.coupon?.id || null,
          }),
        });

        const orderId = order?.data?.id || order?.data?.order?.id;

        if (!orderId) {
          wrapper.insertAdjacentHTML(
            "beforeend",
            `<p>${escape(order?.message || "ثبت سفارش انجام نشد.")}</p>`,
          );
          return;
        }

        const attempts = await request(
          `/payments/attempts/by-order/${encodeURIComponent(orderId)}`,
        );

        const attemptsData = attempts?.data?.attempts || [];

        const attempt = attemptsData[attemptsData.length - 1] || null;

        if (!attempt?.id) {
          wrapper.insertAdjacentHTML(
            "beforeend",
            `
      <p>
        سفارش ثبت شد اما تلاش پرداخت هنوز ایجاد نشده است.
      </p>
    `,
          );
          return;
        }

        window.location.href = `${baseUrl}/payment/success?order_id=${encodeURIComponent(orderId)}&attempt_id=${encodeURIComponent(attempt.id)}`;
      });
    };

    load();
  };

  initDashboard();
  initNotifications();
  initReviews();
};

export default customerCommerce;
