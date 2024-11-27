<?php

namespace WP_Statistics\Service\Admin\Overview;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BasePage;

class OverviewPage extends BasePage
{
    protected $pageSlug = 'overview-new';

    public function __construct()
    {
        parent::__construct();
    }

    public function view()
    {
        $args = [
            'title'     => esc_html__('Overview', 'wp-statistics'),
            'tooltip'   => esc_html__('Quickly view your website’s traffic and visitor analytics.', 'wp-statistics')
        ];

        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/overview/overview'], $args);
        Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
    }
}
