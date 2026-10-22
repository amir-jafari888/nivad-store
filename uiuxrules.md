# استاندارد جامع قوانین UI/UX فرانت‌اند Nivad
## نسخه 2026

---

# بخش 01 — اصل بنیادین طراحی Nivad

Nivad نباید صرفاً «ظاهر زیبا» داشته باشد.

هدف طراحی:

> ساخت تجربه‌ای واضح، سریع، قابل اعتماد، قابل فهم، قابل دسترس و کم‌اصطکاک برای خرید.

هر تصمیم UI باید حداقل یکی از این موارد را بهبود دهد:

- فهم کاربر
- سرعت انجام کار
- اعتماد
- کاهش خطا
- کاهش سردرگمی
- افزایش قابلیت کشف
- دسترس‌پذیری
- سازگاری با دستگاه
- قابلیت پیش‌بینی رفتار
- رضایت کاربر

اگر یک عنصر هیچ ارزش مشخصی ایجاد نمی‌کند:

**نباید صرفاً برای زیبایی وجود داشته باشد.**

اصل سادگی و وضوح از اصول بنیادی طراحی مدرن است. Apple نیز در راهنمای ۲۰۲۶ خود بر هدفمندی، انعطاف‌پذیری، سادگی، کیفیت جزئیات و طراحی انسانی تأکید می‌کند.

---

# بخش 02 — قانون No Guessing در UI/UX

در طراحی Nivad نیز مانند Backend:

**حدس ممنوع است.**

قبل از طراحی هر صفحه باید مشخص شود:

- کاربر اینجا چه می‌خواهد؟
- از کجا وارد شده؟
- قدم بعدی چیست؟
- چه اطلاعاتی لازم دارد؟
- چه اطلاعاتی غیرضروری است؟
- چه خطاهایی ممکن است رخ دهد؟
- چه حالت‌هایی ممکن است اتفاق بیفتد؟
- چه داده‌ای واقعاً موجود است؟
- چه داده‌ای هنوز Backend ندارد؟

اگر رفتار واقعی سیستم مشخص نیست:

**UNKNOWN**

نباید برای آن رفتار ساختگی طراحی شود.

---

# بخش 03 — User Goal First

هر صفحه باید یک هدف اصلی داشته باشد.

مثلاً:

Product Detail:

> تصمیم برای خرید

Cart:

> بررسی و ادامه خرید

Checkout:

> تکمیل سفارش

Search:

> پیدا کردن محصول

Compatibility:

> فهمیدن سازگاری

Setup Builder:

> ساخت یک ترکیب مناسب

هدف اصلی باید از نظر بصری واضح‌تر از اهداف فرعی باشد.

---

# بخش 04 — Visual Hierarchy

هر صفحه باید سلسله‌مراتب بصری مشخص داشته باشد.

ترتیب معمول:

1. هدف اصلی
2. اطلاعات کلیدی
3. تصمیم اصلی
4. اقدام اصلی
5. اطلاعات تکمیلی
6. اقدامات فرعی

نباید همه عناصر با یک شدت بصری نمایش داده شوند.

از:

- اندازه
- وزن فونت
- فاصله
- موقعیت
- کنتراست
- رنگ
- فضای خالی

برای ایجاد Hierarchy استفاده شود.

---

# بخش 05 — Clarity Over Decoration

زیبایی نباید خوانایی را خراب کند.

ممنوع:

- متن کم‌کنتراست صرفاً برای ظاهر
- انیمیشن غیرضروری
- Gradient بدون کاربرد
- سایه‌های بیش از حد
- Cardهای بیش از حد
- Borderهای تزئینی بی‌دلیل
- Iconهای بدون مفهوم
- متن‌های طولانی در محل نامناسب

---

# بخش 06 — Consistency

یک رفتار باید در تمام سایت رفتار مشابه داشته باشد.

مثلاً:

اگر Primary Button در یک صفحه:

> خرید محصول

است، در صفحه دیگر نباید بدون دلیل با ظاهر و رفتار کاملاً متفاوت نمایش داده شود.

Consistency شامل:

