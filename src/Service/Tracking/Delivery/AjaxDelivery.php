<?php

namespace WP_Statistics\Service\Tracking\Delivery;

use WP_Statistics\Abstracts\BaseDeliveryMethod;
use WP_Statistics\Service\Tracking\Pipeline\Tracker;
use WP_Statistics\Service\Tracking\Pipeline\BatchProcessor;
use WP_Statistics\Utils\Request;
use Exception;

/**
 * AJAX delivery method.
 *
 * Routes hits and batch events through admin-ajax.php,
 * which bypasses ad blockers that target REST API URLs.
 *
 * @since 15.0.0
 */
class AjaxDelivery extends BaseDeliveryMethod
{
    public const HIT_ACTION   = 'hit_record';
    public const BATCH_ACTION = 'batch';

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxCallbacks']);
        add_filter('wp_statistics_js_localized_arguments', [$this, 'addLocalizedArguments']);
    }

    /**
     * {@inheritDoc}
     */
    public function getHitUrl(): string
    {
        return admin_url('admin-ajax.php');
    }

    /**
     * {@inheritDoc}
     */
    public function getBatchUrl(): string
    {
        // AJAX batch URL is built client-side from ajaxUrl
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): ?string
    {
        return admin_url('admin-ajax.php');
    }

    /**
     * Add tracking configuration to the localized JavaScript object.
     *
     * @param array $args Existing localized arguments.
     * @return array
     */
    public function addLocalizedArguments(array $args): array
    {
        $args['requestUrl'] = get_site_url();
        $args['hit']        = ['action' => 'wp_statistics_' . self::HIT_ACTION];

        return $args;
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
