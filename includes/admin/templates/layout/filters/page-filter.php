<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('Page', 'wp-statistics'),
    'type'  => 'page'
];

View::load("components/objects/header-filter-select", $args);
