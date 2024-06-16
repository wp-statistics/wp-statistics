<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Exception\SystemErrorException;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

class SinglePlatformView extends BaseView
{
    public function __construct()
    {
        if (!Request::has('platform')) {
            throw new SystemErrorException(
                esc_html__('Invalid platform provided!', 'wp-statistics')
            );
        }
    }

    public function render()
    {
        $args = [
            'title'     => sprintf(esc_html__('%s Report', 'wp-statistics'), Request::get('platform')),
            'backUrl'   => Menus::admin_url('devices', ['tab' => 'platforms']),
            'tooltip'   => esc_html__('Tooltip', 'wp-statistics'),
            'backTitle' => esc_html__('Devices', 'wp-statistics'),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/devices/single-locked', 'layout/footer'], $args);
    }
}
