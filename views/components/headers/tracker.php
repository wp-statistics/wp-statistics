<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

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
            <a href="<?php echo esc_url(Request::get('target')) ?>" title="<?php echo esc_html(Request::get('target')) ?>" target="_blank"><?php echo esc_html(Request::get('target')) ?></a>
        </div>
    </div>
</div>