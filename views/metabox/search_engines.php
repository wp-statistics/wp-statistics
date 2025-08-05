<?php

use WP_Statistics\Components\View;

?>
<div class="o-wrap">
    <div class="wps-postbox-chart--data">
        <div class="wps-postbox-chart--items"></div>
        <div class="wps-postbox-chart--info">
            <div class="wps-postbox-chart--previousPeriod"><?php echo esc_html__('Previous period', 'wp-statistics') ?></div>
            <?php View::load("components/objects/chart-time-range"); ?>
        </div>
    </div>
    <div class="wps-postbox-chart--container">
        <p class="screen-reader-text">
            <?php echo esc_html__('Search engine traffic over the selected period', 'wp-statistics') ?>
        </p>
        <canvas id="wp-statistics-search-widget-chart" height="210" aria-label="Search engine chart" role="img"></canvas>
    </div>
</div>