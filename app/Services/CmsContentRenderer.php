<?php

declare(strict_types=1);

/**
 * Storefront renderer for structured CMS content. Existing interactive storefront
 * modules remain the renderers for their native sections; CMS controls their
 * copy and outer design while retaining live catalog/discovery integrations.
 */
final class CmsContentRenderer
{
    private static ?AdminContentRepository $repository = null;

    private const NATIVE_HOME_COMPONENTS = [
        'hero' => 'hero.php',
        'categories' => 'categories.php',
        'discovery' => 'discovery.php',
        'products' => 'product.php',
        'setup-preview' => 'setup-preview.php',
        'compatibility-preview' => 'compatibility-preview.php',
        'new-products' => 'new-product.php',
        'smart-bundle' => 'smart-bundle.php',
        'value-proposition' => 'value-proposition.php',
        'buying-guide' => 'buying-guide.php',
        'cta' => 'cta.php',
    ];

    private const GENERIC_HOME_SECTIONS = ['value-proposition', 'buying-guide', 'cta'];

    public static function activeContent(): array
    {
        $section = $GLOBALS['cms_active_section'] ?? null;
        return is_array($section) && is_array($section['content'] ?? null) ? $section['content'] : [];
    }

    public static function renderSection(string $pageKey, string $sectionKey): bool
    {
        try {
            foreach (self::repository()->sections($pageKey) as $section) {
                if (($section['section_key'] ?? '') !== $sectionKey || ($section['status'] ?? '') !== 'published' || (int)($section['is_visible'] ?? 0) !== 1) continue;
                echo self::renderMarkup($section['title'], $section['published_content'] ?: $section['content'], $section['published_style'] ?: $section['style'], $section['section_type'], $section['id']);
                return true;
            }
        } catch (Throwable $exception) {
            error_log('CMS section rendering unavailable: ' . get_class($exception));
        }
        return false;
    }

    public static function renderPage(string $pageKey): bool
    {
        try {
            $sections = self::repository()->sections($pageKey);
            if ($sections === []) return false;

            if ($pageKey === 'home') {
                foreach ($sections as $section) {
                    $key = (string)$section['section_key'];
                    $type = (string)$section['section_type'];
                    if (($section['status'] ?? '') === 'published') {
                        if ((int)($section['is_visible'] ?? 0) !== 1) continue;
                        $section['content'] = $section['published_content'] ?: $section['content'];
                        $section['style'] = $section['published_style'] ?: $section['style'];
                        if (isset(self::NATIVE_HOME_COMPONENTS[$key]) && !in_array($key, self::GENERIC_HOME_SECTIONS, true)) {
                            self::renderNativeHomeSection($section, self::NATIVE_HOME_COMPONENTS[$key]);
                        } else {
                            echo self::renderMarkup($section['title'], $section['content'], $section['style'], $type, $section['id']);
                        }
                        continue;
                    }
                    // Until an administrator publishes a draft, preserve the
                    // original PHP section and all of its existing interactions.
                    if (isset(self::NATIVE_HOME_COMPONENTS[$key])) {
                        self::renderOriginalHomeSection(self::NATIVE_HOME_COMPONENTS[$key]);
                    }
                }
                return true;
            }

            // Content pages switch atomically: every registered section must be
            // published before the legacy page is replaced, avoiding partial pages.
            foreach ($sections as $section) {
                if (($section['status'] ?? '') !== 'published') return false;
            }
            foreach ($sections as $section) {
                if ((int)($section['is_visible'] ?? 0) !== 1) continue;
                echo self::renderMarkup(
                    (string)$section['title'],
                    $section['published_content'] ?: $section['content'],
                    $section['published_style'] ?: $section['style'],
                    (string)$section['section_type'],
                    $section['id']
                );
            }
            return true;
        } catch (Throwable $exception) {
            error_log('CMS page rendering unavailable: ' . get_class($exception));
            return false;
        }
    }

    public static function renderAdminPreview(array $section): string
    {
        $key = (string)($section['section_key'] ?? '');
        if (($section['page_key'] ?? '') === 'home' && isset(self::NATIVE_HOME_COMPONENTS[$key]) && !in_array($key, self::GENERIC_HOME_SECTIONS, true)) {
            $path = dirname(__DIR__, 2) . '/partials/' . self::NATIVE_HOME_COMPONENTS[$key];
            if (is_file($path)) {
                $GLOBALS['cms_active_section'] = $section;
                ob_start();
                include $path;
                $component = (string)ob_get_clean();
                unset($GLOBALS['cms_active_section']);
                $image = !empty($section['content']['image_id']) ? self::image((int)$section['content']['image_id'], (string)($section['content']['image_alt'] ?? $section['title'] ?? '')) : '';
                return '<div class="cms-native-section" data-cms-native="' . self::e($key) . '"' . self::styleAttribute((array)($section['style'] ?? [])) . '>' . $image . $component . '</div>';
            }
        }
        return self::renderMarkup(
            (string)($section['title'] ?? 'پیش‌نمایش سکشن'),
            (array)($section['content'] ?? []),
            (array)($section['style'] ?? []),
            (string)($section['section_type'] ?? 'custom'),
            $section['id'] ?? 0
        );
    }

    public static function renderPreview(array $section): string
    {
        return self::renderMarkup(
            (string)($section['title'] ?? 'پیش‌نمایش سکشن'),
            (array)($section['content'] ?? []),
            (array)($section['style'] ?? []),
            (string)($section['section_type'] ?? 'custom'),
            0
        );
    }

    private static function renderOriginalHomeSection(string $file): void
    {
        $path = dirname(__DIR__, 2) . '/partials/' . $file;
        if (!is_file($path)) return;
        $GLOBALS['cms_active_section'] = null;
        include $path;
        unset($GLOBALS['cms_active_section']);
    }

