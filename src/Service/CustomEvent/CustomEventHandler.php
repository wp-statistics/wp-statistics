<?php

namespace WP_Statistics\Service\CustomEvent;

use Exception;
use WP_Statistics\Models\EventsModel;
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
        // Listen for batch tracking custom events
        add_action('wp_statistics_record_custom_event', [$this, 'recordEvent'], 10, 2);
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

            // Create visitor profile for the current request
            $visitorProfile = new VisitorProfile();

            // Parse event data
            $eventDataParser = new CustomEventDataParser($eventName, $eventData, $visitorProfile);
            $parsedData = $eventDataParser->getParsedData();

            // Insert event into the database
            $eventsModel = new EventsModel();
            $eventsModel->insertEvent($parsedData);

        } catch (Exception $e) {
            // Log error but don't break the request
            error_log('WP Statistics: Failed to record custom event - ' . $e->getMessage());
        }
    }
}
