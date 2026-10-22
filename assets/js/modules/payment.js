const payment = () => {
  const baseUrl = window.NIVAD_BASE_URL || "";
  const success = document.querySelector("[data-payment-success]");
  const failed = document.querySelector("[data-payment-failed]");

  if (!success && !failed) return;

  const params = new URLSearchParams(window.location.search);
  const orderId = Number(params.get("order_id"));
  let attemptId = Number(params.get("attempt_id"));
  let attempt = null;
  let busy = false;

  const csrf = async () => {
    const response = await fetch(
      `${baseUrl}/api/index.php?path=${encodeURIComponent("/auth/csrf")}`,
      {
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
        },
      },
    );

    const result = await response.json().catch(() => ({}));

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

    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(result?.message || "درخواست با خطا مواجه شد.");
    }

    return result;
  };

  const escape = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const renderState = (
    wrapper,
    state,
    title,
    message,
    currentAttempt = null,
  ) => {
    if (!wrapper) return;

    wrapper.dataset.paymentState = state;
    wrapper.setAttribute("aria-busy", "false");

    const retryable = ["failed", "cancelled", "expired"].includes(state);

    const recovery = state === "error";

    wrapper.innerHTML = `
      <div class="payment-result" data-payment-result>
        <span
          class="payment-result__state"
          aria-hidden="true">
        </span>

        <h1>
          ${escape(title)}
        </h1>

        <p>
          ${escape(message)}
        </p>

        ${
          currentAttempt?.id
            ? `
              <p>
                شناسه تلاش پرداخت:
                ${escape(currentAttempt.id)}
              </p>
            `
            : ""
        }

        ${
          orderId
            ? `
              <p>
                شماره سفارش:
                ${escape(orderId)}
              </p>
            `
            : ""
        }

        <div class="payment-result__actions">
          ${
            retryable
              ? `
                <button
                  type="button"
                  data-payment-retry>
                  تلاش دوباره
                </button>
              `
              : ""
          }

          ${
            recovery
              ? `
                <button
                  type="button"
                  data-payment-recover>
                  بازیابی وضعیت پرداخت
                </button>
              `
              : ""
          }

          <a href="${escape(`${baseUrl}/account/orders`)}">
            مشاهده سفارش‌ها
          </a>
        </div>
      </div>
    `;

    wrapper
      .querySelector("[data-payment-retry]")
      ?.addEventListener("click", retry);

    wrapper
      .querySelector("[data-payment-recover]")
      ?.addEventListener("click", load);
  };

  const setLoading = (wrapper, message = "در حال بررسی وضعیت پرداخت...") => {
    if (!wrapper) return;

    wrapper.dataset.paymentState = "loading";
    wrapper.setAttribute("aria-busy", "true");

    wrapper.innerHTML = `
      <p>
        ${escape(message)}
      </p>
    `;
  };

  const retry = async () => {
    if (busy || !attempt || !attempt.orderId) return;

    busy = true;

    setLoading(success || failed, "در حال ایجاد تلاش جدید برای پرداخت...");

    try {
      const result = await request("/payments/attempts", {
        method: "POST",
        body: JSON.stringify({
          orderId: attempt.orderId,
          gateway: attempt.gateway,
          amount: attempt.amount,
          currency: attempt.currency,
        }),
      });

      const nextAttempt = result?.data || null;

      if (!nextAttempt?.id) {
        throw new Error(result?.message || "تلاش جدید برای پرداخت ایجاد نشد.");
      }

      attemptId = Number(nextAttempt.id);
      attempt = nextAttempt;

      const target =
        `${window.location.pathname}` +
        `?order_id=${encodeURIComponent(orderId || nextAttempt.orderId)}` +
        `&attempt_id=${encodeURIComponent(nextAttempt.id)}`;

      window.history.replaceState({}, "", target);

      await load(true);
    } catch (error) {
      renderState(
        success || failed,
        "error",
        "بازیابی پرداخت انجام نشد.",
        error?.message || "امکان ایجاد تلاش جدید وجود ندارد.",
        attempt,
      );
    } finally {
      busy = false;
    }
  };

  const load = async (force = false) => {
    if (busy && attemptId && !force) return;

    const wrapper = success || failed;

    setLoading(wrapper);

    if (!attemptId) {
      renderState(
        failed,
        "error",
        "اطلاعات پرداخت ناقص است.",
        "شناسه تلاش پرداخت دریافت نشد.",
      );

      return;
    }

    try {
      const result = await request(
        `/payments/attempts/${encodeURIComponent(attemptId)}`,
      );

      const data = result?.data || null;

      if (!data) {
        throw new Error(result?.message || "تلاش پرداخت موردنظر پیدا نشد.");
      }

      if (orderId && Number(data.orderId) !== orderId) {
        throw new Error("تلاش پرداخت با سفارش فعلی مطابقت ندارد.");
      }

      attempt = data;

      switch (data.status) {
        case "success":
          renderState(
            success,
            "success",
            "پرداخت با موفقیت انجام شد.",
            "پرداخت سفارش شما با موفقیت ثبت شده است.",
            data,
          );
          break;

        case "failed":
          renderState(
            failed,
            "failed",
            "پرداخت ناموفق بود.",
            data.errorMessage || "پرداخت سفارش انجام نشد.",
            data,
          );
          break;

        case "cancelled":
          renderState(
            failed,
            "cancelled",
            "پرداخت لغو شد.",
            "تلاش پرداخت لغو شده است. می‌توانید دوباره تلاش کنید.",
            data,
          );
          break;

        case "expired":
          renderState(
            failed,
            "expired",
            "زمان پرداخت به پایان رسید.",
            "تلاش پرداخت منقضی شده است. می‌توانید دوباره تلاش کنید.",
            data,
          );
          break;

        case "pending":
        case "initiated":
          renderState(
            success,
            data.status,
            "پرداخت در حال پردازش است.",
            "تلاش پرداخت ایجاد شده اما هنوز پرداخت نهایی نشده است.",
            data,
          );
          break;

        default:
          renderState(
            failed,
            "error",
            "وضعیت پرداخت مشخص نیست.",
            `وضعیت فعلی: ${data.status || "unknown"}`,
            data,
          );
      }
    } catch (error) {
      renderState(
        failed,
        "error",
        "دریافت وضعیت پرداخت ناموفق بود.",
        error?.message || "ارتباط با سرویس پرداخت برقرار نشد.",
        attempt,
      );
    }
  };

  load();
};

export default payment;
