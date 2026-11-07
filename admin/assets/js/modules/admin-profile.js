const adminProfile = () => {
  "use strict";

  const profileForm = document.querySelector("[data-admin-profile-form]");
  const passwordForm = document.querySelector("[data-admin-password-form]");
  if ((!profileForm && !passwordForm) || document.documentElement.dataset.adminProfileBound === "true") return;
  document.documentElement.dataset.adminProfileBound = "true";

  const apiUrl = document.querySelector('meta[name="admin-api-url"]')?.content || "";
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const feedback = document.querySelector("[data-profile-feedback]");

  const say = (message, error = false) => {
    if (!feedback) return;
    feedback.textContent = message;
    feedback.dataset.state = error ? "error" : "success";
  };

  const askCurrentPassword = () => new Promise(resolve => {
    const dialog = document.createElement("dialog");
    dialog.className = "admin-profile-reauth-dialog";
    const form = document.createElement("form");
    form.method = "dialog";
    const heading = document.createElement("h2");
    heading.textContent = "تأیید هویت";
    const description = document.createElement("p");
    description.textContent = "برای ذخیره تغییرات، رمز عبور فعلی حساب را وارد کنید.";
    const label = document.createElement("label");
    label.textContent = "رمز عبور فعلی";
    const input = document.createElement("input");
    input.type = "password";
    input.name = "current_password";
    input.autocomplete = "current-password";
    input.maxLength = 1024;
    input.required = true;
    label.append(input);
    const actions = document.createElement("div");
    actions.className = "admin-profile-dialog-actions";
    const cancel = document.createElement("button");
    cancel.type = "button";
    cancel.textContent = "انصراف";
    const submit = document.createElement("button");
    submit.type = "submit";
    submit.textContent = "تأیید و ذخیره";
    actions.append(cancel, submit);
    form.append(heading, description, label, actions);
    dialog.append(form);
    document.body.append(dialog);

    let settled = false;
    const finish = value => {
      if (settled) return;
      settled = true;
      dialog.close();
      dialog.remove();
      resolve(value);
    };
    cancel.addEventListener("click", () => finish(null));
    form.addEventListener("submit", event => {
      event.preventDefault();
      if (!input.value) return;
      finish(input.value);
    });
    dialog.addEventListener("cancel", event => {
      event.preventDefault();
      finish(null);
    });
    dialog.showModal();
    input.focus();
  });

  const send = async payload => {
    if (!apiUrl) throw new Error("نشانی API پنل در دسترس نیست.");
    const currentPassword = await askCurrentPassword();
    if (!currentPassword) throw new Error("عملیات لغو شد؛ تغییری ذخیره نشد.");

    const url = new URL(apiUrl, window.location.href);
    url.searchParams.set("path", "/admin/profile");
    const response = await fetch(url, {
      method: "PATCH",
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf
      },
      body: JSON.stringify({ ...payload, current_password: currentPassword })
    });
    let result;
    try {
      result = await response.json();
    } catch (_) {
      throw new Error("پاسخ سرور قابل خواندن نیست.");
    }
    if (!response.ok || result.success !== true) {
      throw new Error(result.message || "ذخیره تغییرات ناموفق بود.");
    }
    return result.data || {};
  };

  const currentProfileValues = () => {
    if (!profileForm) return {};
    const data = new FormData(profileForm);
    return Object.fromEntries(["first_name", "last_name", "email", "username"].map(key => [key, String(data.get(key) || "").trim()]));
  };

  const setBusy = (form, busy) => {
    const button = form.querySelector('button[type="submit"]');
    if (button) {
      button.disabled = busy;
      button.dataset.originalText ||= button.textContent;
      button.textContent = busy ? "در حال ذخیره…" : button.dataset.originalText;
    }
  };

  if (profileForm) {
    profileForm.addEventListener("submit", async event => {
      event.preventDefault();
      if (!profileForm.reportValidity()) return;
      const data = new FormData(profileForm);
      const payload = Object.fromEntries(["first_name", "last_name", "email", "username"].map(key => [key, String(data.get(key) || "").trim()]));
      setBusy(profileForm, true);
      try {
        await send(payload);
        const name = [payload.first_name, payload.last_name].filter(Boolean).join(" ") || "نام مدیر ثبت نشده";
        const display = document.querySelector("[data-profile-display-name]");
        if (display) display.textContent = name;
        document.querySelectorAll(".admin-shared-first-name").forEach(node => { node.textContent = payload.first_name; });
        document.querySelectorAll(".admin-shared-last-name").forEach(node => { node.textContent = payload.last_name; });
        document.querySelectorAll(".admin-profile-avatar, .admin-shared-avatar").forEach(node => { node.textContent = Array.from(payload.first_name)[0] || "م"; });
        say("اطلاعات شخصی با موفقیت ذخیره شد.");
      } catch (error) {
        say(error.message, true);
      } finally {
        setBusy(profileForm, false);
      }
    });
  }

  if (passwordForm) {
    passwordForm.addEventListener("submit", async event => {
      event.preventDefault();
      if (!passwordForm.reportValidity()) return;
      const data = new FormData(passwordForm);
      const newPassword = String(data.get("new_password") || "");
      const confirmPassword = String(data.get("confirm_password") || "");
      if (newPassword !== confirmPassword) {
        say("تکرار رمز عبور جدید با آن مطابقت ندارد.", true);
        return;
      }
      setBusy(passwordForm, true);
      try {
        await send({ ...currentProfileValues(), new_password: newPassword, confirm_password: confirmPassword });
        passwordForm.reset();
        say("رمز عبور با موفقیت تغییر کرد.");
      } catch (error) {
        say(error.message, true);
      } finally {
        setBusy(passwordForm, false);
      }
    });
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", adminProfile, { once: true });
} else {
  adminProfile();
}

export default adminProfile;
