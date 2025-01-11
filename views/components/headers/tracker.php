<?php

use WP_STATISTICS\Menus;

if (Menus::in_page('download_tracker')) {
    $title   = __('Download Report', 'wp-statistics');
    $tooltip = __('Download Report tooltip', 'wp-statistics');
} elseif (Menus::in_page('link_tracker')) {
    $title   = __('Link Report', 'wp-statistics');
    $tooltip = __('Link Report tooltip', 'wp-statistics');
}
?>

<div class="wps-tracker-header">
    <div>
        <div class="wps-tracker-header__title">

            <h2 class="wps_title">
                <?php echo esc_html($title) ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            </h2>
        </div>
        <div class="wps-tracker-header__info">
            <a href="" title="wp-statistics.com/2024/07/16/discover-the-new-mini-chart-design-in-wp-statistics-v14-9/" target="_blank">wp-statistics.com/2024/07/16/discover-the-new-mini-chart-design-in-wp-statistics-v14-9/</a>
        </div>
    </div>
</div>