- رنگ
- Typography
- Button
- Input
- Card
- Modal
- Toast
- Icon
- Spacing
- Navigation
- Error
- Loading
- Interaction

است.

Apple نیز Consistency را یکی از اصول طراحی معرفی می‌کند.

---

# بخش 07 — Design System First

قبل از طراحی صفحات متعدد باید Componentها و Tokenهای مشترک مشخص باشند.

حداقل:

- Color Tokens
- Typography Tokens
- Spacing Tokens
- Radius Tokens
- Shadow Tokens
- Container
- Grid
- Button
- Input
- Select
- Checkbox
- Radio
- Switch
- Badge
- Card
- Alert
- Toast
- Modal
- Drawer
- Tooltip
- Tabs
- Accordion
- Pagination
- Breadcrumb
- Skeleton
- Spinner

نباید یک Component مشابه را چند بار با CSS متفاوت ساخت.

---

# بخش 08 — Component Reuse

اگر دو بخش رفتار یکسان دارند:

**Component مشترک**

نه:

**دو Implementation جدا**

قبل از ساخت Component جدید باید بررسی شود آیا نمونه موجود دارد یا خیر.

هدف:

> یک رفتار → یک الگوی قابل استفاده مجدد

---

# بخش 09 — Design Before Implementation

هیچ صفحه مهمی نباید مستقیماً با کدنویسی شروع شود.

فرآیند:

```text
Audit
↓
User Goal
↓
UX Flow
↓
Information Architecture
↓
UI Structure
↓
Responsive Design
↓
States
↓
Accessibility
↓
Implementation
↓
Test
↓
Regression
↓
Lock
```

---

# بخش 10 — قانون ضد دوباره‌کاری

این قانون رسمی Nivad است:

> طراحی باید از ابتدا کامل و بر اساس اصول UI/UX انجام شود.

بنابراین:

**Polish Phase جداگانه نداریم.**

اگر فاز پس از:

- UI/UX Audit
- Responsive Audit
- Accessibility Audit
- Interaction Audit
- Visual Audit
- Functional Test

تأیید شد:

**PASS → LOCK**

دیگر صرفاً برای «زیباتر کردن» دوباره وارد فاز نمی‌شویم.

اگر بعداً مشکل واقعی پیدا شد، فقط همان مشکل به‌عنوان Change مشخص بررسی می‌شود.

---

# بخش 11 — Information Architecture

اطلاعات باید بر اساس ذهن کاربر سازمان‌دهی شوند، نه ساختار داخلی کد.

کاربر نباید مجبور باشد معماری Backend را بفهمد.

مثلاً:

Product:

- معرفی
- قیمت
- موجودی
- انتخاب
- مشخصات
- سازگاری
- بررسی‌ها
- پرسش‌ها
- محصولات مرتبط

---

# بخش 12 — Progressive Disclosure

اطلاعات پیچیده را یکجا روی کاربر نریزیم.

ابتدا:

**اطلاعات ضروری**

سپس:

**اطلاعات بیشتر**

سپس:

**جزئیات تخصصی**

این قانون مخصوصاً برای:

- مشخصات فنی
- Compatibility
- Comparison
- Setup
- Checkout

مهم است.

---

# بخش 13 — Cognitive Load

کاربر نباید برای انجام یک کار ساده مجبور به تصمیم‌گیری‌های زیاد شود.

کاهش:

- گزینه‌های غیرضروری
- متن‌های اضافی
- فرم‌های طولانی
- Navigation پیچیده
- Modalهای متوالی
- انتخاب‌های تکراری

---

# بخش 14 — Recognition Over Recall

کاربر نباید مجبور شود چیزی را از حافظه خود به یاد بیاورد.

به‌جای:

«مدل دستگاهت را وارد کن»

در صورت امکان:

- دستگاه‌های اخیر
- پیشنهادها
- Search Suggestions
- گزینه‌های آماده

نمایش داده شود.

این اصل برای:

**Recently Selected Device**

بسیار مهم است.

---

# بخش 15 — User Control

کاربر باید کنترل داشته باشد.

مثلاً:

