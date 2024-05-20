<?php

use WP_STATISTICS\Admin_Template;

$isDataPlusActive = WP_STATISTICS\Helper::isAddOnActive('data-plus');
?>

<?php
if (!$isDataPlusActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'           => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'          => 'DataPlus Add-On',
     'addon_description'    => 'The settings on this page are part of the DataPlus add-on, which enhances WP Statistics by expanding tracking capabilities and providing detailed visitor insights.',
     'addon_features'       => [
         'Track custom post types and taxonomies.',
         'Use advanced filtering for specific query parameters and UTM tags.',
         'Monitor outbound link clicks and downloads.',
         'Compare weekly traffic and view hourly visitor patterns.',
         'Analyze individual content pieces with detailed widgets.',
     ],
     'addon_info'           => 'Unlock deeper insights into your website\'s performance with DataPlus.',
    ], true);
?>
    <div class="postbox">
        <table class="form-table <?php echo !$isDataPlusActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Event Tracking', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Enable or disable tracking features for clicks and downloads', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
            </tr>


            <tr valign="top">
                <th scope="row">
                    <label for="link-tracker"><?php esc_html_e('Link Tracker', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="link-tracker" type="checkbox" value="1" name="wps_link_tracker" <?php checked(WP_STATISTICS\Option::get('link_tracker')) ?>>
                    <label for="link-tracker"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('See which outside links people click on your site.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="download-tracker"><?php esc_html_e('Download Tracker', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="download-tracker" type="checkbox" value="1" name="wps_download_tracker" <?php checked(WP_STATISTICS\Option::get('download_tracker')) ?>>
                    <label for="download-tracker"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Watch which files people download and learn what’s popular.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

<?php
if ($isDataPlusActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='data-plus-settings'"));
}
?>