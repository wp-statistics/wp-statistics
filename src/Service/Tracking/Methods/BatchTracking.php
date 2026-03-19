<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Ip;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;
use Exception;
use WP_REST_Server;
use WP_REST_Request;

/**
 * Independent batch tracking handler.
 *
 * Always registered regardless of the active hit transport method (AJAX, REST,
 * Direct File). Provides both AJAX and REST endpoints:
 *   - AJAX: used by the default JS tracker (ad-blocker safe)
 *   - REST: used by headless/API consumers
 *
 * @since 15.0.0
 */
class BatchTracking
{
    public const BATCH_ACTION = 'batch';

    private const API_NAMESPACE = 'wp-statistics/v2';

    /**
     * Register AJAX and REST batch endpoints.
     */
    public function register(): void
    {
        Ajax::register(self::BATCH_ACTION, [$this, 'ajaxCallback']);
        add_action('rest_api_init', [$this, 'registerRestRoute']);
    }

    /**
     * Batch endpoint URL for JS config.
     *
     * Always uses AJAX — ad-blocker safe.
     *
     * @return string Absolute URL to the batch endpoint.
     */
    public function getBatchEndpoint(): string
    {
        return admin_url('admin-ajax.php') . '?action=wp_statistics_' . self::BATCH_ACTION;
    }

    /**
     * Handle batch request via AJAX.
     */
    public function ajaxCallback(): void
    {
        if (!Request::isFrom('ajax')) {
            return;
        }

        try {
            $batchData = isset($_POST['batch_data']) ? wp_unslash($_POST['batch_data']) : null;
            $result    = self::parseAndProcess($batchData);

            wp_send_json([
                'status'    => true,
                'processed' => $result['processed'],
                'errors'    => $result['errors'],
            ]);
        } catch (Exception $e) {
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Register REST route: /wp-json/wp-statistics/v2/batch
     *
     * Provides batch access for headless/API consumers that don't use admin-ajax.php.
     */
    public function registerRestRoute(): void
    {
        register_rest_route(self::API_NAMESPACE, '/batch', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'restCallback'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle batch request via REST.
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function restCallback(WP_REST_Request $request)
    {
        try {
            $bodyParams = $request->get_body_params();
            $result     = self::parseAndProcess($bodyParams['batch_data'] ?? null);

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

        $response->set_headers(['Cache-Control' => 'no-cache']);

        return $response;
    }

    /**
     * Parse a raw batch_data JSON string and process events.
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
    private function processEvents(array $data): array
    {
        $processed = 0;
        $errors    = [];

        $engagementTime = isset($data['engagement_time']) ? (int) $data['engagement_time'] : 0;

        if ($engagementTime > 0) {
            try {
                $visitorHash      = Ip::hash();
                $thirtyMinutesAgo = DateTime::getUtc('Y-m-d H:i:s', '-30 minutes');

                $session = Query::select('sessions.ID')
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
     * Update session with engagement data using atomic accumulation.
     *
     * Uses SQL COALESCE + addition to atomically increment the duration
     * instead of overwriting it. JS sends incremental deltas (resets after
     * each flush), so each call adds to the existing total.
     *
     * @param int $sessionId        Session ID to update.
     * @param int $engagementTimeMs Engagement time in milliseconds.
     */
    private function updateSessionEngagement(int $sessionId, int $engagementTimeMs): void
    {
        $engagementTimeSec = (int) round($engagementTimeMs / 1000);

        if ($engagementTimeSec < 1) {
            return;
        }

        Query::update('sessions')
            ->set(['ended_at' => DateTime::getUtc()])
            ->setRaw('duration', 'COALESCE(`duration`, 0) + ' . intval($engagementTimeSec))
            ->where('ID', '=', $sessionId)
            ->execute();
    }
}
