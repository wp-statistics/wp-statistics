<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid     = LicenseHelper::isPluginLicenseValid('wp-statistics-data-plus');
$isDataPlusActive   = Helper::isAddOnActive('data-plus');
?>
    <h2 class="wps-settings-box__title">
        <span><?php esc_html_e('Data Plus', 'wp-statistics'); ?></span>
        <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/resources-category/data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings') ?>" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
    </h2>
    <?php

if (!$isDataPlusActive) echo Admin_Template::get_template(
    'layout/partials/addon-premium-feature',
    [
        'addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=data-plus'),
        'addon_title'        => __('DataPlus Add-on', 'wp-statistics'),
        'addon_modal_target' => 'wp-statistics-data-plus',
        'addon_description'  => __('The settings on this page are part of the DataPlus add-on, which enhances WP Statistics by expanding tracking capabilities and providing detailed visitor insights.', 'wp-statistics'),
        'addon_features'     => [
            __('Track custom post types and taxonomies.', 'wp-statistics'),
            __('Use advanced filtering for specific query parameters and UTM tags.', 'wp-statistics'),
            __('Monitor outbound link clicks and downloads.', 'wp-statistics'),
            __('Compare weekly traffic and view hourly visitor patterns.', 'wp-statistics'),
            __('Analyze individual content pieces with detailed widgets.', 'wp-statistics'),
        ],
        true
    ]);


    if ($isDataPlusActive && !$isLicenseValid) {
        View::load("components/lock-sections/notice-inactive-license-addon");
    }
    ?>
    <div class="postbox">
        <table class="form-table <?php echo !$isDataPlusActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2">
                    <h3><?php esc_html_e('Event Tracking', 'wp-statistics'); ?></h3>
                </th>
            </tr>

            <tr data-id="link_tracker_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Link Tracker', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input type="hidden" name="wps_addon_settings[data_plus][link_tracker]" value="0"/>
                    <input id="wps_addon_settings[data_plus][link_tracker]" type="checkbox" value="1" name="wps_addon_settings[data_plus][link_tracker]" <?php checked(Option::getByAddon('link_tracker', 'data_plus', '1'), '1'); ?>>
                    <label for="wps_addon_settings[data_plus][link_tracker]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('See which outside links people click on your site.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="download_tracker_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Download Tracker', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input type="hidden" name="wps_addon_settings[data_plus][download_tracker]" value="0"/>
                    <input id="wps_addon_settings[data_plus][download_tracker]" type="checkbox" value="1" name="wps_addon_settings[data_plus][download_tracker]" <?php checked(Option::getByAddon('download_tracker', 'data_plus', '1'), '1'); ?>>
                    <label for="wps_addon_settings[data_plus][download_tracker]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Watch which files people download and learn what’s popular.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isDataPlusActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2">
                    <h3><?php esc_html_e('User Interface Preferences', 'wp-statistics'); ?></h3>
                </th>
            </tr>

            <tr data-id="latest_visitors_in_editor_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Latest Visitors in Editor', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input type="hidden" name="wps_addon_settings[data_plus][latest_visitors_metabox]" value="0"/>
                    <input id="wps_addon_settings[data_plus][latest_visitors_metabox]" type="checkbox" value="1" name="wps_addon_settings[data_plus][latest_visitors_metabox]" <?php checked(Option::getByAddon('latest_visitors_metabox', 'data_plus', '1'), '1'); ?>>
                    <label for="wps_addon_settings[data_plus][latest_visitors_metabox]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Display the Latest Visitors section on the edit content pages.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

<?php
if ($isDataPlusActive) {
    submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='data-plus-settings'"));
}
?>