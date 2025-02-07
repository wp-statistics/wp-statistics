<?php

use WP_Statistics\Components\View;

$args = [
    'title'  => __('Taxonomy', 'wp-statistics'),
    'type'   => 'taxonomy'
];

View::load("components/objects/header-filter-select", $args);
