# NIVAD — ADMIN PANEL MASTER FEATURE REGISTER
**تاریخ ممیزی:** 2026-10-29  
**Repository:** `amir-jafari888/nivad-store`  
**مبنای ممیزی:** فایل‌های ایده روی `master` و source files قابل بازیابی از همان branch.  
**وضعیت سند:** Audit baseline؛ این فایل کد اجرایی نیست و هیچ قابلیت جدیدی را پیاده‌سازی نمی‌کند.

## 1. قواعد وضعیت

- **PARTIAL**: بخشی از زیرساخت/نمونه پیاده‌سازی در source دیده شده، اما قرارداد کامل، پوشش، اتصال سراسری یا تست اثبات نشده است.
- **MISSING**: در محدوده فایل‌ها و شواهد بررسی‌شده، زیرساختی برای قابلیت پیدا نشده است؛ این برچسب ادعای جست‌وجوی کامل همه repository نیست.
- **UNKNOWN**: برای اعلام وضعیت به schema/runtime/config/tests یا sourceهای بررسی‌نشده نیاز است.
- **BLOCKED**: ممیزی قطعی به محیط/داده/دسترسی‌ای وابسته است که در این بررسی موجود نبود.
- **EXISTS** فقط وقتی استفاده می‌شود که وجود یک قرارداد مشخص در source قابل اثبات باشد؛ به معنی کامل یا production-ready بودن feature نیست.

**مهم:** امتیاز ارزش محصول از ۱۰ است و با وضعیت زیرساخت یا اولویت اجرا یکی نیست. «وجود فایل/صفحه» به معنی تکمیل feature نیست. این مرحله read-only بوده؛ کد و دیتابیس تغییر نکرده‌اند.

## 2. Master Feature Register

