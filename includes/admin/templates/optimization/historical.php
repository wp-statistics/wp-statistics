<?php
// Get the historical number of visitors to the site
$historical_visitors = WP_STATISTICS\Historical::get('visitors');

// Get the historical number of visits to the site
$historical_visits = WP_STATISTICS\Historical::get('visits');

?>
<div class="wrap wps-wrap">
    <div class="postbox">
        <form action="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page&tab=historical')) ?>" id="wps_historical_form" method="post">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2"><h3><?php esc_html_e('Historical Data Entry', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top" id="wps_historical_purge" style="display: none">
                    <th scope="row" colspan=2>
                        <?php esc_html_e('Reminder: After database purging, please reload this page to update these figures correctly.', 'wp-statistics'); ?>
                    </th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="wps_historical_visitors"><?php esc_html_e('Historical Total Visitors', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <input type="text" size="10" value="<?php echo esc_attr($historical_visitors); ?>" id="wps_historical_visitors" name="wps_historical_visitors">
                        <p class="description"><?php echo sprintf(__('Enter the accumulated count of unique visitors to your site from its inception up to now. For example, if you\'ve transitioned from another tracking tool and it reported 5,000 unique visitors up to the point of switching, input that figure here. This ensures your statistics reflect the entire history of your website\'s traffic. Currently set to %s.', 'wp-statistics'), esc_html(number_format_i18n($historical_visitors))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="wps_historical_visits"><?php esc_html_e('Historical Total Site Views', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <input type="text" size="10" value="<?php echo esc_attr($historical_visits); ?>" id="wps_historical_visits" name="wps_historical_visits">
                        <p class="description"><?php echo sprintf(__('Enter the total number of site visits (including repeat visits) from its start until now. If your previous tool indicated 20,000 total site visits before moving to WP Statistics, input that number. This allows for a seamless integration of past site visit data. Currently set to %s.', 'wp-statistics'), esc_html(number_format_i18n($historical_visits))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <td colspan=2>
                        <input type="hidden" name="submit" value="1"/>
                        <button id="historical-submit" class="button button-primary" type="submit" value="1" name="historical-submit"><?php esc_html_e('Save Changes', 'wp-statistics'); ?></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
