<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_visitors_search-terms_locked', true);

if ($isLocked) {
    $args = [
        'page_title' => __('See what visitors search for on your site', 'wp-statistics'),
        'benefits'   => [
            __('What visitors search for on your site', 'wp-statistics'),
            __('Top entry and exit pages', 'wp-statistics'),
            __('Link clicks and file downloads', 'wp-statistics'),
        ],
        'addon_name'         => esc_html__('Data Plus', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-data-plus',
        'campaign'           => 'data-plus',
        'images'             => ['data-plus-advanced-filtering.png', 'data-plus-category.png', 'data-plus-comparison-widget.png', 'data-plus-download-tracker-recents.png'],
    ];
    View::load("pages/lock-page", $args);
}