const customerSupport = () => {
  "use strict";
  const root = document.querySelector("[data-customer-support-page]");
  if (!root || root.dataset.bound === "true") return;
  root.dataset.bound = "true";
  const apiBase = root.dataset.apiBase || "";
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const state = root.querySelector("[data-support-state]");
  const feedback = root.querySelector("[data-support-message]");
  const list = root.querySelector("[data-customer-ticket-list]");

  const endpoint = path => {
    const url = new URL(apiBase, window.location.href);
    url.searchParams.set("path", path);
    return url.toString();
  };
  const request = async (path, method = "GET", body = null) => {
    const headers = { Accept: "application/json" };
    const options = { method, credentials: "same-origin", headers };
    if (method !== "GET") {
      headers["Content-Type"] = "application/json";
      headers["X-CSRF-TOKEN"] = csrf;
      options.body = JSON.stringify(body || {});
    }
    const response = await fetch(endpoint(path), options);
    const result = await response.json();
    if (!response.ok || result.success !== true) throw new Error(result.message || "درخواست ناموفق بود.");
    return result.data || {};
  };
  const el = (tag, text, cls) => {
    const node = document.createElement(tag);
    if (text !== undefined) node.textContent = String(text);
    if (cls) node.className = cls;
    return node;
  };
  const say = (message, error = false) => {
    if (feedback) {
      feedback.textContent = message;
      feedback.dataset.state = error ? "error" : "success";
    }
    if (state && error) state.textContent = message;
  };

  const load = async () => {
    try {
      const data = await request("/support/tickets");
      list.replaceChildren();
      const tickets = data.items || [];
      if (!tickets.length) list.append(el("p", "هنوز تیکتی ثبت نکرده‌اید."));
      tickets.forEach(ticket => {
        const article = el("article", undefined, "customer-shop__panel account-ticket");
        article.append(el("strong", ticket.ticket_number + " · " + ticket.subject));
        article.append(el("p", "وضعیت: " + ticket.status + " · اولویت: " + ticket.priority + " · آخرین پیام: " + ticket.last_message_at));
        const details = document.createElement("details");
        const summary = el("summary", "مشاهده گفتگو");
        const messages = el("div", undefined, "account-ticket-messages");
        summary.addEventListener("click", async () => {
          if (details.dataset.loaded === "true") return;
          try {
            const result = await request("/support/tickets/" + ticket.id);
            (result.ticket.messages || []).forEach(message => {
              messages.append(el("p", (message.sender_type === "customer" ? "شما" : "پشتیبانی") + ": " + message.message));
            });
            if (ticket.status !== "closed") {
              const form = document.createElement("form");
              form.className = "account-password-form";
              const textarea = document.createElement("textarea");
              textarea.name = "message"; textarea.rows = 3; textarea.maxLength = 20000; textarea.required = true; textarea.placeholder = "پیام خود را بنویسید…";
              const submit = el("button", "ارسال پاسخ"); submit.type = "submit"; submit.className = "customer-shop__button";
              form.append(textarea, submit);
              form.addEventListener("submit", async event => {
                event.preventDefault();
                submit.disabled = true;
                try {
                  await request("/support/tickets/" + ticket.id + "/messages", "POST", { message: textarea.value });
                  await load(); say("پیام شما ثبت شد.");
                } catch (error) { say(error.message, true); }
                finally { submit.disabled = false; }
              });
              messages.append(form);
            }
            details.dataset.loaded = "true";
          } catch (error) { say(error.message, true); }
        });
        details.append(summary, messages);
        article.append(details);
        list.append(article);
      });
      if (state) state.textContent = "تعداد درخواست‌ها: " + tickets.length;
    } catch (error) {
      if (state) state.textContent = error.message;
    }
  };

  root.querySelector("[data-customer-ticket-form]")?.addEventListener("submit", async event => {
    event.preventDefault();
    const form = event.currentTarget;
    const submit = form.querySelector('[type="submit"]');
    submit.disabled = true;
    const data = new FormData(form);
    try {
      await request("/support/tickets", "POST", {
        subject: data.get("subject"), category: data.get("category"),
        priority: data.get("priority"), message: data.get("message")
      });
      form.reset();
      await load();
      say("تیکت شما ثبت شد.");
    } catch (error) { say(error.message, true); }
    finally { submit.disabled = false; }
  });
  root.querySelector("[data-support-refresh]")?.addEventListener("click", load);
  load();
};

export default customerSupport;
