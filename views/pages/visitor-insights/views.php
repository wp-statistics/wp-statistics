<?php
use WP_Statistics\Components\View;
?>
<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox mb-8">
            <div class="postbox-header--table-title">
                <h2>
                    <?php esc_html_e('Traffic Trends', 'wp-statistics'); ?>
                </h2>
            </div>
            <div class="inside">
                <?php View::load("components/charts/traffic-trends"); ?>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox">
            <div class="postbox-header--table-title">
                <h2>
                    <?php esc_html_e('Latest Views', 'wp-statistics'); ?>
                </h2>
            </div>
            <?php
            $args = [
                'page_column_title' => esc_html__('Page', 'wp-statistics'),
                'data'              => $data['data'],
                'pagination'        => isset($pagination) ? $pagination : null
            ];
            View::load("components/tables/visitors", $args);
            ?>
        </div>
    </div>
</div>