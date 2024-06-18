<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
        <?php if (!empty($description_text)) : ?>
            <p><?php echo $description_text ?></p>
        <?php endif; ?>
    </div>
    <div class="wps-content-analytics-chart-items">
        <?php if ($type === 'post-type'): ?>
            <div class="wps-content-analytics-chart--item wps-content-analytics-chart--item--published">
                <p><?php echo esc_html__('Published Posts', 'wp-statistics') ?></p>
                <span>126</span>
            </div>
        <?php endif ?>
        <div class="wps-content-analytics-chart--item wps-content-analytics-chart--item--views">
            <p><?php echo esc_html__('Views', 'wp-statistics') ?></p>
            <span>352.3K</span>
        </div>
        <div class="wps-content-analytics-chart--item wps-content-analytics-chart--item--visitors">
            <p><?php echo esc_html__('Visitors', 'wp-statistics') ?></p>
            <span>105.4K</span>
        </div>
    </div>
    <div class="wps-content-analytics-chart">
        <?php if ($type === 'post-type'): ?>
            <canvas id="performance-chart" height="299"></canvas>
        <?php else: ?>
            <canvas id="performance-chart-single" height="299"></canvas>
        <?php endif ?>
    </div>
</div>