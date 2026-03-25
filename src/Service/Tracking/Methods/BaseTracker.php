<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Service\Tracking\Core\Tracker;
use Exception;

/**
 * Abstract base for tracking methods.
 *
 * Every tracking method (REST, AJAX, Hybrid Mode) extends this so that
 * TrackerManager can delegate without special-casing any method.
 *
 * @since 15.0.0
 */
abstract class BaseTracker
{
    /**
     * Register the method's endpoints with WordPress.
     */
    abstract public function register(): void;

    /**
     * Configuration the JS tracker needs to send hits via this method.
     *
     * @return array{hitEndpoint: string, batchEndpoint: string}
     */
    abstract public function getTrackerConfig(): array;

    /**
     * Tracker method type identifier for the `wp_statistics_tracker_enabled` filter.
     *
     * @return string e.g. 'ajax', 'rest', 'hybrid'
     */
    abstract public function getMethodType(): string;

    /**
     * Diagnostic route string for health checks.
     */
    abstract public function getRoute(): ?string;

    /**
     * Called when this method becomes the active tracking method.
     */
    public function activate(): void {}

    /**
     * Called when this method is no longer the active tracking method.
     */
    public function deactivate(): void {}

    /**
     * Parse batch payload and delegate to Tracker + event hook.
     *
     * @param string|null $rawData Raw JSON from the request.
     * @return array{processed: int, errors: string[]}
     * @throws Exception On missing or invalid payload.
     */
    protected function processBatch(?string $rawData): array
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
