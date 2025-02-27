<?php
use WP_Statistics\Components\View;

$isLocked = apply_filters('wp_statistics_referrals_utm-performance_locked', true);

if ($isLocked) {
    // TODO: Replace with "components/locked-marketing-page"
    View::load("components/locked-page", [
        'campaign'  => '',
        'src'       => '',
    ]);
}