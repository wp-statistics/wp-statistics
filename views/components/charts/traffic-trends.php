<?php

use WP_Statistics\Components\View;

?>
<div class="o-wrap wps-p-0">
    <div class="wps-postbox-chart wps-postbox-chart--<?php echo esc_attr($chart_id) ?>">
        <div class="wps-postbox-chart--data">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--info">
                <div class="wps-postbox-chart--previousPeriod">
                    <?php esc_html_e('Previous period', 'wp-statistics'); ?>
                </div>
                <?php View::load("components/objects/chart-time-range"); ?>
            </div>
        </div>
        <div class="wps-postbox-chart--container">
            <canvas id="<?php echo esc_attr($chart_id) ?>" aria-label="<?php echo esc_html__('Traffic trend chart', 'wp-statistics') ?>" role="img">
        </div>
    </div>
</div>