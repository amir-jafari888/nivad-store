<?php

declare(strict_types=1);

/**
 * Append-only product analytics recorder.
 *
 * Uses the already-migrated admin_analytics_events foundation table.
 * Dimensions contain pseudonymous identifiers only; raw search terms and
 * direct customer identifiers are never persisted.
 */
final class ProductAnalyticsRecorder
{
    public static function record(
        PDO $db,
        string $eventKey,
        ?string $entityType = null,
        int|string|null $entityId = null,
        ?float $numericValue = null,
        array $dimensions = []
    ): int {
        $eventKey = trim($eventKey);
        if ($eventKey === '' || mb_strlen($eventKey) > 120) {
            throw new InvalidArgumentException('کلید رویداد تحلیلی نامعتبر است.');
        }

        $safeDimensions = SensitiveDataRedactor::redact($dimensions);
        $json = $safeDimensions === []
            ? null
            : json_encode($safeDimensions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $stmt = $db->prepare(
            'INSERT INTO admin_analytics_events
                (event_key, entity_type, entity_id, actor_admin_id, value_numeric, dimensions_json)
             VALUES
                (:event_key, :entity_type, :entity_id, NULL, :value_numeric, :dimensions_json)'
        );
        $stmt->execute([
            'event_key' => $eventKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId === null ? null : (string)$entityId,
            'value_numeric' => $numericValue,
            'dimensions_json' => $json,
        ]);

        return (int)$db->lastInsertId();
    }
}
