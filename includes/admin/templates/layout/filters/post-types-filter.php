<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('Post Type', 'wp-statistics'),
    'type'  => 'post-types'
];

View::load("components/objects/header-filter-select", $args);