| ID | قابلیت یکتا (ادغام ایده‌های تکراری دو سند) | محل در پنل | ارزش /10 | اولویت پیشنهادی | زیرساخت فعلی | شواهد موجود در source بررسی‌شده | شکاف اصلی / شرط پذیرش |
|---|---|---|---:|---|---|---|---|
| ADM-001 | هویت مدیر، ورود/خروج و اعتبارسنجی نشست | Core / Security | 10 | P0 | PARTIAL | `AdminAuthService` احراز هویت، session validation، expiry، active-account check و audit logging دارد؛ logout و CSRF نیز در UIهای بررسی‌شده دیده می‌شوند. | مسیرهای واقعی login/logout و responseهای HTML/API باید یکپارچه و با تست‌های route/session ثابت شوند؛ session revoke و rotation کامل نیز جداگانه بررسی شوند. |
| ADM-002 | دسترسی امن به تمام routeها و APIهای ادمین | Core / Security | 10 | P0 | PARTIAL | `AdminAuthMiddleware` نشست و `admin.access` را بررسی می‌کند. در شاخه فعلی، نبودن ID و denial با JSON پاسخ داده می‌شود؛ ممکن است برای browser page مناسب نباشد. | route-by-route middleware map، HTML vs JSON error contract، authorization در سطح service/API و تست دسترسی مستقیم لازم است. |
| ADM-003 | نقش‌ها و permissionهای ریزدانه | Administration | 10 | P0 | PARTIAL | `AuthorizationService` با repositoryهای permission، user-role، role-permission و role lookup کار می‌کند. | ماتریس واقعی DB و تمام permission checks/routes بررسی نشده؛ permission واحد `admin.access` به تنهایی برای least privilege کافی نیست. نیازمند inventory واقعی schema/data و تست ماتریس نقش‌ها. |
| ADM-004 | Super Admin با دسترسی کامل و امن | Administration | 10 | P0 | UNKNOWN | در source بررسی‌شده، `AuthorizationService` صرفاً role/permission assignment فعال را می‌سنجد و bypass ویژه‌ای برای Super Admin نشان نمی‌دهد. | باید تصمیم explicit در قرارداد authorization باشد؛ دسترسی کامل فقط server-side و قابل آزمون، نه با نام role یا مخفی‌کردن UI. وضعیت واقعی DB و نقش‌ها باید دوباره از محیط هدف خوانده شود. |
| ADM-005 | ساخت/ویرایش/غیرفعال‌سازی مدیران و تخصیص نقش | Administration | 10 | P0 | UNKNOWN | لینک و صفحه مدیریت مدیران در sourceهای نمونه بررسی‌شده اثبات نشد. | مسیرها، controller/service/repository، validation، جلوگیری از self-escalation، آخرین Super Admin، confirmation و audit باید موجود و تست شوند. |
| ADM-006 | مشاهده فهرست نشست‌ها و آنلاین/آخرین فعالیت مدیران | Security Operations | 9 | P0 | PARTIAL | `AdminAuthService` از `AdminSessionService` برای اعتبارسنجی و touch نشست استفاده می‌کند. | وجود session validation به معنی session inventory UI نیست؛ storage/schema، login/last activity/logout و status transitions، admin listing و تست revoke باید تأیید شوند. |
| ADM-007 | پایان‌دادن/لغو نشست مدیر از سوی سطح مجاز | Security Operations | 9 | P1 | PARTIAL | `AdminSessionService` و `AdminSessionRepository` متدهای revoke نشست جاری و revoke همه نشست‌های یک مدیر دارند؛ endpoint/UI مدیریتی و permission اختصاصی از شواهد بررسی‌شده ثابت نشد. | عملیات server-side، invalidation فوری، permission اختصاصی، جلوگیری از self-lockout و audit رویداد اجباری است. |
| ADM-008 | تاریخچه تغییرات و audit trail برای عملیات مهم | Audit & Security | 10 | P0 | PARTIAL | صفحه `admin/audit-logs/index.php` به `/api/admin/audit-logs` متصل است؛ `AdminAuthenticationAuditService` رویدادهای احراز هویت را به Logger می‌فرستد. | باید منبع داده، schema، retention، coverage تمام عملیات حساس، actor/target/before-after/outcome و جلوگیری از نشت داده بررسی شود. صفحه موجود کافی نیست. |
| ADM-009 | ثبت مقدار قبلی/جدید برای تغییرات | Audit & Security | 10 | P0 | UNKNOWN | صفحه audit فیلدهای action/module/entity/description/IP/time می‌خواند؛ snapshot قبل/بعد از source فعلی اثبات نشد. | ثبت before/after با redaction و نسخه‌بندی امن؛ تست تغییر قیمت/موجودی/نقش/سفارش و حفظ اسرار. |
| ADM-010 | داشبورد عملیاتی با KPIهای واقعی | Dashboard | 9 | P1 | PARTIAL | `admin/index.php` از `AdminDashboardMetricsService` استفاده می‌کند؛ این سرویس `ReportService` را مصرف می‌کند. | source data، تعریف هر metric، رفتار empty/error، بازه زمانی و تطبیق با DB هنوز باید تست شود؛ health/incidents/pending actions کامل اثبات نشده‌اند. |
| ADM-011 | مدیریت محصولات، دسته‌ها، برندها، ویژگی‌ها و مشخصات | Commerce Operations | 10 | P0 | PARTIAL | مسیرها/صفحات این حوزه‌ها در پروژه گزارش شده‌اند. | CRUD کامل، validation، permissions، media، bulk actions، audit و تست هر مسیر باید جداگانه trace شوند؛ وجود directory اثبات کامل‌بودن نیست. |
| ADM-012 | سفارش، پرداخت، مرجوعی، بازپرداخت و کوپن | Commerce Operations | 10 | P0 | PARTIAL | صفحات/ماژول‌های عملیاتی در پروژه گزارش شده‌اند؛ dashboard نیز گزارش‌های تجاری را مصرف می‌کند. | state transitions، تراکنش‌ها، idempotency، authorization، audit و provider integration باید بررسی و تست شوند. |
| ADM-013 | موجودی، ارسال و هشدار کمبود کالا | Commerce Operations | 10 | P0 | PARTIAL | بخش inventory و لینک shipping در shell گزارش شده‌اند. | منبع stock واحد، ledger adjustment، shipment route validity، low-stock thresholds و audit لازم است. |
| ADM-014 | کنترل صفحات و محتوای storefront / CMS | Storefront & Content | 9 | P1 | PARTIAL | صفحات/ماژول‌های slider، collection، discovery، compatibility، setup و catalog در ساختار پروژه گزارش شده‌اند. | source of truth برای هر سکشن، publish/unpublish، ordering، draft/preview، schedule و permissionها هنوز feature-by-feature تأیید نشده‌اند. |
| ADM-015 | کتابخانه رسانه و مدیریت فایل | Storefront & Content | 7 | P2 | UNKNOWN | قرارداد کامل media library از sourceهای بررسی‌شده اثبات نشد. | upload validation، file type/size policy، storage path، access control، alt text و حذف امن باید طراحی/ممیزی شود. |
| ADM-016 | اعلان داخلی ادمین (in-app notification center) | Shared / Communications | 9 | P0 | PARTIAL | `admin/notifications/index.php` یک فرم ارسال اعلان به `/api/admin/notifications` دارد؛ این صفحه فعلی عنوان «ارسال اعلان برای کاربر» دارد، نه اثبات inbox کامل ادمین. | schema، recipient model، read/unread، severity، source link، acknowledgment، authorization و UI inbox باید بررسی شوند. |
| ADM-017 | ایمیل/Gmail با حساب Gmail اختصاصی و ارسال رایگان | Shared / Communications | 9 | P1 | UNKNOWN | SMTP/mail provider، delivery log و تنظیمات محرمانه از sourceهای بررسی‌شده تأیید نشد. | adapter/provider contract، secrets خارج از Git، SMTP auth، rate limits، retry/dedup، delivery status و fallback داخلی لازم است. رایگان‌بودن تضمین‌شده نیست و محدودیت‌های Gmail وابسته به حساب/سیاست آن است. |
| ADM-018 | سیاست ارسال اعلان، اولویت‌بندی و جلوگیری از spam | Shared / Communications | 8 | P1 | UNKNOWN | policy، deduplication و rate limiting اعلان از شواهد فعلی اثبات نشد. | event types/severity/recipients، throttling، dedupe key و ثبت نتیجه delivery تعریف و تست شود. |
| ADM-019 | ثبت خطاها و تشخیص incidentهای واقعی | Observability | 9 | P1 | PARTIAL | Logger از سرویس audit احراز هویت استفاده می‌شود؛ سامانه incident مرکزی در sourceهای بررسی‌شده اثبات نشد. | centralized exception/error capture، correlation، dedupe، severity، lifecycle و notification امن لازم است. |
| ADM-020 | System health و بررسی route/database/service | Observability | 8 | P2 | UNKNOWN | داشبورد از metrics گزارش‌گیری استفاده می‌کند؛ health-check subsystem اثبات نشده است. | فقط dependencyهای واقعی monitor شوند؛ timeout، health result، تاریخچه، هشدار و خطای false positive تست شود. |
| ADM-021 | صف job، webhook و سلامت درگاه پرداخت | Observability | 7 | P3 / مشروط | UNKNOWN | وجود queue/background worker یا webhook integration در sourceهای بررسی‌شده ثابت نشد. | فقط پس از اثبات وجود هر integration؛ در غیر این صورت این بخش نباید UI نمایشی یا metric جعلی داشته باشد. |
| ADM-022 | پنل پشتیبانی و ticket/conversation مستقل از incidentها | Support | 8 | P2 | UNKNOWN | Ticket system با assignment/conversation/status از شواهد بررسی‌شده اثبات نشد. | customer/admin message contracts، status، assignment، history، privacy و notification لازم است. |
| ADM-023 | تحلیل تعامل محصول: view، wishlist، cart، conversion و جست‌وجو | Analytics | 8 | P2 | UNKNOWN | dashboard/reporting وجود دارد، اما event contracts برای product views، wishlist/cart funnel و search impressions اثبات نشده‌اند. | event instrumentation، تعریف unique view، retention/privacy و تطبیق conversion با سفارش‌های واقعی لازم است. |
| ADM-024 | خریدراهنما و محتوای آموزشی محصول | Storefront Intelligence | 8 | P2 | UNKNOWN | این ایده از سابقه طراحی storefront آمده؛ زیرساخت مدیریتی خاص آن در source بررسی‌شده اثبات نشد. | ابتدا تصمیم درباره مدل محتوا و customer-facing page؛ پنل فقط ابزار مدیریت واقعی آن را می‌گیرد. |
| ADM-025 | مقایسه محصول | Storefront Intelligence | 8 | P2 | PARTIAL | وجود حوزه مشخصات/ویژگی محصول می‌تواند داده پایه فراهم کند، اما قرارداد comparison کامل اثبات نشده است. | attribute normalization، معیارهای مقایسه، داده ناقص و UI مقایسه باید تست شود. |
| ADM-026 | گراف سازگاری محصولات/لوازم جانبی | Storefront Intelligence | 9 | P1 | PARTIAL | بخش‌های discovery/compatibility/setup در ساختار پروژه گزارش شده‌اند. | مدل رابطه سازگاری، قواعد validation، مدیریت edgeها و تست داده ناسازگار باید بررسی شود. |
| ADM-027 | نمایش چیدمان/Setup و پیشنهاد اجزای سازگار | Storefront Intelligence | 8 | P2 | PARTIAL | ماژول setup در ساختار پروژه گزارش شده؛ قرارداد recommendation/bundle کامل اثبات نشده است. | data model، rule engine، inventory-aware suggestions و مسیرهای مدیریتی باید audit شوند. |
| ADM-028 | Recently viewed / recently compared | Storefront Intelligence | 6 | P3 | UNKNOWN | رویدادها/ذخیره‌سازی این رفتار از شواهد فعلی اثبات نشده‌اند. | پس از instrumentation و تصمیم privacy/retention اجرا شود. |
| ADM-029 | توصیه شخصی‌سازی‌شده و Smart Bundles | Storefront Intelligence | 7–8 | P3 | UNKNOWN | مدل/سرویس توصیه و قواعد bundle از sourceهای بررسی‌شده اثبات نشده‌اند. | ابتدا rule-based MVP با داده واقعی؛ از ادعای AI/شخصی‌سازی بدون داده و ارزیابی پرهیز شود. |
| ADM-030 | Global search و command palette | Admin Productivity | 8 | P2 | UNKNOWN | قابلیت سراسری در sourceهای بررسی‌شده اثبات نشده است. | مجوز-آگاه برای product/order/user/ticket، محدودیت نتایج و keyboard accessibility. |
| ADM-031 | Bulk actions و saved filters/views | Admin Productivity | 8 | P2 | UNKNOWN | قرارداد عمومی این قابلیت‌ها از sourceهای بررسی‌شده اثبات نشد. | bulk action باید permission-aware، CSRF-protected، قابل audit و دارای confirmation/partial failure report باشد. |
| ADM-032 | Activity timeline برای entityها و revision history محتوا | Shared / Content | 8 | P2 | PARTIAL | صفحه audit log موجود است؛ timeline per-entity و content revision history اثبات نشده‌اند. | event schema مشترک، before/after، pagination و اتصال هر entity به رخدادهای واقعی لازم است. |
| ADM-033 | Draft, preview و scheduled publishing | Storefront & Content | 8 | P2 | UNKNOWN | workflow زمان‌بندی/نسخه‌بندی از شواهد فعلی اثبات نشده است. | state machine، timezone، scheduler واقعی، preview امن و تست publish/unpublish لازم است. |
| ADM-034 | تنظیمات شخصی UI در برابر تنظیمات سراسری سیستم | Settings | 7 | P1 | PARTIAL | `admin/settings/index.php` به‌عنوان تنظیمات ترجیحی UI گزارش شده (local/browser-oriented). | settings server-side و global config باید جدا، با permission و persistence واقعی تعریف شوند؛ UI preference نباید امنیت سیستم را وانمود کند. |
| ADM-035 | Permission-denied و خطاهای قابل فهم برای browser/API | Shared UX / Security | 10 | P0 | PARTIAL | middleware فعلی در برخی مسیرها JSON 401/403 برمی‌گرداند؛ قرارداد UI یکپارچه برای صفحات عادی اثبات نشده است. | HTML page باید error state مناسب بگیرد؛ API همچنان status code دقیق داشته باشد؛ محتوای محافظت‌شده نباید قبل از authorization render شود. |
| ADM-036 | RTL، accessibility، responsive، shared shell و design tokens | Shared UX | 10 | P0 | PARTIAL | `admin/includes/auth.php` shell مشترک را تزریق می‌کند، اما dashboard دارای shell اختصاصی است؛ صفحات notifications/audit logs استایل inline و مستقل دارند. | shell واحد واقعی، breakpoint CSS architecture، keyboard/focus/contrast، loading/empty/error states و تست mobile/tablet/desktop باید audit شوند. |
| ADM-037 | پروفایل مدیر با نام واقعی و fallback امن | Shared UX | 8 | P1 | PARTIAL | shell مشترک نام را از داده مدیر می‌سازد؛ قرارداد کامل first/last name و رفتار fallback از این بررسی به‌طور کامل ثابت نشد. | تست identity/session binding و عدم نمایش email/username به‌عنوان نام در صورت نبود قرارداد. |
| ADM-038 | Security operations / suspicious activity / anti-fraud signals | Security Operations | 8 | P1 | PARTIAL | rate-limit و failed-login tracking در `AdminAuthService`/login-attempt service مصرف می‌شود؛ تشخیص گسترده anomaly اثبات نشده است. | rule definitions، thresholdها، false positive handling، audit و notification؛ بدون داده معتبر نباید ادعای تشخیص تقلب شود. |
| ADM-039 | تأیید دومرحله‌ای برای عملیات حساس و separation of duties | Security Operations | 9 | P1 | UNKNOWN | confirmation/reauth/approval workflow از sourceهای بررسی‌شده اثبات نشده است. | فهرست عملیات حساس، actor separation، expiry، replay protection و audit مشخص شود. |
| ADM-040 | backup/recovery awareness | Observability | 7 | P3 / مشروط | UNKNOWN | قرارداد backup status/recovery از sourceهای بررسی‌شده اثبات نشده است. | فقط در صورت وجود فرآیند backup واقعی و قابل مشاهده؛ پنل نباید status ساختگی نشان دهد یا secrets را نمایش دهد. |
| ADM-041 | Admin route discovery hardening / تغییر URL ورود | Security Operations | 4 | P3 | PARTIAL | rewrite صریح `/admin/logout` در `.htaccess` دیده می‌شود؛ مسیرهای login/admin نیازمند trace کامل‌اند. | تغییر URL صرفاً defense-in-depth است؛ authentication، authorization، CSRF و rate-limit اصل امنیت‌اند. قبل از تغییر، route matrix و تست regression لازم است. |
| ADM-042 | گزارش‌های تجاری و analytics | Reports | 8 | P1 | PARTIAL | `AdminDashboardMetricsService` به `ReportService` وابسته است. | تمام report contracts، SQL/data source، timezone، currency, permissions و تطبیق عددها با داده واقعی باید بررسی شود. |
| ADM-043 | Audit-safe bulk admin/account changes و admin lifecycle events | Administration / Audit | 9 | P1 | UNKNOWN | auth audit برای login/logout/session invalidation وجود دارد؛ audit کامل تغییر حساب/نقش از source فعلی اثبات نشد. | event coverage برای create/disable/role-change/session-revoke و ثبت نتیجه/actor/target لازم است. |
| ADM-044 | مراقبت از secrets و داده‌های حساس در logs/notifications | Cross-cutting Security | 10 | P0 | PARTIAL | `AdminAuthenticationAuditService` متدی برای شناسایی کلیدهای حساس دارد، اما استفاده سراسری آن از source فعلی اثبات نشد. | redaction باید مرکزی و اجباری باشد؛ تست regression برای password/token/session/PII در logs و email. |

