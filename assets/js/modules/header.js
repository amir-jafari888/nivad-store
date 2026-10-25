const header = () => {
  const menuButton = document.querySelector("[data-menu-toggle]");
  const mobileMenu = document.querySelector("[data-mobile-menu]");
  const profileButton = document.querySelector("[data-profile-toggle]");
  const profileMenu = document.querySelector("[data-profile-menu]");
  const themeButtons = document.querySelectorAll("[data-theme-toggle]");

  const menuIcon = menuButton?.querySelector(".icon-menu");
  const focusableSelector =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

  let lastMenuTrigger = null;
  let lastProfileTrigger = null;

  const setMenuIcon = (open) => {
    if (!menuIcon) return;
    menuIcon.innerHTML = open
      ? '<path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'
      : '<path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>';
  };

  const restoreFocus = (element) => {
    if (element && document.contains(element)) {
      element.focus({ preventScroll: true });
    }
  };

  const closeMenu = ({ restore = true } = {}) => {
    if (!menuButton || !mobileMenu) return;
    mobileMenu.classList.remove("is-open");
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.setAttribute("aria-label", "باز کردن منو");
    mobileMenu.setAttribute("aria-hidden", "true");
    setMenuIcon(false);

    if (restore) {
      restoreFocus(lastMenuTrigger || menuButton);
      lastMenuTrigger = null;
    }
  };

  const openMenu = () => {
    if (!menuButton || !mobileMenu) return;
    lastMenuTrigger = menuButton;
    mobileMenu.classList.add("is-open");
    menuButton.setAttribute("aria-expanded", "true");
    menuButton.setAttribute("aria-label", "بستن منو");
    mobileMenu.setAttribute("aria-hidden", "false");

    const firstFocusable = mobileMenu.querySelector(focusableSelector);
    firstFocusable?.focus({ preventScroll: true });
    setMenuIcon(true);
  };

  menuButton?.addEventListener("click", () => {
    mobileMenu?.classList.contains("is-open") ? closeMenu() : openMenu();
  });

  mobileMenu
    ?.querySelectorAll("a")
    .forEach((link) => link.addEventListener("click", () => closeMenu({ restore: false })));

  const closeProfile = ({ restore = false } = {}) => {
    if (!profileButton || !profileMenu) return;
    profileMenu.hidden = true;
    profileButton.setAttribute("aria-expanded", "false");

    if (restore) {
      restoreFocus(lastProfileTrigger || profileButton);
      lastProfileTrigger = null;
    }
  };

  const openProfile = () => {
    if (!profileButton || !profileMenu) return;
    lastProfileTrigger = profileButton;
    profileMenu.hidden = false;
    profileButton.setAttribute("aria-expanded", "true");

    const firstFocusable = profileMenu.querySelector(focusableSelector);
    firstFocusable?.focus({ preventScroll: true });
  };

  profileButton?.addEventListener("click", (event) => {
    event.stopPropagation();
    profileMenu?.hidden ? openProfile() : closeProfile({ restore: true });
  });

  profileMenu?.addEventListener("click", (event) => event.stopPropagation());

  const normalizeBrandAndRoutes = () => {
    const brand = document.querySelector(".brand-logo");
    const brandText = brand?.querySelector(".brand-logo__text");

    if (brandText) {
      brandText.textContent = "نیواد";
    }

    brand?.setAttribute("aria-label", "نیواد - صفحه اصلی");

    document.querySelectorAll('[data-nav-link="categories"]').forEach((link) => {
      const currentHref = link.getAttribute("href");

      if (!currentHref) return;

      try {
        const target = new URL(currentHref, window.location.href);
        target.pathname = target.pathname.replace(/\/products\/?$/, "/categories");
        link.setAttribute("href", target.toString());
      } catch {
        // Preserve the server-provided route when URL normalization is unavailable.
      }
    });
  };

  normalizeBrandAndRoutes();

  const setActiveNavigation = () => {
    const links = document.querySelectorAll("[data-nav-link]");
    if (!links.length) return;

    const pathname = window.location.pathname.replace(/\/+$/, "") || "/";
    let activeKey = "store";

    if (pathname === "/products" || pathname.startsWith("/products/")) {
      activeKey = "products";
    } else if (pathname === "/discover" || pathname.startsWith("/discover/")) {
      activeKey = "discover";
    } else if (
      pathname === "/categories" ||
      pathname.startsWith("/categories/")
    ) {
      activeKey = "categories";
    } else if (pathname === "/pages/store/compatibility.php") {
      activeKey = "compatibility";
    } else if (pathname === "/pages/content/faq.php") {
      activeKey = "guide";
    } else if (pathname === "/pages/content/contact.php") {
      activeKey = "support";
    } else if (pathname === "/pages/content/about.php") {
      activeKey = "about";
    }

    links.forEach((link) => {
      const isActive = link.dataset.navLink === activeKey;
      link.classList.toggle("is-active", isActive);

      if (isActive) {
        link.setAttribute("aria-current", "page");
      } else {
        link.removeAttribute("aria-current");
      }
    });
  };

  setActiveNavigation();

  const readStoredTheme = () => {
    try {
      const stored = localStorage.getItem("digital-store-theme");
      return stored === "light" || stored === "dark" ? stored : null;
    } catch {
      return null;
    }
  };

  const getTheme = () => {
    const stored = readStoredTheme();
    if (stored) return stored;

    return window.matchMedia?.("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  };

  const applyTheme = (theme, persist = true) => {
    const normalized = theme === "dark" ? "dark" : "light";
    document.documentElement.dataset.theme = normalized;

    if (persist) {
      try {
        localStorage.setItem("digital-store-theme", normalized);
      } catch {
        // Storage can be unavailable in private/restricted browsing contexts.
      }
    }

    themeButtons.forEach((button) => {
      const isDark = normalized === "dark";
      button.setAttribute("aria-pressed", String(isDark));
      button.setAttribute(
        "aria-label",
        isDark ? "فعال کردن حالت روشن" : "فعال کردن حالت تاریک",
      );

      const label = button.querySelector("[data-theme-label]");
      if (label) label.textContent = isDark ? "حالت روشن" : "حالت تاریک";
    });
  };

  applyTheme(getTheme(), Boolean(readStoredTheme()));

  themeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      applyTheme(getTheme() === "dark" ? "light" : "dark");
    });
  });

  const trapFocus = (container, event) => {
    if (event.key !== "Tab") return;

    const focusable = [...container.querySelectorAll(focusableSelector)].filter(
      (element) => !element.hidden,
    );

    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  };

  document.addEventListener("click", (event) => {
    if (
      profileMenu &&
      !profileMenu.contains(event.target) &&
      !profileButton?.contains(event.target)
    ) {
      closeProfile();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      if (mobileMenu?.classList.contains("is-open")) closeMenu();
      if (profileMenu && !profileMenu.hidden) closeProfile({ restore: true });
      return;
    }

    if (mobileMenu?.classList.contains("is-open")) trapFocus(mobileMenu, event);
    if (profileMenu && !profileMenu.hidden) trapFocus(profileMenu, event);
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 600 && mobileMenu?.classList.contains("is-open")) {
      closeMenu();
    }
  });
};

export default header;
