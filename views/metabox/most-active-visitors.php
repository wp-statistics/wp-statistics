<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by views.
View::load('components/tables/visitor-table', [
    'visitors' => !empty($data) ? $data : [],
    'sort'     => 'views',
    'empty'    => ['date' => $filters['date'] ?? []],
]);
