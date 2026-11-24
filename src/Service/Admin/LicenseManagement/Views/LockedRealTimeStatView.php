<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;

class LockedRealTimeStatView extends BaseView
{
    public function render()
    {
        $copy = AddonCatalog::get('wp-statistics-realtime-stats');
        $args = [
            'page_title' => $copy['headline'],
            'benefits'   => $copy['benefits'],
            'addon_name'         => esc_html__('Real-Time Stats', 'wp-statistics'),
            'addon_slug'         => 'wp-statistics-realtime-stats',
            'campaign'           => 'realtime-stats',
            'images'             => ['realtime-stats.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}
