<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$args = [
    'title' => __('User Role', 'wp-statistics'),
    'type'  => 'user-role'
];

View::load("components/objects/header-filter-select", $args);
