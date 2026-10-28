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

  const mount = () => {
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

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mount, { once: true });
  } else {
    mount();
  }
};

export default navigationState;
