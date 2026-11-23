<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_referrals_campaigns_locked', true);

if ($isLocked) {
    $args = [
        'page_title' => __('See which campaigns bring your visitors', 'wp-statistics'),
        'benefits'   => [
            __('Campaign and UTM link reports', 'wp-statistics'),
            __('Google Search keywords, clicks and impressions', 'wp-statistics'),
            __('Goals that count your conversions', 'wp-statistics'),
        ],
        'addon_name'         => esc_html__('Marketing', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-marketing',
        'campaign'           => 'marketing',
        'images'             => ['marketing-lock.png', 'campaign-lock.png', 'goals-lock.png'],
    ];
    View::load("pages/lock-page", $args);
}