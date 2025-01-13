<div class="wps-postbox-chart wps-postbox-chart--<?php echo esc_attr($chart_id)?>">
    <div class="wps-postbox-chart--data">
        <div class="wps-postbox-chart--items"></div>
        <div class="wps-postbox-chart--previousPeriod">
            <?php esc_html_e('Previous period', 'wp-statistics'); ?>
        </div>
    </div>
    <div class="wps-postbox-chart--container">
        <canvas id="<?php echo esc_attr($chart_id)?>">
    </div>
</div>