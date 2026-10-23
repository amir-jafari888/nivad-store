const PERFORMANCE_ROOT_MARGIN = "400px 0px";
const DEFERRED_SECTION_SELECTOR = "main > section:not(.hero):not(.site-header)";

const scheduleIdle = (callback) => {
  if (typeof window.requestIdleCallback === "function") {
    window.requestIdleCallback(callback, { timeout: 1200 });
    return;
  }
  window.setTimeout(callback, 200);
};

const markNonCriticalImages = () => {
  const images = document.querySelectorAll("img");
  const heroImages = document.querySelectorAll(".hero img");

  heroImages.forEach((image, index) => {
    image.decoding = "async";
    if (index === 0) {
      image.loading = "eager";
      image.fetchPriority = "high";
      return;
    }
    image.loading = "lazy";
    image.fetchPriority = "low";
  });

  images.forEach((image) => {
    if (!image.hasAttribute("decoding")) image.decoding = "async";

    const isCritical =
      image.closest(".site-header, [data-critical-media]") !== null ||
      image === heroImages[0];

    if (isCritical) return;
    if (!image.hasAttribute("loading")) image.loading = "lazy";
    if (image.loading === "lazy" && !image.hasAttribute("fetchpriority")) {
      image.fetchPriority = "low";
    }
  });
};

const deferOffscreenSections = () => {
  const sections = document.querySelectorAll(DEFERRED_SECTION_SELECTOR);
  sections.forEach((section, index) => {
    if (index >= 2) section.style.contentVisibility = "auto";
  });
};

const observePerformance = () => {
  if (typeof PerformanceObserver !== "function") return;

  const metrics = (window.NIVAD_PERFORMANCE = window.NIVAD_PERFORMANCE || {});
  const observe = (type, handler, options = {}) => {
    try {
      const observer = new PerformanceObserver((list) => {
        list.getEntries().forEach(handler);
      });
      observer.observe({ type, buffered: true, ...options });
      return observer;
    } catch {
      return null;
    }
  };

  observe("largest-contentful-paint", (entry) => {
    metrics.lcp = entry.startTime;
  });

  observe("layout-shift", (entry) => {
    if (!entry.hadRecentInput) metrics.cls = (metrics.cls || 0) + entry.value;
  });

  observe("longtask", (entry) => {
    metrics.longTasks = (metrics.longTasks || 0) + 1;
    metrics.longTaskDuration = (metrics.longTaskDuration || 0) + entry.duration;
  });

  observe("event", (entry) => {
    if (entry.duration >= 100) {
      metrics.slowInteractions = (metrics.slowInteractions || 0) + 1;
    }
  }, { durationThreshold: 40 });
};

const initPerformance = () => {
  markNonCriticalImages();
  deferOffscreenSections();
  observePerformance();

  scheduleIdle(() => {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    if (!lazyImages.length || !("IntersectionObserver" in window)) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.decoding = "async";
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: PERFORMANCE_ROOT_MARGIN },
    );

    lazyImages.forEach((image) => observer.observe(image));
  });
};

export default initPerformance;
