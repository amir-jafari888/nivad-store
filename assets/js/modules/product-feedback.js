const productFeedback = () => {
  const productPage = document.querySelector("[data-product-feedback]");

  if (!productPage) return;

  const productId = Number(productPage.dataset.productId);

  if (!productId) return;

  const baseUrl = window.NIVAD_BASE_URL || "";

  const reviews = productPage.querySelector("[data-product-reviews]");

  const reviewForm = productPage.querySelector("[data-product-review-form]");

  const questions = productPage.querySelector("[data-product-questions]");

  const questionForm = productPage.querySelector(
    "[data-product-question-form]",
  );

  const request = async (path, options = {}) => {
    const headers = {
      Accept: "application/json",
      ...(options.headers || {}),
    };

    if (options.method && options.method !== "GET") {
      headers["Content-Type"] = "application/json";
    }

    const response = await fetch(
      `${baseUrl}/api/index.php?path=${encodeURIComponent(path)}`,
      {
        ...options,
        headers,
        credentials: "same-origin",
      },
    );

    return response.json();
  };

  const renderReviews = (items) => {
    if (!reviews) return;

    reviews.innerHTML = items.length
      ? items
          .map(
            (item) => `
                <article class="product-review">
                  <h3>
                    ${item.title || "دیدگاه کاربر"}
                  </h3>

                  <p>
                    ${item.body || ""}
                  </p>

                  <strong>
                    امتیاز:
                    ${item.rating || 0}
                  </strong>
                </article>
              `,
          )
          .join("")
      : "<p>هنوز دیدگاهی برای این محصول ثبت نشده است.</p>";
  };

  const renderQuestions = (items) => {
    if (!questions) return;

    questions.innerHTML = items.length
      ? items
          .map(
            (item) => `
                <article class="product-question">
                  <h3>
                    ${item.body || ""}
                  </h3>

                  <div
                    data-question-answers="${item.id}">
                    در حال دریافت پاسخ...
                  </div>
                </article>
              `,
          )
          .join("")
      : "<p>هنوز سوالی ثبت نشده است.</p>";
  };

  const loadReviews = async () => {
    const result = await request(`/products/${productId}/reviews`);

    renderReviews(result?.data?.items || result?.items || []);
  };

  const loadQuestions = async () => {
    const result = await request(`/products/${productId}/questions`);

    const items = result?.data?.items || result?.items || [];

    renderQuestions(items);

    for (const item of items) {
      const answers = await request(`/questions/${item.id}/answers`);

      const container = questions.querySelector(
        `[data-question-answers="${item.id}"]`,
      );

      if (!container) continue;

      const answerItems = answers?.data?.items || answers?.items || [];

      container.innerHTML = answerItems.length
        ? answerItems.map((answer) => `<p>${answer.body || ""}</p>`).join("")
        : "<p>پاسخی ثبت نشده است.</p>";
    }
  };

  reviewForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const data = new FormData(reviewForm);

    const result = await request("/reviews", {
      method: "POST",
      body: JSON.stringify({
        productId,
        rating: Number(data.get("rating")),
        title: data.get("title") || null,
        body: data.get("body") || "",
      }),
    });

    const message = reviewForm.querySelector("[data-feedback-message]");

    if (message) {
      message.textContent = result?.message || "عملیات انجام شد.";
    }

    if (result?.success) {
      reviewForm.reset();
      loadReviews();
    }
  });

  questionForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const data = new FormData(questionForm);

    const result = await request(`/products/${productId}/questions`, {
      method: "POST",
      body: JSON.stringify({
        body: data.get("body") || "",
      }),
    });

    const message = questionForm.querySelector("[data-feedback-message]");

    if (message) {
      message.textContent = result?.message || "عملیات انجام شد.";
    }

    if (result?.success) {
      questionForm.reset();
      loadQuestions();
    }
  });

  loadReviews().catch(console.error);

  loadQuestions().catch(console.error);
};

export default productFeedback;
