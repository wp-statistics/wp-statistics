<?php
namespace WP_Statistics\Service\CustomEvent;

use Exception;
use WP_Statistics\Models\EventsModel;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Ajax;
use WP_STATISTICS\Exclusion;
use WP_Statistics\Service\Analytics\VisitorProfile;

class CustomEventActions
{
    public function register()
    {
        Ajax::register('custom_event', [$this, 'insertCustomEvent']);
    }

    public function insertCustomEvent()
    {
        try {
            $nonce = Request::get('nonce');

            if (!wp_verify_nonce($nonce, 'wp_statistics_custom_event')) {
                throw new Exception(esc_html__('Access denied.', 'wp-statistics-marketing'));
            }

            $visitorProfile = new VisitorProfile();

            $exclusion = Exclusion::check($visitorProfile);
            if ($exclusion['exclusion_match'] === true) {
                Exclusion::record($exclusion);

                throw new Exception($exclusion['exclusion_reason']);
            }

            $eventName = Request::get('event_name');
            $eventData = Request::get('event_data');

            // Decode the json event data
            $eventData = json_decode(stripslashes($eventData), true);

            // Parse event data
            $eventDataParser = new CustomEventDataParser($eventName, $eventData, $visitorProfile);
            $parsedData      = $eventDataParser->getParsedData();

            // Insert event into the database
            $eventsModel = new EventsModel();
            $eventsModel->insertEvent($parsedData);

            wp_send_json_success(['message' => esc_html__('Event recorded successfully.', 'wp-statistics')], 200);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 200);
        }

        exit;
    }
}