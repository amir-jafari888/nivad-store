export default function adminDashboardUi() {
  const dashboard = document.querySelector(".admin-dashboard-page");
  if (!dashboard) return;

  const profile = dashboard.querySelector(".admin-shared-topbar-profile");
  if (profile && profile.tagName !== "A") {
    const link = document.createElement("a");
    link.className = `${profile.className} admin-profile-link`;
    link.href = "/admin/profile/index.php";
    link.setAttribute("aria-label", "مشاهده و تنظیم پروفایل مدیر");
    while (profile.firstChild) link.appendChild(profile.firstChild);
    profile.replaceWith(link);
  }

  const main = dashboard.querySelector(".admin-dashboard-main");
  if (!main || main.querySelector(".admin-dashboard-settings-strip")) return;

  const heading = main.querySelector(".admin-dashboard-page-heading");
  if (!heading) return;

  const strip = document.createElement("section");
  strip.className = "admin-dashboard-settings-strip";
  strip.setAttribute("aria-labelledby", "admin-dashboard-settings-title");
  strip.innerHTML = `
    <div class="admin-dashboard-settings-copy">
      <strong id="admin-dashboard-settings-title">تنظیمات تجربه داشبورد</strong>
      <span>تراکم، اندازه متن و رفتار ناوبری از تنظیمات پنل کنترل می‌شوند؛ داده‌های عملیاتی داشبورد دست‌کاری نمی‌شوند.</span>
    </div>
    <div class="admin-dashboard-settings-actions">
      <a href="/admin/settings">تنظیمات پنل</a>
      <a href="/admin/profile/index.php">پروفایل مدیر</a>
    </div>
  `;
  heading.insertAdjacentElement("afterend", strip);
}
