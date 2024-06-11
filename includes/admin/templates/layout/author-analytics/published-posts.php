<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-scatter-chart">
        <div class="chart-container">
            <canvas id="publishedChart"></canvas>
        </div>
    </div>
</div>