- Back
- Cancel
- Undo
- Close
- Remove
- Change
- Edit
- Retry

نباید کاربر در Flowها گیر بیفتد.

---

# بخش 16 — Predictability

قبل از کلیک باید تا حد ممکن مشخص باشد چه اتفاقی می‌افتد.

مثلاً:

دکمه:

> حذف محصول

باید واقعاً حذف کند.

نه اینکه Modal غیرمرتبط باز کند.

---

# بخش 17 — Feedback

هر Action مهم باید Feedback داشته باشد.

مثلاً:

Add to Cart:

```text
Click
↓
Loading
↓
Success
↓
Cart Updated
```

نه اینکه کاربر کلیک کند و هیچ اتفاق قابل مشاهده‌ای رخ ندهد.

---

# بخش 18 — Immediate Feedback

برای Actionهایی که سریع انجام می‌شوند:

Feedback باید سریع باشد.

برای عملیات طولانی:

Loading State لازم است.

---

# بخش 19 — Loading States

هر عملیات احتمالی باید State مشخص داشته باشد:

- Initial Loading
- Skeleton
- Spinner
- Button Loading
- Section Loading
- Page Loading
- Search Loading
- Filter Loading
- Cart Loading
- Checkout Loading
- Payment Loading
- Admin Loading

نباید صفحه هنگام Loading ناگهان خالی شود.

---

# بخش 20 — Skeleton

Skeleton برای محتوایی مناسب است که ساختار آن از قبل مشخص است.

مثلاً:

- Product Card
- Product Detail
- Dashboard
- Table
- Search Results

Skeleton نباید صرفاً یک Spinner بزرگ باشد.

---

# بخش 21 — Empty State

Empty State باید بگوید:

1. چه چیزی خالی است؟
2. چرا؟
3. کاربر چه کاری می‌تواند انجام دهد؟

ساختار:

```text
Illustration / Icon
↓
Title
↓
Description
↓
CTA
```

مثلاً:

> سبد خرید شما خالی است

> هنوز محصولی به سبد خرید اضافه نکرده‌اید.

> مشاهده محصولات

---

# بخش 22 — Error State

Error نباید فقط:

> Error!

باشد.

باید شامل:

- توضیح قابل فهم
- علت احتمالی در صورت امکان
- اقدام بعدی
- Retry
- Back
- Home

باشد.

---

# بخش 23 — Partial Failure

اگر یک Widget خراب شد، کل صفحه نباید خراب شود.

مثلاً:

Home:

```text
Hero       PASS
Products   FAIL
Categories PASS
Discovery  PASS
```

باید فقط بخش Product Error شود.

---

# بخش 24 — Form Design

فرم باید:

- Label مشخص
- Placeholder مناسب
- Required مشخص
- Validation
- Error
- Success
- Disabled
- Loading
- Focus
- Filled
- Readonly

داشته باشد.

---

# بخش 25 — Inline Validation

در صورت امکان خطا نزدیک همان Field نمایش داده شود.

نه اینکه فقط بالای صفحه نوشته شود:

> اطلاعات اشتباه است.

---

# بخش 26 — Error Message Quality

پیام خطا باید:

- واضح
- کوتاه
- انسانی
- عملیاتی

باشد.

بد:

> Invalid input

خوب:

> شماره موبایل واردشده معتبر نیست.

بهتر:

> شماره موبایل را با فرمت 09xxxxxxxxx وارد کنید.

---

# بخش 27 — Password UX

برای Password:

- Show/Hide
- Password Strength در صورت نیاز
- Error
- Requirements
- Caps Lock در صورت نیاز

---

# بخش 28 — Search UX

Search باید:

- سریع
- قابل مشاهده
- قابل فهم
- دارای Suggestions
- دارای Loading
- دارای Empty State
- دارای Error State

باشد.

Search نباید کاربر را به صفحه‌ای با نتیجه نامفهوم بفرستد.

---

# بخش 29 — E-Commerce Product Card

Product Card باید حداقل اطلاعات لازم برای تصمیم اولیه را بدهد:

