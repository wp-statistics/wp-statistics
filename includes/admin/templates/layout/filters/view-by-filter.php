<?php
if (!defined('ABSPATH')) exit;

use WP_Statistics\Components\View;

$args = [
    'title' => esc_html__('View by', 'wp-statistics'),
    'type'  => 'view_by'
];

View::load("components/objects/header-filter-select", $args);