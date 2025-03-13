<?php

use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;

$isDataPlusActive = Helper::isAddOnActive('data-plus');

if (!$isDataPlusActive):
?>
    <div class="wps-filter-query-params wps-head-filters__item loading disabled">
        <button class="disabled wps-tooltip-premium">
            <?php esc_html_e('Query Parameter : All', 'wp-statistics'); ?>
            <span class="wps-tooltip_templates tooltip-premium tooltip-premium--bottom tooltip-premium--right">
                <span id="tooltip_realtime">
                    <a data-target="wp-statistics-data-plus" class="js-wps-openPremiumModal"> <?php esc_html_e('Learn More', 'wp-statistics'); ?></a>
                    <span>
                        <?php esc_html_e('Premium Feature', 'wp-statistics'); ?>
                    </span>
                </span>
            </span>
        </button>
    </div>
<?php
    return;
endif;
$args = [
    'title' => __('Query Parameter', 'wp-statistics'),
    'type'  => 'query-params'
];

View::load("components/objects/header-filter-select", $args);
