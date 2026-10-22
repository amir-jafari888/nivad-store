const checkout = () => {
  const wrapper =
    document.querySelector(
      "[data-customer-checkout]",
    );

  if (!wrapper) return;

  const apiBase =
    wrapper.dataset.apiBase || "";

  const baseUrl =
    window.NIVAD_BASE_URL || "";

  const state = {
    data: null,
    addresses: [],
    addressId: null,
    shippingMethodId: null,
    coupon: null,
    busy: false,
  };

  const qs = (selector) =>
    wrapper.querySelector(selector);

  const escape = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const formatPrice = (
    value,
    currency = "IRR",
  ) =>
    `${escape(value ?? "0")} ${
      currency === "IRR"
        ? "ریال"
        : escape(currency)
    }`;

  const setState = (message) => {
    const node =
      qs("[data-checkout-state]");

    if (node) {
      node.textContent = message;
    }
  };

  const setError = (message = "") => {
    const node =
      qs("[data-checkout-error]");

    if (node) {
      node.textContent = message;
    }
  };

  const csrf = async () => {
    const response =
      await fetch(
        `${apiBase}?path=${encodeURIComponent(
          "/auth/csrf",
        )}`,
        {
          credentials: "same-origin",
          headers: {
            Accept: "application/json",
          },
        },
      );

    if (!response.ok) {
      return "";
    }

    const result =
      await response.json();

    return result?.data?.token || "";
  };

  const request = async (
    path,
    options = {},
  ) => {
    const method =
      options.method || "GET";

    const headers = {
      Accept: "application/json",
      ...(options.headers || {}),
    };

    if (
      method !== "GET" &&
      method !== "HEAD"
    ) {
      headers["Content-Type"] =
        "application/json";

      const token = await csrf();

      if (token) {
        headers["X-CSRF-TOKEN"] =
          token;
      }
    }

    const response =
      await fetch(
        `${apiBase}?path=${encodeURIComponent(
          path,
        )}`,
        {
          ...options,
          method,
          headers,
          credentials: "same-origin",
        },
      );

    const data =
      await response
        .json()
        .catch(() => ({}));

    if (!response.ok) {
      throw new Error(
        data?.message ||
          "درخواست با خطا مواجه شد.",
      );
    }

    return data;
  };

  const renderSummary = () => {
    const data = state.data;

    const shipping =
      state.shippingMethodId
        ? data?.shipping?.methods?.find(
            (item) =>
              Number(item.id) ===
              Number(
                state.shippingMethodId,
              ),
          )
        : null;

    const subtotal =
      Number(data?.subtotal || 0);

    const shippingCost =
      Number(shipping?.cost || 0);

    const discount =
      Number(
        state.coupon?.discount
          ?.discount_amount || 0,
      );

    const total =
      Math.max(
        0,
        subtotal +
          shippingCost -
          discount,
      );

    const node =
      qs("[data-checkout-summary]");

    if (!node) return;

    node.innerHTML = `
      <section class="checkout-card">
        <h2>خلاصه سفارش</h2>

        <dl>
          <div>
            <dt>تعداد کالا</dt>
            <dd>
              ${escape(
                data?.totalQuantity ?? 0,
              )}
            </dd>
          </div>

          <div>
            <dt>مبلغ کالاها</dt>
            <dd>
              ${formatPrice(
                data?.subtotal,
                data?.currency,
              )}
            </dd>
          </div>

          <div>
            <dt>ارسال</dt>
            <dd>
              ${formatPrice(
                shipping?.cost || "0",
                data?.currency,
              )}
            </dd>
          </div>

          ${
            discount
              ? `
                <div>
                  <dt>تخفیف</dt>
                  <dd>
                    ${formatPrice(
                      discount,
                      data?.currency,
                    )}
                  </dd>
                </div>
              `
              : ""
          }

          <div class="checkout-total">
            <dt>مبلغ نهایی</dt>
            <dd>
              ${formatPrice(
                total.toFixed(4),
                data?.currency,
              )}
            </dd>
          </div>
        </dl>
      </section>
    `;
  };

  const renderAddresses = () => {
    const node =
      qs("[data-checkout-address]");

    if (!node) return;

    node.innerHTML = `
      <section class="checkout-card">
        <div class="checkout-card__head">
          <div>
            <span class="checkout-card__eyebrow">
              مرحله ۲
            </span>
            <h2>آدرس تحویل</h2>
          </div>

          <a
            href="${escape(
              `${baseUrl}/?page=account/addresses`,
            )}"
            data-address-manage>
            مدیریت آدرس‌ها
          </a>
        </div>

        <div class="checkout-addresses">
          ${
            state.addresses
              .map(
                (address) => `
                  <label class="checkout-address">
                    <input
                      type="radio"
                      name="checkoutAddress"
                      value="${escape(
                        address.id,
                      )}"
                      ${
                        Number(address.id) ===
                        Number(
                          state.addressId,
                        )
                          ? "checked"
                          : ""
                      }>

                    <span>
                      <strong>
                        ${escape(
                          address.title,
                        )}
                      </strong>

                      <small>
                        ${escape(
                          address.recipient_name,
                        )}
                        ·
                        ${escape(
                          address.phone,
                        )}
                      </small>

                      <small>
                        ${escape(
                          address.province,
                        )}
                       ،
                        ${escape(
                          address.city,
                        )}
                       ،
                        ${escape(
                          address.address,
                        )}
                      </small>
                    </span>
                  </label>
                `,
              )
              .join("") ||
            `<p>
              هیچ آدرس معتبری برای Checkout وجود ندارد.
            </p>`
          }
        </div>
      </section>
    `;
  };

  const renderShipping = () => {
    const node =
      qs("[data-checkout-shipping]");

    if (!node) return;

    const methods =
      state.data?.shipping?.methods ||
      [];

    node.innerHTML = `
      <section class="checkout-card">
        <div class="checkout-card__head">
          <div>
            <span class="checkout-card__eyebrow">
              مرحله ۳
            </span>
            <h2>روش ارسال</h2>
          </div>
        </div>

        <div class="checkout-shipping">
          ${
            methods
              .map(
                (method) => `
                  <label class="checkout-shipping__option">
                    <input
                      type="radio"
                      name="shippingMethod"
                      value="${escape(
                        method.id,
                      )}"
                      ${
                        Number(method.id) ===
                        Number(
                          state.shippingMethodId,
                        )
                          ? "checked"
                          : ""
                      }>

                    <span>
                      <strong>
                        ${escape(
                          method.name,
                        )}
                      </strong>

                      <small>
                        ${escape(
                          method.description ||
                            "ارسال به آدرس انتخاب‌شده",
                        )}
                      </small>

                      <small>
                        ${
                          method.estimated_min_days !=
                          null
                            ? `تحویل حدود
                              ${escape(
                                method.estimated_min_days,
                              )}
                              تا
                              ${escape(
                                method.estimated_max_days ??
                                  method.estimated_min_days,
                              )}
                              روز`
                            : "زمان تحویل مشخص نشده"
                        }
                      </small>
                    </span>

                    <b>
                      ${formatPrice(
                        method.cost,
                        method.currency,
                      )}
                    </b>
                  </label>
                `,
              )
              .join("") ||
            `<p>
              برای این آدرس روش ارسال قابل استفاده‌ای پیدا نشد.
            </p>`
          }
        </div>
      </section>
    `;
  };

  const renderReview = () => {
    const node =
      qs("[data-checkout-review]");

    if (!node) return;

    const address =
      state.addresses.find(
        (item) =>
          Number(item.id) ===
          Number(state.addressId),
      );

    node.innerHTML = `
      <section class="checkout-card">
        <div class="checkout-card__head">
          <div>
            <span class="checkout-card__eyebrow">
              مرحله ۴
            </span>
            <h2>بازبینی نهایی</h2>
          </div>
        </div>

        <div class="checkout-review">
          <p>
            <strong>تحویل به:</strong>
            ${escape(
              address?.recipient_name ||
                state.data?.address
                  ?.recipient_name ||
                "",
            )}
          </p>

          <p>
            ${escape(
              address?.province ||
                state.data?.address
                  ?.province ||
                "",
            )}
           ،
            ${escape(
              address?.city ||
                state.data?.address?.city ||
                "",
            )}
          </p>

          <p>
            ${escape(
              address?.address ||
                state.data?.address
                  ?.address ||
                "",
            )}
          </p>
        </div>

        <div class="checkout-coupon">
          <label for="checkout-coupon-code">
            کد تخفیف
          </label>

          <div>
            <input
              id="checkout-coupon-code"
              name="coupon"
              type="text"
              data-coupon-code
              autocomplete="off">

            <button
              type="button"
              data-coupon-apply>
              اعمال
            </button>
          </div>

          <p
            data-coupon-message
            aria-live="polite">
          </p>
        </div>

        <label class="checkout-note">
          یادداشت سفارش

          <textarea
            data-customer-note
            maxlength="1000"
            rows="3">
          </textarea>
        </label>

        <button
          type="button"
          class="checkout-primary"
          data-place-order
          ${
            state.busy ||
            !state.shippingMethodId
              ? "disabled"
              : ""
          }>
          ثبت سفارش و ادامه
        </button>
      </section>
    `;
  };

  const loadAddresses = async () => {
    const result =
      await request(
        "/account/addresses",
      );

    state.addresses =
      result?.data?.addresses ||
      result?.data ||
      [];

    const selected =
      state.addresses.find(
        (item) =>
          item.is_default ||
          item.isDefault,
      );

    state.addressId =
      state.addressId ||
      selected?.id ||
      state.data?.address?.id ||
      null;

    renderAddresses();
  };

  const loadCheckout = async () => {
    const path =
      state.addressId
        ? `/checkout?address_id=${encodeURIComponent(
            state.addressId,
          )}`
        : "/checkout";

    const result =
      await request(path);

    state.data = result.data;

    state.addressId =
      state.data?.address?.id ||
      state.addressId;

    state.shippingMethodId =
      state.shippingMethodId ||
      state.data?.shipping
        ?.methods?.[0]?.id ||
      null;

    renderAddresses();
    renderShipping();
    renderReview();
    renderSummary();

    setState(
      "اطلاعات سفارش آماده است.",
    );
  };

  const applyCoupon = async () => {
    const input =
      qs("[data-coupon-code]");

    const message =
      qs("[data-coupon-message]");

    const code =
      input?.value.trim();

    if (!code) {
      if (message) {
        message.textContent =
          "کد تخفیف را وارد کنید.";
      }

      return;
    }

    try {
      const result =
        await request(
          "/coupons/validate",
          {
            method: "POST",
            body: JSON.stringify({
              code,
              subtotal:
                state.data?.subtotal ||
                "0",
            }),
          },
        );

      state.coupon = result.data;

      if (message) {
        message.textContent =
          result.message ||
          "کد تخفیف اعمال شد.";
      }

      renderSummary();
    } catch (error) {
      state.coupon = null;

      if (message) {
        message.textContent =
          error.message;
      }

      renderSummary();
    }
  };

  const placeOrder = async () => {
    if (
      state.busy ||
      !state.addressId ||
      !state.shippingMethodId
    ) {
      return;
    }

    state.busy = true;
    setError("");
    renderReview();

    try {
      const note =
        qs("[data-customer-note]")
          ?.value.trim() ||
        null;

      const result =
        await request(
          "/orders",
          {
            method: "POST",
            body: JSON.stringify({
              addressId:
                Number(state.addressId),
              shippingMethodId:
                Number(
                  state.shippingMethodId,
                ),
              customerNote: note,
              couponId:
                state.coupon?.coupon?.id ||
                null,
            }),
          },
        );

      const order =
        result?.data?.order ||
        result?.data;

      if (!order?.id) {
        throw new Error(
          "سفارش ایجاد شد اما شناسه سفارش دریافت نشد.",
        );
      }

      setState(
        `سفارش شماره ${
          order.orderNumber ||
          order.id
        } ثبت شد.`,
      );

      const node =
        qs("[data-checkout-review]");

      if (node) {
        node.innerHTML = `
          <section class="checkout-card checkout-success">
            <h2>سفارش ثبت شد</h2>

            <p>
              سفارش شما آماده انتقال به مرحله پرداخت است.
            </p>

            <strong>
              شماره سفارش:
              ${escape(
                order.orderNumber ||
                  order.id,
              )}
            </strong>

            <a
              class="checkout-primary"
              href="${escape(
                `${baseUrl}/?page=account/orders/index`,
              )}">
              مشاهده سفارش
            </a>
          </section>
        `;
      }
    } catch (error) {
      setError(error.message);
      state.busy = false;
      renderReview();
    }
  };

  wrapper.addEventListener(
    "change",
    async (event) => {
      const address =
        event.target.closest(
          "input[name=checkoutAddress]",
        );

      if (address) {
        state.addressId =
          Number(address.value);

        state.shippingMethodId =
          null;

        await loadCheckout();

        return;
      }

      const shipping =
        event.target.closest(
          "input[name=shippingMethod]",
        );

      if (shipping) {
        state.shippingMethodId =
          Number(shipping.value);

        renderReview();
        renderSummary();
      }
    },
  );

  wrapper.addEventListener(
    "click",
    async (event) => {
      if (
        event.target.closest(
          "[data-coupon-apply]",
        )
      ) {
        await applyCoupon();
      }

      if (
        event.target.closest(
          "[data-place-order]",
        )
      ) {
        await placeOrder();
      }
    },
  );

  const init = async () => {
    try {
      await loadAddresses();
      await loadCheckout();
    } catch (error) {
      setState(
        "امکان آماده‌سازی Checkout وجود ندارد.",
      );

      setError(error.message);
    }
  };

  init();
};

export default checkout;