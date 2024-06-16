<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Exception\SystemErrorException;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

class SingleModelView extends BaseView
{
    public function __construct()
    {
        if (!Request::has('model')) {
            throw new SystemErrorException(
                esc_html__('Invalid model provided!', 'wp-statistics')
            );
        }
    }

    public function render()
    {
        $args = [
            'title'     => sprintf(esc_html__('%s Report', 'wp-statistics'), Request::get('model')),
            'backUrl'   => Menus::admin_url('devices', ['tab' => 'models']),
            'tooltip'   => esc_html__('Tooltip', 'wp-statistics'),
            'backTitle' => esc_html__('Devices', 'wp-statistics'),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/devices/single-locked', 'layout/footer'], $args);
    }
}
