const header = () => {
  const menuButton = document.querySelector("[data-menu-toggle]");
  const mobileMenu = document.querySelector("[data-mobile-menu]");
  const profileButton = document.querySelector("[data-profile-toggle]");
  const profileMenu = document.querySelector("[data-profile-menu]");
  const themeButtons = document.querySelectorAll("[data-theme-toggle]");

  const menuIcon = menuButton?.querySelector(".icon-menu");

  const setMenuIcon = (open) => {
    if (!menuIcon) return;
    menuIcon.innerHTML = open
      ? '<path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'
      : '<path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>';
  };

  const closeMenu = () => {
    if (!menuButton || !mobileMenu) return;
    mobileMenu.classList.remove("is-open");
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.setAttribute("aria-label", "باز کردن منو");
    mobileMenu.setAttribute("aria-hidden", "true");
    setMenuIcon(false);
  };

  const openMenu = () => {
    if (!menuButton || !mobileMenu) return;
    mobileMenu.classList.add("is-open");
    menuButton.setAttribute("aria-expanded", "true");
    menuButton.setAttribute("aria-label", "بستن منو");
    mobileMenu.setAttribute("aria-hidden", "false");
    setMenuIcon(true);
  };

  menuButton?.addEventListener("click", () => {
    mobileMenu?.classList.contains("is-open") ? closeMenu() : openMenu();
  });

  mobileMenu
    ?.querySelectorAll("a")
    .forEach((link) => link.addEventListener("click", closeMenu));

  const closeProfile = () => {
    if (!profileButton || !profileMenu) return;
    profileMenu.hidden = true;
    profileButton.setAttribute("aria-expanded", "false");
  };

  const openProfile = () => {
    if (!profileButton || !profileMenu) return;
    profileMenu.hidden = false;
    profileButton.setAttribute("aria-expanded", "true");
  };

  profileButton?.addEventListener("click", (event) => {
    event.stopPropagation();
    profileMenu?.hidden ? openProfile() : closeProfile();
  });

  profileMenu?.addEventListener("click", (event) => event.stopPropagation());

  const setActiveNavigation = () => {
    const links = document.querySelectorAll("[data-nav-link]");

    if (!links.length) {
      return;
    }

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

  const getTheme = () => localStorage.getItem("digital-store-theme") || "light";

  const applyTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem("digital-store-theme", theme);

    themeButtons.forEach((button) => {
      const isDark = theme === "dark";
      button.setAttribute("aria-pressed", String(isDark));
      button.setAttribute(
        "aria-label",
        isDark ? "فعال کردن حالت روشن" : "فعال کردن حالت تاریک",
      );

      const label = button.querySelector("[data-theme-label]");
      if (label) label.textContent = isDark ? "حالت روشن" : "حالت تاریک";
    });
  };

  applyTheme(getTheme());

  themeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      applyTheme(getTheme() === "dark" ? "light" : "dark");
    });
  });

  document.addEventListener("click", (event) => {
    if (
      profileMenu &&
      !profileMenu.contains(event.target) &&
      !profileButton?.contains(event.target)
    )
      closeProfile();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeMenu();
      closeProfile();
    }
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 600) closeMenu();
  });
};

export default header;
