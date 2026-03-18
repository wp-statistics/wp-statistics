<?php

namespace WP_Statistics\Service\Tracking\Core;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Ip;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Query;
use Exception;

/**
 * Shared batch event processing logic.
 *
 * Transport-agnostic: REST and AJAX tracking methods parse the request
 * in their own callbacks, then delegate here for business logic.
 *
 * @since 15.0.0
 */
class BatchProcessor
{
    /**
     * Parse a raw batch_data JSON string and process events.
     *
     * Shared entry point for both REST and AJAX batch callbacks.
     *
     * @param string|null $rawBatchData Raw JSON string from the request.
     * @return array{processed: int, errors: string[]}
     * @throws Exception On missing or invalid payload.
     */
    public static function parseAndProcess(?string $rawBatchData): array
    {
        if (empty($rawBatchData)) {
            throw new Exception('Missing batch data', 400);
        }

        $data = json_decode($rawBatchData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON payload', 400);
        }

        return (new self())->processEvents($data);
    }

    /**
     * Process batch payload.
     *
     * Handles session engagement update and processes any custom events.
     * The session is identified server-side using the visitor's IP hash.
     *
     * @param array $data Batch payload containing engagement_time and events.
     * @return array{processed: int, errors: string[]}
     */
    public function processEvents(array $data): array
    {
        $processed = 0;
        $errors    = [];

        $engagementTime = isset($data['engagement_time']) ? (int) $data['engagement_time'] : 0;

        if ($engagementTime > 0) {
            try {
                $visitorHash    = Ip::hash();
                $thirtyMinutesAgo = DateTime::getUtc('Y-m-d H:i:s', '-30 minutes');

                $session = Query::select('sessions.*')
                    ->from('sessions')
                    ->join('visitors', ['sessions.visitor_id', 'visitors.ID'])
                    ->where('visitors.hash', '=', $visitorHash)
                    ->where('sessions.ended_at', '>=', $thirtyMinutesAgo)
                    ->orderBy('sessions.ID', 'DESC')
                    ->perPage(1)
                    ->getRow();

                if ($session && !empty($session->ID)) {
                    $this->updateSessionEngagement((int) $session->ID, $engagementTime);
                    $processed++;
                }
            } catch (Exception $e) {
                $errors[] = 'Session update failed: ' . $e->getMessage();
            }
        }

        if (!empty($data['events']) && is_array($data['events'])) {
            foreach ($data['events'] as $index => $event) {
                try {
                    $this->processEvent($event);
                    $processed++;
                } catch (Exception $e) {
                    $errors[] = 'Event ' . $index . ' (' . ($event['type'] ?? 'unknown') . '): ' . $e->getMessage();
                }
            }
        }

        return [
            'processed' => $processed,
            'errors'    => $errors,
        ];
    }

    /**
     * Process a single event.
     *
     * @param array $event Event data.
     * @throws Exception If event processing fails.
     */
    private function processEvent(array $event): void
    {
        if (empty($event['type'])) {
            throw new Exception('Missing event type');
        }

        $eventData = $event['data'] ?? [];

        switch ($event['type']) {
            case 'custom_event':
                $this->handleCustomEvent($eventData);
                break;
        }
    }

    /**
     * Handle custom event.
     *
     * @param array $data Event data containing event_name and event_data.
     */
    private function handleCustomEvent(array $data): void
    {
        if (empty($data['event_name'])) {
            return;
        }

        $eventName = sanitize_text_field($data['event_name']);
        $eventData = isset($data['event_data']) ? $data['event_data'] : [];

        if (is_string($eventData)) {
            $eventData = json_decode($eventData, true) ?: [];
        }

        do_action('wp_statistics_custom_event_batch', $eventName, $eventData);
        do_action('wp_statistics_record_custom_event', $eventName, $eventData);
    }

    /**
     * Update session with engagement data.
     *
     * @param int $sessionId          Session ID to update.
     * @param int $engagementTimeMs   Engagement time in milliseconds.
     */
    private function updateSessionEngagement(int $sessionId, int $engagementTimeMs): void
    {
        $engagementTimeSec = (int) round($engagementTimeMs / 1000);

        $record = (object) ['ID' => $sessionId];

        RecordFactory::session($record)->update([
            'ended_at' => DateTime::getUtc(),
            'duration' => $engagementTimeSec,
        ]);
    }
}
