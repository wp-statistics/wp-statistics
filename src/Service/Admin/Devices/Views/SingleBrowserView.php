<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_STATISTICS\Menus;

class SingleBrowserView extends SingleView
{
    public function __construct()
    {
        parent::__construct([
            'key'             => 'browser',
            'back_title'      => esc_html__('Browsers', 'wp-statistics'),
            'back_url'        => Menus::admin_url('devices', ['tab' => 'browsers']),
            'first_col_title' => esc_html__('Version', 'wp-statistics'),
        ]);
    }
}
