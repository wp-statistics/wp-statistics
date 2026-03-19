<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Utils\Request;
use Exception;
use WP_REST_Server;
use WP_REST_Request;

/**
 * Thin endpoint handler for batch tracking.
 *
 * Always registered regardless of the active hit transport method (AJAX, REST,
 * Hybrid Mode). Provides both AJAX and REST endpoints:
 *   - AJAX: used by the default JS tracker (ad-blocker safe)
 *   - REST: used by headless/API consumers
 *
 * Parses the batch payload, delegates engagement to {@see Tracker::recordEngagement()},
 * and dispatches raw events via the `wp_statistics_batch_events` hook.
 *
 * @since 15.0.0
 */
class BatchEndpoint
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
            $result    = $this->process($batchData);

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
            $result     = $this->process($bodyParams['batch_data'] ?? null);

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
     * Parse payload and delegate to Tracker + event hook.
     *
     * @param string|null $rawData Raw JSON from the request.
     * @return array{processed: int, errors: string[]}
     * @throws Exception On missing or invalid payload.
     */
    private function process(?string $rawData): array
    {
        if (empty($rawData)) {
            throw new Exception('Missing batch data', 400);
        }

        $data = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON payload', 400);
        }

        $processed      = 0;
        $errors         = [];
        $engagementTime = isset($data['engagement_time']) ? (int) $data['engagement_time'] : 0;
        $events         = !empty($data['events']) && is_array($data['events']) ? $data['events'] : [];

        if ($engagementTime > 0) {
            try {
                if ((new Tracker())->recordEngagement($engagementTime)) {
                    $processed++;
                }
            } catch (Exception $e) {
                $errors[] = 'Session update failed: ' . $e->getMessage();
            }
        }

        if (!empty($events)) {
            do_action('wp_statistics_batch_events', $events);
            $processed += count($events);
        }

        return ['processed' => $processed, 'errors' => $errors];
    }
}
