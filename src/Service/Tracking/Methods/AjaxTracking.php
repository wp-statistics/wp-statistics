<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Service\Tracking\Core\BatchProcessor;
use WP_Statistics\Utils\Request;
use Exception;

/**
 * AJAX tracking method.
 *
 * Routes hits and batch events through admin-ajax.php,
 * which bypasses ad blockers that target REST API URLs.
 *
 * @since 15.0.0
 */
class AjaxTracking extends BaseTracking
{
    public const HIT_ACTION   = 'hit_record';
    public const BATCH_ACTION = 'batch';

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Ajax::register(self::HIT_ACTION, [$this, 'hitCallback']);
        Ajax::register(self::BATCH_ACTION, [$this, 'batchCallback']);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackerConfig(): array
    {
        return [
            'baseUrl'          => admin_url('admin-ajax.php'),
            'hitEndpoint'      => '?action=wp_statistics_' . self::HIT_ACTION,
            'batchEndpoint'    => '?action=wp_statistics_' . self::BATCH_ACTION,
        ];
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
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Handle batch request via AJAX.
     */
    public function batchCallback(): void
    {
        if (!Request::isFrom('ajax')) {
            return;
        }

        try {
            $batchData = isset($_POST['batch_data']) ? wp_unslash($_POST['batch_data']) : null;
            $result    = BatchProcessor::parseAndProcess($batchData);

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
