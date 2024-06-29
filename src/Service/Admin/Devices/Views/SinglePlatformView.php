<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_STATISTICS\Menus;

class SinglePlatformView extends SingleView
{
    public function __construct()
    {
        parent::__construct([
            'key'             => 'platform',
            'back_title'      => esc_html__('Platforms', 'wp-statistics'),
            'back_url'        => Menus::admin_url('devices', ['tab' => 'platforms']),
            'first_col_title' => esc_html__('Version', 'wp-statistics'),
        ]);
    }
}
