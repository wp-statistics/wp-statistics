<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

if (Menus::in_page('download_tracker')) {
    $title   = __('Download Report', 'wp-statistics');
} elseif (Menus::in_page('link_tracker')) {
    $title   = __('Link Report', 'wp-statistics');
}
?>

<div class="wps-tracker-header">
    <div>
        <div class="wps-tracker-header__title">

            <h2 class="wps_title">
                <?php echo esc_html($title) ?>
            </h2>
        </div>
        <div class="wps-tracker-header__info">
            <a href="<?php echo esc_url(Request::get('target')) ?>" title="<?php echo esc_html(Request::get('target')) ?>" target="_blank"><?php echo esc_html(Request::get('target')) ?></a>
        </div>
    </div>
</div>