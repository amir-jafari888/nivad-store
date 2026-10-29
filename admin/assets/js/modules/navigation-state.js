const navigationState = () => {
  "use strict";

  const normalize = (value) => {
    const path = value.replace(/\\/g, "/").replace(/\/+$/, "");
    return path === "" ? "/" : path;
  };

  const isMatch = (current, target) => {
    if (target === "/admin") return current === target;
    return current === target || current.startsWith(`${target}/`);
  };

  const mountActiveNavigation = () => {
    const current = normalize(window.location.pathname);
    const links = Array.from(
      document.querySelectorAll(
        ".admin-shared-nav a, .admin-dashboard-nav a"
      )
    );

    const candidates = links
      .map((link) => {
        try {
          const target = normalize(
            new URL(link.href, window.location.origin).pathname
          );
          return { link, target };
        } catch (_) {
          return null;
        }
      })
      .filter((entry) => entry !== null && isMatch(current, entry.target))
      .sort((a, b) => b.target.length - a.target.length);

    links.forEach((link) => link.removeAttribute("aria-current"));
    const active = candidates[0];
    if (!active) return;

    active.link.setAttribute("aria-current", "page");
    const group = active.link.closest("details");
    if (group) group.open = true;
  };

  const mountResponsiveNavigation = () => {
    const sidebar = document.querySelector("[data-admin-sidebar]");
    const toggles = Array.from(document.querySelectorAll("[data-admin-menu-toggle]"));
    if (!sidebar || toggles.length === 0) return;

    const mobileQuery = window.matchMedia("(max-width: 1009px)");
    const closeButton = document.createElement("button");
    closeButton.type = "button";
    closeButton.className = "admin-nav-close";
    closeButton.dataset.adminMenuClose = "";
    closeButton.setAttribute("aria-label", "بستن منوی مدیریت");
    closeButton.textContent = "×";
    sidebar.prepend(closeButton);

    const backdrop = document.createElement("button");
    backdrop.type = "button";
    backdrop.className = "admin-nav-backdrop";
    backdrop.setAttribute("aria-label", "بستن منوی مدیریت");
    backdrop.tabIndex = -1;
    document.body.append(backdrop);

    let lastFocused = null;

    const setOpen = (open) => {
      const shouldOpen = mobileQuery.matches && open;
      document.body.classList.toggle("admin-nav-open", shouldOpen);
      sidebar.setAttribute("aria-hidden", String(mobileQuery.matches && !shouldOpen));
      sidebar.inert = mobileQuery.matches && !shouldOpen;
      toggles.forEach((toggle) => toggle.setAttribute("aria-expanded", String(shouldOpen)));
      if (shouldOpen) {
        lastFocused = document.activeElement;
        const firstControl = sidebar.querySelector("a[href], summary, button:not(:disabled)");
        firstControl?.focus({ preventScroll: true });
      } else if (lastFocused && typeof lastFocused.focus === "function") {
        lastFocused.focus({ preventScroll: true });
        lastFocused = null;
      }
    };

    toggles.forEach((toggle) => {
      toggle.addEventListener("click", () => {
        const open = !document.body.classList.contains("admin-nav-open");
        setOpen(open);
      });
    });

    closeButton.addEventListener("click", () => setOpen(false));
    backdrop.addEventListener("click", () => setOpen(false));
    document.addEventListener("keydown", (event) => {
      if (!document.body.classList.contains("admin-nav-open")) return;
      if (event.key === "Escape") {
        setOpen(false);
        return;
      }
      if (event.key !== "Tab") return;
      const focusable = Array.from(
        sidebar.querySelectorAll('a[href], summary, button:not(:disabled), input:not(:disabled), select:not(:disabled), textarea:not(:disabled), [tabindex]:not([tabindex="-1"])')
      ).filter((element) => !element.hidden && element.getClientRects().length > 0);
      if (focusable.length === 0) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });

    const syncBreakpoint = () => {
      if (mobileQuery.matches) {
        setOpen(false);
      } else {
        document.body.classList.remove("admin-nav-open");
        sidebar.removeAttribute("aria-hidden");
        sidebar.inert = false;
        toggles.forEach((toggle) => toggle.setAttribute("aria-expanded", "false"));
      }
    };

    if (typeof mobileQuery.addEventListener === "function") {
      mobileQuery.addEventListener("change", syncBreakpoint);
    } else {
      mobileQuery.addListener(syncBreakpoint);
    }
    syncBreakpoint();
  };

  const mount = () => {
    mountActiveNavigation();
    mountResponsiveNavigation();
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mount, { once: true });
  } else {
    mount();
  }
};

export default navigationState;
