const account = (() => {
  const baseUrl = window.NIVAD_BASE_URL || "";

  const getCsrf = async () => {
    const response = await fetch(`${baseUrl}/api/index.php?path=/auth/csrf`, {
      credentials: "same-origin",
    });

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

      const token = await getCsrf();

      if (token !== "") {
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

  const escapeHtml = (value) =>
    String(value ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const initProfile = () => {
    const form = document.querySelector("[data-account-profile-form]");

    if (!form) {
      return;
    }

    const message = form.querySelector("[data-account-profile-message]");

    const load = async () => {
      const result = await request("/account/profile");

      const user = result?.data?.user;

      if (!user) {
        return;
      }

      form.elements.first_name.value = user.first_name || "";

      form.elements.last_name.value = user.last_name || "";

      form.elements.email.value = user.email || "";

      form.elements.phone.value = user.phone || "";
    };

    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      const result = await request("/account/profile", {
        method: "PATCH",
        body: JSON.stringify({
          first_name: form.elements.first_name.value,
          last_name: form.elements.last_name.value,
        }),
      });

      if (message) {
        message.textContent = result?.message || "عملیات انجام شد.";
      }

      if (result?.data?.user) {
        form.elements.first_name.value = result.data.user.first_name;

        form.elements.last_name.value = result.data.user.last_name;
      }
    });

    load();
  };

  const addressFormData = (form) => ({
    title: form.elements.title.value,
    recipient_name: form.elements.recipient_name.value,
    phone: form.elements.phone.value,
    province: form.elements.province.value,
    city: form.elements.city.value,
    address: form.elements.address.value,
    postal_code: form.elements.postal_code.value,
    building_number: form.elements.building_number.value || null,
    unit: form.elements.unit.value || null,
    is_default: form.elements.is_default.checked,
  });

  const initAddresses = () => {
    const wrapper = document.querySelector("[data-account-addresses]");

    const form = document.querySelector("[data-account-address-form]");

    if (!wrapper || !form) {
      return;
    }

    const message = form.querySelector("[data-account-address-message]");

    const cancel = form.querySelector("[data-account-address-cancel]");

    let editingId = null;

    const reset = () => {
      form.reset();
      form.elements.id.value = "";
      editingId = null;
    };

    const render = (addresses) => {
      wrapper.innerHTML = addresses
        .map(
          (address) => `
                    <article
                        class="account-address"
                        data-address-id="${address.id}"
                    >
                        <strong>
                            ${escapeHtml(address.title)}
                        </strong>

                        <p>
                            ${escapeHtml(address.recipient_name)}
                        </p>

                        <p>
                            ${escapeHtml(address.province)}
                            -
                            ${escapeHtml(address.city)}
                        </p>

                        <p>
                            ${escapeHtml(address.address)}
                        </p>

                        <div>
                            ${address.is_default ? "<span>پیش‌فرض</span>" : ""}

                            <button
                                type="button"
                                data-address-edit="${address.id}"
                            >
                                ویرایش
                            </button>

                            <button
                                type="button"
                                data-address-default="${address.id}"
                            >
                                پیش‌فرض
                            </button>

                            <button
                                type="button"
                                data-address-delete="${address.id}"
                            >
                                حذف
                            </button>
                        </div>
                    </article>
                `,
        )
        .join("");
    };

    const load = async () => {
      const result = await request("/account/addresses");

      render(result?.data?.addresses || []);
    };

    wrapper.addEventListener("click", async (event) => {
      const edit = event.target.closest("[data-address-edit]");

      const makeDefault = event.target.closest("[data-address-default]");

      const remove = event.target.closest("[data-address-delete]");

      if (edit) {
        const id = Number(edit.dataset.addressEdit);

        const result = await request("/account/addresses");

        const address = (result?.data?.addresses || []).find(
          (item) => Number(item.id) === id,
        );

        if (!address) {
          return;
        }

        editingId = id;
        form.elements.id.value = id;

        Object.keys(addressFormData(form)).forEach((field) => {
          if (form.elements[field]) {
            if (field === "is_default") {
              form.elements[field].checked = Boolean(address[field]);
            } else {
              form.elements[field].value = address[field] ?? "";
            }
          }
        });

        form.scrollIntoView({
          behavior: "smooth",
        });

        return;
      }

      if (makeDefault) {
        const id = Number(makeDefault.dataset.addressDefault);

        await request(`/account/addresses/${id}/default`, {
          method: "POST",
        });

        await load();

        return;
      }

      if (remove) {
        const id = Number(remove.dataset.addressDelete);

        await request(`/account/addresses/${id}`, {
          method: "DELETE",
        });

        await load();
      }
    });

    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      const payload = addressFormData(form);

      const path =
        editingId === null
          ? "/account/addresses"
          : `/account/addresses/${editingId}`;

      const result = await request(path, {
        method: editingId === null ? "POST" : "PUT",
        body: JSON.stringify(payload),
      });

      if (message) {
        message.textContent = result?.message || "عملیات انجام شد.";
      }

      if (result?.data?.address) {
        reset();
        await load();
      }
    });

    cancel?.addEventListener("click", reset);

    load();
  };

  const initOrders = () => {
    const wrapper = document.querySelector("[data-account-orders]");

    if (!wrapper) {
      return;
    }

    request("/orders").then((result) => {
      const orders = result?.data?.orders || [];

      wrapper.innerHTML = orders
        .map(
          (order) => `
                        <article class="account-order">
                            <h2>
                                سفارش #${escapeHtml(order.id)}
                            </h2>

                            <p>
                                وضعیت:
                                ${escapeHtml(order.status)}
                            </p>

                            <a
                                href="?page=account/orders/show&id=${order.id}"
                            >
                                مشاهده جزئیات
                            </a>
                        </article>
                    `,
        )
        .join("");
    });
  };

  const initOrderDetail = () => {
    const wrapper = document.querySelector("[data-account-order-detail]");

    if (!wrapper) {
      return;
    }

    const params = new URLSearchParams(window.location.search);

    const id = Number(params.get("id"));

    if (!id) {
      wrapper.textContent = "شناسه سفارش نامعتبر است.";
      return;
    }

    request(`/orders/${id}`).then((result) => {
      const data = result?.data;

      if (!data) {
        wrapper.textContent = "سفارش پیدا نشد.";
        return;
      }

      wrapper.innerHTML = `
                    <h1>
                        سفارش #${escapeHtml(data.order?.id || id)}
                    </h1>

                    <p>
                        وضعیت:
                        ${escapeHtml(data.order?.status)}
                    </p>
                `;
    });
  };

  const initReturns = () => {
    const wrapper = document.querySelector("[data-account-returns]");

    if (!wrapper) {
      return;
    }

    request("/returns").then((result) => {
      const returns = result?.data?.returns || [];

      wrapper.innerHTML = returns
        .map(
          (item) => `
                        <article
                            class="account-return"
                        >
                            <h2>
                                مرجوعی #${escapeHtml(item.id)}
                            </h2>

                            <p>
                                وضعیت:
                                ${escapeHtml(item.status)}
                            </p>

                            <a
                                href="?page=account/returns/show&id=${item.id}"
                            >
                                مشاهده جزئیات
                            </a>
                        </article>
                    `,
        )
        .join("");
    });
  };

  const initReturnDetail = () => {
    const wrapper = document.querySelector("[data-account-return-detail]");

    if (!wrapper) {
      return;
    }

    const params = new URLSearchParams(window.location.search);

    const id = Number(params.get("id"));

    if (!id) {
      wrapper.textContent = "شناسه مرجوعی نامعتبر است.";
      return;
    }

    request(`/returns/${id}`).then((result) => {
      const data = result?.data;

      if (!data) {
        wrapper.textContent = "درخواست مرجوعی پیدا نشد.";
        return;
      }

      wrapper.innerHTML = `
                    <h1>
                        مرجوعی #${escapeHtml(data.return?.id || id)}
                    </h1>

                    <p>
                        وضعیت:
                        ${escapeHtml(data.return?.status)}
                    </p>
                `;
    });
  };

  const init = () => {
    initProfile();
    initAddresses();
    initOrders();
    initOrderDetail();
    initReturns();
    initReturnDetail();
  };

  return {
    init,
  };
})();

export default account;