## 3. نتیجه ممیزی زیرساخت

### زیرساختی که در source قابل مشاهده است
- احراز هویت مدیر، بررسی فعال‌بودن حساب و نشست، expiry/session validation.
- چارچوب role/permission مبتنی بر repositoryها و permission check با نام `admin.access`.
- rate-limit و ثبت تلاش‌های ورود در مسیر احراز هویت.
- audit/logging مرتبط با login، login failure و session invalidation.
- صفحه‌های موجود برای dashboard، audit logs، notifications و UI settings؛ وجود صفحه به معنی کامل‌بودن feature نیست.
- سرویس metrics داشبورد با وابستگی به `ReportService`.

### شکاف‌های مهم که قبل از implementation باید بسته شوند
1. **Authorization/Super Admin:** منطق دسترسی کامل ویژه Super Admin در `AuthorizationService` دیده نشد؛ نقش/permission واقعی DB نیز باید در محیط هدف تأیید شود.
2. **مدیریت مدیران و نشست‌ها:** سرویس نشست مصرف می‌شود، اما UI/route و عملیات کامل admin lifecycle/revoke از شواهد بررسی‌شده ثابت نشده‌اند.
3. **Audit before/after:** صفحه و بخشی از auth logging هست؛ تاریخچه تغییر مقادیر قبلی/جدید برای همه عملیات حساس ثابت نشده.
4. **Notifications + Gmail:** یک endpoint/form اعلان وجود دارد؛ inbox کامل admin، mail transport، delivery logs/retry/dedup اثبات نشده‌اند.
5. **Error/incident/support/health:** Logger و auth logging وجود دارند؛ incident lifecycle، ticketing و health monitoring مرکزی اثبات نشده‌اند.
6. **CMS workflow و analytics:** صفحات محتوایی و گزارش‌گیری پایه وجود دارند؛ publish scheduling, revision history و product-event analytics نیاز به audit مجزا دارند.
7. **Route/UX regression:** middleware فعلی برای identity/permission denial پاسخ JSON می‌دهد؛ این با نیاز تجربه browser برای `/admin` و صفحه login باید در route tracing بررسی شود. این مشاهده نشانه قابل پیگیری است، نه اثبات قطعی علت خطای فعلی.
8. **UI architecture:** shared shell موجود است، اما صفحات بررسی‌شده از نظر layout/style یکدست نیستند؛ معماری CSS/JS و responsive باید صفحه‌به‌صفحه اعتبارسنجی شود.

