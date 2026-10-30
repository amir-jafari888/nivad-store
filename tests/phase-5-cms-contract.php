<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = 0;
$failures = [];

function cmsCheck(string $label, bool $condition): void
{
    global $checks, $failures;
    $checks++;
    if ($condition) {
        echo '<span style="color:green">PASS</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
    } else {
        $failures[] = $label;
        echo '<span style="color:red">FAIL</span> ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "<br>\n";
    }
}

function cmsFile(string $path): string
{
    global $root;
    $content = file_get_contents($root . '/' . $path);
    if (!is_string($content)) throw new RuntimeException('Required contract file is missing: ' . $path);
    return $content;
}

require_once $root . '/app/Core/AdminContentRegistry.php';

$migration = cmsFile('database/migrations/20261030_admin_cms_phase5.sql');
$repository = cmsFile('app/Repositories/AdminContentRepository.php');
$service = cmsFile('app/Services/AdminContentService.php');
$renderer = cmsFile('app/Services/CmsContentRenderer.php');
$controller = cmsFile('app/Controllers/AdminContentController.php');
$routes = cmsFile('routes/api.php');
$permissionMap = cmsFile('app/Core/AdminRoutePermissionMap.php');
$preview = cmsFile('admin/content/preview.php');
$editor = cmsFile('admin/assets/js/modules/admin-content-editor.js');
$workflow = cmsFile('.github/workflows/admin-phase-5-cms.yml');

cmsCheck('CMS migration creates page and section tables', str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_cms_pages') && str_contains($migration, 'CREATE TABLE IF NOT EXISTS admin_cms_sections'));
cmsCheck('CMS migration does not contain destructive DROP statements', preg_match('/\bDROP\s+(TABLE|DATABASE|COLUMN)\b/i', $migration) !== 1);
cmsCheck('CMS migration stores separate draft and published payloads', str_contains($migration, 'published_content_json') && str_contains($migration, 'published_style_json'));
cmsCheck('CMS migration stores published visibility and order separately', str_contains($migration, 'published_is_visible') && str_contains($migration, 'published_sort_order'));
cmsCheck('CMS migration seeds landing and public content pages', substr_count($migration, "('home',") === 1 && str_contains($migration, "('buying-guide',") && str_contains($migration, "('privacy',"));
cmsCheck('CMS migration avoids assuming an administrator ID', !str_contains($migration, 'seed.content_json, seed.style_json, 1, 1'));
cmsCheck('CMS migration reuses existing revision and media tables', str_contains($repository, 'admin_content_revisions') && str_contains($repository, 'admin_media_assets'));
cmsCheck('CMS registry contains nine allowlisted pages', count(AdminContentRegistry::pages()) === 9);
cmsCheck('CMS registry includes all public static content pages', isset(AdminContentRegistry::pages()['about'], AdminContentRegistry::pages()['faq'], AdminContentRegistry::pages()['terms']));
cmsCheck('CMS registry rejects unregistered page keys', (static function (): bool { try { AdminContentRegistry::page('../admin'); return false; } catch (InvalidArgumentException) { return true; } })());
cmsCheck('CMS registry rejects executable content', (static function (): bool { try { AdminContentRegistry::validateContent(['body' => '<script>alert(1)</script>']); return false; } catch (InvalidArgumentException) { return true; } })());
cmsCheck('CMS registry validates gradients and color tokens', (static function (): bool { try { AdminContentRegistry::validateStyle(['background_type' => 'gradient', 'gradient_from' => '#112233', 'gradient_to' => '#445566', 'gradient_angle' => 135]); return true; } catch (Throwable) { return false; } })());
cmsCheck('CMS service validates media IDs before save', str_contains($service, 'validateMediaReferences') && str_contains($service, 'mediaById'));
cmsCheck('CMS service keeps revision restore in draft', str_contains($service, 'restoreRevision') && str_contains($repository, 'status = "draft"'));
cmsCheck('CMS repository publishes draft snapshots independently', str_contains($repository, 'published_content_json = content_json') && str_contains($repository, 'published_is_visible = is_visible'));
cmsCheck('CMS repository blocks deleting referenced or historical media', str_contains($repository, 'isMediaReferenced') && str_contains($repository, 'admin_content_revisions'));
cmsCheck('CMS renderer preserves homepage native modules', str_contains($renderer, 'NATIVE_HOME_COMPONENTS') && str_contains($renderer, 'renderOriginalHomeSection'));
cmsCheck('CMS renderer separates draft from published storefront data', str_contains($renderer, 'has_published_version') && str_contains($renderer, 'published_is_visible'));
cmsCheck('CMS renderer uses semantic H1 for page introductions', str_contains($renderer, '$headingTag = $type === \'intro\' ? \'h1\' : \'h2\''));
cmsCheck('CMS renderer supports FAQ disclosure semantics', str_contains($renderer, 'cms-faq-list') && str_contains($renderer, '<details><summary>'));
cmsCheck('CMS media delivery restricts public files to published references', str_contains($repository, 'publishedMedia') && str_contains($repository, 'published_content_json IS NOT NULL'));
cmsCheck('CMS controller exposes CRUD, publish, schedule, revisions and media', str_contains($controller, 'createSection') && str_contains($controller, 'publish') && str_contains($controller, 'schedule') && str_contains($controller, 'restoreRevision') && str_contains($controller, 'uploadMedia'));
cmsCheck('CMS API routes are CSRF protected for writes', str_contains($routes, '$adminCapabilityWrite = [') && str_contains($routes, 'CsrfMiddleware::class'));
cmsCheck('CMS routes have explicit view, manage and publish permissions', str_contains($permissionMap, "'admin.content.sections.publish' => 'admin.content.publish'") && str_contains($permissionMap, "'admin.media.index' => 'admin.media.view'"));
cmsCheck('CMS preview listens for same-origin live updates', str_contains($preview, "event.source!==parent") && str_contains($preview, "event.data?.type!=='cms-preview'"));
cmsCheck('CMS editor posts preview updates without page reload', str_contains($editor, "postMessage({type:'cms-preview'") && str_contains($editor, "addEventListener('input',preview)"));
cmsCheck('CMS editor supports section archive and restore', str_contains($editor, "method:'DELETE'") && str_contains($editor, "/restore"));
cmsCheck('CMS editor supports media upload, alt editing and deletion', str_contains($editor, "method:'POST'") && str_contains($editor, "method:'PATCH'") && str_contains($editor, "method:'DELETE'"));
cmsCheck('CMS workflow includes PHP and JavaScript syntax checks', str_contains($workflow, 'php -l') && str_contains($workflow, 'node --input-type=module --check'));

echo '<hr><strong>CMS CONTRACT TESTS: ' . ($checks - count($failures)) . '/' . $checks . ' PASS</strong><br>';
if ($failures !== []) {
    echo '<strong>Failures:</strong><br>' . implode('<br>', array_map(static fn(string $item): string => htmlspecialchars($item, ENT_QUOTES, 'UTF-8'), $failures));
    exit(1);
}
