const homeCommerce = () => {
  const baseUrl = window.NIVAD_BASE_URL || "";

  const collections = document.querySelector("[data-home-collections]");

  const sliders = document.querySelector("[data-home-sliders]");

  if (!collections && !sliders) {
    return;
  }

  const request = async (path) => {
    const url = new URL(`${baseUrl}/api/index.php`, window.location.origin);

    url.searchParams.set("path", path);

    const response = await fetch(url.toString(), {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const payload = await response.json();

    if (!response.ok || payload?.success === false) {
      throw new Error(payload?.message || "خطا در دریافت اطلاعات صفحه.");
    }

    return payload?.data ?? {};
  };

  const renderCollections = (items) => {
    if (!collections) {
      return;
    }

    const escapeHTML = (value) =>
      String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    collections.innerHTML = items
      .map((item) => {
        const slug = encodeURIComponent(item.slug || "");

        return `
        <article class="home-collection">
          <a
            href="${baseUrl}/collections/${slug}">
            <h3>
              ${escapeHTML(item.name || "")}
            </h3>
            <p>
              ${escapeHTML(item.description || "")}
            </p>
          </a>
        </article>
      `;
      })
      .join("");
  };

  const renderSliders = (items) => {
    if (!sliders) return;

    sliders.innerHTML = items
      .map(
        (slider) => `
              <article
                class="home-slider"
                data-slider-id="${slider.id}">
                <h2>
                  ${slider.name || ""}
                </h2>

                <p>
                  ${slider.description || ""}
                </p>
              </article>
            `,
      )
      .join("");
  };

  if (collections) {
    request("/collections")
      .then((data) => {
        renderCollections(data.items || []);
      })
      .catch(console.error);
  }

  if (sliders) {
    request("/sliders")
      .then((data) => {
        renderSliders(data.items || []);
      })
      .catch(console.error);
  }
};

export default homeCommerce;
