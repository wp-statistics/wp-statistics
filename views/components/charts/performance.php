<?php
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postType = Request::get('tab', 'post');
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if (isset($tooltip) &&  $tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="c-wps-performance-chart">
        <div class="c-wps-performance-chart__items">
            <div class="c-wps-performance-chart__item js-wps-performance-chart__item c-wps-performance-chart__item--visitors">
                <p><?php echo esc_html__('Visitors', 'wp-statistics'); ?></p>
                <span><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors'])); ?></span>
            </div>
            <div class="c-wps-performance-chart__item  js-wps-performance-chart__item c-wps-performance-chart__item--views">
                <p><?php echo esc_html__('Views', 'wp-statistics'); ?></p>
                <span><?php echo esc_html(Helper::formatNumberWithUnit($data['views'])); ?></span>
            </div>
            <?php if ($type !== 'single'): ?>
                <div class="c-wps-performance-chart__item js-wps-performance-chart__item c-wps-performance-chart__item--published">
                    <?php if ($type === 'category' || $type === 'categorySingle'): ?>
                        <p><?php esc_html_e('Published Contents', 'wp-statistics'); ?></p>
                    <?php else: ?>
                        <p><?php echo sprintf(esc_html__('Published %s', 'wp-statistics'), Helper::getPostTypeName($postType)); ?></p>
                    <?php endif; ?>
                    <span><?php echo esc_html(Helper::formatNumberWithUnit($data['posts'])); ?></span>
                </div>
            <?php endif ?>
        </div>
    </div>
    <div class="c-wps-performance-chart__container">
        <?php
        $canvasIds = [
            'category' => 'performance-category-chart',
            'categorySingle' => 'performance-category-chart-single',
            'post-type' => 'performance-chart',
            'single' => 'performance-chart-single',
        ];

        if (isset($canvasIds[$type])) {
            echo '<canvas id="' . $canvasIds[$type] . '" height="299"></canvas>';
        }
        ?>
    </div>
</div>