<?php

declare(strict_types=1);

/**
 * Allowlisted CMS pages and section types. This registry is the contract between
 * the admin editor, persistence layer and storefront renderer.
 */
final class AdminContentRegistry
{
    private const PAGES = [
        'home' => ['title' => 'صفحه اصلی', 'route' => '/', 'sections' => ['hero', 'categories', 'discovery', 'products', 'setup-preview', 'compatibility-preview', 'new-products', 'value-proposition', 'buying-guide', 'cta', 'smart-bundle']],
        'about' => ['title' => 'درباره ما', 'route' => '/pages/content/about.php', 'sections' => ['intro', 'content', 'links']],
        'contact' => ['title' => 'تماس با ما', 'route' => '/pages/content/contact.php', 'sections' => ['intro', 'content', 'links']],
        'faq' => ['title' => 'سؤالات متداول', 'route' => '/pages/content/faq.php', 'sections' => ['intro', 'faq']],
        'buying-guide' => ['title' => 'راهنمای خرید', 'route' => '/pages/content/buying-guide.php', 'sections' => ['intro', 'guide', 'content']],
        'shipping' => ['title' => 'راهنمای ارسال', 'route' => '/pages/content/shipping.php', 'sections' => ['intro', 'content']],
        'returns' => ['title' => 'شرایط مرجوعی', 'route' => '/pages/content/returns.php', 'sections' => ['intro', 'content']],
        'privacy' => ['title' => 'حریم خصوصی', 'route' => '/pages/content/privacy.php', 'sections' => ['intro', 'content']],
        'terms' => ['title' => 'قوانین و مقررات', 'route' => '/pages/content/terms.php', 'sections' => ['intro', 'content']],
    ];

    private const SECTION_TYPES = [
        'hero', 'categories', 'discovery', 'products', 'setup-preview',
        'compatibility-preview', 'new-products', 'value-proposition',
        'buying-guide', 'cta', 'smart-bundle', 'intro', 'content', 'links',
        'faq', 'guide', 'feature-grid', 'image-text', 'custom',
    ];

    public static function pages(): array
    {
        return self::PAGES;
    }

    public static function page(string $key): array
    {
        if (!isset(self::PAGES[$key])) {
            throw new InvalidArgumentException('صفحه انتخاب‌شده در قرارداد CMS ثبت نشده است.');
        }
        return self::PAGES[$key];
    }

    public static function sectionType(string $type): string
    {
        $type = strtolower(trim($type));
        if (!in_array($type, self::SECTION_TYPES, true)) {
            throw new InvalidArgumentException('نوع سکشن در قرارداد CMS مجاز نیست.');
        }
        return $type;
    }

    public static function validateContent(array $content): array
    {
        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (strlen($json) > 250000) {
            throw new InvalidArgumentException('حجم محتوای سکشن از حد مجاز بیشتر است.');
        }
        self::validateTree($content, 0);
        return $content;
    }

    public static function validateStyle(array $style): array
    {
        $allowed = ['background', 'background_type', 'gradient_from', 'gradient_to', 'gradient_angle', 'color', 'muted_color', 'accent_color', 'padding_top', 'padding_bottom', 'radius', 'text_align', 'container_width', 'overlay_opacity'];
        $clean = [];
        foreach ($style as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('ویژگی ظاهری ناشناخته است: ' . (string)$key);
            }
            if (in_array($key, ['background', 'gradient_from', 'gradient_to', 'color', 'muted_color', 'accent_color'], true)) {
                if (!is_string($value) || !preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,%\s]+\))$/', $value)) {
                    throw new InvalidArgumentException('رنگ یا مقدار گرادیان معتبر نیست.');
                }
            } elseif (in_array($key, ['padding_top', 'padding_bottom', 'radius', 'container_width'], true)) {
                if (!is_numeric($value) || (float)$value < 0 || (float)$value > 1600) {
                    throw new InvalidArgumentException('مقدار اندازه ظاهری خارج از محدوده مجاز است.');
                }
                $clean[$key] = (float)$value;
                continue;
            } elseif ($key === 'gradient_angle') {
                if (!is_numeric($value) || (int)$value < 0 || (int)$value > 360) {
                    throw new InvalidArgumentException('زاویه گرادیان باید بین ۰ تا ۳۶۰ باشد.');
                }
                $clean[$key] = (int)$value;
                continue;
            } elseif ($key === 'overlay_opacity') {
                if (!is_numeric($value) || (float)$value < 0 || (float)$value > 1) {
                    throw new InvalidArgumentException('شفافیت Overlay باید بین صفر و یک باشد.');
                }
                $clean[$key] = (float)$value;
                continue;
            } elseif ($key === 'background_type' && !in_array($value, ['solid', 'gradient', 'transparent'], true)) {
                throw new InvalidArgumentException('نوع پس‌زمینه معتبر نیست.');
            } elseif ($key === 'text_align' && !in_array($value, ['right', 'center', 'left'], true)) {
                throw new InvalidArgumentException('تراز متن معتبر نیست.');
            }
            $clean[$key] = $value;
        }
        return $clean;
    }

    private static function validateTree(array $node, int $depth): void
    {
        if ($depth > 8) {
            throw new InvalidArgumentException('عمق ساختار محتوای سکشن بیش از حد مجاز است.');
        }
        foreach ($node as $key => $value) {
            if (is_string($key) && preg_match('/^(on[a-z]+|script|style|srcdoc)$/i', $key)) {
                throw new InvalidArgumentException('ویژگی اجرایی یا HTML ناامن در محتوا مجاز نیست.');
            }
            if (is_string($value)) {
                if (strlen($value) > 12000 || preg_match('/<\s*script\b|javascript\s*:|data\s*:\s*text\/html/i', $value)) {
                    throw new InvalidArgumentException('متن محتوا شامل مقدار ناامن یا بیش از حد طولانی است.');
                }
            } elseif (is_array($value)) {
                self::validateTree($value, $depth + 1);
            } elseif (!is_null($value) && !is_bool($value) && !is_numeric($value)) {
                throw new InvalidArgumentException('نوع داده محتوای سکشن پشتیبانی نمی‌شود.');
            }
        }
    }
}
