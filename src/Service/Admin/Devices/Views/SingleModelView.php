<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_STATISTICS\Menus;

class SingleModelView extends SingleView
{
    public function __construct()
    {
        parent::__construct([
            'key'             => 'model',
            'back_title'      => esc_html__('Models', 'wp-statistics'),
            'back_url'        => Menus::admin_url('devices', ['tab' => 'models']),
            'first_col_title' => esc_html__('Version', 'wp-statistics'),
        ]);
    }
}
