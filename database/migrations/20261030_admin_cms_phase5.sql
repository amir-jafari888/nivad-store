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
