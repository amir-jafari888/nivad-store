const adminPasswordVisibility = () => {
  "use strict";

  const toggles = document.querySelectorAll("[data-password-toggle]");

  toggles.forEach((button) => {
    if (button.dataset.passwordToggleBound === "true") return;

    const inputId = button.getAttribute("aria-controls");
    const input = inputId ? document.getElementById(inputId) : null;

    if (!(input instanceof HTMLInputElement)) return;

    const visibleIcon = button.querySelector('[data-password-icon="visible"]');
    const hiddenIcon = button.querySelector('[data-password-icon="hidden"]');

    if (!(visibleIcon instanceof SVGElement) || !(hiddenIcon instanceof SVGElement)) return;

    const setVisibility = (visible) => {
      input.type = visible ? "text" : "password";
      button.setAttribute(
        "aria-label",
        visible ? "مخفی‌کردن رمز عبور" : "نمایش رمز عبور",
      );
      visibleIcon.hidden = !visible;
      hiddenIcon.hidden = visible;
    };

    button.dataset.passwordToggleBound = "true";
    button.addEventListener("click", () => {
      setVisibility(input.type === "password");
    });

    setVisibility(false);
  });
};

export default adminPasswordVisibility;
