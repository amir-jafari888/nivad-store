<?php

declare(strict_types=1);

/**
 * Pure CSV serializer for report exports. Data access and report authorization
 * stay in ReportService/controller; this class prevents spreadsheet formula
 * execution and bounds export size.
 */
final class AdminReportExportService
{
    private const MAX_ROWS = 10000;
    private const MAX_COLUMNS = 100;
    private const MAX_CELL_BYTES = 20000;

    public function toCsv(array $headers, array $rows, bool $includeUtf8Bom = true): string
    {
        if ($headers === [] || count($headers) > self::MAX_COLUMNS) {
            throw new InvalidArgumentException('CSV export requires 1–100 columns.');
        }

        if (count($rows) > self::MAX_ROWS) {
            throw new InvalidArgumentException('CSV export exceeds the 10000-row limit.');
        }

        $fieldKeys = [];
        $normalizedHeaders = [];
        foreach ($headers as $fieldKey => $header) {
            if (
                !is_string($fieldKey) ||
                preg_match('/^[A-Za-z][A-Za-z0-9_.-]{0,99}$/', $fieldKey) !== 1 ||
                !is_string($header) ||
                trim($header) === '' ||
                mb_strlen($header) > 120
            ) {
                throw new InvalidArgumentException('CSV headers must map safe field keys to labels of at most 120 characters.');
            }
            $fieldKeys[] = $fieldKey;
            $normalizedHeaders[] = $header;
        }

        $stream = fopen('php://temp', 'w+b');
        if (!is_resource($stream)) {
            throw new RuntimeException('CSV output stream could not be opened.');
        }

        try {
            if ($includeUtf8Bom) {
                fwrite($stream, "\xEF\xBB\xBF");
            }

            fputcsv($stream, array_map([$this, 'safeCell'], $normalizedHeaders), ',', '"', '\\');

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    throw new InvalidArgumentException('Every CSV row must be an array.');
                }

                $values = [];
                for ($index = 0; $index < count($normalizedHeaders); $index++) {
                    $value = array_key_exists($index, $row)
                        ? $row[$index]
                        : null;
                    $values[] = $this->safeCell($value);
                }

                fputcsv($stream, $values, ',', '"', '\\');
            }

            rewind($stream);
            $csv = stream_get_contents($stream);
            if (!is_string($csv)) {
                throw new RuntimeException('CSV output could not be read.');
            }

            return $csv;
        } finally {
            fclose($stream);
        }
    }

    private function safeCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_scalar($value)) {
            $value = (string) $value;
        } else {
            $value = '[UNSUPPORTED VALUE]';
        }

        if (strlen($value) > self::MAX_CELL_BYTES) {
            $value = substr($value, 0, self::MAX_CELL_BYTES) . '…';
        }

        // Spreadsheet programs may execute formula-leading values. Prefix an
        // apostrophe after leading whitespace/control characters are checked.
        if (preg_match('/^[\x00-\x20]*[=+@-]/', $value) === 1) {
            $value = "'" . $value;
        }

        return str_replace(["\0"], '', $value);
    }
}
