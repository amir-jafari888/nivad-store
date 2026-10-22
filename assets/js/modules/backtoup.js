const backToTop = () => {
  const button = document.getElementById("backToTop");
  const circle = document.querySelector(".progress-ring__circle");

  if (!button) return;

  const hasCircle = Boolean(circle?.r?.baseVal);

  const radius = hasCircle ? circle.r.baseVal.value : 0;

  const circumference = 2 * Math.PI * radius;

  if (hasCircle) {
    circle.style.strokeDasharray = `${circumference} ${circumference}`;

    circle.style.strokeDashoffset = String(circumference);
  }

  const setProgress = (percent) => {
    if (!hasCircle) return;

    const safePercent = Math.max(0, Math.min(100, percent));

    const offset = circumference - (safePercent / 100) * circumference;

    circle.style.strokeDashoffset = String(offset);
  };

  const update = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop || 0;

    const scrollHeight = document.documentElement.scrollHeight || 0;

    const viewportHeight =
      document.documentElement.clientHeight || window.innerHeight || 0;

    const scrollableHeight = Math.max(0, scrollHeight - viewportHeight);

    const progress =
      scrollableHeight > 0 ? (scrollTop / scrollableHeight) * 100 : 0;

    setProgress(progress);

    button.classList.toggle("show", scrollTop > 300);

    button.setAttribute("aria-hidden", String(scrollTop <= 300));
  };

  let ticking = false;

  const handleScroll = () => {
    if (ticking) return;

    ticking = true;

    window.requestAnimationFrame(() => {
      update();
      ticking = false;
    });
  };

  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  };

  button.addEventListener("click", scrollToTop);

  window.addEventListener("scroll", handleScroll, { passive: true });

  window.addEventListener("resize", update, { passive: true });

  update();

  window.digitalStoreBackToTop = {
    update,
    scrollToTop,
    setProgress,
  };
};

export default backToTop;
