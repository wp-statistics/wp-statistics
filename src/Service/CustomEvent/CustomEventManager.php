<?php

namespace WP_Statistics\Service\CustomEvent;

use Exception;
use WP_STATISTICS\Exclusion;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Ajax;
use WP_Statistics\Components\Assets;
use WP_Statistics\Models\EventsModel;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Marketing\Services\CustomEvent\CustomEventHelper;

class CustomEventManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerAjaxCallbacks']);
        add_action('wp_enqueue_scripts', [$this, 'localizeScripts'], 20);
    }

    public function localizeScripts()
    {
        Assets::localize('tracker', 'Marketing_Event', [
            'customEventAjaxUrl' => add_query_arg(['action' => 'wp_statistics_custom_event', 'nonce' => wp_create_nonce('wp_statistics_custom_event')], admin_url('admin-ajax.php')),
        ]);
    }

    public function registerAjaxCallbacks()
    {
        Ajax::register('custom_event', [$this, 'customEventCallback']);
    }

    public function customEventCallback()
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