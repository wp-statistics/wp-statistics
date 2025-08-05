<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('Campaign', 'wp-statistics'),
    'type'  => 'utm_params'
];

View::load("components/objects/header-filter-select", $args);