- تصویر
- نام
- قیمت
- وضعیت موجودی در صورت اهمیت
- تخفیف در صورت وجود
- Wishlist
- CTA
- اطلاعات مهم انتخاب

نباید Card با اطلاعات زیاد خفه شود.

---

# بخش 30 — Product Detail

Product Detail باید به سؤال‌های اصلی کاربر جواب دهد:

- این چیست؟
- چقدر است؟
- موجود است؟
- برای من مناسب است؟
- با دستگاه من سازگار است؟
- چرا این را پیشنهاد می‌دهید؟
- چه چیزی همراهش لازم دارم؟
- آیا گزینه بهتری وجود دارد؟
- چگونه خرید کنم؟

---

# بخش 31 — Compatibility UX

Compatibility باید:

- واضح
- سریع
- قابل اعتماد
- قابل توضیح

باشد.

حالت‌ها:

- Compatible
- Incompatible
- Conditional
- Unknown
- Checking
- Error

---

# بخش 32 — Compatibility Confidence

اگر سیستم میزان اطمینان ارائه می‌کند:

مثلاً:

> اطمینان از سازگاری: بسیار بالا

باید دلیل قابل فهم نیز در صورت امکان وجود داشته باشد.

نباید صرفاً یک عدد بدون معنی نمایش داده شود.

---

# بخش 33 — Why This Product

هر Recommendation مهم باید بتواند پاسخ دهد:

> چرا این محصول؟

مثلاً:

- سازگاری
- قیمت
- کاربرد
- مشخصات
- انتخاب دستگاه
- بودجه

---

# بخش 34 — Smart Alternative

وقتی محصول مناسب نیست:

کاربر نباید به بن‌بست برسد.

Flow:

```text
Problem
↓
Explanation
↓
Alternative
↓
Reason
↓
Action
```

---

# بخش 35 — Smart Setup

Setup Builder باید همیشه وضعیت مشخص داشته باشد:

- Empty
- Selecting
- Checking
- Compatible
- Incompatible
- Complete
- Incomplete
- Error
- Saved

---

# بخش 36 — Budget UX

Budget Optimizer نباید کاربر را مجبور به محاسبه دستی کند.

باید:

- Budget
- Current Total
- Remaining
- Savings
- Recommended Combination

واضح باشد.

---

# بخش 37 — Comparison UX

Comparison باید به سؤال:

> «کدام را بخرم؟»

کمک کند.

فقط جدول Technical نباشد.

---

# بخش 38 — Checkout UX

Checkout باید تا حد امکان بدون حواس‌پرتی باشد.

مراحل:

```text
Address
↓
Shipping
↓
Order Summary
↓
Payment
```

اطلاعات غیرضروری نباید Flow را قطع کند.

---

# بخش 39 — Cart UX

Cart باید واضح نشان دهد:

- محصول
- تعداد
- قیمت
- تخفیف
- مجموع
- تغییر قیمت
- موجودی
- حذف
- تغییر تعداد
- ادامه خرید
- Checkout

---

# بخش 40 — Payment UX

Payment باید وضعیت‌های مشخص داشته باشد:

- Processing
- Success
- Failed
- Cancelled
- Timeout
- Retry

صفحه موفقیت باید واقعاً حس موفقیت بدهد.

صفحه خطا باید راه خروج داشته باشد.

---

# بخش 41 — Toast

Toast برای Feedback کوتاه استفاده شود.

نه برای:

- Errorهای مهم
- اطلاعات حیاتی
- پیام‌هایی که نیاز به Action دارند

Toast باید:

- قابل فهم
- قابل تشخیص
- قابل دسترسی
- زمان مناسب

داشته باشد.

---

# بخش 42 — Modal

Modal فقط وقتی استفاده شود که Context فعلی حفظ شود.

Modal برای:

- Confirmation
- انتخاب
- هشدار
- عملیات مهم

مناسب است.

Modal نباید برای هر چیز کوچکی استفاده شود.

---

# بخش 43 — Drawer

Drawer برای:

- Cart
- Mobile Navigation
- Filter
- Contextual Panels

مناسب است.

Drawer باید:

- Close
- Escape
- Focus Management
- Scroll Management

