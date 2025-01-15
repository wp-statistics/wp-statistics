<?php

use WP_Statistics\Components\View;
use WP_Statistics\Utils\Request;

$activeFilters = Request::has('referrer');

$classes[] = $activeFilters ? 'wp-referral-filter--active' : '';
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