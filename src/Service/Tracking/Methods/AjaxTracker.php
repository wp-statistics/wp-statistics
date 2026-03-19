<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Tracking\Core\Tracker;
use WP_Statistics\Utils\Request;
use Exception;

/**
 * AJAX tracking method.
 *
 * Routes hits through admin-ajax.php,
 * which bypasses ad blockers that target REST API URLs.
 *
 * @since 15.0.0
 */
class AjaxTracker extends BaseTracker
{
    public const ACTION = 'collect';

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Ajax::register(self::ACTION, [$this, 'hitCallback']);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackerConfig(): array
    {
        return [
            'baseUrl'      => admin_url('admin-ajax.php'),
            'hitEndpoint'  => '?action=wp_statistics_' . self::ACTION,
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
            wp_send_json(['status' => false, 'data' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
