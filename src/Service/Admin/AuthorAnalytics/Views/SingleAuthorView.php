<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use Exception;
use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;

class SingleAuthorView extends BaseView
{
    public function render()
    {
        $args = [
            'page_title' => __('See how each author performs', 'wp-statistics'),
            'benefits'   => [
                __('Views and posts for every author', 'wp-statistics'),
                __('Each author\'s top posts', 'wp-statistics'),
                __('Engagement trends over time', 'wp-statistics'),
            ],
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'images'            => ['data-plus-single-author.png'],
            'export'            => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}