    private static function renderNativeHomeSection(array $section, string $file): void
    {
        $path = dirname(__DIR__, 2) . '/partials/' . $file;
        if (!is_file($path)) {
            echo self::renderMarkup($section['title'], $section['content'], $section['style'], $section['section_type'], $section['id']);
            return;
        }
        $styleAttribute = self::styleAttribute((array)$section['style']);
        $GLOBALS['cms_active_section'] = $section;
        ob_start();
        include $path;
        $component = (string)ob_get_clean();
        unset($GLOBALS['cms_active_section']);
        $image = !empty($section['content']['image_id']) ? self::image((int)$section['content']['image_id'], (string)($section['content']['image_alt'] ?? $section['title'])) : '';
        echo '<div class="cms-native-section" data-cms-native="' . self::e((string)$section['section_key']) . '" data-cms-section="' . (int)$section['id'] . '"' . $styleAttribute . '>' . $image . $component . '</div>';
    }

    private static function styleAttribute(array $style): string
    {
        $style = AdminContentRegistry::validateStyle($style);
        $vars = [];
        if (($style['background_type'] ?? 'transparent') === 'solid' && isset($style['background'])) $vars[] = '--cms-background:' . $style['background'];
        else $vars[] = '--cms-background:transparent';
        $gradientEnabled = ($style['background_type'] ?? 'transparent') === 'gradient';
        foreach ([
            'gradient_from' => '--cms-gradient-from',
            'gradient_to' => '--cms-gradient-to',
            'color' => '--cms-color',
            'muted_color' => '--cms-muted',
            'accent_color' => '--cms-accent',
        ] as $key => $property) {
            if ($gradientEnabled && isset($style[$key])) $vars[] = $property . ':' . $style[$key];
        }
        if (!$gradientEnabled) { $vars[] = '--cms-gradient-from:transparent'; $vars[] = '--cms-gradient-to:transparent'; }
        if (isset($style['gradient_angle'])) $vars[] = '--cms-gradient-angle:' . (int)$style['gradient_angle'] . 'deg';
        foreach (['padding_top' => 'padding-top', 'padding_bottom' => 'padding-bottom', 'radius' => 'radius', 'container_width' => 'container-width'] as $key => $property) {
            if (isset($style[$key])) $vars[] = '--cms-' . $property . ':' . (float)$style[$key] . 'px';
        }
        if (isset($style['text_align'])) $vars[] = '--cms-text-align:' . $style['text_align'];
        return $vars ? ' style="' . self::e(implode(';', $vars)) . '"' : '';
    }

    private static function renderMarkup(string $title, array $content, array $style, string $type, int|string $id): string
    {
        $styleAttribute = self::styleAttribute($style);
        $classes = 'cms-live-section cms-live-section--' . preg_replace('/[^a-z0-9_-]/', '', $type);
        $html = '<section class="' . self::e($classes) . '" data-cms-section="' . self::e((string)$id) . '"' . $styleAttribute . '><div class="cms-live-section__inner">';
        if (!empty($content['eyebrow'])) $html .= '<p class="cms-live-section__eyebrow">' . self::e((string)$content['eyebrow']) . '</p>';
        $heading = (string)($content['heading'] ?? $title);
        if ($heading !== '') $html .= '<h2 class="cms-live-section__heading">' . self::e($heading) . '</h2>';
        if (!empty($content['body'])) $html .= '<p class="cms-live-section__body">' . nl2br(self::e((string)$content['body'])) . '</p>';
        if (!empty($content['image_id'])) $html .= self::image((int)$content['image_id'], (string)($content['image_alt'] ?? $heading));
        $items = is_array($content['items'] ?? null) ? $content['items'] : [];
        if ($items !== []) {
            $html .= '<div class="cms-live-section__grid">';
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                $html .= '<article class="cms-live-card">';
                if (!empty($item['image_id'])) $html .= self::image((int)$item['image_id'], (string)($item['image_alt'] ?? $item['title'] ?? ''));
                if (!empty($item['eyebrow'])) $html .= '<span class="cms-live-card__eyebrow">' . self::e((string)$item['eyebrow']) . '</span>';
                if (!empty($item['title'])) $html .= '<h3>' . self::e((string)$item['title']) . '</h3>';
                if (!empty($item['body'])) $html .= '<p>' . self::e((string)$item['body']) . '</p>';
                if (!empty($item['url']) && !empty($item['link_label'])) $html .= '<a href="' . self::e(self::safeUrl((string)$item['url'])) . '">' . self::e((string)$item['link_label']) . '</a>';
                $html .= '</article>';
            }
            $html .= '</div>';
        }
        if (!empty($content['button_label']) && !empty($content['button_url'])) {
            $html .= '<a class="cms-live-section__button" href="' . self::e(self::safeUrl((string)$content['button_url'])) . '">' . self::e((string)$content['button_label']) . '</a>';
        }
        $html .= '</div></section>';
        return $html;
    }

    private static function image(int $id, string $alt): string
    {
        if ($id < 1) return '';
        return '<img class="cms-live-section__image" src="' . self::e(url('/api/content-media/' . $id)) . '" alt="' . self::e($alt) . '" loading="lazy" decoding="async">';
    }

    private static function safeUrl(string $url): string
    {
        $url = trim($url);
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) return url($url);
        if (preg_match('/^https:\/\/[a-z0-9.-]+(?::[0-9]+)?(?:\/[^\s]*)?$/i', $url)) return $url;
        return '#';
    }

    private static function repository(): AdminContentRepository
    {
        if (self::$repository === null) self::$repository = new AdminContentRepository();
        return self::$repository;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
