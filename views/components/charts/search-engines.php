<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>

    <div class="wps-postbox-chart--data">
        <div class="wps-postbox-chart--items" ></div>
        <div class="wps-postbox-chart--previousPeriod">
            <?php esc_html_e('Previous period', 'wp-statistics'); ?>
        </div>
    </div>
    <div class="wps-postbox-chart--container">
        <canvas id="<?php echo $unique_id; ?>"></canvas>
    </div>
 </div>