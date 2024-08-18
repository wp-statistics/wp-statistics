<?php

namespace WP_Statistics\Service\Admin\Visitors\Views;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;

class SingleVisitorView extends BaseView
{
    public function __construct()
    {

    }

    public function render()
    {
        $args = [
            'title'          => esc_html__('Visitor Report - User ID: ', 'wp-statistics'),
            'tooltip'        => esc_html__('Visitor Report', 'wp-statistics'),
            'backUrl'        => Menus::admin_url('visitors'),
            'backTitle'      => esc_html__('Visitor and Views Report', 'wp-statistics'),
            'searchBoxTitle' => esc_html__('IP, Hash, Username, or Email', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load('pages/visitors/single-visitor');
        Admin_Template::get_template(['layout/footer'], $args);
    }
}