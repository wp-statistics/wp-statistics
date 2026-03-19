<?php

namespace WP_Statistics\Service\CustomEvent;

use Exception;
use WP_Statistics\Components\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Analytics\VisitorProfile;

/**
 * Handles custom event recording from batch tracking and direct calls.
 *
 * This class registers listeners for custom event actions fired by BatchTracking
 * and processes them to record events in the database.
 *
 * @since 15.0.0
 */
class CustomEventHandler
{
    /**
     * Initialize the handler by registering action hooks.
     */
    public function __construct()
    {
        // Listen for batch events (raw array from BatchTracking)
        add_action('wp_statistics_batch_events', [$this, 'onBatchEvents']);

        // Keep direct recording hook for non-batch callers (e.g., PHP API)
        add_action('wp_statistics_record_custom_event', [$this, 'recordEvent'], 10, 2);
    }

    /**
     * Handle raw batch events dispatched by BatchTracking.
     *
     * Iterates the raw events array, picks out 'custom_event' entries,
     * sanitizes them, and records each one. Other event types are ignored.
     *
     * @param array $events Raw events array from the batch payload.
     */
    public function onBatchEvents(array $events): void
    {
        foreach ($events as $event) {
            $type = $event['type'] ?? '';

            if ($type !== 'custom_event') {
                continue;
            }

            $data      = $event['data'] ?? [];
            $eventName = sanitize_text_field($data['event_name'] ?? '');
            $eventData = $data['event_data'] ?? [];

            if (is_string($eventData)) {
                $eventData = json_decode($eventData, true) ?: [];
            }

            if (empty($eventName)) {
                continue;
            }

            do_action('wp_statistics_custom_event_batch', $eventName, $eventData);

            $this->recordEvent($eventName, $eventData);
        }
    }

    /**
     * Record a custom event to the database.
     *
     * @param string $eventName The event name/identifier.
     * @param array  $eventData Additional event data.
     * @return void
     */
    public function recordEvent($eventName, $eventData = [])
    {
        try {
            if (empty($eventName)) {
                return;
            }

            if (!Option::getValue('event_tracking', false)) {
                return;
            }

            // Create visitor profile for the current request
            $visitorProfile = new VisitorProfile();

            // Parse event data
            $eventDataParser = new CustomEventDataParser($eventName, $eventData, $visitorProfile);
            $parsedData = $eventDataParser->getParsedData();

            // Insert event into the database using RecordFactory
            RecordFactory::event()->insert([
                'date'       => current_time('mysql'),
                'page_id'    => $parsedData['page_id'] ?? null,
                'visitor_id' => $parsedData['visitor_id'] ?? null,
                'user_id'    => $parsedData['user_id'] ?? null,
                'event_name' => $parsedData['event_name'],
                'event_data' => is_array($parsedData['event_data']) ? json_encode($parsedData['event_data']) : $parsedData['event_data'],
            ]);

        } catch (Exception $e) {
            // Log error but don't break the request
            error_log('WP Statistics: Failed to record custom event - ' . $e->getMessage());
        }
    }
}
