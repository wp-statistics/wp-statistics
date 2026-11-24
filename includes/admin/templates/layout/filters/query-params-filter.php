<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;

$isDataPlusActive = Helper::isAddOnActive('data-plus');

if (!$isDataPlusActive):
?>
    <div class="wps-filter-query-params wps-head-filters__item loading disabled">
        <?php
        View::load('components/objects/premium-locked-button', [
            'label' => __('Query Parameter : All', 'wp-statistics'),
            'addon' => 'wp-statistics-data-plus',
        ]);
        ?>
    </div>
<?php
    return;
endif;
$args = [
    'title' => __('Query Parameter', 'wp-statistics'),
    'type'  => 'query-params'
];

View::load("components/objects/header-filter-select", $args);