صحیح داشته باشد.

---

# بخش 44 — Navigation

Navigation باید:

- Predictable
- Consistent
- Discoverable

باشد.

کاربر باید بداند:

- کجاست
- از کجا آمده
- به کجا می‌تواند برود

---

# بخش 45 — Breadcrumb

Breadcrumb برای صفحات عمیق:

```text
خانه
/
دسته‌بندی
/
محصول
```

استفاده شود.

Breadcrumb نباید جایگزین Navigation اصلی شود.

---

# بخش 46 — Mobile First

طراحی باید از ابتدا برای:

**Mobile ≤600**

در نظر گرفته شود.

سپس:

**Tablet 601–1009**

و:

**Desktop ≥1010**

تکمیل شود.

Responsive نباید در آخر به‌عنوان Patch اضافه شود.

---

# بخش 47 — Responsive Behavior

Responsive فقط تغییر Width نیست.

باید بررسی شود:

- Layout
- Navigation
- Typography
- Buttons
- Forms
- Tables
- Cards
- Modals
- Drawers
- Images
- Spacing
- Information hierarchy

در تمام اندازه‌ها.

Apple نیز بر Layout سازگار با اندازه نمایشگر، Orientation و شرایط مختلف تأکید می‌کند.

---

# بخش 48 — Touch UX

روی موبایل:

- عناصر قابل لمس باید اندازه مناسب داشته باشند.
- فاصله بین Controls باید کافی باشد.
- Actionهای نزدیک به هم نباید باعث Tap اشتباه شوند.

WCAG 2.2 معیار Target Size (Minimum) را نیز به‌عنوان معیار قابل بررسی دارد.

---

# بخش 49 — Keyboard UX

تمام قابلیت‌های مهم باید با Keyboard قابل استفاده باشند.

شامل:

- Navigation
- Buttons
- Forms
- Dropdown
- Modal
- Drawer
- Tabs
- Accordion
- Search
- Pagination

WCAG 2.2 صراحتاً دسترسی عملکردها با Keyboard را الزام می‌کند.

---

# بخش 50 — Focus

Focus باید:

- قابل مشاهده
- واضح
- Consistent
- غیرمزاحم

باشد.

Focus نباید پشت Sticky Header/Footer پنهان شود. WCAG 2.2 برای Focus Not Obscured معیار مشخص دارد.

---

# بخش 51 — Accessibility

استاندارد پایه Nivad:

**WCAG 2.2 AA**

چهار اصل:

- Perceivable
- Operable
- Understandable
- Robust

این چهار اصل پایه ساختار WCAG هستند.

---

# بخش 52 — Semantic HTML

تا جای ممکن:

```text
button → Button
a → Link
nav → Navigation
main → Main
header → Header
footer → Footer
form → Form
label → Label
```

نباید یک `div` را بدون دلیل جایگزین عنصر معنایی مناسب کرد.

---

# بخش 53 — ARIA

ARIA باید برای تکمیل Semantics استفاده شود، نه جایگزین HTML صحیح.

مثلاً Stateهای:

- aria-expanded
- aria-selected
- aria-pressed
- aria-invalid
- aria-describedby
- aria-live

در موارد مناسب.

---

# بخش 54 — Screen Reader

اطلاعات مهم نباید فقط بصری باشند.

مثلاً:

رنگ قرمز به‌تنهایی نباید معنی Error بدهد.

باید:

- Text
- Semantic State
- ARIA

نیز در صورت نیاز وجود داشته باشد.

---

# بخش 55 — Color

رنگ نباید تنها روش انتقال معنا باشد.

مثلاً:

قرمز = Error

باید همراه با:

- Icon
- Text
- Semantic State

باشد.

WCAG روی استفاده از رنگ، Contrast و Non-text Contrast تأکید دارد.

---

# بخش 56 — Contrast

متن، Icon و عناصر ضروری باید Contrast کافی داشته باشند.

استاندارد پایه:

**WCAG 2.2 AA**

Contrast باید در:

- Light
- Dark در صورت وجود
- Disabled
- Hover
- Focus
- Error
- Success

بررسی شود.

