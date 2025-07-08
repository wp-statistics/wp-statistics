<?php

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_pages_entry-pages_locked', true);

if ($isLocked) {
    $args = [
        'page_title'         => esc_html__('Data Plus: Advanced Analytics for Deeper Insights', 'wp-statistics'),
        'page_second_title'  => esc_html__('WP Statistics Premium: Beyond Just Data Plus', 'wp-statistics'),
        'addon_name'         => esc_html__('Data Plus', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-data-plus',
        'header_end'         => true,
        'campaign'           => 'data-plus',
        'more_title'         => esc_html__('Learn More About Data Plus', 'wp-statistics'),
        'premium_btn_title'  => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
        'images'             => ['data-plus-advanced-filtering.png', 'data-plus-category.png', 'data-plus-comparison-widget.png', 'data-plus-download-tracker-recents.png'],
        'description'        => esc_html__('Data Plus is a premium add-on for WP Statistics that unlocks powerful analytics features, providing a complete view of your site’s performance. Take advantage of advanced tools that help you understand visitor behavior, enhance your content, and track engagement on a new level. With Data Plus, you can make data-driven decisions to grow your site more effectively.', 'wp-statistics'),
        'second_description' => esc_html__('When you upgrade to WP Statistics Premium, you don’t just get Data Plus — you gain access to all premium add-ons, delivering detailed insights and tools for every aspect of your site.', 'wp-statistics')
    ];
    View::load("pages/lock-page", $args);
}