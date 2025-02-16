<?php

use WP_Statistics\Components\View;

$activeFilters = 0;

foreach ($_GET as $params_key => $params_item) {
    if (! empty($params_item) && in_array($params_key, ['utm_source', 'utm_medium', 'utm_campaign', 'referrer', 'pid'])) {
        $activeFilters++;
    }
}
$classes[] = 'wps-modal-filter';
$classes[] = $activeFilters > 0 ? 'wp-modal-filter--active' : '';
$classes[] = is_rtl() ? 'wps-pull-left' : 'wps-pull-right';

$args = [
    'filter_type'   => 'wps-modal',
    'classes'       => implode(' ', $classes),
    'activeFilters' => $activeFilters,
];

View::load("components/objects/header-filter-button", $args);
?>