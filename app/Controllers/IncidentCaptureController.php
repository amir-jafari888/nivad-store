<?php

declare(strict_types=1);

/**
 * Public, CSRF-protected and rate-limited browser telemetry endpoint.
 * It accepts metadata only; no raw message, stack trace, form value or query string.
 */
final class IncidentCaptureController extends Controller
{
    public function client(): never
    {
        Session::start();
        $now = time();
        $timestamps = Session::get('_incident_capture_timestamps', []);
        if (!is_array($timestamps)) $timestamps = [];

        $timestamps = array_values(array_filter(
            $timestamps,
            static fn ($value): bool => is_int($value) && $value > ($now - 60)
        ));
        if (count($timestamps) >= 10) $this->error('تعداد گزارش‌های خطا بیش از حد مجاز است.', 429);

        $data = Request::json();
        $eventType = (string) ($data['event_type'] ?? '');
        if (!in_array($eventType, ['runtime_error', 'unhandled_rejection', 'resource_error'], true)) {
            $this->error('نوع رخداد مرورگر معتبر نیست.', 422);
        }

        $page = parse_url((string) ($data['page'] ?? '/'), PHP_URL_PATH);
        $file = parse_url((string) ($data['file'] ?? ''), PHP_URL_PATH);
        $errorType = (string) ($data['error_type'] ?? 'Error');
        if (preg_match('/^[A-Za-z][A-Za-z0-9_]{0,79}$/', $errorType) !== 1) $errorType = 'Error';

        $line = filter_var($data['line'] ?? null, FILTER_VALIDATE_INT);
        $column = filter_var($data['column'] ?? null, FILTER_VALIDATE_INT);
        $timestamps[] = $now;
        Session::set('_incident_capture_timestamps', $timestamps);

        AdminIncidentCaptureService::capture(
            'javascript.runtime',
            'warning',
            'Browser ' . $eventType . ' (' . $errorType . ')',
            [
                'event_type' => $eventType,
                'error_type' => $errorType,
                'page' => is_string($page) ? mb_substr($page, 0, 255) : '/',
                'file' => is_string($file) ? basename($file) : null,
                'line' => $line !== false && $line !== null && $line > 0 ? min($line, 10000000) : null,
                'column' => $column !== false && $column !== null && $column > 0 ? min($column, 10000000) : null,
            ],
            'javascript.' . $eventType,
            'A browser-side error event was reported.'
        );

        $this->json(['success' => true, 'accepted' => true], 202);
    }
}
