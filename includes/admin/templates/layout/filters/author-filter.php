<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('Author', 'wp-statistics'),
    'type'  => 'author'
];

View::load("components/objects/header-filter-select", $args);
