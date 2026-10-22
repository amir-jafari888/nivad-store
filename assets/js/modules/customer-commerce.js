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

    const response = await fetch(
      `${baseUrl}/api/index.php?path=${encodeURIComponent(path)}`,
      {
        ...options,
        method,
        headers,
        credentials: "same-origin",
      },
    );

    return response.json();
  };

  const escape = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const initDashboard = () => {
    const wrapper = document.querySelector("[data-account-dashboard]");

    if (!wrapper) return;

    const load = async () => {
      const [security, verification, sessions, notifications, orders] =
        await Promise.all([
          request("/account/security"),
          request("/account/verification-status"),
          request("/account/sessions"),
          request("/notifications/count"),
          request("/orders"),
        ]);

      const sessionsData = sessions?.data?.sessions || [];

      const ordersData = orders?.data?.orders || [];

      wrapper.innerHTML = `
        <section class="account-summary">
          <div>
            <strong>امنیت حساب</strong>
            <span>
              ${security?.data ? "فعال" : "نامشخص"}
            </span>
          </div>

          <div>
            <strong>تأیید حساب</strong>
            <span>
              ${verification?.data ? "بررسی شد" : "نامشخص"}
            </span>
          </div>

          <div>
            <strong>جلسات فعال</strong>
            <span>
              ${sessionsData.length}
            </span>
          </div>

          <div>
            <strong>اعلان خوانده‌نشده</strong>
            <span>
              ${notifications?.data?.count || 0}
            </span>
          </div>

          <div>
            <strong>سفارش‌ها</strong>
            <span>
              ${ordersData.length}
            </span>
          </div>
        </section>

        <nav class="account-navigation">
          <a href="?page=account/profile">
            پروفایل
          </a>

          <a href="?page=account/addresses">
            آدرس‌ها
          </a>

          <a href="?page=account/orders/index">
            سفارش‌ها
          </a>

          <a href="?page=account/returns/index">
            مرجوعی‌ها
          </a>

          <a href="?page=account/reviews">
            دیدگاه‌ها
          </a>

          <a href="?page=account/notifications">
            اعلان‌ها
          </a>

          <a href="${baseUrl}/wishlist">
              علاقه‌مندی‌ها
          </a>  

          <a href="${baseUrl}/cart">
              سبد خرید
          </a>
        </nav>
      `;
    };

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
