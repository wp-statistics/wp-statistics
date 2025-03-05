<?php

use WP_Statistics\Components\View;

View::load("components/tables/search-queries");

View::load("components/objects/no-data", [
    'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-3.svg',
    'title' => __('Data coming soon!', 'wp-statistics')
]);
