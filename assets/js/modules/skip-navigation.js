const skipNavigation = () => {
  const existingTarget = document.getElementById("main-content");
  if (existingTarget) {
    if (!existingTarget.hasAttribute("tabindex")) existingTarget.tabIndex = -1;
    return;
  }

  const main = document.querySelector("main, [role='main']");
  if (!main) return;

  if (!main.id) {
    main.id = "main-content";
    if (!main.hasAttribute("tabindex")) main.tabIndex = -1;
    return;
  }

  const target = document.createElement("span");
  target.id = "main-content";
  target.tabIndex = -1;
  target.className = "skip-navigation-target";
  target.setAttribute("role", "region");
  target.setAttribute("aria-label", "محتوای اصلی");
  target.textContent = "محتوای اصلی";
  main.before(target);
};

export default skipNavigation;
