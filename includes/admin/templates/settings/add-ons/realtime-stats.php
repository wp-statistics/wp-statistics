<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid        = LicenseHelper::isPluginLicenseValid('wp-statistics-realtime-stats');
$isRealTimeStatsActive = WP_STATISTICS\Helper::isAddOnActive('realtime-stats');
?>
    <h2 class="wps-settings-box__title"><span><?php esc_html_e('Real-time Stats', 'wp-statistics'); ?></span></h2>
<?php
if (!$isRealTimeStatsActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature', [
    'addon_title'        => __('Real-Time Add-on', 'wp-statistics'),
    'addon_modal_target' => 'wp-statistics-realtime-stats',
    'addon_campaign'     => 'realtime-stats',
], true);

if ($isRealTimeStatsActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}
?>

    <div class="postbox">
        <table class="form-table <?php echo !$isRealTimeStatsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Update Interval', 'wp-statistics'); ?></h3></th>
            </tr>
            <tr data-id="chart_and_map_refresh_rate_tr">
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

<?php
if ($isRealTimeStatsActive) {
    submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('id' => 'realtime_submit', 'OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='realtime-stats-settings'"));
}
?>