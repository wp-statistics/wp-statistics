<?php

use WP_STATISTICS\Admin_Template;

$isRealTimeStatsActive = WP_STATISTICS\Helper::isAddOnActive('realtime-stats');
?>

<?php
if (!$isRealTimeStatsActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'           => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'          => 'Real-Time Add-On',
     'addon_description'    => 'The settings on this page are part of the Real-Time add-on, which allows you to track your visitors and online users in real time without needing to refresh the page.',
     'addon_features'       => [
         'Monitor website traffic and activity instantly.',
         'Display real-time statistics directly on your WordPress dashboard.',
     ],
     'addon_info'           => 'Keep a close eye on your website\'s performance with the Real-Time add-on.',
    ], true);
?>

    <div class="postbox">
        <table class="form-table <?php echo !$isRealTimeStatsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Update Interval', 'wp-statistics'); ?></h3></th>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="realtime-stats-interval-time"><?php esc_html_e('Chart & Map Refresh Rate (seconds)', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="number" class="regular-text code" id="realtime-stats-interval-time" name="wps_addon_settings[realtime_stats][interval_time]" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('interval_time', 'realtime_stats')); ?>" style="min-width: 50px"/>
                    <p class="description"><?php esc_html_e('Set the time interval for how frequently the real-time data visuals should update.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isRealTimeStatsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Content Filtering', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="realtime-stats-exclude-pages"><?php esc_html_e('Exclude Posts in Statistics', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[realtime_stats][exclude_pages]" id="realtime-stats-exclude-pages">
                        <option value="0" <?php selected(WP_STATISTICS\Option::getByAddon('exclude_pages', 'realtime_stats'), '0'); ?>><?php esc_html_e('No', 'wp-statistics'); ?></option>
                        <option value="1" <?php selected(WP_STATISTICS\Option::getByAddon('exclude_pages', 'realtime_stats'), '1'); ?>><?php esc_html_e('Yes', 'wp-statistics'); ?></option>
                    </select>
                    <p class="description">
                        <?php _e(sprintf(__('Select \'Yes\' to exclude all posts from the real-time statistics. For detailed logs, ensure "Record User Page Views" are activated in <a href="%s">WP Statistics Settings</a>.', 'wp-statistics'), admin_url('admin.php?page=wps_settings_page&tab=general-settings'))); ?>
                    </p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

<?php
if ($isRealTimeStatsActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='realtime-stats-settings'"));
}
?>