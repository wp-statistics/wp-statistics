<?php
use WP_Statistics\Components\View;

$args = [
    'title' => esc_html__('LLM', 'wp-statistics'),
    'type'  => 'source_name'
];

View::load("components/objects/header-filter-select", $args);
