<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;

class LockedDataPlusView extends BaseView
{
    public function render()
    {
        $args = [
            'page_title'        => esc_html__('Unlock Detailed Insights', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => '',
            'more_title'        => esc_html__('Learn More', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Go Premium', 'wp-statistics'),
            'images'            => [],
            'description'       => ''
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}