---

# بخش 57 — Typography

Typography باید:

- خوانا
- قابل اسکن
- سلسله‌مراتب‌دار
- Responsive

باشد.

برای Nivad فارسی:

- فونت فارسی اصلی
- وزن مناسب
- Line Height مناسب
- فاصله مناسب

باید ثابت و Systematic باشد.

---

# بخش 58 — Long Text

متن طولانی باید:

- Section
- Heading
- Paragraph
- List
- Table

داشته باشد.

یک دیوار متن نباید جایگزین Information Architecture شود.

---

# بخش 59 — Iconography

Icon باید:

- معنی مشخص
- Style یکسان
- اندازه منطقی
- Alignment صحیح

داشته باشد.

Icon تنها بدون Label فقط زمانی مجاز است که معنای آن برای کاربر قابل تشخیص باشد.

---

# بخش 60 — Images

هر Image مهم باید:

- Alt مناسب
- Loading State
- Placeholder
- Error State

داشته باشد.

تصویر تزئینی نباید Alt غیرضروری داشته باشد.

---

# بخش 61 — Product Media

Product Gallery باید حالات:

- Loading
- Loaded
- Broken
- Placeholder
- Thumbnail Selected
- Zoom در صورت نیاز
- Mobile Gallery

را پوشش دهد.

---

# بخش 62 — Motion

Animation باید:

- هدف داشته باشد
- کوتاه باشد
- Context را حفظ کند
- مانع کار نشود

Animation نباید صرفاً برای «جذاب شدن» اضافه شود.

---

# بخش 63 — Reduced Motion

کاربرانی که Reduced Motion فعال کرده‌اند باید تجربه مناسب داشته باشند.

حرکت‌های غیرضروری باید کاهش یابند.

---

# بخش 64 — Micro Interaction

Micro-interaction باید Feedback ایجاد کند:

- Add to Cart
- Wishlist
- Copy
- Save
- Submit
- Expand
- Collapse

نه اینکه فقط تزئینی باشد.

---

# بخش 65 — Performance UX

Performance بخشی از UX است.

باید مراقب:

- Layout Shift
- Image Size
- Excessive Animation
- Blocking UI
- Huge Assets
- Unnecessary Requests

باشیم.

---

# بخش 66 — Perceived Performance

حتی وقتی عملیات زمان می‌برد، کاربر باید بفهمد سیستم در حال کار است.

استفاده مناسب از:

- Skeleton
- Progress
- Spinner
- Optimistic Feedback در موارد امن
- Placeholder

مجاز است.

---

# بخش 67 — Offline / Network

باید حالات:

- Offline
- Online Recovery
- Slow Network
- Timeout
- Request Failed
- Server Unavailable
- Retry

طراحی شوند.

---

# بخش 68 — Session Expiration

اگر Session منقضی شد:

نباید کاربر با صفحه خراب مواجه شود.

باید:

- توضیح
- Login
- حفظ Context در صورت امکان

ارائه شود.

---

# بخش 69 — 404 / Error Pages

Error Page باید بخشی از Design System باشد.

هر Error Page باید:

- Status
- توضیح
- CTA
- Back
- Home
- Retry در صورت نیاز

داشته باشد.

---

# بخش 70 — 404 UX

404 نباید Dead End باشد.

کاربر باید بتواند:

- Home
- Search
- Back

را انتخاب کند.

---

# بخش 71 — Trust UX

برای فروشگاه:

- قیمت واضح
- موجودی واضح
- شرایط ارسال
- شرایط بازگشت
- اطلاعات محصول
- Reviews
- Questions
- Compatibility
- توضیح Recommendation

باید قابل دسترسی باشند.

---

# بخش 72 — Persuasive UX

فروش نباید با Dark Pattern انجام شود.

ممنوع:

- فریب
- Countdown جعلی
- Scarcity جعلی
- گزینه‌های گمراه‌کننده
- Subscription ناخواسته
- Hidden Cost

---

# بخش 73 — Transparency

کاربر باید قبل از تصمیم مهم بداند:

