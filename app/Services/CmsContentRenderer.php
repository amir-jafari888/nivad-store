<?php

declare(strict_types=1);

/**
 * Storefront renderer for published CMS sections. Only structured content is
 * rendered; HTML and executable attributes are never accepted from the editor.
 */
final class CmsContentRenderer
{
    private static ?AdminContentRepository $repository = null;

    public static function renderSection(string $pageKey, string $sectionKey): bool
    {
        try {
            foreach (self::repository()->sections($pageKey) as $section) {
                if (($section['section_key'] ?? '') !== $sectionKey || ($section['status'] ?? '') !== 'published' || (int)($section['is_visible'] ?? 0) !== 1) continue;
                echo self::renderMarkup($section['title'], $section['content'] ?? [], $section['style'] ?? [], $section['section_type'] ?? 'custom', $section['id'] ?? 0);
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
            $published = array_values(array_filter($sections, static fn(array $section): bool => ($section['status'] ?? '') === 'published' && (int)($section['is_visible'] ?? 0) === 1));
            if ($published === []) return false;
            foreach ($published as $section) {
                echo self::renderMarkup($section['title'], $section['published_content'] ?: $section['content'], $section['published_style'] ?: $section['style'], $section['section_type'], $section['id']);
            }
            return true;
        } catch (Throwable $exception) {
            error_log('CMS page rendering unavailable: ' . get_class($exception));
            return false;
        }
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

    private static function repository(): AdminContentRepository
    {
        if (self::$repository === null) self::$repository = new AdminContentRepository();
        return self::$repository;
    }

    private static function renderMarkup(string $title, array $content, array $style, string $type, int|string $id): string
    {
        $style = AdminContentRegistry::validateStyle($style);
        $vars = [];
        if (isset($style['background'])) $vars[] = '--cms-background:' . $style['background'];
        if (isset($style['gradient_from'])) $vars[] = '--cms-gradient-from:' . $style['gradient_from'];
        if (isset($style['gradient_to'])) $vars[] = '--cms-gradient-to:' . $style['gradient_to'];
        if (isset($style['gradient_angle'])) $vars[] = '--cms-gradient-angle:' . (int)$style['gradient_angle'] . 'deg';
        if (isset($style['color'])) $vars[] = '--cms-color:' . $style['color'];
        if (isset($style['muted_color'])) $vars[] = '--cms-muted:' . $style['muted_color'];
        if (isset($style['accent_color'])) $vars[] = '--cms-accent:' . $style['accent_color'];
        if (isset($style['padding_top'])) $vars[] = '--cms-padding-top:' . (float)$style['padding_top'] . 'px';
        if (isset($style['padding_bottom'])) $vars[] = '--cms-padding-bottom:' . (float)$style['padding_bottom'] . 'px';
        if (isset($style['radius'])) $vars[] = '--cms-radius:' . (float)$style['radius'] . 'px';
        if (isset($style['container_width'])) $vars[] = '--cms-container-width:' . (float)$style['container_width'] . 'px';
        if (isset($style['text_align'])) $vars[] = '--cms-text-align:' . $style['text_align'];
        $classes = 'cms-live-section cms-live-section--' . preg_replace('/[^a-z0-9_-]/', '', $type);
        $styleAttribute = $vars ? ' style="' . self::e(implode(';', $vars)) . '"' : '';
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
        return '<img class="cms-live-section__image" src="' . self::e(url('/content-media/' . $id)) . '" alt="' . self::e($alt) . '" loading="lazy" decoding="async">';
    }

    private static function safeUrl(string $url): string
    {
        $url = trim($url);
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) return url($url);
        if (preg_match('/^https:\/\/[a-z0-9.-]+(?::[0-9]+)?(?:\/[^\s]*)?$/i', $url)) return $url;
        return '#';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
