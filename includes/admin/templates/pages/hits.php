<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="<?php echo esc_attr(\WP_STATISTICS\Meta_Box::getMetaBoxKey('hits')); ?>">
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
                <div class="inside">
                    <table width="auto" class="widefat table-stats wps-summary-stats" id="summary-stats">
                        <tbody>
                        <tr>
                            <th></th>
                            <?php if (\WP_STATISTICS\Option::get('visits')) { ?>
                                <th class="th-center"><?php esc_html_e('Views', 'wp-statistics'); ?></th> <?php } ?>
                            <?php if (\WP_STATISTICS\Option::get('visitors')) { ?>
                                <th class="th-center"><?php esc_html_e('Visitors', 'wp-statistics'); ?></th> <?php } ?>
                        </tr>

                        <tr>
                            <th><?php esc_html_e('Chart Total:', 'wp-statistics'); ?></th>
                            <?php if (\WP_STATISTICS\Option::get('visits')) { ?>
                                <th class="th-center"><span id="number-total-chart-visits"></span></th> <?php } ?>
                            <?php if (\WP_STATISTICS\Option::get('visitors')) { ?>
                                <th class="th-center"><span id="number-total-chart-visitors"></span></th> <?php } ?>
                        </tr>

                        <tr>
                            <th><?php esc_html_e('All Time Total:', 'wp-statistics'); ?></th>
                            <?php if (\WP_STATISTICS\Option::get('visits')) { ?>
                                <th class="th-center"><span><?php echo esc_html(number_format_i18n($total_visits)); ?></span></th> <?php } ?>
                            <?php if (\WP_STATISTICS\Option::get('visitors')) { ?>
                                <th class="th-center"><span><?php echo esc_html(number_format_i18n($total_visitors)); ?></span></th> <?php } ?>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
