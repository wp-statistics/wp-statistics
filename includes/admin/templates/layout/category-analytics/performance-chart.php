<?php
use WP_STATISTICS\Helper;
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if (isset($tooltip) &&  $tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
        <?php if (!empty($description)) : ?>
            <p><?php echo esc_html($description) ?></p>
        <?php endif; ?>
    </div>
    <div class="wps-category-analytics-chart-items">
        <div class="wps-category-analytics-chart--item wps-category-analytics-chart--item--views">
            <p><?php esc_html_e('Views', 'wp-statistics') ?></p>
            <span><?php echo esc_html(Helper::formatNumberWithUnit($data['views'])) ?></span>
        </div>
         <div class="wps-category-analytics-chart--item wps-category-analytics-chart--item--visitors">
            <p><?php esc_html_e('Visitors', 'wp-statistics') ?></p>
            <span><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors'])) ?></span>
        </div>
         <div class="wps-category-analytics-chart--item wps-category-analytics-chart--item--published">
            <p><?php esc_html_e('Published Contents', 'wp-statistics') ?></p>
            <span><?php echo esc_html(Helper::formatNumberWithUnit($data['posts'])) ?></span>
        </div>
     </div>
    <div class="wps-category-analytics-chart">
        <?php if ($type === 'category'): ?>
            <canvas id="performance-category-chart" height="299"></canvas>
        <?php else: ?>
            <canvas id="performance-category-chart-single" height="299"></canvas>
        <?php endif; ?>
    </div>
</div>