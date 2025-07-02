<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Url;
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
            <?php
                $target     = Request::get('target');
                $filename   = basename(Url::getPath($target));
                $url        = str_replace($filename, '', $target);
            ?>
            <a href="<?php echo esc_url($target) ?>" title="<?php echo esc_html($target) ?>" target="_blank">
                <span><?php echo esc_html($url) ?></span><span><?php echo esc_html($filename) ?></span>
            </a>
        </div>
    </div>
</div>