-- NIVAD Phase 5 CMS — additive migration. Does not alter or drop existing tables.
-- Requires the Phase 3/4 admin foundation migration to have been applied first.
-- Run only after a verified database backup. This migration is NOT auto-executed.

CREATE TABLE IF NOT EXISTS admin_cms_pages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    page_key VARCHAR(80) NOT NULL,
    title VARCHAR(160) NOT NULL,
    route_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_by_admin_id BIGINT UNSIGNED NULL,
    updated_by_admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_cms_page_key (page_key),
    KEY idx_admin_cms_pages_order (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_cms_sections (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    page_id BIGINT UNSIGNED NOT NULL,
    section_key VARCHAR(80) NOT NULL,
    section_type VARCHAR(50) NOT NULL,
    title VARCHAR(160) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    content_json LONGTEXT NOT NULL,
    style_json LONGTEXT NOT NULL,
    published_content_json LONGTEXT NULL,
    published_style_json LONGTEXT NULL,
    published_is_visible TINYINT(1) NULL DEFAULT NULL,
    published_sort_order INT NULL DEFAULT NULL,
    scheduled_at DATETIME NULL,
    published_at DATETIME NULL,
    created_by_admin_id BIGINT UNSIGNED NULL,
    updated_by_admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_cms_section_key (page_id, section_key),
    KEY idx_admin_cms_sections_page_order (page_id, deleted_at, sort_order),
    KEY idx_admin_cms_sections_schedule (status, scheduled_at),
    CONSTRAINT fk_admin_cms_sections_page FOREIGN KEY (page_id) REFERENCES admin_cms_pages(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_cms_pages (page_key, title, route_path, sort_order) VALUES
('home', 'صفحه اصلی', '/', 10),
('about', 'درباره ما', '/pages/content/about.php', 20),
('contact', 'تماس با ما', '/pages/content/contact.php', 30),
('faq', 'سؤالات متداول', '/pages/content/faq.php', 40),
('buying-guide', 'راهنمای خرید', '/pages/content/buying-guide.php', 50),
('shipping', 'راهنمای ارسال', '/pages/content/shipping.php', 60),
('returns', 'شرایط مرجوعی', '/pages/content/returns.php', 70),
('privacy', 'حریم خصوصی', '/pages/content/privacy.php', 80),
('terms', 'قوانین و مقررات', '/pages/content/terms.php', 90)
ON DUPLICATE KEY UPDATE page_key = VALUES(page_key);

-- Initial landing-page section contracts. content_json uses safe structured fields,
-- not executable HTML. The renderer binds these fields to the existing component classes.
INSERT INTO admin_cms_sections (page_id, section_key, section_type, title, sort_order, status, content_json, style_json, created_by_admin_id, updated_by_admin_id)
SELECT p.id, seed.section_key, seed.section_type, seed.title, seed.sort_order, 'draft', seed.content_json, seed.style_json, 1, 1
FROM admin_cms_pages p
JOIN (
    SELECT 'hero' section_key, 'hero' section_type, 'Hero اصلی' title, 10 sort_order,
      '{"eyebrow":"NIVAD DIGITAL STORE","heading":"تجربه‌ای هوشمندتر از خرید دیجیتال","body":"محصول مناسب خودت را با اطلاعات شفاف‌تر پیدا کن.","button_label":"مشاهده محصولات","button_url":"/products","image_id":null}' content_json,
      '{"background_type":"gradient","gradient_from":"#101827","gradient_to":"#253b5b","gradient_angle":135,"color":"#ffffff","padding_top":96,"padding_bottom":96,"radius":24}' style_json
    UNION ALL SELECT 'categories','categories','دسته‌بندی‌ها',20,'{"eyebrow":"EXPLORE","heading":"دسته‌بندی محصولات","body":"محصولات را بر اساس نیازت پیدا کن."}','{"background_type":"transparent","padding_top":64,"padding_bottom":64}'
    UNION ALL SELECT 'discovery','discovery','دیسکاوری',30,'{"eyebrow":"DISCOVER","heading":"محصولات منتخب","body":"محتوای دیسکاوری فروشگاه."}','{"background_type":"solid","background":"#f5f7fa","padding_top":64,"padding_bottom":64}'
    UNION ALL SELECT 'products','products','محصولات',40,'{"eyebrow":"STORE CATALOG","heading":"محصولات فروشگاه","body":"برای مشاهده محصولات به‌روز، فهرست فروشگاه را ببین.","button_label":"مشاهده همه محصولات","button_url":"/products"}','{"background_type":"transparent","padding_top":64,"padding_bottom":64}'
    UNION ALL SELECT 'setup-preview','setup-preview','پیش‌نمایش ستاپ',50,'{"eyebrow":"BUILD YOUR SETUP","heading":"ستاپ مناسب خودت را بساز","body":"ترکیب تجهیزات متناسب با نیازت را پیدا کن.","button_label":"مشاهده ستاپ‌ها","button_url":"/pages/store/setup.php"}','{"background_type":"gradient","gradient_from":"#111827","gradient_to":"#334155","gradient_angle":120,"color":"#ffffff","padding_top":72,"padding_bottom":72}'
    UNION ALL SELECT 'compatibility-preview','compatibility-preview','پیش‌نمایش سازگاری',60,'{"eyebrow":"NIVAD COMPATIBILITY FINDER","heading":"قبل از خرید، از سازگاری مطمئن شو.","body":"مدل دستگاهت را انتخاب کن و محصولات مرتبط را پیدا کن.","button_label":"بررسی سازگاری","button_url":"/pages/store/compatibility.php"}','{"background_type":"solid","background":"#f3f6fa","padding_top":72,"padding_bottom":72}'
    UNION ALL SELECT 'new-products','new-products','فهرست محصولات',70,'{"eyebrow":"STORE CATALOG","heading":"محصولات فروشگاه","body":"اطلاعات محصولات در فهرست اصلی فروشگاه نمایش داده می‌شود.","button_label":"مشاهده همه محصولات","button_url":"/products"}','{"background_type":"transparent","padding_top":64,"padding_bottom":64}'
    UNION ALL SELECT 'value-proposition','value-proposition','ارزش پیشنهادی',80,'{"eyebrow":"THE NIVAD DIFFERENCE","heading":"خرید دیجیتال، با انتخابی مطمئن‌تر","body":"از شناخت مشخصات تا بررسی سازگاری؛ ابزارهای لازم برای تصمیم بهتر را یک‌جا داشته باش.","items":[{"title":"مشخصات شفاف","body":"ویژگی‌های مهم هر محصول را واضح ببین."},{"title":"بررسی سازگاری","body":"سازگاری محصول با دستگاهت را بررسی کن."},{"title":"انتخاب بر اساس نیاز","body":"متناسب با کاربرد واقعی تصمیم بگیر."},{"title":"راهنمای خرید کاربردی","body":"با راهنماهای روشن گزینه‌ها را بشناس."}]}','{"background_type":"transparent","padding_top":64,"padding_bottom":64}'
    UNION ALL SELECT 'buying-guide','buying-guide','راهنمای خرید',90,'{"eyebrow":"BUYING GUIDE","heading":"راهنمای خرید","body":"قبل از انتخاب، تفاوت‌ها را بشناس و متناسب با نیازت تصمیم بگیر.","items":[{"title":"چه شارژری برای گوشی من مناسب است؟","body":"توان خروجی، استاندارد شارژ و سازگاری را بررسی کن.","image_id":null,"url":"/pages/content/buying-guide.php#charger"},{"title":"USB-C یا Thunderbolt؛ چه تفاوتی دارند؟","body":"سرعت انتقال داده و کاربرد هر استاندارد را مقایسه کن.","image_id":null,"url":"/pages/content/buying-guide.php#usb-c-thunderbolt"},{"title":"برای انتخاب کابل HDMI چه چیزهایی مهم است؟","body":"رزولوشن و نرخ نوسازی مناسب نمایشگرت را بشناس.","image_id":null,"url":"/pages/content/buying-guide.php#hdmi"}]}','{"background_type":"transparent","padding_top":64,"padding_bottom":64}'
    UNION ALL SELECT 'cta','cta','دعوت به اقدام',100,'{"eyebrow":"FIND YOUR MATCH","heading":"محصول مناسب دستگاهت را پیدا کن.","body":"برند و مدل دستگاهت را انتخاب کن تا محصولات سازگار را ببینی.","button_label":"پیدا کردن محصول","button_url":"/finder"}','{"background_type":"gradient","gradient_from":"#111827","gradient_to":"#36506d","gradient_angle":120,"color":"#ffffff","padding_top":72,"padding_bottom":72,"radius":24}'
    UNION ALL SELECT 'smart-bundle','smart-bundle','ست پیشنهادی هوشمند',110,'{"eyebrow":"Smart Bundle & Discount","heading":"ست پیشنهادی هوشمند","body":"بر اساس روابط واقعی سازگاری و Setup، ترکیب مکمل را پیشنهاد می‌کنیم."}','{"background_type":"transparent","padding_top":64,"padding_bottom":64}'
) AS seed ON p.page_key = 'home'
WHERE NOT EXISTS (SELECT 1 FROM admin_cms_sections s WHERE s.page_id = p.id AND s.section_key = seed.section_key);

-- Content-page starter sections. They remain drafts until an administrator reviews and publishes them.
INSERT INTO admin_cms_sections (page_id, section_key, section_type, title, sort_order, status, content_json, style_json, created_by_admin_id, updated_by_admin_id)
SELECT p.id, 'intro', 'intro', p.title, 10, 'draft',
       JSON_OBJECT('heading', p.title, 'body', CONCAT('محتوای صفحه ', p.title, ' از پنل مدیریت قابل ویرایش است.')),
       JSON_OBJECT('background_type','transparent','padding_top',40,'padding_bottom',24), NULL, NULL
FROM admin_cms_pages p
WHERE p.page_key <> 'home'
  AND NOT EXISTS (SELECT 1 FROM admin_cms_sections s WHERE s.page_id = p.id AND s.section_key = 'intro');


-- Replace generic starter copy with the verified copy already present in the
-- corresponding public PHP pages. All imported sections remain drafts.
UPDATE admin_cms_sections s
INNER JOIN admin_cms_pages p ON p.id = s.page_id
SET s.content_json = CASE p.page_key
 WHEN 'about' THEN JSON_OBJECT('eyebrow','درباره نیواد','heading','درباره ما','body','نیواد برای ساده‌تر کردن بررسی و انتخاب گوشی و تجهیزات دیجیتال طراحی شده است. هدف این صفحات، ارائه اطلاعات روشن و کمک به مقایسه گزینه‌ها پیش از خرید است.')
 WHEN 'contact' THEN JSON_OBJECT('eyebrow','پشتیبانی مشتریان','heading','تماس با ما','body','اگر درباره محصول یا سفارش خود پرسشی دارید، ابتدا راهنمای مرتبط را بررسی کنید. اطلاعات تماس مستقیم تنها زمانی نمایش داده می‌شود که مسیر رسمی و تأییدشده‌ای برای آن در پروژه تعریف شده باشد.')
 WHEN 'faq' THEN JSON_OBJECT('eyebrow','راهنمای مشتری','heading','سؤالات متداول','body','پاسخ‌های کوتاه به پرسش‌های رایج درباره استفاده از فروشگاه نیواد.')
 WHEN 'buying-guide' THEN JSON_OBJECT('eyebrow','راهنمای انتخاب','heading','راهنمای خرید','body','پیش از خرید، مشخصات دستگاه، کاربرد موردنظر و استانداردهای پشتیبانی‌شده را بررسی کنید. شکل ظاهری یک درگاه یا کابل به‌تنهایی تضمین‌کننده سازگاری یا سرعت مشخص نیست.')
 WHEN 'shipping' THEN JSON_OBJECT('eyebrow','تحویل سفارش','heading','ارسال سفارش','body','گزینه‌ها و جزئیات ارسال ممکن است بر اساس سفارش و نشانی تحویل متفاوت باشند. اطلاعات نهایی و قابل اتکا را در مراحل خرید و جزئیات سفارش خود بررسی کنید.')
 WHEN 'returns' THEN JSON_OBJECT('eyebrow','خدمات پس از خرید','heading','مرجوعی کالا','body','امکان ثبت درخواست مرجوعی و نتیجه بررسی آن به سفارش، اقلام خریداری‌شده و شرایط معتبر فروشگاه بستگی دارد. پیش از اقدام، اطلاعات سفارش و شرایط اعلام‌شده برای آن را بررسی کنید.')
 WHEN 'privacy' THEN JSON_OBJECT('eyebrow','حریم خصوصی','heading','حریم خصوصی','body','این صفحه توضیحی عمومی درباره داده‌هایی است که ممکن است هنگام استفاده از امکانات فروشگاه وارد کنید. متن نهایی این سیاست باید با پیکربندی واقعی سامانه و الزامات حقوقی کسب‌وکار تطبیق داده و توسط مسئول مربوط تأیید شود.')
 WHEN 'terms' THEN JSON_OBJECT('eyebrow','قوانین فروشگاه','heading','شرایط استفاده','body','با استفاده از امکانات فروشگاه، لازم است اطلاعات نمایش‌داده‌شده در هر مرحله را با دقت بررسی کنید. این متن چارچوب عمومی است و جایگزین شرایط حقوقی نهایی و تأییدشده کسب‌وکار نیست.')
 ELSE s.content_json END
WHERE s.section_key = 'intro';

INSERT INTO admin_cms_sections (page_id, section_key, section_type, title, sort_order, status, content_json, style_json, created_by_admin_id, updated_by_admin_id)
SELECT p.id, seed.section_key, seed.section_type, seed.title, seed.sort_order, 'draft',
       seed.content_json, JSON_OBJECT('background_type','transparent','padding_top',32,'padding_bottom',32), NULL, NULL
FROM admin_cms_pages p
JOIN (
    SELECT 'about' page_key, 'focus' section_key, 'content' section_type, 'چه چیزی در فروشگاه پیدا می‌کنید؟' title, 20 sort_order,
      JSON_OBJECT('heading','چه چیزی در فروشگاه پیدا می‌کنید؟','body','در فروشگاه می‌توانید محصولات دیجیتال و لوازم جانبی را بررسی کنید، اطلاعات موجود را مقایسه کنید و از ابزارهای راهنمای انتخاب برای شناخت بهتر سازگاری و کاربرد محصولات کمک بگیرید.') content_json
    UNION ALL SELECT 'about','principles','content','رویکرد ما',30,JSON_OBJECT('heading','رویکرد ما','body','اطلاعات محصول باید تا حد امکان واضح و قابل بررسی باشد. مشخصات و موجودی نمایش‌داده‌شده در صفحه محصول و مراحل خرید را پیش از ثبت سفارش بررسی کنید؛ در صورت ابهام، از مسیرهای پشتیبانی موجود در سایت کمک بگیرید.')
    UNION ALL SELECT 'about','links','links','از کجا شروع کنید؟',40,JSON_OBJECT('heading','از کجا شروع کنید؟','items',JSON_ARRAY(JSON_OBJECT('title','مشاهده محصولات','url','/products','link_label','مشاهده محصولات'),JSON_OBJECT('title','راهنمای خرید','url','/pages/content/buying-guide.php','link_label','راهنمای خرید'),JSON_OBJECT('title','سؤالات متداول','url','/pages/content/faq.php','link_label','سؤالات متداول')))
    UNION ALL SELECT 'contact','orders','content','پرسش درباره سفارش',20,JSON_OBJECT('heading','پرسش درباره سفارش','body','برای بررسی سفارش‌های ثبت‌شده، از بخش سفارش‌های حساب کاربری استفاده کنید. مشاهده این بخش به ورود به حساب نیاز دارد.','button_label','مشاهده سفارش‌ها','button_url','/pages/account/orders/index.php')
    UNION ALL SELECT 'contact','returns','content','درخواست مرجوعی',30,JSON_OBJECT('heading','درخواست مرجوعی','body','برای سفارش واجد شرایط، جزئیات و وضعیت درخواست را از بخش مرجوعی حساب کاربری پیگیری کنید. ثبت درخواست به معنی تأیید خودکار آن نیست.','button_label','بخش مرجوعی','button_url','/pages/account/returns/index.php')
    UNION ALL SELECT 'contact','help','content','راهنماهای خودیاری',40,JSON_OBJECT('heading','راهنماهای خودیاری','body','پاسخ پرسش‌های رایج را در سؤالات متداول و اطلاعات ارسال را در راهنمای ارسال سفارش بخوانید.','items',JSON_ARRAY(JSON_OBJECT('title','سؤالات متداول','url','/pages/content/faq.php','link_label','مشاهده'),JSON_OBJECT('title','راهنمای ارسال','url','/pages/content/shipping.php','link_label','مشاهده')))
    UNION ALL SELECT 'faq','faq','faq','پرسش‌ها و پاسخ‌ها',20,JSON_OBJECT('heading','پرسش‌ها و پاسخ‌های متداول','items',JSON_ARRAY(JSON_OBJECT('title','چطور محصول موردنظر خود را پیدا کنم؟','body','از جست‌وجوی فروشگاه، دسته‌بندی‌ها و فیلترهای موجود استفاده کنید. مشخصات و سازگاری محصول را پیش از خرید بررسی کنید.'),JSON_OBJECT('title','چطور وضعیت سفارش خود را پیگیری کنم؟','body','وارد حساب کاربری شوید و بخش سفارش‌ها را باز کنید. اطلاعات قابل مشاهده به وضعیت ثبت‌شده برای سفارش وابسته است.'),JSON_OBJECT('title','هزینه و زمان ارسال چطور مشخص می‌شود؟','body','هزینه و گزینه‌های ارسال را در مراحل خرید و اطلاعات سفارش بررسی کنید. این صفحه زمان یا هزینه ثابتی را تضمین نمی‌کند.'),JSON_OBJECT('title','چطور درخواست مرجوعی ثبت کنم؟','body','شرایط مربوط به سفارش را در راهنمای مرجوعی بخوانید و در صورت واجد شرایط بودن، از بخش مرجوعی حساب کاربری اقدام کنید.'),JSON_OBJECT('title','چطور برای انتخاب محصول راهنمایی بگیرم؟','body','راهنمای خرید را برای نکات مربوط به شارژر، کابل USB-C و Thunderbolt و کابل HDMI ببینید.'),JSON_OBJECT('title','برای مسائل مربوط به حساب کاربری چه کنم؟','body','از صفحه حساب کاربری و بخش مربوط به سفارش‌ها یا مرجوعی استفاده کنید.')))
    UNION ALL SELECT 'buying-guide','charger','guide','چطور شارژر مناسب انتخاب کنم؟',20,JSON_OBJECT('heading','چطور شارژر مناسب انتخاب کنم؟','body','توان موردنیاز دستگاه و استانداردهای شارژ پشتیبانی‌شده را در مشخصات سازنده بررسی کنید. شارژر با توان بالاتر لزوماً سرعت بیشتری ایجاد نمی‌کند؛ سرعت نهایی به سازگاری دستگاه، شارژر و کابل بستگی دارد.','items',JSON_ARRAY(JSON_OBJECT('title','مدل دقیق دستگاه را بررسی کنید.'),JSON_OBJECT('title','پروتکل‌های شارژ پشتیبانی‌شده را با مشخصات شارژر مقایسه کنید.'),JSON_OBJECT('title','توان و مشخصات کابل را نیز در نظر بگیرید.')))
    UNION ALL SELECT 'buying-guide','usb-c-thunderbolt','guide','USB-C و Thunderbolt چه تفاوتی دارند؟',30,JSON_OBJECT('heading','USB-C و Thunderbolt چه تفاوتی دارند؟','body','USB-C شکل کانکتور است؛ Thunderbolt یک فناوری ارتباطی با قابلیت‌ها و الزامات مشخص است. همه درگاه‌ها و کابل‌های USB-C از قابلیت‌ها و سرعت‌های Thunderbolt پشتیبانی نمی‌کنند. مشخصات هر دو دستگاه و گواهی یا مشخصات کابل را بررسی کنید.')
    UNION ALL SELECT 'buying-guide','hdmi','guide','برای انتخاب کابل HDMI چه نکاتی مهم است؟',40,JSON_OBJECT('heading','برای انتخاب کابل HDMI چه نکاتی مهم است؟','body','رزولوشن، نرخ نوسازی، قابلیت‌های تصویری موردنیاز و استانداردهای پشتیبانی‌شده در هر دو دستگاه را مشخص کنید. طول کابل و کیفیت ساخت نیز در انتخاب مؤثرند؛ نام نسخه یا شکل کانکتور به‌تنهایی عملکرد نهایی را تضمین نمی‌کند.')
    UNION ALL SELECT 'buying-guide','compatibility','guide','قبل از خرید سازگاری را بررسی کنید',50,JSON_OBJECT('heading','قبل از خرید سازگاری را بررسی کنید','body','مدل دقیق دستگاه و مشخصات ورودی و خروجی آن را بررسی کنید. در صورت تردید، از اطلاعات سازنده کمک بگیرید و تنها بر اساس شباهت ظاهری کانکتورها تصمیم نگیرید.','button_label','ابزار سازگاری','button_url','/pages/store/compatibility.php')
    UNION ALL SELECT 'shipping','options','content','گزینه‌های ارسال',20,JSON_OBJECT('heading','گزینه‌های ارسال','body','روش‌های قابل انتخاب، در صورت پشتیبانی، هنگام ثبت سفارش نمایش داده می‌شوند. اگر گزینه‌ای برای نشانی یا سفارش شما نمایش داده نمی‌شود، آن را در دسترس فرض نکنید.')
    UNION ALL SELECT 'shipping','cost','content','هزینه و زمان تحویل',30,JSON_OBJECT('heading','هزینه و زمان تحویل','body','هزینه و برآورد زمان تحویل را از خلاصه سفارش و اطلاعات ثبت‌شده همان سفارش بررسی کنید. این صفحه عدد ثابت یا زمان تحویل تضمین‌شده‌ای اعلام نمی‌کند.')
    UNION ALL SELECT 'shipping','tracking','content','پیگیری سفارش',40,JSON_OBJECT('heading','پیگیری سفارش','body','پس از ثبت سفارش، وضعیت موجود را در بخش سفارش‌های حساب کاربری بررسی کنید. جزئیات پیگیری به اطلاعاتی وابسته است که برای سفارش ثبت شده است.','button_label','مشاهده سفارش‌ها','button_url','/pages/account/orders/index.php')
    UNION ALL SELECT 'shipping','help','content','نیاز به راهنمایی دارید؟',50,JSON_OBJECT('heading','نیاز به راهنمایی دارید؟','body','ابتدا سؤالات متداول را ببینید. برای سفارش ثبت‌شده، اطلاعات همان سفارش را آماده داشته باشید.','button_label','سؤالات متداول','button_url','/pages/content/faq.php')
    UNION ALL SELECT 'returns','eligibility','content','بررسی شرایط',20,JSON_OBJECT('heading','بررسی شرایط','body','مهلت، استثناها و شرایط پذیرش باید بر اساس سیاست تأییدشده فروشگاه و اطلاعات سفارش تعیین شوند. این صفحه بدون تأیید سیاست رسمی، مهلت یا پذیرش قطعی برای همه کالاها اعلام نمی‌کند.')
    UNION ALL SELECT 'returns','request','content','ثبت درخواست',30,JSON_OBJECT('heading','ثبت درخواست','body','در صورت در دسترس بودن قابلیت مرجوعی برای سفارش، وارد حساب کاربری شوید و از بخش مرجوعی درخواست خود را ثبت کنید. اطلاعات اقلام و دلیل درخواست را دقیق وارد کنید.','button_label','ثبت درخواست مرجوعی','button_url','/pages/account/returns/index.php')
    UNION ALL SELECT 'returns','review','content','بررسی و نتیجه',40,JSON_OBJECT('heading','بررسی و نتیجه','body','ارسال درخواست به معنی تأیید آن یا تضمین بازپرداخت نیست. وضعیت و نتیجه را در جزئیات درخواست و حساب کاربری بررسی کنید.')
    UNION ALL SELECT 'returns','help','content','پرسش‌های بیشتر',50,JSON_OBJECT('heading','پرسش‌های بیشتر','body','برای پرسش‌های عمومی به سؤالات متداول مراجعه کنید. هرگونه مهلت، هزینه یا روش بازپرداخت باید پیش از انتشار به‌صورت رسمی تأیید شود.','button_label','سؤالات متداول','button_url','/pages/content/faq.php')
    UNION ALL SELECT 'privacy','data','content','چه اطلاعاتی ممکن است وارد شود؟',20,JSON_OBJECT('heading','چه اطلاعاتی ممکن است وارد شود؟','body','بسته به قابلیتی که استفاده می‌کنید، اطلاعات حساب، اطلاعات تماس و نشانی تحویل، اطلاعات لازم برای سفارش و پیام‌هایی که در فرم‌های موجود وارد می‌کنید ممکن است پردازش شوند. از واردکردن اطلاعات غیرضروری در فیلدهای آزاد خودداری کنید.')
    UNION ALL SELECT 'privacy','purpose','content','کاربرد اطلاعات',30,JSON_OBJECT('heading','کاربرد اطلاعات','body','اطلاعات واردشده باید تنها برای اجرای قابلیت مربوط، مدیریت حساب و سفارش، ارائه پشتیبانی یا رعایت الزامات قانونی قابل‌اعمال استفاده شوند. جزئیات دقیق هر پردازش باید با رفتار واقعی نسخه مستقر سامانه تطبیق داده شود.')
    UNION ALL SELECT 'privacy','security','content','حفاظت و دسترسی',40,JSON_OBJECT('heading','حفاظت و دسترسی','body','از رمز عبور خود محافظت کنید و آن را با دیگران به اشتراک نگذارید. این صفحه به‌تنهایی تضمین فنی یا حقوقی درباره امنیت، مدت نگهداری یا اشتراک‌گذاری داده‌ها ایجاد نمی‌کند؛ این موارد باید در سیاست نهایی و مستندات واقعی سامانه مشخص شوند.')
    UNION ALL SELECT 'privacy','questions','content','پرسش درباره حریم خصوصی',50,JSON_OBJECT('heading','پرسش درباره حریم خصوصی','body','برای پیگیری سفارش یا درخواست‌های مربوط به حساب از بخش حساب کاربری استفاده کنید. مسیر اختصاصی درخواست‌های حریم خصوصی باید پس از تأیید مالک کسب‌وکار در این صفحه درج شود.')
    UNION ALL SELECT 'terms','account','content','حساب کاربری',20,JSON_OBJECT('heading','حساب کاربری','body','اطلاعات ثبت‌نام و سفارش را دقیق وارد کنید و از دسترسی دیگران به حساب خود جلوگیری کنید. در صورت مشاهده فعالیت ناشناس، از امکانات امنیتی حساب استفاده کنید.')
    UNION ALL SELECT 'terms','product','content','اطلاعات محصول و قیمت',30,JSON_OBJECT('heading','اطلاعات محصول و قیمت','body','مشخصات، قیمت، گزینه‌ها و موجودی قابل مشاهده را در صفحه محصول و پیش از تأیید نهایی سفارش بررسی کنید. ثبت اطلاعات در فرم به‌تنهایی به معنی تأیید یا تکمیل سفارش نیست؛ وضعیت نهایی را از پیام سامانه و بخش سفارش‌ها بررسی کنید.')
    UNION ALL SELECT 'terms','order','content','ثبت و پیگیری سفارش',40,JSON_OBJECT('heading','ثبت و پیگیری سفارش','body','پیش از ثبت نهایی، اقلام، تعداد، نشانی و خلاصه هزینه‌ها را بررسی کنید. هرگونه تغییر وضعیت سفارش باید بر اساس اطلاعات ثبت‌شده در سامانه پیگیری شود.')
    UNION ALL SELECT 'terms','returns','content','ارسال و مرجوعی',50,JSON_OBJECT('heading','ارسال و مرجوعی','body','اطلاعات مربوط به ارسال و شرایط مرجوعی در صفحات ارسال سفارش و مرجوعی ارائه می‌شود. شرایط نهایی کسب‌وکار باید پیش از انتشار این صفحه تأیید شود.','items',JSON_ARRAY(JSON_OBJECT('title','ارسال سفارش','url','/pages/content/shipping.php','link_label','مشاهده'),JSON_OBJECT('title','مرجوعی کالا','url','/pages/content/returns.php','link_label','مشاهده')))
    UNION ALL SELECT 'terms','review','content','بازبینی شرایط',60,JSON_OBJECT('heading','بازبینی شرایط','body','نسخه نهایی باید شامل تاریخ اجرا، اطلاعات مسئول کسب‌وکار و مفاد حقوقی تأییدشده باشد. تا پیش از این تأیید، این صفحه نباید به‌عنوان مشاوره حقوقی یا سند حقوقی کامل تلقی شود.')
) AS seed ON seed.page_key = p.page_key
WHERE NOT EXISTS (SELECT 1 FROM admin_cms_sections s WHERE s.page_id = p.id AND s.section_key = seed.section_key);

-- Preserve the current storefront order/visibility until each draft section is published.
UPDATE admin_cms_sections SET published_sort_order = sort_order, published_is_visible = is_visible WHERE published_sort_order IS NULL;
