<?php

use WP_Statistics\Components\View;

$args = [
    'title' => __('User Role', 'wp-statistics'),
    'type'  => 'user-role'
];

View::load("components/objects/header-filter-select", $args);
