<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
use WP_Statistics\Service\CustomEvent\CustomEventDataParser;
use WP_Statistics\Models\EventsModel;

if (!function_exists('wp_statistics_event')) {
    function wp_statistics_event($eventName, $eventData = []) {
        try {
            // Parse event data (trusted server-side caller)
            $eventDataParser = new CustomEventDataParser($eventName, $eventData, null, true);
            $parsedData      = $eventDataParser->getParsedData();

            // Insert event into the database
            $eventsModel = new EventsModel();
            $eventsModel->insertEvent($parsedData);
        } catch (\Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
        }
    }
}