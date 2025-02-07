<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('Post Type', 'wp-statistics'),
    'type'  => 'post-type'
];

View::load("components/objects/header-filter-select", $args);
