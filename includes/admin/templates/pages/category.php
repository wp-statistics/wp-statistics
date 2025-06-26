<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="<?php echo esc_attr(\WP_STATISTICS\Meta_Box::getMetaBoxKey('pages-chart')); ?>">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php esc_html_e('Category Statistics Chart', 'wp-statistics'); ?></span></h2>
                    <button class="handlediv" aria-label="toggle button" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf(__('Toggle panel: %s', 'wp-statistics'), __('Category Statistics Chart', 'wp-statistics')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="inside">
                    <!-- Do Js -->
                </div>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php esc_html_e('Category Statistics Summary', 'wp-statistics'); ?></span></h2>
                    <button class="handlediv" aria-label="toggle button" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf(__('Toggle panel: %s', 'wp-statistics'), __('Category Statistics Summary', 'wp-statistics')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="inside">
                    <table class="widefat table-stats wps-summary-stats" id="summary-stats">
                        <tbody>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col" class="th-center"><?php esc_html_e('Count', 'wp-statistics'); ?></th>
                        </tr>

                        <?php
                        if (isset($number_post_in_category)) {
                            ?>
                            <tr>
                                <th scope="col"><?php esc_html_e('The Number of Posts in Category', 'wp-statistics'); ?>:</th>
                                <th scope="col" class="th-center">
                                    <span><?php echo esc_html(number_format_i18n($number_post_in_category)); ?></span></th>
                            </tr>
                            <?php
                        }
                        ?>

                        <tr>
                            <th scope="col"><?php esc_html_e('Chart Views', 'wp-statistics'); ?>:</th>
                            <th scope="col" class="th-center"><span id="number-total-chart-visits"></span></th>
                        </tr>

                        <tr>
                            <th scope="col"><?php esc_html_e('All Time Views', 'wp-statistics'); ?>:</th>
                            <th scope="col" class="th-center"><span id="number-total-visits"></span></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (count($top_list) > 0) { ?>
    <div class="postbox-container wps-postbox-full">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="postbox-header postbox-toggle">
                        <h2 class="hndle wps-d-inline-block"><span><?php echo esc_attr($top_title); ?></span></h2>
                        <button class="handlediv" aria-label="toggle button" type="button" aria-expanded="true">
                            <span class="screen-reader-text"><?php printf(__('Toggle panel: %s', 'wp-statistics'), esc_attr($top_title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="inside">
                        <table class="widefat table-stats wps-summary-stats" id="summary-stats">
                            <tbody>
                            <tr>
                                <th></th>
                                <th class="th-center"><?php esc_html_e('Count', 'wp-statistics'); ?></th>
                            </tr>
                            <?php
                            foreach ($top_list as $item) {
                                ?>
                                <tr>
                                    <th>
                                        <a href="<?php echo esc_url($item['link']); ?>" title="<?php echo esc_attr($item['name']); ?>"><?php echo esc_attr($item['name']); ?></a>
                                    </th>
                                    <th class="th-center">
                                        <span><?php echo esc_html(number_format_i18n($item['count_visit'])); ?></span></th>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>