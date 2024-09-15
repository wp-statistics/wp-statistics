<?php

use WP_Statistics\Components\View;

?>
<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox mb-8">
            <div class="postbox-header--table-title">
                <h2>
                    <?php esc_html_e('Income Visitor Chart', 'wp-statistics'); ?>
                </h2>
            </div>
            <div class="inside">
                <?php View::load("components/charts/income-visitor-chart"); ?>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox">
            <?php
            $args = [
                'data'                 => [],
                'show_source_category' => false,
                'pagination'           => isset($pagination) ? $pagination : null
            ];
            View::load("components/tables/referred", $args);
            ?>
        </div>
    </div>
</div>