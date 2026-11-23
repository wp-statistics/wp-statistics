<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_referrals_google-search_locked', true);

if ($isLocked) {
    $args = [
        'page_title' => __('See your Google Search keywords in WordPress', 'wp-statistics'),
        'benefits'   => [
            __('Keywords, clicks and impressions from Search Console', 'wp-statistics'),
            __('Top pages and positions in Google', 'wp-statistics'),
            __('Campaign reports and goals too', 'wp-statistics'),
        ],
        'addon_name'         => esc_html__('Marketing', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-marketing',
        'campaign'           => 'marketing',
        'images'             => ['marketing-lock.png', 'campaign-lock.png', 'goals-lock.png'],
    ];
    View::load("pages/lock-page", $args);
}