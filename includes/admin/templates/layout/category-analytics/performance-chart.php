<?php
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postType = Request::get('tab', 'post');
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
        <?php if (!empty($description)) : ?>
            <p><?php echo esc_html($description) ?></p>
        <?php endif; ?>
    </div>
    <div class="wps-category-analytics-chart-items">
             <div class="wps-category-analytics-chart--item wps-category-analytics-chart--item--published">
                <p><?php echo sprintf(esc_html__('Published %s', 'wp-statistics'), Helper::getPostTypeName($postType)) ?></p>
                <span><?php echo esc_html(Helper::formatNumberWithUnit($data['posts'])) ?></span>
            </div>
         <div class="wps-category-analytics-chart--item wps-category-analytics-chart--item--views">
            <p><?php echo esc_html__('Views', 'wp-statistics') ?></p>
            <span><?php echo esc_html(Helper::formatNumberWithUnit($data['views'])) ?></span>
        </div>
        <div class="wps-category-analytics-chart--item wps-category-analytics-chart--item--visitors">
            <p><?php echo esc_html__('Visitors', 'wp-statistics') ?></p>
            <span><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors'])) ?></span>
        </div>
    </div>
    <div class="wps-category-analytics-chart">
        <canvas id="performance-chart" height="299"></canvas>
    </div>
</div>