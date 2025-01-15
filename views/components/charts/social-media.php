<?php
use WP_Statistics\Components\View;
?>
<div class="o-wrap wps-p-0">
    <div class="wps-postbox-chart">
        <div class="wps-postbox-chart--data c-chart__wps-skeleton--legend">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--info">
                <div class="wps-postbox-chart--previousPeriod">
                    <?php esc_html_e('Previous period', 'wp-statistics') ?>
                </div>
                <?php View::load("components/objects/chart-time-range"); ?>
            </div>
        </div>
        <div class="wps-postbox-chart--container c-chart__wps-skeleton">
            <canvas id="socialMediaChart">
        </div>
    </div>
</div>