<?php 
namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;

class SingleView extends BaseView
{
    public function render()
    {
        $args = [
            'title'     => esc_html__('Germany Traffic Report', 'wp-statistics'),
            'backUrl'   => Menus::admin_url('geographic'),
            'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
            'backTitle' => esc_html__('Geographic', 'wp-statistics'),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/geographic/single-locked', 'layout/footer'], $args);
    }
}