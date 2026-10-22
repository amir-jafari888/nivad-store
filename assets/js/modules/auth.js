const auth = () => {
  const baseUrl = String(window.NIVAD_BASE_URL || "").replace(/\/$/, "");
  const apiBase = `${baseUrl}/api/index.php`;

  const authPages = document.querySelectorAll("[data-auth-page]");

  const request = async (path, options = {}) => {
    const url = new URL(apiBase, window.location.origin);

    url.searchParams.set("path", path);

    const config = {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        ...(options.body instanceof URLSearchParams
          ? {
              "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
            }
          : {}),
        ...(options.headers || {}),
      },
      ...options,
    };

    const response = await fetch(url.toString(), config);

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
      const error = new Error(payload?.message || "عملیات ناموفق بود.");

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

  const formBody = async (values) => {
    const body = new URLSearchParams();

    body.set("csrf_token", await csrf());

    Object.entries(values).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        body.set(key, String(value));
      }
    });

    return body;
  };

  const setMessage = (root, message, type = "") => {
    const element = root.querySelector("[data-auth-message]");

    if (!element) {
      return;
    }

    element.textContent = message;
    element.dataset.state = type;
  };

  const setLoading = (form, loading) => {
    const button = form.querySelector("button[type='submit']");

    if (!button) {
      return;
    }

    button.disabled = loading;

    if (loading) {
      button.dataset.originalText = button.textContent;
      button.textContent = "در حال پردازش...";
    } else if (button.dataset.originalText) {
      button.textContent = button.dataset.originalText;
    }
  };

  const handleLogin = async (root, form) => {
    setLoading(form, true);
    setMessage(root, "");

    try {
      const body = await formBody({
        identifier: form.identifier.value,
        password: form.password.value,
      });

      await request("/login", {
        method: "POST",
        body,
      });

      setMessage(root, "ورود با موفقیت انجام شد. در حال انتقال...", "success");

      const redirect = root.dataset.redirect || `${baseUrl}/`;

      window.setTimeout(() => {
        window.location.href = redirect;
      }, 500);
    } catch (error) {
      setMessage(root, error.message || "ورود انجام نشد.", "error");
    } finally {
      setLoading(form, false);
    }
  };

  const handleRegister = async (root, form) => {
    setLoading(form, true);
    setMessage(root, "");

    try {
      const body = await formBody({
        first_name: form.first_name.value,
        last_name: form.last_name.value,
        email: form.email.value,
        phone: form.phone.value,
        password: form.password.value,
        password_confirmation: form.password_confirmation.value,
      });

      await request("/register", {
        method: "POST",
        body,
      });

      setMessage(
        root,
        "ثبت‌نام با موفقیت انجام شد. برای تأیید ایمیل اقدام کنید.",
        "success",
      );

      window.setTimeout(() => {
        window.location.href = `${baseUrl}/pages/auth/login.php`;
      }, 900);
    } catch (error) {
      setMessage(root, error.message || "ثبت‌نام انجام نشد.", "error");
    } finally {
      setLoading(form, false);
    }
  };

  const handleForgotPassword = async (root, form) => {
    setLoading(form, true);
    setMessage(root, "");

    try {
      const body = await formBody({
        email: form.email.value,
      });

      await request("/forgot-password", {
        method: "POST",
        body,
      });

      setMessage(
        root,
        "اگر حسابی با این ایمیل وجود داشته باشد، لینک بازیابی ارسال خواهد شد.",
        "success",
      );
    } catch (error) {
      setMessage(root, error.message || "درخواست بازیابی ثبت نشد.", "error");
    } finally {
      setLoading(form, false);
    }
  };

  const handleResetPassword = async (root, form) => {
    setLoading(form, true);
    setMessage(root, "");

    try {
      const body = await formBody({
        token: form.token.value,
        new_password: form.new_password.value,
        new_password_confirmation: form.new_password_confirmation.value,
      });

      await request("/reset-password", {
        method: "POST",
        body,
      });

      setMessage(
        root,
        "رمز عبور با موفقیت تغییر کرد. در حال انتقال...",
        "success",
      );

      window.setTimeout(() => {
        window.location.href = `${baseUrl}/pages/auth/login.php`;
      }, 900);
    } catch (error) {
      setMessage(root, error.message || "بازیابی رمز عبور انجام نشد.", "error");
    } finally {
      setLoading(form, false);
    }
  };

  const handleVerifyEmail = async (root, form) => {
    setLoading(form, true);
    setMessage(root, "");

    try {
      const body = await formBody({
        token: form.token.value,
      });

      await request("/verify-email", {
        method: "POST",
        body,
      });

      setMessage(
        root,
        "ایمیل با موفقیت تأیید شد. اکنون می‌توانید وارد شوید.",
        "success",
      );
    } catch (error) {
      setMessage(root, error.message || "تأیید ایمیل انجام نشد.", "error");
    } finally {
      setLoading(form, false);
    }
  };

  const handleResendVerification = async (root, form) => {
    setLoading(form, true);
    setMessage(root, "");

    try {
      const body = await formBody({
        email: form.email.value,
      });

      await request("/resend-email-verification", {
        method: "POST",
        body,
      });

      setMessage(root, "درخواست ارسال مجدد تأیید ایمیل ثبت شد.", "success");
    } catch (error) {
      setMessage(
        root,
        error.message || "ارسال مجدد تأیید ایمیل انجام نشد.",
        "error",
      );
    } finally {
      setLoading(form, false);
    }
  };

  const bindPage = (root) => {
    const loginForm = root.querySelector("[data-auth-login-form]");

    const registerForm = root.querySelector("[data-auth-register-form]");

    const forgotForm = root.querySelector("[data-auth-forgot-form]");

    const resetForm = root.querySelector("[data-auth-reset-form]");

    const verifyForm = root.querySelector("[data-auth-verify-form]");

    const resendForm = root.querySelector("[data-auth-resend-form]");

    loginForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      handleLogin(root, loginForm);
    });

    registerForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      handleRegister(root, registerForm);
    });

    forgotForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      handleForgotPassword(root, forgotForm);
    });

    resetForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      handleResetPassword(root, resetForm);
    });

    verifyForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      handleVerifyEmail(root, verifyForm);
    });

    resendForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      handleResendVerification(root, resendForm);
    });

    const token = new URLSearchParams(window.location.search).get("token");

    if (token) {
      const tokenInput = root.querySelector("input[name='token']");

      if (tokenInput) {
        tokenInput.value = token;
      }
    }
  };

  authPages.forEach(bindPage);

  const authNavigation = async () => {
    const primary = document.querySelector(".profile-menu__primary");

    const secondary = document.querySelector(".profile-menu__secondary");

    const mobileAccount = document.querySelector(".mobile-menu__account");

    if (!primary && !secondary && !mobileAccount) {
      return;
    }

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

        if (mobileAccount) {
          const strong = mobileAccount.querySelector("strong");

          if (strong) {
            strong.textContent = "پروفایل من";
          }

          mobileAccount.style.cursor = "pointer";

          mobileAccount.addEventListener("click", () => {
            window.location.href = `${baseUrl}/pages/account/index.php`;
          });
        }
      } else {
        if (primary) {
          primary.href = `${baseUrl}/pages/auth/login.php`;
        }

        if (secondary) {
          secondary.href = `${baseUrl}/pages/auth/register.php`;
        }
      }
    } catch {
      if (primary) {
        primary.href = `${baseUrl}/pages/auth/login.php`;
      }

      if (secondary) {
        secondary.href = `${baseUrl}/pages/auth/register.php`;
      }
    }
  };

  authNavigation();

  document.addEventListener("click", async (event) => {
    const logout = event.target.closest("[data-auth-logout]");

    if (!logout) {
      return;
    }

    event.preventDefault();

    try {
      const body = await formBody({});

      await request("/logout", {
        method: "POST",
        body,
      });

      window.location.reload();
    } catch {
      window.location.href = `${baseUrl}/pages/auth/login.php`;
    }
  });
};

export default auth;
