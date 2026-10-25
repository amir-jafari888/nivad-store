(function () {
  var root = document.documentElement;
  var key = "digital-store-theme";
  var stored = null;

  try {
    stored = window.localStorage.getItem(key);
  } catch (error) {
    stored = null;
  }

  var validStored = stored === "light" || stored === "dark";
  var systemDark = false;

  if (window.matchMedia) {
    systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  }

  root.dataset.theme = validStored ? stored : (systemDark ? "dark" : "light");
})();
