<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_pages_exit-pages_locked', true);

if ($isLocked) {
    $args = [
        'page_title' => __('See where visitors leave your site', 'wp-statistics'),
        'benefits'   => [
            __('Top exit pages and their traffic', 'wp-statistics'),
            __('Entry pages and search terms', 'wp-statistics'),
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