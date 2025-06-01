<?php

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$postType = Request::get('tab', 'post');
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if (isset($tooltip) && $tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="o-wrap wps-p-0">
        <div class="wps-postbox-chart--data c-wps-performance-chart__items">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--info">
                <div class="wps-postbox-chart--previousPeriod">
                    <?php esc_html_e('Previous period', 'wp-statistics') ?>
                </div>
                <?php View::load("components/objects/chart-time-range"); ?>
            </div>
        </div>
        <div class="wps-postbox-chart--container c-wps-performance-chart__container">
            <?php
            $canvasIds = [
                'category'       => 'performance-category-chart',
                'categorySingle' => 'performance-category-chart-single',
                'post-type'      => 'performance-chart',
                'single'         => 'performance-chart-single',
            ];

            if (isset($canvasIds[$type])) {
                echo '<canvas id="' . esc_attr($canvasIds[$type]) . '" height="299"></canvas>';
            }
            ?>
        </div>
    </div>
</div>