- قیمت
- تخفیف
- هزینه ارسال در صورت مشخص بودن
- شرایط
- محدودیت
- وضعیت محصول

---

# بخش 74 — Checkout Trust

در Checkout:

- هزینه‌ها
- مجموع
- روش ارسال
- روش پرداخت
- اطلاعات سفارش

باید واضح باشند.

---

# بخش 75 — Personalization

Personalization باید مفید باشد، نه مزاحم.

مثلاً:

- دستگاه اخیر
- محصولات مرتبط
- خریدهای قبلی
- پیشنهادهای مرتبط

اما کاربر باید Context را بفهمد.

---

# بخش 76 — Recommendation Transparency

Recommendation نباید Black Box باشد.

در صورت امکان:

> چرا این محصول؟

باید قابل فهم باشد.

این قانون برای Nivad یک اصل اختصاصی است.

---

# بخش 77 — AI UX

اگر در آینده AI اضافه شود:

باید:

- مشخص شود AI چه کاری انجام می‌دهد.
- Confidence در صورت معنی‌دار بودن نمایش داده شود.
- امکان اصلاح ورودی وجود داشته باشد.
- کاربر بتواند پیشنهاد AI را رد کند.
- AI نباید بدون اطلاع کاربر تصمیم مهم بگیرد.

---

# بخش 78 — Smart Feature Failure

اگر Smart Feature نتوانست تصمیم بگیرد:

نباید وانمود کند جواب قطعی دارد.

مثلاً:

> اطلاعات کافی برای بررسی قطعی سازگاری وجود ندارد.

بهتر از:

> سازگار است.

در حالی که سیستم مطمئن نیست.

---

# بخش 79 — User Recovery

هر Error مهم باید مسیر Recovery داشته باشد.

```text
Error
↓
Explain
↓
Recover
↓
Retry / Back / Alternative
```

---

# بخش 80 — Undo

برای Actionهای قابل برگشت:

Undo در صورت مناسب بودن بهتر از Confirmationهای اضافی است.

مثلاً:

> محصول حذف شد — بازگردانی

---

# بخش 81 — Confirmation

Confirmation فقط برای عملیات مهم یا مخرب.

مثلاً:

- حذف دائمی
- لغو سفارش
- حذف آدرس مهم

نه برای هر Click ساده.

---

# بخش 82 — Data Preservation

در Errorهای Form نباید اطلاعات واردشده بدون دلیل از بین برود.

مثلاً اگر Checkout خطا داد:

اطلاعات کاربر نباید بی‌دلیل پاک شود.

---

# بخش 83 — Unsaved Changes

اگر کاربر فرم مهمی را تغییر داده و قصد خروج دارد:

در صورت نیاز:

> تغییرات ذخیره نشده‌اند.

---

# بخش 84 — Pagination / Infinite Scroll

هر دو باید بر اساس نیاز واقعی انتخاب شوند.

Pagination برای:

- Catalog
- Admin Tables

معمولاً قابل کنترل‌تر است.

Infinite Scroll فقط زمانی که UX واقعاً از آن سود ببرد.

---

# بخش 85 — Tables

Table باید:

- Header واضح
- Alignment
- Responsive Strategy
- Loading
- Empty
- Error
- Pagination
- Sorting
- Filtering

داشته باشد.

---

# بخش 86 — Mobile Tables

نباید Table دسکتاپ را کورکورانه روی موبایل فشرده کرد.

ممکن است لازم باشد:

- Horizontal Scroll
- Card Transformation
- Priority Columns

استفاده شود.

---

# بخش 87 — Admin UX

Admin باید:

- سریع
- قابل اسکن
- متراکم اما خوانا
- Consistent
- قابل جستجو
- قابل فیلتر

باشد.

Admin نباید صرفاً نسخه کوچک Storefront باشد.

---

# بخش 88 — Dashboard UX

Dashboard باید اطلاعاتی بدهد که Action ایجاد کنند.

KPI بدون Context ارزش محدودی دارد.

---

# بخش 89 — Responsive Admin

Admin نیز باید برای:

- Desktop
- Tablet
- Mobile

رفتار مشخص داشته باشد، حتی اگر استفاده اصلی Desktop باشد.

