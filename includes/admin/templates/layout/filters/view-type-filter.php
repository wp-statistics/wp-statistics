<?php
if (!defined('ABSPATH')) exit;

use WP_Statistics\Components\View;

$args = [
    'title' => esc_html__('View', 'wp-statistics'),
    'type'  => 'view_type'
];

View::load("components/objects/header-filter-select", $args);