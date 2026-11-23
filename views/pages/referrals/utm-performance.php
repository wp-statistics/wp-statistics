<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_referrals_utm-performance_locked', true);

if ($isLocked) {
    $args = [
        'page_title' => __('See how every UTM link performs', 'wp-statistics'),
        'benefits'   => [
            __('Visitors from each UTM source, medium and campaign', 'wp-statistics'),
            __('Build UTM links and see their results', 'wp-statistics'),
            __('Goals that count your conversions', 'wp-statistics'),
        ],
        'addon_name'         => esc_html__('Marketing', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-marketing',
        'campaign'           => 'marketing',
        'images'             => ['marketing-lock.png', 'campaign-lock.png', 'goals-lock.png'],
    ];
    View::load("pages/lock-page", $args);
}