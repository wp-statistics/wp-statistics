<?php

namespace WP_Statistics\Service\Tracking\Controllers;

use WP_Statistics\Abstracts\BaseTrackerController;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Globals\Option;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Utils\Request;
use Exception;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Batch Tracking Controller
 *
 * Handles batched engagement events from the client-side tracker.
 * Receives multiple events in a single request, reducing HTTP overhead
 * and ensuring reliable data delivery during page exits.
 *
 * The session is identified server-side using the visitor's IP hash,
 * not passed from the client (to prevent spoofing).
 *
 * Expected payload structure:
 * {
 *     "engagement_time": 5000,
 *     "events": [
 *         { "type": "custom_event", "data": { "event_name": "click", "event_data": {} } }
 *     ]
 * }
 *
 * @since 15.0.0
 */
class BatchTracking extends BaseTrackerController
{
    /**
     * Endpoint slug for batch tracking.
     *
     * @var string
     */
    protected const ENDPOINT_BATCH = 'batch';

    /**
     * AJAX action name for batch tracking.
     *
     * @var string
     */
    public const BATCH_ACTION = 'batch';

    /**
     * Initialize the batch tracking controller.
     *
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register REST API and AJAX endpoints for batch tracking.
     *
     * @return void
     * @since 15.0.0
     */
    public function register()
    {
        if (!Option::getValue('use_cache_plugin')) {
            return;
        }

        // Register REST API route (when ad blocker bypass is disabled)
        if (!Option::getValue('bypass_ad_blockers', false)) {
            add_action('rest_api_init', [$this, 'registerRoutes']);
        }

        // Register AJAX callback (when ad blocker bypass is enabled)
        if (Option::getValue('bypass_ad_blockers', false)) {
            add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
        }
    }

