<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;

$isLocked = apply_filters('wp_statistics_referrals_campaigns_locked', true);

if ($isLocked) {
    $copy = AddonCatalog::get('wp-statistics-marketing');
    $args = [
        'page_title' => $copy['headline'],
        'benefits'   => $copy['benefits'],
        'addon_name'         => esc_html__('Marketing', 'wp-statistics'),
        'addon_slug'         => 'wp-statistics-marketing',
        'campaign'           => 'marketing',
        'images'             => ['marketing-lock.png', 'campaign-lock.png', 'goals-lock.png'],
    ];
    View::load("pages/lock-page", $args);
}