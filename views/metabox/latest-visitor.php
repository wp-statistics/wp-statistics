<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by last view.
View::load('components/tables/visitor-table', [
    'visitors' => !empty($data) ? $data : [],
    'sort'     => 'last',
    'empty'    => ['message' => __('No visitors recorded yet.', 'wp-statistics'), 'range' => false, 'icon' => 'activity'],
]);