    /**
     * Register REST API routes for batch tracking.
     *
     * @return void
     * @since 15.0.0
     */
    public function registerRoutes()
    {
        register_rest_route($this->namespace, '/' . self::ENDPOINT_BATCH, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'processBatch'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Register AJAX callbacks for batch tracking.
     *
     * @param array $list Existing AJAX endpoints list
     * @return array Updated list with batch endpoint
     * @since 15.0.0
     */
    public function registerAjaxCallbacks($list)
    {
        $list[] = [
            'class'  => $this,
            'action' => self::BATCH_ACTION,
            'public' => true,
        ];

        return $list;
    }

    /**
     * Process batch request from REST API.
     *
     * @param WP_REST_Request $request The REST API request object
     * @return WP_REST_Response The REST API response
     * @since 15.0.0
     */
    public function processBatch(WP_REST_Request $request)
    {
        try {
            $body = $request->get_body();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(__('Invalid JSON payload', 'wp-statistics'), 400);
            }

            $result = $this->processEvents($data);

            $response = rest_ensure_response([
                'status'    => true,
                'processed' => $result['processed'],
                'errors'    => $result['errors'],
            ]);

        } catch (Exception $e) {
            $response = rest_ensure_response([
                'status' => false,
                'data'   => $e->getMessage(),
            ]);
            $response->set_status($e->getCode() ?: 400);
        }

        $response->set_headers([
            'Cache-Control' => 'no-cache',
        ]);

        return $response;
    }

    /**
     * Handle batch request via AJAX.
     *
     * @return void
     * @since 15.0.0
     */
    public function batch_action_callback()
    {
        if (!Request::isFrom('ajax')) {
            return;
        }

        try {
            // Get batch_data from POST request (JSON string)
            $batchData = isset($_POST['batch_data']) ? sanitize_text_field(wp_unslash($_POST['batch_data'])) : '';

            if (empty($batchData)) {
                throw new Exception(__('Missing batch data', 'wp-statistics'), 400);
            }

            $data = json_decode($batchData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(__('Invalid JSON payload', 'wp-statistics'), 400);
            }

            $result = $this->processEvents($data);

            wp_send_json([
                'status'    => true,
                'processed' => $result['processed'],
                'errors'    => $result['errors'],
            ]);

        } catch (Exception $e) {
            wp_send_json([
                'status' => false,
                'data'   => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Process batch payload.
     *
     * Handles session engagement update and processes any custom events.
     * The session is identified server-side using the visitor's IP hash.
     *
     * @param array $data Batch payload containing engagement_time and events
     * @return array Processing result with counts
     * @since 15.0.0
     */
    protected function processEvents($data)
    {
        $processed = 0;
        $errors    = [];

        $engagementTime = isset($data['engagement_time']) ? (int) $data['engagement_time'] : 0;

        // Update session engagement if engagement_time is provided
        if ($engagementTime > 0) {
            try {
                // Get visitor's IP hash to identify their session
                $visitorProfile = new VisitorProfile();
                $visitorHash    = $visitorProfile->getProcessedIPForStorage();

                // Find the visitor's active session
                $sessionModel = new SessionModel();
                $session      = $sessionModel->getActiveSessionByHash($visitorHash);

                if ($session && !empty($session->ID)) {
                    $this->updateSessionEngagement((int) $session->ID, $engagementTime);
                    $processed++;
                }
            } catch (Exception $e) {
                $errors[] = sprintf(
                    __('Session update failed: %s', 'wp-statistics'),
                    $e->getMessage()
                );
            }
        }

        // Process custom events if provided
        if (!empty($data['events']) && is_array($data['events'])) {
            foreach ($data['events'] as $index => $event) {
                try {
                    $this->processEvent($event);
                    $processed++;
                } catch (Exception $e) {
                    $errors[] = sprintf(
                        __('Event %d (%s): %s', 'wp-statistics'),
                        $index,
                        $event['type'] ?? 'unknown',
                        $e->getMessage()
                    );
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
     * @param array $event Event data
     * @return void
     * @throws Exception If event processing fails
     * @since 15.0.0
     */
    protected function processEvent($event)
    {
        if (empty($event['type'])) {
            throw new Exception(__('Missing event type', 'wp-statistics'));
        }

        $eventData = $event['data'] ?? [];

        switch ($event['type']) {
            case 'custom_event':
                $this->handleCustomEvent($eventData);
                break;

            default:
                // Unknown event type - log but don't fail
                break;
        }
    }

    /**
     * Handle custom event (wp_statistics_event).
     * Forwards to the custom event recording system.
     *
     * @param array $data Event data containing event_name and event_data
     * @return void
     * @since 15.0.0
     */
    protected function handleCustomEvent($data)
    {
        if (empty($data['event_name'])) {
            return;
        }

        $eventName = sanitize_text_field($data['event_name']);
        $eventData = isset($data['event_data']) ? $data['event_data'] : [];

        // If event_data is a JSON string, decode it
        if (is_string($eventData)) {
            $eventData = json_decode($eventData, true) ?: [];
        }

        /**
         * Allow other plugins/addons to handle the custom event.
         * This fires the same action that the direct custom event endpoint uses.
         *
         * @param string $eventName The event name
         * @param array  $eventData The event data
         * @since 15.0.0
         */
        do_action('wp_statistics_custom_event_batch', $eventName, $eventData);

        /**
         * Also fire the standard custom event action for compatibility.
         *
         * @param string $eventName The event name
         * @param array  $eventData The event data
         */
        do_action('wp_statistics_record_custom_event', $eventName, $eventData);
    }

    /**
     * Update session with engagement data.
     *
     * @param int $sessionId Session ID to update
     * @param int $engagementTimeMs Engagement time in milliseconds
     * @return void
     * @since 15.0.0
     */
    protected function updateSessionEngagement($sessionId, $engagementTimeMs)
    {
        // Convert milliseconds to seconds
        $engagementTimeSec = (int) round($engagementTimeMs / 1000);

        // Create a record object with the session ID
        $record = (object) ['ID' => $sessionId];

        // Use RecordFactory to update the session
        RecordFactory::session($record)->update([
            'ended_at' => DateTime::getUtc(),
            'duration' => $engagementTimeSec,
        ]);
    }

    /**
     * Get the base route for this tracking controller.
     *
     * @return string The REST API namespace
     * @since 15.0.0
     */
    public function getRoute()
    {
        return $this->namespace . '/' . self::ENDPOINT_BATCH;
    }
}
