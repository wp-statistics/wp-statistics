<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_pages_entry-pages_locked', true);

if ($isLocked) {
    $args = [
        'page_title' => __('See where visitors enter your site', 'wp-statistics'),
        'benefits'   => [
            __('Top entry pages and their traffic', 'wp-statistics'),
            __('Exit pages and search terms', 'wp-statistics'),
            __('Link clicks and file downloads', 'wp-statistics'),
        ],
        'addon_name'         => esc_html__('Data Plus', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-data-plus',
        'header_end'         => true,
        'campaign'           => 'data-plus',
        'images'             => ['data-plus-advanced-filtering.png', 'data-plus-category.png', 'data-plus-comparison-widget.png', 'data-plus-download-tracker-recents.png'],
    ];
    View::load("pages/lock-page", $args);
}