const customerCommerce = () => {
  const baseUrl = window.NIVAD_BASE_URL || "";

  const csrf = async () => {
    const response = await fetch(
      `${baseUrl}/api/index.php?path=${encodeURIComponent("/auth/csrf")}`,
      {
        credentials: "same-origin",
      },
    );

    const result = await response.json();

    return result?.data?.token || "";
  };

  const request = async (path, options = {}) => {
    const method = options.method || "GET";

    const headers = {
      Accept: "application/json",
      ...(options.headers || {}),
    };

    if (method !== "GET" && method !== "HEAD") {
      headers["Content-Type"] = "application/json";

      const token = await csrf();

      if (token) {
        headers["X-CSRF-TOKEN"] = token;
      }
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
        const [securityResult, verificationResult, sessionsResult, notificationsResult, ordersResult] = await Promise.all([
          request("/account/security"), request("/account/verification-status"), request("/account/sessions"),
          request("/notifications/count"), request("/orders"),
        ]);
        const sessions = Array.isArray(sessionsResult?.data) ? sessionsResult.data : (Array.isArray(sessionsResult?.data?.sessions) ? sessionsResult.data.sessions : []);
        const orders = Array.isArray(ordersResult?.data?.orders) ? ordersResult.data.orders : [];
        if (security) security.innerHTML = `<h2>امنیت حساب</h2><p>${escape(securityResult?.message || "اطلاعات امنیت حساب دریافت شد.")}</p>`;
        if (verification) {
          const verified = Boolean(verificationResult?.data?.email_verified ?? verificationResult?.data?.verified ?? verificationResult?.data?.is_verified);
          verification.innerHTML = `<h2>تأیید حساب</h2><p>${verified ? "ایمیل تأیید شده است." : "ایمیل هنوز تأیید نشده یا وضعیت آن نامشخص است."}</p>`;
        }
        if (sessionsNode) sessionsNode.innerHTML = sessions.length ? sessions.map((item) => `<article class="account-session"><strong>${escape(item.user_agent || "دستگاه نامشخص")}</strong><p>IP: <span dir="ltr">${escape(item.ip_address || "نامشخص")}</span></p><p>آخرین فعالیت: ${escape(item.updated_at || "نامشخص")}</p><button type="button" data-revoke-session="${escape(item.id)}">بستن این نشست</button></article>`).join("") : "<p>نشست فعال دیگری وجود ندارد.</p>";
        wrapper.querySelectorAll("[data-account-metric]").forEach((node) => {
          if (node.dataset.accountMetric === "notifications") node.textContent = String(notificationsResult?.data?.count ?? 0);
          if (node.dataset.accountMetric === "orders") node.textContent = String(orders.length);
        });
        show(state, "اطلاعات حساب دریافت شد.");
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
        const result = revoke ? await request("/account/sessions/revoke", { method: "POST", body: JSON.stringify({ session_id: Number(revoke.dataset.revokeSession) }) }) : await request("/logout-other-sessions", { method: "POST", body: JSON.stringify({}) });
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
        const result = await request("/account/password", { method: "POST", body: JSON.stringify({ current_password: passwordForm.elements.current_password.value, new_password: passwordForm.elements.new_password.value, new_password_confirmation: passwordForm.elements.new_password_confirmation.value }) });
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

    const load = async () => {
      const result = await request("/notifications");

      const items = result?.data?.items || [];

      list.innerHTML = items.length
        ? items
            .map(
              (item) => `
                  <article
                    class="account-notification"
                    data-notification-id="${escape(item.id)}"
                  >
                    <strong>
                      ${escape(item.title)}
                    </strong>

                    <p>
                      ${escape(item.body)}
                    </p>

                    <span>
                      ${escape(item.createdAt)}
                    </span>

                    ${
                      item.isRead
                        ? ""
                        : `
                          <button
                            type="button"
                            data-notification-read="${escape(item.id)}"
                          >
                            خوانده شد
                          </button>
                        `
                    }
                  </article>
                `,
            )
            .join("")
        : "<p>اعلانی وجود ندارد.</p>";
    };

    wrapper.addEventListener("click", async (event) => {
      const button = event.target.closest("[data-notification-read]");

      if (!button) return;

      const id = Number(button.dataset.notificationRead);

      if (!id) return;

      await request(`/notifications/${id}/read`, {
        method: "PATCH",
        body: JSON.stringify({}),
      });

      await load();
    });

    const readAllButton = wrapper.querySelector(
      "[data-notifications-read-all]",
    );

    readAllButton?.addEventListener("click", async () => {
      await request("/notifications/read-all", {
        method: "PATCH",
        body: JSON.stringify({}),
      });

      await load();
    });

    load();
  };

  const initReviews = () => {
    const wrapper = document.querySelector("[data-account-reviews]");

    const form = document.querySelector("[data-account-review-form]");

    if (!wrapper) return;

    const load = async () => {
      const result = await request("/reviews/mine");

      const items = result?.data?.items || [];

      wrapper.innerHTML = items.length
        ? items
            .map(
              (item) => `
                  <article class="account-review">
                    <h2>
                      ${escape(item.title || "دیدگاه")}
                    </h2>

                    <p>
                      ${escape(item.body)}
                    </p>

                    <span>
                      امتیاز:
                      ${escape(item.rating)}
                    </span>

                    <span>
                      وضعیت:
                      ${escape(item.status)}
                    </span>
                  </article>
                `,
            )
            .join("")
        : "<p>هنوز دیدگاهی ثبت نکرده‌اید.</p>";
    };

    form?.addEventListener("submit", async (event) => {
      event.preventDefault();

      const productId = Number(form.elements.productId.value);

      const rating = Number(form.elements.rating.value);

      const title = form.elements.title.value;

      const body = form.elements.body.value;

      const result = await request("/reviews", {
        method: "POST",
        body: JSON.stringify({
          productId,
          rating,
          title,
          body,
        }),
      });

      const message = form.querySelector("[data-review-message]");

      if (message) {
        message.textContent = result?.message || "عملیات انجام شد.";
      }

      if (result?.data?.review) {
        form.reset();
        await load();
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
