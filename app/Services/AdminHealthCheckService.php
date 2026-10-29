<?php

declare(strict_types=1);

/**
 * Non-invasive health/readiness inspection.
 *
 * It deliberately does not open a database connection, make network calls,
 * mutate files, or disclose secret values. Database readiness is reported as
 * blocked until a verified target schema is approved and tested.
 */
final class AdminHealthCheckService
{
    public function run(): array
    {
        $checks = [];
        $root = dirname(__DIR__, 2);

        $checks[] = $this->check(
            'php.version',
            PHP_VERSION_ID >= 80200,
            'PHP 8.2 or newer is required.',
            'runtime'
        );

        foreach (['PDO', 'pdo_mysql', 'mbstring', 'openssl', 'json'] as $extension) {
            $loaded = $extension === 'PDO'
                ? class_exists(PDO::class)
                : extension_loaded($extension);

            $checks[] = $this->check(
                'php.extension.' . strtolower($extension),
                $loaded,
                $loaded ? 'Required runtime capability is available.' : 'Required PHP extension is missing.',
                'runtime'
            );
        }

        foreach ([
            'app/Core/Bootstrap.php',
            'app/Core/Request.php',
            'app/Core/Response.php',
            'app/Core/AdminPermissionCatalog.php',
            'app/Core/AdminFeatureRegistry.php',
            'app/Services/AuthorizationService.php',
            'app/Services/AdminFeatureAccessService.php',
            'app/Services/AdminActivityAuditService.php',
        ] as $relativePath) {
            $exists = is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

            $checks[] = $this->check(
                'source.' . str_replace(['/', '.'], ['.', '_'], $relativePath),
                $exists,
                $exists ? 'Required source file exists.' : 'Required source file is missing.',
                'application'
            );
        }

        $mailUser = $this->environmentValue('NIVAD_GMAIL_SMTP_USER');
        $mailPassword = $this->environmentValue('NIVAD_GMAIL_SMTP_APP_PASSWORD');
        $mailHost = $this->environmentValue('NIVAD_GMAIL_SMTP_HOST');
        $mailPort = $this->environmentValue('NIVAD_GMAIL_SMTP_PORT');

        $gmailConfigured = $mailUser !== ''
            && $mailPassword !== ''
            && filter_var($mailUser, FILTER_VALIDATE_EMAIL) !== false
            && ($mailHost === '' || strtolower($mailHost) === 'smtp.gmail.com')
            && ($mailPort === '' || in_array($mailPort, ['465', '587'], true));

        $checks[] = $this->check(
            'mail.gmail.configuration',
            $gmailConfigured,
            $gmailConfigured
                ? 'Gmail SMTP environment variables are present; connectivity and delivery have not been tested.'
                : 'Gmail SMTP is not configured in the process environment. No secret values are displayed.',
            'external_service',
            $gmailConfigured ? 'configured_unverified' : 'not_configured'
        );

        $checks[] = [
            'key' => 'database.schema',
            'status' => 'blocked',
            'ok' => null,
            'message' => 'Database checks are intentionally paused pending verified schema, backup, and explicit migration gate.',
            'category' => 'database',
        ];

        $counts = ['ok' => 0, 'fail' => 0, 'blocked' => 0];
        foreach ($checks as $item) {
            if ($item['status'] === 'ok' || $item['status'] === 'configured_unverified') {
                $counts['ok']++;
            } elseif ($item['status'] === 'blocked') {
                $counts['blocked']++;
            } else {
                $counts['fail']++;
            }
        }

        return [
            'status' => $counts['fail'] > 0 ? 'degraded' : ($counts['blocked'] > 0 ? 'partial' : 'ready'),
            'summary' => $counts,
            'checks' => $checks,
            'database_accessed' => false,
            'network_accessed' => false,
            'secrets_disclosed' => false,
        ];
    }

    private function check(
        string $key,
        bool $ok,
        string $message,
        string $category,
        ?string $successStatus = null
    ): array {
        return [
            'key' => $key,
            'status' => $ok ? ($successStatus ?? 'ok') : 'fail',
            'ok' => $ok,
            'message' => $message,
            'category' => $category,
        ];
    }

    private function environmentValue(string $name): string
    {
        $value = getenv($name);

        return is_string($value) ? trim($value) : '';
    }
}
