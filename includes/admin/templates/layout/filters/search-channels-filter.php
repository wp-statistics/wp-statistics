<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('Source Category', 'wp-statistics'),
    'type'  => 'search-channels'
];

View::load("components/objects/header-filter-select", $args);
