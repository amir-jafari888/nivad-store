const auth = () => {
  const baseUrl = String(window.NIVAD_BASE_URL || "").replace(/\/$/, "");
  const apiBase = `${baseUrl}/api/index.php`;
  const authPages = document.querySelectorAll("[data-auth-page]");
  const genericNetworkMessage = "ارتباط با سرور برقرار نشد. اتصال اینترنت را بررسی کنید و دوباره تلاش کنید.";

  const endpointUrl = (path) => {
    const url = new URL(apiBase, window.location.origin);
    url.searchParams.set("path", path);
    return url;
  };

  const request = async (path, options = {}) => {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => controller.abort(), 15000);
    const headers = new Headers(options.headers || {});
    headers.set("Accept", "application/json");

    if (options.body instanceof URLSearchParams) {
      headers.set("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
    }

    try {
      const response = await fetch(endpointUrl(path), {
        credentials: "same-origin",
        ...options,
        headers,
        signal: options.signal || controller.signal,
      });
      const contentType = response.headers.get("content-type") || "";
      let payload = null;

      if (contentType.includes("application/json")) {
        payload = await response.json().catch(() => null);
      } else {
        const raw = await response.text().catch(() => "");
        payload = raw ? { message: "پاسخ سرور قابل پردازش نیست." } : null;
      }

      if (!response.ok || payload?.success === false) {
        const error = new Error(
          response.status === 419
            ? "نشست امنیتی منقضی شده است. صفحه را تازه‌سازی و دوباره تلاش کنید."
            : response.status === 401
              ? (payload?.message || "اطلاعات ورود معتبر نیست یا نشست شما پایان یافته است.")
              : (payload?.message || "عملیات ناموفق بود."),
        );
        error.status = response.status;
        error.payload = payload;
        throw error;
      }

      if (!payload || typeof payload !== "object" || Array.isArray(payload)) {
        throw new Error("پاسخ معتبر از سرور دریافت نشد.");
      }

      return payload.data ?? payload;
    } catch (error) {
      if (error?.name === "AbortError") {
        const timeoutError = new Error("زمان پاسخ‌گویی سرور به پایان رسید. دوباره تلاش کنید.");
        timeoutError.status = 408;
        throw timeoutError;
      }
      if (error instanceof TypeError) {
        const networkError = new Error(genericNetworkMessage);
        networkError.status = 0;
        throw networkError;
      }
      throw error;
    } finally {
      window.clearTimeout(timeoutId);
    }
  };

  const csrf = async () => {
    const data = await request("/auth/csrf");
    const token = data?.token;
    if (typeof token !== "string" || token.length === 0) {
      throw new Error("دریافت توکن امنیتی ممکن نشد. صفحه را تازه‌سازی کنید.");
    }
    return token;
  };

  const formBody = async (values) => {
    const body = new URLSearchParams();
    body.set("csrf_token", await csrf());
    Object.entries(values).forEach(([key, value]) => {
      if (value !== undefined && value !== null) body.set(key, String(value));
    });
    return body;
  };

  const clearFieldErrors = (form) => {
    form.querySelectorAll("[data-auth-field-error]").forEach((node) => {
      const field = node.closest("label")?.querySelector("input, select, textarea");
      if (field) {
        const ids = (field.getAttribute("aria-describedby") || "")
          .split(/\\s+/)
          .filter((id) => id && id !== node.id);
        if (ids.length) field.setAttribute("aria-describedby", ids.join(" "));
        else field.removeAttribute("aria-describedby");
      }
      node.remove();
    });
    form.querySelectorAll("[aria-invalid='true']").forEach((field) => field.removeAttribute("aria-invalid"));
  };

  const showFieldErrors = (form, errors) => {
    if (!errors || typeof errors !== "object" || Array.isArray(errors)) return;
    Object.entries(errors).forEach(([name, message]) => {
      const field = form.elements.namedItem(name);
      if (!(field instanceof HTMLElement) || !("setAttribute" in field)) return;
      field.setAttribute("aria-invalid", "true");
      const error = document.createElement("small");
      error.className = "auth-field-error";
      error.dataset.authFieldError = "true";
      error.id = `auth-error-${name.replace(/[^a-zA-Z0-9_-]/g, "-")}`;
      error.textContent = String(Array.isArray(message) ? message[0] || "" : message || "مقدار واردشده معتبر نیست.");
      const describedBy = field.getAttribute("aria-describedby");
      field.setAttribute("aria-describedby", [describedBy, error.id].filter(Boolean).join(" "));
      field.closest("label")?.append(error);
    });
  };

  const setMessage = (root, message, type = "", form = null) => {
    const element = (form || root).querySelector("[data-auth-message]");
    if (!element) return;
    element.textContent = message;
    element.dataset.state = type;
    element.setAttribute("role", type === "error" ? "alert" : "status");
  };

  const setLoading = (form, loading) => {
    form.setAttribute("aria-busy", String(loading));
    form.querySelectorAll("button[type='submit']").forEach((button) => {
      if (!(button instanceof HTMLButtonElement)) return;
      if (loading) {
        if (!button.dataset.originalText) button.dataset.originalText = button.textContent || "";
        button.disabled = true;
        button.textContent = "در حال پردازش...";
      } else {
        button.disabled = false;
        if (button.dataset.originalText) button.textContent = button.dataset.originalText;
      }
    });
  };

  const submit = async (root, form, path, values, successMessage, onSuccess = null, options = {}) => {
    if (form.dataset.submitting === "true") return;
    form.dataset.submitting = "true";
    clearFieldErrors(form);
    setMessage(root, "", "", form);
    setLoading(form, true);

    try {
      const body = await formBody(values());
      await request(path, { method: "POST", body });
      setMessage(root, successMessage, "success", form);
      if (typeof onSuccess === "function") onSuccess();
    } catch (error) {
      const fieldErrors = error?.payload?.errors || error?.payload?.data?.errors;
      showFieldErrors(form, fieldErrors);
      const isNetworkFailure = error?.status === 0 || error?.status === 408;
      const safeMessage = options.genericFailure && !isNetworkFailure
        ? options.genericFailure
        : (error?.message || "عملیات انجام نشد. دوباره تلاش کنید.");
      setMessage(root, safeMessage, "error", form);
    } finally {
      form.dataset.submitting = "false";
      setLoading(form, false);
    }
  };

  const redirectAfter = (url, delay = 800) => {
    window.setTimeout(() => {
      window.location.assign(url);
    }, delay);
  };

  const login = (root, form) => submit(
    root, form, "/login",
    () => ({ identifier: form.elements.namedItem("identifier").value, password: form.elements.namedItem("password").value }),
    "ورود با موفقیت انجام شد. در حال انتقال...",
    () => redirectAfter(root.dataset.redirect || `${baseUrl}/`, 500),
  );

  const register = (root, form) => submit(
    root, form, "/register",
    () => ({
      first_name: form.elements.namedItem("first_name").value,
      last_name: form.elements.namedItem("last_name").value,
      email: form.elements.namedItem("email").value,
      phone: form.elements.namedItem("phone").value,
      password: form.elements.namedItem("password").value,
      password_confirmation: form.elements.namedItem("password_confirmation").value,
    }),
    "ثبت‌نام با موفقیت انجام شد. برای تأیید ایمیل اقدام کنید.",
    () => redirectAfter(`${baseUrl}/pages/auth/verify-email.php`, 1000),
  );

  const forgotPassword = (root, form) => submit(
    root, form, "/forgot-password",
    () => ({ email: form.elements.namedItem("email").value }),
    "اگر حسابی با این ایمیل وجود داشته باشد، راهنمای بازیابی ارسال خواهد شد.",
    null,
    { genericFailure: "درخواست دریافت شد. اگر حسابی با این ایمیل وجود داشته باشد، راهنمای بازیابی ارسال خواهد شد." },
  );

  const resetPassword = (root, form) => submit(
    root, form, "/reset-password",
    () => ({
      token: form.elements.namedItem("token").value,
      new_password: form.elements.namedItem("new_password").value,
      new_password_confirmation: form.elements.namedItem("new_password_confirmation").value,
    }),
    "رمز عبور با موفقیت تغییر کرد. در حال انتقال...",
    () => redirectAfter(`${baseUrl}/pages/auth/login.php`),
  );

  const verifyEmail = (root, form) => submit(
    root, form, "/verify-email",
    () => ({ token: form.elements.namedItem("token").value }),
    "ایمیل با موفقیت تأیید شد. اکنون می‌توانید وارد شوید.",
  );

  const resendVerification = (root, form) => submit(
    root, form, "/resend-email-verification",
    () => ({ email: form.elements.namedItem("email").value }),
    "اگر حسابی نیازمند تأیید با این ایمیل وجود داشته باشد، درخواست ارسال مجدد بررسی می‌شود.",
  );

  const bindPage = (root) => {
    const bindings = [
      ["[data-auth-login-form]", login],
      ["[data-auth-register-form]", register],
      ["[data-auth-forgot-form]", forgotPassword],
      ["[data-auth-reset-form]", resetPassword],
      ["[data-auth-verify-form]", verifyEmail],
      ["[data-auth-resend-form]", resendVerification],
    ];

    bindings.forEach(([selector, handler]) => {
      const form = root.querySelector(selector);
      if (!form) return;
      form.addEventListener("submit", (event) => {
        event.preventDefault();
        handler(root, form);
      });
    });

    const token = new URLSearchParams(window.location.search).get("token");
    if (token) {
      root.querySelectorAll("input[name='token']").forEach((field) => {
        if (!field.value) field.value = token;
      });
    }
  };

  authPages.forEach(bindPage);

  const authNavigation = async () => {
    const primary = document.querySelector(".profile-menu__primary");
    const secondary = document.querySelector(".profile-menu__secondary");
    const mobileAccount = document.querySelector(".mobile-menu__account");
    const mobileLogin = document.querySelector(".mobile-menu__login");
    const mobileRegister = document.querySelector(".mobile-menu__register");
    if (!primary && !secondary && !mobileAccount && !mobileLogin && !mobileRegister) return;

    try {
      const data = await request("/auth/status");
      if (data.authenticated) {
        if (primary) {
          primary.textContent = "پروفایل من";
          primary.href = `${baseUrl}/pages/account/index.php`;
        }
        if (secondary) {
          secondary.textContent = "خروج";
          secondary.href = "#";
          secondary.dataset.authLogout = "true";
        }
        if (mobileLogin) {
          mobileLogin.textContent = "پروفایل من";
          mobileLogin.href = `${baseUrl}/pages/account/index.php`;
        }
        if (mobileRegister) {
          mobileRegister.textContent = "خروج";
          mobileRegister.href = "#";
          mobileRegister.dataset.authLogout = "true";
        }
        if (mobileAccount) {
          const strong = mobileAccount.querySelector("strong");
          if (strong) strong.textContent = "پروفایل من";
          mobileAccount.style.cursor = "pointer";
          mobileAccount.setAttribute("role", "link");
          mobileAccount.setAttribute("tabindex", "0");
          const goToAccount = () => window.location.assign(`${baseUrl}/pages/account/index.php`);
          mobileAccount.addEventListener("click", goToAccount);
          mobileAccount.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
              event.preventDefault();
              goToAccount();
            }
          });
        }
      } else {
        if (primary) primary.href = `${baseUrl}/pages/auth/login.php`;
        if (secondary) secondary.href = `${baseUrl}/pages/auth/register.php`;
        if (mobileLogin) mobileLogin.href = `${baseUrl}/pages/auth/login.php`;
        if (mobileRegister) mobileRegister.href = `${baseUrl}/pages/auth/register.php`;
      }
    } catch {
      if (primary) primary.href = `${baseUrl}/pages/auth/login.php`;
      if (secondary) secondary.href = `${baseUrl}/pages/auth/register.php`;
      if (mobileLogin) mobileLogin.href = `${baseUrl}/pages/auth/login.php`;
      if (mobileRegister) mobileRegister.href = `${baseUrl}/pages/auth/register.php`;
    }
  };

  authNavigation();

  document.addEventListener("click", async (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;
    const logout = target.closest("[data-auth-logout]");
    if (!logout) return;
    event.preventDefault();
    if (logout.dataset.submitting === "true") return;
    logout.dataset.submitting = "true";
    try {
      await request("/logout", { method: "POST", body: await formBody({}) });
      window.location.reload();
    } catch (error) {
      if (error?.status === 401) {
        window.location.reload();
        return;
      }
      window.location.assign(`${baseUrl}/pages/auth/login.php`);
    } finally {
      logout.dataset.submitting = "false";
    }
  });
};

export default auth;