---

# بخش 90 — RTL

Nivad فارسی است.

RTL باید از ابتدا در:

- Layout
- Grid
- Spacing
- Icon Placement
- Navigation
- Forms
- Tables
- Breadcrumb
- Modal
- Drawer

در نظر گرفته شود.

RTL نباید در آخر با `direction: rtl` حل شود.

---

# بخش 91 — Persian Typography

در فارسی باید مراقب:

- نیم‌فاصله
- اعداد
- علائم
- Line Height
- Alignment
- Mixed English/Persian
- Technical Terms

باشیم.

---

# بخش 92 — Persian / English Mixed Content

برای مدل‌ها و اصطلاحات فنی:

- Direction
- Alignment
- Number formatting
- Wrapping

باید کنترل شود.

مثلاً:

`USB-C`

نباید Layout را خراب کند.

---

# بخش 93 — Localization Ready

حتی اگر فعلاً فارسی است، Componentها نباید به متن فارسی Hardcode شده وابسته باشند به شکلی که توسعه آینده را غیرممکن کند.

---

# بخش 94 — Content Design

Microcopy باید:

- کوتاه
- واضح
- انسانی
- Action-oriented

باشد.

---

# بخش 95 — CTA

هر CTA باید فعل و نتیجه مشخص داشته باشد.

بهتر:

> مشاهده محصول

بدتر:

> ادامه

در جایی که معلوم نیست ادامه چه چیزی است.

---

# بخش 96 — Button Hierarchy

حداقل:

- Primary
- Secondary
- Tertiary
- Destructive
- Ghost

و Stateهای:

- Default
- Hover
- Focus
- Active
- Disabled
- Loading
- Success

---

# بخش 97 — Disabled State

Disabled نباید تنها با Opacity 0.5 تعریف شود.

باید:

- دلیل قابل فهم
- وضعیت واقعی
- Accessibility

در نظر گرفته شود.

---

# بخش 98 — Input UX

Input باید:

- Label
- Focus
- Error
- Success
- Disabled
- Placeholder
- Helper
- Character Limit در صورت نیاز

داشته باشد.

---

# بخش 99 — Select / Dropdown

باید:

- Keyboard
- Focus
- Selected
- Disabled
- Loading
- Empty

را پشتیبانی کند.

---

# بخش 100 — Final UI/UX Gate

هیچ فاز Frontend بدون این Gate قفل نمی‌شود:

### UX

- User Goal
- User Flow
- Information Architecture
- Cognitive Load
- Navigation
- Feedback

### UI

- Hierarchy
- Typography
- Color
- Spacing
- Components
- Consistency

### State

- Loading
- Empty
- Error
- Success
- Disabled
- Partial Failure

### Responsive

- Mobile
- Tablet
- Desktop

### Accessibility

- WCAG 2.2 AA
- Keyboard
- Focus
- Screen Reader
- Contrast
- Touch Target
- Semantic HTML

### Technical

- CSS Architecture
- JS Integration
- Component Reuse
- Performance
- Media
- Route Integrity

### Final

```text
UI/UX AUDIT
PASS

IMPLEMENTATION
PASS

RESPONSIVE
PASS

ACCESSIBILITY
PASS

STATE COVERAGE
PASS

REGRESSION
PASS

NO POLISH REQUIRED
YES

LOCK
YES
```

---

# اصل نهایی Nivad

Nivad نباید این چرخه را داشته باشد:

```text
Design ناقص
↓
Code
↓
مشکل
↓
Polish
↓
Redesign
↓
Polish دوباره
```

چرخه رسمی:

```text
AUDIT
↓
UX DESIGN
↓
UI DESIGN
↓
RESPONSIVE DESIGN
↓
STATE DESIGN
↓
ACCESSIBILITY DESIGN
↓
IMPLEMENTATION
↓
TEST
↓
REGRESSION
↓
PASS
↓
LOCK
```

**اگر یک فاز از این چرخه عبور کرد، دیگر Polish جداگانه ندارد.**

هدف:

> **Design Right First. Build Right Once. Lock It.**