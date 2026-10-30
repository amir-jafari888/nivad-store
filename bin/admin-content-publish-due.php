<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';
Bootstrap::boot();

try {
    $published = (new AdminContentService())->publishDue();
    fwrite(STDOUT, 'Published due CMS sections: ' . $published . PHP_EOL);
    exit(0);
} catch (Throwable $exception) {
    error_log('CMS scheduled publishing worker failed: ' . get_class($exception));
    fwrite(STDERR, 'CMS scheduled publishing failed. Check the application error log.' . PHP_EOL);
    exit(1);
}
