<?php

use WP_Statistics\Components\View;
use WP_Statistics\Utils\Request;

$activeFilters = Request::has('referrer');

$classes[] = 'wps-modal-filter';
$classes[] = $activeFilters ? 'wp-modal-filter--active' : '';
$classes[] = is_rtl() ? 'wps-pull-left' : 'wps-pull-right';

$args = [
    'filter_type'   => 'wps-modal',
    'classes'       => implode(' ', $classes),
    'activeFilters' => $activeFilters,
];

View::load("components/objects/header-filter-button", $args);
?>