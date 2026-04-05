<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Tracking\Core\RateLimiter;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Utils\Request;
use Exception;

/**
 * AJAX tracking method.
 *
 * Routes hits and batch data through admin-ajax.php,
 * which bypasses ad blockers that target REST API URLs.
 *
 * @since 15.0.0
 */
class AjaxTracker extends BaseTracker
{
    public const ACTION       = 'collect';
    public const BATCH_ACTION = 'batch';

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Ajax::register(self::ACTION, [$this, 'hitCallback']);
        Ajax::register(self::BATCH_ACTION, [$this, 'batchCallback']);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackerConfig(): array
    {
        return [
            'hitEndpoint'   => '?action=wp_statistics_' . self::ACTION,
            'batchEndpoint' => '?action=wp_statistics_' . self::BATCH_ACTION,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodType(): string
    {
        return 'ajax';
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): ?string
    {
        return admin_url('admin-ajax.php');
    }

    /**
     * Handle hit recording via AJAX.
     */
    public function hitCallback(): void
    {
        if (!Request::isFrom('ajax')) {
            return;
        }

        try {
            (new Tracker())->record();
            wp_send_json(['status' => true]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            if ($code === 429) {
                header('Retry-After: ' . RateLimiter::getTimeWindow());
            }
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $code);
        }
    }

    /**
     * Handle batch tracking via AJAX.
     */
    public function batchCallback(): void
    {
        if (!Request::isFrom('ajax')) {
            return;
        }

        try {
            $batchData = isset($_POST['batch_data']) ? wp_unslash($_POST['batch_data']) : null;
            $result    = $this->processBatch($batchData);

            wp_send_json([
                'status'    => true,
                'processed' => $result['processed'],
                'errors'    => $result['errors'],
            ]);
        } catch (Exception $e) {
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
