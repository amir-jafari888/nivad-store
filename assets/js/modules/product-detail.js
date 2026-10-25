import { updateCommerceContext } from "./commerce-context.js";

const productDetail = () => {
  const root = document.querySelector("[data-product-detail]");

  if (!root) {
    return;
  }

  const apiBase =
    root.dataset.apiBase || `${window.NIVAD_BASE_URL || ""}/api/index.php`;

  const slug = String(root.dataset.productSlug || "").trim();

  const productId = Number(root.dataset.productId || 0);

  if (!slug || !productId) {
    return;
  }

  const state = root.querySelector("[data-product-detail-state]");

  const content = root.querySelector("[data-product-detail-content]");

  const mainImage = root.querySelector("[data-product-main-image]");

  const gallery = root.querySelector("[data-product-gallery]");

  const brand = root.querySelector("[data-product-brand]");

  const category = root.querySelector("[data-product-category]");

  const title = root.querySelector("[data-product-title]");

  const shortDescription = root.querySelector("[data-product-short]");

  const price = root.querySelector("[data-product-price]");

  const availability = root.querySelector("[data-product-availability]");

  const variantsSection = root.querySelector("[data-product-variants-section]");

  const variants = root.querySelector("[data-product-variants]");

  const addCart = root.querySelector("[data-product-add-cart]");

  const wishlistButton = root.querySelector("[data-product-wishlist]");

  const descriptionSection = root.querySelector(
    "[data-product-description-section]",
  );

  const description = root.querySelector("[data-product-description]");

  const compatibilitySection = root.querySelector(
    "[data-product-compatibility-section]",
  );

  const compatibility = root.querySelector("[data-product-compatibility]");

  const smartSection = root.querySelector("[data-product-smart-section]");

  const complements = root.querySelector("[data-product-complements]");

  const reviews = root.querySelector("[data-product-reviews]");

  const reviewSummary = root.querySelector("[data-product-review-summary]");

  const ratingSummary = root.querySelector("[data-product-rating-summary]");

  const questions = root.querySelector("[data-product-questions]");

  const reviewForm = root.querySelector("[data-product-review-form]");

  const questionForm = root.querySelector("[data-product-question-form]");

  const escapeHTML = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const formatPrice = (value, currency = "IRR") => {
    const number = Number(value);

    if (!Number.isFinite(number)) {
      return "قیمت نامشخص";
    }

    return `${number.toLocaleString("fa-IR")} ${
      currency === "IRR" ? "تومان" : escapeHTML(currency)
    }`;
  };

  const request = async (path, options = {}) => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", path);

    const method = String(options.method || "GET").toUpperCase();

    const headers = {
      Accept: "application/json",
      ...(options.headers || {}),
    };

    if (method !== "GET" && method !== "HEAD") {
      headers["Content-Type"] = "application/json";
    }

    const response = await fetch(url.toString(), {
      ...options,
      method,
      headers,
      credentials: "same-origin",
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      const error = new Error(payload?.message || "دریافت اطلاعات ناموفق بود.");

      error.status = response.status;

      error.payload = payload;

      throw error;
    }

    return payload?.data ?? {};
  };

  const csrf = async () => {
    const data = await request("/auth/csrf");

    return String(data?.token || "");
  };

  const authenticatedRequest = async (path, values) => {
    const token = await csrf();

    return request(path, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": token,
      },
      body: JSON.stringify(values),
    });
  };

  let isLoading = false;

  const setState = (message, type = "loading") => {
    if (!state) {
      return;
    }

    state.replaceChildren();

    state.dataset.state = type;

    const messageElement = document.createElement("p");

    messageElement.textContent = message;

    state.appendChild(messageElement);

    if (type !== "error") {
      return;
    }

    const retryButton = document.createElement("button");

    retryButton.type = "button";

    retryButton.className = "product-detail-retry";

    retryButton.dataset.productRetry = "true";

    retryButton.textContent = "تلاش دوباره";

    retryButton.setAttribute(
      "aria-label",
      "تلاش دوباره برای دریافت اطلاعات محصول",
    );

    retryButton.addEventListener("click", async () => {
      if (isLoading) {
        return;
      }

      retryButton.disabled = true;

      await load();
    });

    state.appendChild(retryButton);
  };

  const setFormState = (form, message, type = "idle") => {
    const target =
      form?.querySelector("[data-feedback-message]") ||
      form?.querySelector("[data-review-message]") ||
      form?.querySelector("[data-question-message]");

    if (target) {
      target.dataset.state = type;

      target.textContent = message;
    }
  };

  const setFormSubmitting = (form, submitting) => {
    if (!form) {
      return;
    }

    const button = form.querySelector('button[type="submit"]');

    if (!button) {
      return;
    }

    button.disabled = submitting;

    button.setAttribute("aria-busy", String(submitting));
  };

  const showContent = () => {
    if (content) {
      content.hidden = false;
    }

    if (state) {
      state.hidden = true;
    }
  };

  const resolveMediaUrl = (value) => {
    const path = String(value || "").trim();

    if (!path) {
      return "";
    }

    if (/^https?:\/\//i.test(path)) {
      return path;
    }

    if (path.startsWith("/")) {
      return path;
    }

    return `${window.NIVAD_BASE_URL || ""}/${path.replace(/^\/+/, "")}`;
  };

  let product = null;

  let selectedVariant = null;

  const renderGallery = (items) => {
    const media = Array.isArray(items) ? items : [];

    if (!mainImage || !gallery) {
      return;
    }

    gallery.innerHTML = "";

    if (!media.length) {
      mainImage.hidden = true;

      return;
    }

    const primary = media.find((item) => item?.is_primary) || media[0];

    const setMainImage = (item) => {
      const url = resolveMediaUrl(item?.url);

      if (!url) {
        return;
      }

      mainImage.src = url;

      mainImage.alt = String(item?.alt || product?.name || "تصویر محصول");

      mainImage.hidden = false;
    };

    setMainImage(primary);

    media.forEach((item, index) => {
      const button = document.createElement("button");

      button.type = "button";

      button.className = "product-detail-gallery__thumb";

      button.dataset.index = String(index);

      button.setAttribute("role", "listitem");

      const url = resolveMediaUrl(item?.thumbnail_url || item?.url);

      button.innerHTML = `
        <img
          src="${escapeHTML(url)}"
          alt="${escapeHTML(item?.alt || product?.name || "تصویر محصول")}"
          loading="lazy"
          decoding="async">
      `;

      button.addEventListener("click", () => {
        setMainImage(item);

        gallery
          .querySelectorAll(".is-active")
          .forEach((element) => element.classList.remove("is-active"));

        button.classList.add("is-active");
      });

      gallery.appendChild(button);
    });

    gallery
      .querySelector(".product-detail-gallery__thumb")
      ?.classList.add("is-active");
  };

  const renderProductInfo = () => {
    if (!product) {
      return;
    }

    if (brand) {
      brand.textContent = product.brand?.name || "";
    }

    if (category) {
      category.textContent = product.category?.name || "";
    }

    if (title) {
      title.textContent = product.name || "جزئیات محصول";
    }

    if (shortDescription) {
      shortDescription.textContent = product.short_description || "";
    }

    if (description && product.description) {
      description.textContent = product.description;

      if (descriptionSection) {
        descriptionSection.hidden = false;
      }
    }

    selectedVariant =
      product.variants?.find((item) => item?.is_default) ||
      product.variants?.[0] ||
      null;

    renderSelectedVariant();
  };

  const renderSelectedVariant = () => {
    if (product) updateCommerceContext({ product: { id: Number(product.id || productId), slug, variantId: Number(selectedVariant?.id || 0) } });
    if (!selectedVariant) {
      if (price) {
        price.textContent = "قیمت نامشخص";
      }

      if (addCart) {
        addCart.disabled = true;
      }

      syncWishlistAttributes();

      document.dispatchEvent(
        new CustomEvent("product-detail:variant-changed", {
          detail: {
            productId: Number(product?.id || productId || 0),
            variantId: 0,
            price: null,
            compareAtPrice: null,
            stockStatus: "unknown",
          },
        }),
      );

      return;
    }

    if (price) {
      price.textContent = formatPrice(
        selectedVariant.price,
        selectedVariant.currency,
      );
    }

    const stock = selectedVariant.availability;

    if (availability) {
      if (!stock) {
        availability.textContent = "وضعیت موجودی مشخص نیست.";

        availability.dataset.state = "unknown";
      } else if (stock.status === "in_stock") {
        availability.textContent =
          stock.available_quantity <= stock.low_stock_threshold
            ? "موجودی محدود"
            : "موجود";

        availability.dataset.state = "in-stock";
      } else {
        availability.textContent = "ناموجود";

        availability.dataset.state = "out-of-stock";
      }
    }

    if (addCart) {
      addCart.disabled = !stock || stock.status !== "in_stock";
    }

    syncWishlistAttributes();

    document.dispatchEvent(
      new CustomEvent("product-detail:variant-changed", {
        detail: {
          productId: Number(product?.id || productId || 0),
          variantId: Number(selectedVariant.id || 0),
          price: selectedVariant.price ?? null,
          compareAtPrice: selectedVariant.compare_at_price ?? null,
          stockStatus: stock?.status || "unknown",
        },
      }),
    );
  };

  const renderVariants = () => {
    const items = Array.isArray(product?.variants) ? product.variants : [];

    if (!variants || !variantsSection) {
      return;
    }

    variants.innerHTML = "";

    if (items.length <= 1) {
      variantsSection.hidden = true;

      return;
    }

    variantsSection.hidden = false;

    items.forEach((variant) => {
      const button = document.createElement("button");

      button.type = "button";

      button.className = "product-detail-variant";

      button.dataset.variantId = String(variant.id);

      button.setAttribute(
        "aria-pressed",
        String(Number(selectedVariant?.id) === Number(variant.id)),
      );

      const stock = variant.availability;

      const disabled = !stock || stock.status !== "in_stock";

      button.disabled = disabled;

      button.innerHTML = `
        <strong>
          ${escapeHTML(variant.name || `مدل ${variant.id}`)}
        </strong>
        <span>
          ${escapeHTML(formatPrice(variant.price, variant.currency))}
        </span>
      `;

      button.addEventListener("click", () => {
        selectedVariant = variant;

        variants
          .querySelectorAll(".product-detail-variant")
          .forEach((element) => {
            element.setAttribute("aria-pressed", String(element === button));
          });

        renderSelectedVariant();

        syncWishlistAttributes();
      });

      variants.appendChild(button);
    });
  };

  const updateWishlistState = () => {
    if (!wishlistButton || !window.digitalStoreWishlist) {
      return;
    }

    const ids = window.digitalStoreWishlist.getWishlist();

    const active = ids.includes(String(product?.id || ""));

    wishlistButton.classList.toggle("is-active", active);

    wishlistButton.setAttribute("aria-pressed", String(active));

    wishlistButton.disabled = !selectedVariant;
  };

  const syncWishlistAttributes = () => {
    if (!wishlistButton) {
      return;
    }

    wishlistButton.dataset.wishlist = String(productId);

    wishlistButton.dataset.wishlistVariantId = String(selectedVariant?.id || 0);

    updateWishlistState();
  };

  const renderCompatibility = async () => {
    if (!compatibility || !compatibilitySection) {
      return;
    }

    try {
      const data = await request("/compatibility/devices");

      const devices = Array.isArray(data?.items) ? data.items : [];

      if (!devices.length) {
        compatibilitySection.hidden = true;

        return;
      }

      compatibility.innerHTML = `
      <p>
        برای بررسی سازگاری،
        دستگاه خود را انتخاب کنید.
      </p>

      <div
        class="product-detail-compatibility__controls">

        <label>
          <span class="sr-only">
            دستگاه
          </span>

          <select
            data-compatibility-device
            aria-label="انتخاب دستگاه">

            <option value="">
              انتخاب دستگاه
            </option>

            ${devices
              .map(
                (device) => `
                  <option
                    value="${escapeHTML(device.id)}">
                    ${escapeHTML(
                      device.name || device.model || device.slug || "دستگاه",
                    )}
                  </option>
                `,
              )
              .join("")}

          </select>

        </label>

        <button
          type="button"
          data-compatibility-check
          aria-busy="false">

          بررسی سازگاری

        </button>

      </div>

      <div
        data-compatibility-result
        aria-live="polite">
      </div>
    `;

      compatibilitySection.hidden = false;

      const select = compatibility.querySelector("[data-compatibility-device]");

      const check = compatibility.querySelector("[data-compatibility-check]");

      const result = compatibility.querySelector("[data-compatibility-result]");

      check?.addEventListener("click", async () => {
        const deviceId = Number(select?.value || 0);

        if (!deviceId) {
          if (result) {
            result.textContent = "ابتدا دستگاه را انتخاب کنید.";
          }

          return;
        }

        updateCommerceContext({ compatibility: { deviceId } });

        check.disabled = true;

        check.setAttribute("aria-busy", "true");

        if (result) {
          result.textContent = "در حال بررسی سازگاری...";
        }

        try {
          const response = await request(
            `/compatibility/devices/${deviceId}/products`,
          );

          const items = Array.isArray(response?.items) ? response.items : [];

          const found = items.find(
            (item) => Number(item?.product_id) === Number(product?.id),
          );

          if (!result) {
            return;
          }

          if (!found) {
            result.textContent = "برای این دستگاه سازگاری ثبت‌شده‌ای پیدا نشد.";

            return;
          }

          if (found.compatibility_type === "compatible") {
            result.textContent = "این محصول با دستگاه انتخاب‌شده سازگار است.";
          } else if (found.compatibility_type === "partially_compatible") {
            result.textContent =
              "سازگاری این محصول با دستگاه انتخاب‌شده جزئی است.";
          } else {
            result.textContent = "این محصول با دستگاه انتخاب‌شده سازگار نیست.";
          }
        } catch (error) {
          if (!result) {
            return;
          }

          result.textContent =
            error?.status === 401
              ? "برای بررسی سازگاری باید وارد حساب شوید."
              : "بررسی سازگاری ناموفق بود.";
        } finally {
          check.disabled = false;

          check.setAttribute("aria-busy", "false");
        }
      });
    } catch {
      compatibilitySection.hidden = true;
    }
  };

  const renderComplements = () => {
    if (!complements || !smartSection) {
      return;
    }

    const items = Array.isArray(product?.complements)
      ? product.complements
      : [];

    if (!items.length) {
      complements.innerHTML = "";
      return;
    }

    const normalizedItems = items
      .filter((item) => {
        return (
          Number(item?.id || 0) > 0 &&
          Number(item?.id || 0) !== Number(product?.id || 0) &&
          String(item?.slug || "").trim() !== ""
        );
      })
      .map((item) => ({
        id: Number(item.id),
        slug: String(item.slug || "").trim(),
        name: String(item.name || "محصول"),
        shortDescription: String(
          item.short_description || item.shortDescription || "",
        ).trim(),
        variantId: Number(item?.variant?.id || item?.variant_id || 0),
        price: item?.variant?.price ?? item?.price ?? null,
        currency: String(
          item?.variant?.currency || item?.currency || "",
        ).trim(),
        relationType: String(
          item?.relation_type || item?.relationType || "",
        ).trim(),
      }));

    if (!normalizedItems.length) {
      complements.innerHTML = "";
      return;
    }

    complements.innerHTML = normalizedItems
      .map((item) => {
        const price =
          item.price !== null &&
          item.price !== undefined &&
          String(item.price).trim() !== ""
            ? formatPrice(item.price, item.currency)
            : "مشاهده محصول";

        return `
        <article
          class="product-detail-complement"
          data-complement-product-id="${item.id}">

          <div class="product-detail-complement__content">

            <span class="product-detail-complement__label">
              ${escapeHTML(
                item.relationType === "recommended"
                  ? "پیشنهاد شده برای این محصول"
                  : "محصول مکمل",
              )}
            </span>

            <h3 class="product-detail-complement__title">
              <a
                href="${
                  window.NIVAD_BASE_URL || ""
                }/products/${encodeURIComponent(item.slug)}">
                ${escapeHTML(item.name)}
              </a>
            </h3>

            ${
              item.shortDescription
                ? `
                  <p class="product-detail-complement__description">
                    ${escapeHTML(item.shortDescription)}
                  </p>
                `
                : ""
            }

            <span class="product-detail-complement__price">
              ${escapeHTML(price)}
            </span>

          </div>

        </article>
      `;
      })
      .join("");

    smartSection.hidden = false;
  };

  const renderRatingSummary = (rating) => {
    const target = reviewSummary || ratingSummary;

    if (!target) {
      return;
    }

    const average = Number(rating?.average || 0);

    const count = Number(rating?.count || 0);

    target.innerHTML = `
      <strong>${escapeHTML(average.toLocaleString("fa-IR"))}</strong>
      <span>
        از ۵ · ${escapeHTML(count.toLocaleString("fa-IR"))} دیدگاه
      </span>
    `;
  };

  const renderReviews = (items, rating) => {
    if (!reviews) {
      return;
    }

    renderRatingSummary(rating);

    const list = Array.isArray(items) ? items : [];

    reviews.innerHTML = list.length
      ? list
          .map(
            (item) => `
              <article
                class="product-detail-review">

                <div>
                  <strong>
                    ${escapeHTML(item.title || "دیدگاه کاربر")}
                  </strong>

                  <span>
                    ${escapeHTML(`${item.rating || 0} از 5`)}
                  </span>

                  ${
                    item.is_verified_purchase
                      ? `<span class="product-detail-review__verified">خرید تاییدشده</span>`
                      : ""
                  }
                </div>

                <p>
                  ${escapeHTML(item.body || "")}
                </p>

              </article>
            `,
          )
          .join("")
      : `
          <p class="product-detail-empty">
            هنوز دیدگاهی برای این محصول ثبت نشده است.
          </p>
        `;
  };

  const loadReviews = async () => {
    if (reviews) {
      reviews.textContent = "در حال دریافت دیدگاه‌ها...";
    }

    try {
      const data = await request(`/products/${productId}/reviews`);

      renderReviews(data?.items || [], data?.rating || null);
    } catch {
      if (reviews) {
        reviews.innerHTML = `
          <p class="product-detail-error">
            دریافت دیدگاه‌ها ناموفق بود.
          </p>

          <button
            type="button"
            class="product-detail-retry"
            data-reviews-retry>
            تلاش دوباره
          </button>
        `;

        reviews
          .querySelector("[data-reviews-retry]")
          ?.addEventListener("click", loadReviews);
      }
    }
  };

  const renderQuestions = (items) => {
    if (!questions) {
      return;
    }

    const list = Array.isArray(items) ? items : [];

    questions.innerHTML = list.length
      ? list
          .map(
            (item) => `
              <article
                class="product-detail-question">

                <strong>
                  ${escapeHTML(item.question || item.body || "")}
                </strong>

                <div
                  data-question-answers="${escapeHTML(item.id)}"
                  aria-live="polite">
                  در حال دریافت پاسخ...
                </div>

              </article>
            `,
          )
          .join("")
      : `
          <p class="product-detail-empty">
            هنوز پرسشی برای این محصول ثبت نشده است.
          </p>
        `;
  };

  const loadQuestionAnswers = async (items) => {
    if (!questions) {
      return;
    }

    for (const item of items) {
      const container = questions.querySelector(
        `[data-question-answers="${CSS.escape(String(item.id))}"]`,
      );

      if (!container) {
        continue;
      }

      try {
        const data = await request(`/questions/${item.id}/answers`);

        const answers = Array.isArray(data?.items) ? data.items : [];

        container.innerHTML = answers.length
          ? answers
              .map(
                (answer) => `
                  <p>
                    ${escapeHTML(answer.answer || answer.body || "")}
                  </p>
                `,
              )
              .join("")
          : `
              <p>
                پاسخی ثبت نشده است.
              </p>
            `;
      } catch {
        container.innerHTML = `
          <p class="product-detail-error">
            دریافت پاسخ‌ها ناموفق بود.
          </p>

          <button
            type="button"
            class="product-detail-retry"
            data-answer-retry="${escapeHTML(item.id)}">
            تلاش دوباره
          </button>
        `;

        container
          .querySelector("[data-answer-retry]")
          ?.addEventListener("click", async (event) => {
            const button = event.currentTarget;

            button.disabled = true;

            await loadQuestionAnswers([item]);
          });
      }
    }
  };

  const loadQuestions = async () => {
    if (questions) {
      questions.textContent = "در حال دریافت پرسش‌ها...";
    }

    try {
      const data = await request(`/products/${productId}/questions`);

      const items = Array.isArray(data?.items) ? data.items : [];

      renderQuestions(items);

      await loadQuestionAnswers(items);
    } catch {
      if (questions) {
        questions.innerHTML = `
          <p class="product-detail-error">
            دریافت پرسش‌ها ناموفق بود.
          </p>

          <button
            type="button"
            class="product-detail-retry"
            data-questions-retry>
            تلاش دوباره
          </button>
        `;

        questions
          .querySelector("[data-questions-retry]")
          ?.addEventListener("click", loadQuestions);
      }
    }
  };

  addCart?.addEventListener("click", () => {
    if (!selectedVariant || !window.digitalStoreCart) {
      return;
    }

    const stock = selectedVariant.availability;

    if (!stock || stock.status !== "in_stock") {
      return;
    }

    window.digitalStoreCart.add(
      {
        id: String(product.id),

        variantId: Number(selectedVariant.id),

        name: String(product.name || ""),

        brand: String(product.brand?.name || ""),

        price: Number(selectedVariant.price),

        spec: String(selectedVariant.name || ""),
      },
      1,
    );

    addCart.textContent = "به سبد خرید اضافه شد";

    window.setTimeout(() => {
      addCart.textContent = "افزودن به سبد خرید";
    }, 1200);
  });

  /*
   * Wishlist click handling intentionally remains
   * delegated to wishlist.js through [data-wishlist].
   * This module only maintains the required
   * product/variant attributes and visual state.
   */

  wishlistButton?.setAttribute("data-wishlist", String(productId));

  wishlistButton?.setAttribute(
    "data-wishlist-variant-id",
    String(selectedVariant?.id || 0),
  );

  reviewForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (!reviewForm.checkValidity()) {
      reviewForm.reportValidity();

      return;
    }

    const data = new FormData(reviewForm);

    const rating = Number(data.get("rating"));

    const body = String(data.get("body") || "").trim();

    if (!Number.isInteger(rating) || rating < 1 || rating > 5 || body === "") {
      reviewForm.reportValidity();

      return;
    }

    const submitButton = reviewForm.querySelector("[data-review-submit]");

    setFormSubmitting(reviewForm, true);

    setFormState(reviewForm, "در حال ارسال دیدگاه...", "loading");

    if (submitButton) {
      submitButton.disabled = true;
    }

    try {
      const result = await authenticatedRequest("/reviews", {
        productId: productId,

        rating: Number(data.get("rating")),

        title: data.get("title") || null,

        body: String(data.get("body") || ""),
      });

      setFormState(
        reviewForm,
        result?.message ||
          "دیدگاه شما با موفقیت ثبت شد و پس از تایید نمایش داده می‌شود.",
        "success",
      );

      reviewForm.reset();

      await loadReviews();
    } catch (error) {
      setFormState(
        reviewForm,
        error?.message || "ثبت دیدگاه ناموفق بود.",
        "error",
      );
    } finally {
      setFormSubmitting(reviewForm, false);
    }
  });

  questionForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (!questionForm.checkValidity()) {
      questionForm.reportValidity();

      return;
    }

    const data = new FormData(questionForm);

    const question = String(data.get("question") || "").trim();

    if (question === "") {
      questionForm.reportValidity();

      return;
    }

    const questionSubmitButton = questionForm.querySelector(
      "[data-question-submit]",
    );

    setFormSubmitting(questionForm, true);

    setFormState(questionForm, "در حال ارسال پرسش...", "loading");

    if (questionSubmitButton) {
      questionSubmitButton.disabled = true;
    }

    try {
      const result = await authenticatedRequest(
        `/products/${productId}/questions`,
        {
          product_id: productId,

          question: String(data.get("question") || ""),
        },
      );

      setFormState(
        questionForm,
        result?.message ||
          "پرسش شما با موفقیت ثبت شد و پس از تایید نمایش داده می‌شود.",
        "success",
      );

      questionForm.reset();

      await loadQuestions();
    } catch (error) {
      setFormState(
        questionForm,
        error?.message || "ثبت پرسش ناموفق بود.",
        "error",
      );
    } finally {
      setFormSubmitting(questionForm, false);
    }
  });

  const load = async () => {
    if (isLoading) {
      return;
    }

    isLoading = true;

    if (content) {
      content.hidden = true;
    }

    if (state) {
      state.hidden = false;
    }

    setState("در حال دریافت اطلاعات محصول...", "loading");

    try {
      const data = await request(
        `/storefront/products/detail?slug=${encodeURIComponent(slug)}`,
      );

      product = data || null;

      document.dispatchEvent(
        new CustomEvent("product-detail:loaded", {
          detail: {
            productId: Number(product?.id || 0),
            variantId: Number(
              product?.variant?.id ||
                product?.variants?.find((item) => item?.is_default)?.id ||
                product?.variants?.[0]?.id ||
                0,
            ),
          },
        }),
      );

      const activeVariantId = Number(
        product?.variant?.id ||
          product?.variants?.find((item) => item?.is_default)?.id ||
          product?.variants?.[0]?.id ||
          0,
      );

      document.dispatchEvent(
        new CustomEvent("product-detail:complete-setup-context", {
          detail: {
            productId: Number(product?.id || 0),
            variantId: activeVariantId,
            categoryId: Number(
              product?.category?.id || product?.category_id || 0,
            ),
            availability:
              product?.variant?.availability?.status ||
              product?.availability?.status ||
              null,
            completeSetupAvailable: true,
          },
        }),
      );

      if (!product) {
        throw new Error("محصول پیدا نشد.");
      }

      renderProductInfo();

      renderGallery(product.media);

      renderVariants();

      syncWishlistAttributes();

      showContent();

      await Promise.allSettled([
        renderCompatibility(),
        renderRelated(),
        renderComplements(),
        loadReviews(),
        loadQuestions(),
      ]);
    } catch (error) {
      if (content) {
        content.hidden = true;
      }

      if (state) {
        state.hidden = false;
      }

      setState(error?.message || "دریافت اطلاعات محصول ناموفق بود.", "error");
    } finally {
      isLoading = false;
    }
  };

  load();
};

export default productDetail;