## 4. محدودیت‌های این ممیزی

- فایل‌های ایده روی `master` خوانده شدند؛ برای sourceهای بالا چند فایل مستقیم از `master` بازیابی شدند.
- در این نوبت schema کامل دیتابیس محیط runtime، تمام route registrationها، تمام endpointها، تمام migrationها، همه test files و اجرای مرورگر/HTTP بررسی نشده‌اند؛ بنابراین وضعیت آن موارد **UNKNOWN** است، نه MISSING قطعی.
- جست‌وجوی code-search از connector نتیجه‌ای برنگرداند؛ به همین دلیل هیچ ادعایی درباره نبود feature در کل repository صرفاً از search خالی نتیجه‌گیری نشده است.
- این مرحله read-only است؛ هیچ فایل اجرایی یا دیتابیس تغییر نکرده و هیچ تست runtime ادعا نمی‌شود.

## 5. گام بعدی پیشنهادی

1. تأیید این register به‌عنوان baseline.
2. ممیزی کامل route/middleware و بازتولید خطای `/admin`، login و dashboard.
3. استخراج واقعی schema/migrations و role-permission matrix از محیط مورد استفاده.
4. ردیابی هر ADM-ID به route → middleware → controller → service → repository/model → table → UI → tests.
5. سپس تبدیل هر مورد به قرارداد پذیرش و اجرای چرخه: **AUDIT → CONTRACT → DESIGN → IMPLEMENT → TEST → PASS → LOCK**.

**قاعده تصمیم:** تا وقتی یک feature به شواهد اجرایی و تست قابل تکرار وصل نشده، آن را PASS/LOCK یا production-ready اعلام نکنید.
