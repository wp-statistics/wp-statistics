<?php
use WP_Statistics\Components\View;

$activeFilters = 0;

foreach ($_GET as $params_key => $params_item) {
    if (in_array($params_key, ['referrer'])) {
        $activeFilters++;
    }
}

$classes[] = $activeFilters > 0 ? 'wp-referral-filter--active' : '';
$classes[] = is_rtl() ? 'wps-pull-left' : 'wps-pull-right';
?>

<?php

$args = [
    'filter_type'   => 'referral',
    'classes'       => implode(' ', $classes),
    'activeFilters' => $activeFilters,
 ];

View::load("components/objects/header-filter-button", $args);
?>