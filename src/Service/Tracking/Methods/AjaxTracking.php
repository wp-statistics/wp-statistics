<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Service\Tracking\Pipeline\Tracker;
use WP_Statistics\Service\Tracking\Pipeline\BatchProcessor;
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
        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
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
     * Register hit and batch AJAX callbacks.
     *
     * @param array $list Existing AJAX endpoints list.
     * @return array
     */
    public function registerAjaxCallbacks(array $list): array
    {
        $list[] = [
            'class'  => $this,
            'action' => self::HIT_ACTION,
            'public' => true,
        ];

        $list[] = [
            'class'  => $this,
            'action' => self::BATCH_ACTION,
            'public' => true,
        ];

        return $list;
    }

    /**
     * Handle hit recording via AJAX.
     */
    public function hit_record_action_callback(): void
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
    public function batch_action_callback(): void
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
