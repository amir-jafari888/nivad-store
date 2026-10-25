const header = () => {
  const menuButton = document.querySelector("[data-menu-toggle]");
  const mobileMenu = document.querySelector("[data-mobile-menu]");
  const profileButton = document.querySelector("[data-profile-toggle]");
  const profileMenu = document.querySelector("[data-profile-menu]");

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
    setMenuIcon(true);

    mobileMenu.querySelector(focusableSelector)?.focus({ preventScroll: true });
  };

  menuButton?.addEventListener("click", () => {
    mobileMenu?.classList.contains("is-open") ? closeMenu() : openMenu();
  });

  mobileMenu?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => closeMenu({ restore: false }));
  });

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
    profileMenu
      .querySelector(focusableSelector)
      ?.focus({ preventScroll: true });
  };

  profileButton?.addEventListener("click", (event) => {
    event.stopPropagation();
    profileMenu?.hidden ? openProfile() : closeProfile({ restore: true });
  });

  profileMenu?.addEventListener("click", (event) => event.stopPropagation());

  const setActiveNavigation = () => {
    const links = [...document.querySelectorAll("[data-nav-link]")];

    if (!links.length) return;

    const rawPathname =
      window.location.pathname.replace(/\/+$/, "") || "/";

    const normalizedBase =
      document.documentElement.dataset.basePath?.replace(/\/+$/, "") || "";

    const pathname =
      normalizedBase &&
      (rawPathname === normalizedBase ||
        rawPathname.startsWith(`${normalizedBase}/`))
        ? rawPathname.slice(normalizedBase.length) || "/"
        : rawPathname;

    let activeKey = null;

    if (pathname === "/" || pathname === "/index.php") {
      activeKey = "home";
    } else if (
      pathname === "/categories" ||
      pathname.startsWith("/categories/")
    ) {
      activeKey = "categories";
    } else if (
      pathname === "/pages/shop/wishlist.php" ||
      pathname.startsWith("/pages/shop/wishlist/")
    ) {
      activeKey = "wishlist";
    } else if (
      pathname === "/pages/shop/cart.php" ||
      pathname.startsWith("/pages/shop/cart/")
    ) {
      activeKey = "cart";
    } else if (
      pathname === "/pages/account" ||
      pathname.startsWith("/pages/account/")
    ) {
      activeKey = "account";
    }

    links.forEach((link) => {
      const key = link.dataset.navLink;
      const isHomeAlias = activeKey === "home" && key === "store";
      const isActive = activeKey !== null && (key === activeKey || isHomeAlias);

      link.classList.toggle("is-active", isActive);

      if (isActive) {
        link.setAttribute("aria-current", "page");
      } else {
        link.removeAttribute("aria-current");
      }
    });
  };

  setActiveNavigation();

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
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 600 && mobileMenu?.classList.contains("is-open")) {
      closeMenu();
    }
  });
};

export default header;
