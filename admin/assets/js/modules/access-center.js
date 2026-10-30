const adminAccessCenter = () => {
  "use strict";
  const root = document.querySelector("[data-admin-access]");
  if (!root || root.dataset.bound === "true") return;
  root.dataset.bound = "true";
  const apiUrl = root.dataset.apiUrl;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const feedback = root.querySelector("[data-access-feedback]");
  let roles = [];
  let permissions = [];

  const urlFor = (path) => {
    const url = new URL(apiUrl, window.location.href);
    url.searchParams.set("path", "/admin/access" + path);
    return url.toString();
  };
  const requestReauthentication = () => new Promise(resolve => {
    const dialog = document.createElement("dialog");
    const form = document.createElement("form");
    form.method = "dialog";
    const title = el("h2", "تأیید مجدد هویت");
    const description = el("p", "برای اجرای تغییر امنیتی، رمز عبور فعلی حساب خود را وارد کنید.");
    const input = document.createElement("input");
    input.type = "password";
    input.name = "reauth_password";
    input.autocomplete = "current-password";
    input.required = true;
    input.maxLength = 1024;
    input.setAttribute("aria-label", "رمز عبور فعلی");
    const cancel = el("button", "انصراف");
    cancel.type = "button";
    const confirm = el("button", "تأیید و ادامه");
    confirm.type = "submit";
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
      finish(input.value);
    });
    dialog.addEventListener("cancel", event => {
      event.preventDefault();
      finish(null);
    });
    form.append(title, description, input, cancel, confirm);
    dialog.append(form);
    root.append(dialog);
    dialog.showModal();
    input.focus();
  });

  const request = async (path, method = "GET", body = null) => {
    const headers = { Accept: "application/json" };
    const options = { method, credentials: "same-origin", headers };
    if (method !== "GET") {
      const reauthPassword = await requestReauthentication();
      if (!reauthPassword) throw new Error("تأیید مجدد هویت لغو شد؛ تغییری انجام نشد.");
      headers["Content-Type"] = "application/json";
      headers["X-CSRF-TOKEN"] = csrf;
      options.body = JSON.stringify({ ...(body || {}), reauth_password: reauthPassword });
    }
    const response = await fetch(urlFor(path), options);
    const result = await response.json();
    if (!response.ok || result.success !== true) throw new Error(result.message || "درخواست ناموفق بود.");
    return result.data || {};
  };
  const say = (message, error = false) => {
    feedback.textContent = message;
    feedback.dataset.state = error ? "error" : "success";
  };
  const el = (tag, value, cls) => {
    const node = document.createElement(tag);
    if (value !== undefined) node.textContent = String(value);
    if (cls) node.className = cls;
    return node;
  };
  const fillRoleOptions = () => {
    const multi = root.querySelector("[data-access-role-options]");
    const single = root.querySelector("[data-access-role-select]");
    [multi, single].forEach(select => {
      if (!select) return;
      select.replaceChildren();
      roles.filter(role => role.slug !== "super-admin" && role.status === "active").forEach(role => {
        const option = el("option", role.name); option.value = role.id; select.append(option);
      });
    });
  };
  const loadSessions = async () => {
    const data = await request("/sessions");
    const target = root.querySelector('[data-access-list="sessions"]');
    target.replaceChildren();
    (data.items || []).forEach(session => {
      const article = el("article", undefined, "admin-access-row");
      article.append(el("strong", "نشست #" + session.id + " · " + (session.admin_name || "مدیر نامشخص")));
      article.append(el("p", "IP: " + (session.ip_address || "ثبت نشده")));
      article.append(el("p", "زمان ورود: " + session.created_at + " · آخرین فعالیت: " + session.last_activity_at + " · انقضا: " + session.expires_at));
      article.append(el("p", session.user_agent || "User-Agent ثبت نشده"));
      const revoke = el("button", "لغو نشست"); revoke.type = "button";
      revoke.addEventListener("click", async () => {
        try { await request("/sessions/" + session.id, "DELETE"); await loadSessions(); say("نشست لغو شد."); }
        catch (error) { say(error.message, true); }
      });
      article.append(revoke); target.append(article);
    });
    if (!(data.items || []).length) target.append(el("p", "نشست فعال دیگری وجود ندارد."));
  };

  const loadSessionHistory = async () => {
    const data = await request("/sessions/history");
    const target = root.querySelector('[data-access-list="session-history"]');
    target.replaceChildren();
    (data.items || []).forEach(session => {
      const article = el("article", undefined, "admin-access-row");
      const statusLabels = { active: "فعال", revoked: "لغوشده", expired: "منقضی‌شده" };
      article.append(el("strong", "نشست #" + session.id + " · " + (session.admin_name || "مدیر نامشخص")));
      article.append(el("p", "وضعیت: " + (statusLabels[session.session_status] || "نامشخص")));
      article.append(el("p", "شروع: " + (session.created_at || "نامشخص") + " · آخرین فعالیت: " + (session.last_activity_at || "نامشخص")));
      article.append(el("p", "انقضا: " + (session.expires_at || "نامشخص") + " · لغو: " + (session.revoked_at || "ندارد")));
      article.append(el("p", "IP: " + (session.ip_address || "ثبت نشده")));
      article.append(el("p", session.user_agent || "User-Agent ثبت نشده"));
      target.append(article);
    });
    if (!(data.items || []).length) target.append(el("p", "رکوردی در تاریخچه نگهداری‌شده نشست‌ها وجود ندارد."));
  };

  const loadRoles = async () => {
    const data = await request("/roles");
    roles = data.items || [];
    fillRoleOptions();
    const target = root.querySelector('[data-access-list="roles"]');
    target.replaceChildren();
    roles.forEach(role => {
      const article = el("article", undefined, "admin-access-row");
      article.append(el("strong", role.name + " · " + role.slug));
      article.append(el("p", "وضعیت: " + role.status + " · مجوزها: " + role.permission_count + " · مدیران: " + role.admin_count));
      if (role.slug !== "super-admin") {
        const status = document.createElement("select");
        [["active","فعال"],["inactive","غیرفعال"]].forEach(([value,label]) => {
          const option = el("option", label); option.value = value; option.selected = value === role.status; status.append(option);
        });
        const button = el("button", "ثبت وضعیت"); button.type = "button";
        button.addEventListener("click", async () => {
          try { await request("/roles/" + role.id, "PATCH", { name: role.name, description: role.description || "", status: status.value }); await refresh(); say("نقش به‌روزرسانی شد."); }
          catch (error) { say(error.message, true); }
        });
        article.append(status, button);
      } else {
        article.append(el("small", "نقش اصلی محافظت‌شده است."));
      }
      target.append(article);
    });
  };
  const loadAdmins = async () => {
    const data = await request("/admins");
    const target = root.querySelector('[data-access-list="admins"]');
    target.replaceChildren();
    (data.items || []).forEach(admin => {
      const article = el("article", undefined, "admin-access-row");
      article.append(el("strong", admin.username + " · " + admin.email));
      article.append(el("p", (admin.first_name || "") + " " + (admin.last_name || "") + " · نقش‌ها: " + (admin.role_names || "بدون نقش")));
      const status = document.createElement("select");
      [["active","فعال"],["inactive","غیرفعال"],["blocked","مسدود"],["pending","در انتظار"]].forEach(([value,label]) => {
        const option = el("option", label); option.value = value; option.selected = value === admin.status; status.append(option);
      });
      const update = el("button", "ذخیره وضعیت"); update.type = "button";
      update.addEventListener("click", async () => {
        try {
          await request("/admins/" + admin.id, "PATCH", {
            email: admin.email, first_name: admin.first_name || "", last_name: admin.last_name || "", status: status.value
          });
          await loadAdmins(); say("وضعیت مدیر به‌روزرسانی شد.");
        } catch (error) { say(error.message, true); }
      });
      const effective = el("button", "نمایش دسترسی مؤثر");
      effective.type = "button";
      const effectiveList = el("div", undefined, "admin-access-effective-permissions");
      effective.addEventListener("click", async () => {
        try {
          const access = await request("/admins/" + admin.id + "/effective-permissions");
          effectiveList.replaceChildren();
          (access.items || []).forEach(permission => {
            const line = el("p", permission.name + " · " + (permission.granted_by || "نقش نامشخص"));
            effectiveList.append(line);
          });
          if (!(access.items || []).length) effectiveList.append(el("p", "برای این مدیر مجوز فعالی ثبت نشده است."));
          say("گزارش دسترسی مؤثر دریافت شد.");
        } catch (error) { say(error.message, true); }
      });
      const roleSelect = document.createElement("select"); roleSelect.multiple = true;
      const assigned = String(admin.role_slugs || "").split(",");
      roles.filter(role => role.slug !== "super-admin" && role.status === "active").forEach(role => {
        const option = el("option", role.name); option.value = role.id; option.selected = assigned.includes(role.slug); roleSelect.append(option);
      });
      const assign = el("button", "ذخیره نقش‌ها"); assign.type = "button";
      assign.addEventListener("click", async () => {
        try {
          await request("/admins/" + admin.id + "/roles", "PUT", { role_ids: Array.from(roleSelect.selectedOptions).map(option => Number(option.value)) });
          await loadAdmins(); say("نقش‌های مدیر به‌روزرسانی شدند.");
        } catch (error) { say(error.message, true); }
      });
      const remove = el("button", "غیرفعال‌سازی و لغو نشست‌ها"); remove.type = "button";
      remove.addEventListener("click", async () => {
        if (!window.confirm("حساب این مدیر غیرفعال شود و تمام نشست‌های او لغو شوند؟")) return;
        try { await request("/admins/" + admin.id, "DELETE"); await refresh(); say("حساب مدیر غیرفعال و نشست‌ها لغو شدند."); }
        catch (error) { say(error.message, true); }
      });
      article.append(status, update, el("small", "تخصیص نقش"), roleSelect, assign, remove, effective, effectiveList);
      target.append(article);
    });
  };
  const loadPermissions = async () => {
    const data = await request("/permissions");
    permissions = data.items || [];
    const target = root.querySelector('[data-access-list="permissions"]');
    target.replaceChildren();
    const groups = new Map();
    permissions.forEach(permission => {
      const group = permission.module || "general";
      if (!groups.has(group)) groups.set(group, []);
      groups.get(group).push(permission);
    });
    groups.forEach((items, name) => {
      const row = el("article", undefined, "admin-access-row");
      row.append(el("strong", name + " · " + items.length + " مجوز"));
      row.append(el("p", items.map(item => item.action).join("، ")));
      target.append(row);
    });
    await loadRolePermissionSelection();
  };
  const loadRolePermissionSelection = async () => {
    const select = root.querySelector("[data-access-role-select]");
    const target = root.querySelector("[data-access-permissions]");
    if (!select || !target) return;
    const role = roles.find(item => String(item.id) === select.value);
    target.replaceChildren();
    if (!role) return;
    if (role.slug === "super-admin") {
      target.append(el("p", "مجوزهای Super Admin فقط‌خواندنی هستند."));
      return;
    }
    const current = await request("/roles/" + role.id + "/permissions");
    const selected = new Set((current.items || []).map(item => Number(item.id)));
    permissions.forEach(permission => {
      const label = el("label", undefined, "admin-access-permission");
      const checkbox = document.createElement("input");
      checkbox.type = "checkbox"; checkbox.value = permission.id; checkbox.checked = selected.has(Number(permission.id));
      label.append(checkbox, document.createTextNode(permission.module + " / " + permission.action));
      target.append(label);
    });
  };
  const refresh = async () => {
    await loadRoles();
    await loadAdmins();
    await loadPermissions();
  };

  root.querySelector("[data-access-refresh-sessions]")?.addEventListener("click", () => loadSessions().catch(error => say(error.message, true)));
  root.querySelector("[data-access-history-sessions]")?.addEventListener("click", () => loadSessionHistory().catch(error => say(error.message, true)));

  root.querySelector("[data-access-role-select]")?.addEventListener("change", () => {
    loadRolePermissionSelection().catch(error => say(error.message, true));
  });

  const bind = (name, callback) => {
    const form = root.querySelector('[data-access-form="' + name + '"]');
    form?.addEventListener("submit", async event => {
      event.preventDefault();
      const submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;
      try { await callback(new FormData(form)); form.reset(); await refresh(); say("عملیات با موفقیت انجام شد."); }
      catch (error) { say(error.message, true); }
      finally { if (submit) submit.disabled = false; }
    });
  };

  bind("admin", async data => {
    await request("/admins", "POST", {
      username: data.get("username"), email: data.get("email"), password: data.get("password"),
      first_name: data.get("first_name"), last_name: data.get("last_name"),
      role_ids: data.getAll("role_ids[]").map(Number)
    });
  });
  bind("role", async data => {
    await request("/roles", "POST", { name: data.get("name"), slug: data.get("slug"), description: data.get("description") });
  });
  bind("permissions", async () => {
    const roleId = root.querySelector("[data-access-role-select]")?.value;
    if (!roleId) throw new Error("ابتدا یک نقش انتخاب کنید.");
    const role = roles.find(item => String(item.id) === roleId);
    if (role?.slug === "super-admin") throw new Error("مجوزهای Super Admin قابل بازنویسی نیستند.");
    const ids = Array.from(root.querySelectorAll("[data-access-permissions] input:checked")).map(input => Number(input.value));
    await request("/roles/" + roleId + "/permissions", "PUT", { permission_ids: ids });
  });

  refresh().then(() => loadSessions()).then(() => say("اطلاعات دسترسی‌ها دریافت شد.")).catch(error => say(error.message, true));
};

export default adminAccessCenter;
