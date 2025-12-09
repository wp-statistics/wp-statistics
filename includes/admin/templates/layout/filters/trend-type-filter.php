<?php
if (!defined('ABSPATH')) exit;

use WP_Statistics\Components\View;

$args = [
    'title' => esc_html__('Trend type', 'wp-statistics'),
    'type'  => 'trend_type'
];

View::load("components/objects/header-filter-select", $args);
