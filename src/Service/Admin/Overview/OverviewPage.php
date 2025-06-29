<?php

namespace WP_Statistics\Service\Admin\Overview;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BasePage;
use WP_Statistics\Service\Admin\ModalHandler\Modal;

class OverviewPage extends BasePage
{
    protected $pageSlug = 'overview';

    public function __construct()
    {
        parent::__construct();

        $this->handleDismissWidgets();
    }

    public function render()
    {
        $args = [
            'title'             => esc_html__('Overview', 'wp-statistics'),
            'tooltip'           => esc_html__('Quickly view your website’s traffic and visitor analytics.', 'wp-statistics'),
            'real_time_button'  => true
        ];

        Modal::showOnce('welcome-premium');

        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/overview/overview'], $args);
        Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
    }

    public function handleDismissWidgets()
    {
        if (Request::compare('action', 'wp_statistics_dismiss_widget')) {
            if (!Request::has('widget_id')) return;

            check_admin_referer('wp_statistics_dismiss_widget', 'nonce');

            $widgetId           = Request::get('widget_id');
            $dismissedWidgets   = get_option('wp_statistics_dismissed_widgets', []);

            if (!in_array($widgetId, $dismissedWidgets, true)) {
                $dismissedWidgets[] = $widgetId;
                update_option('wp_statistics_dismissed_widgets', $dismissedWidgets);
            }

            wp_redirect(remove_query_arg(['nonce', 'action', 'widget_id']));
            exit;
        }
    }
}
