<?php

use WP_STATISTICS\Admin_Template;

$isRestApiActive = WP_STATISTICS\Helper::isAddOnActive('rest-api');
?>

<?php
if (!$isRestApiActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'                => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'               => 'Rest API Add-On',
     'addon_description'         => 'The settings on this page are part of the REST API add-on, which enables the following endpoints in the WordPress REST API:',
     'addon_features'            => [
         'Browsers',
         'Hits',
         'Referrers',
         'Search Engines',
         'Summary',
         'Visitors',
         'Pages',
     ],
     'addon_info'                => 'For more information about the API and endpoints, please refer to the',
     'addon_documentation_title' => 'API documentation',
     'addon_documentation_slug'  => esc_url('https://documenter.getpostman.com/view/3239688/2s8Z6vZER4'),

    ], true);
?>


    <div class="postbox">
        <table class="form-table <?php echo !$isRestApiActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('WordPress REST API Integration', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="realtime-stats-interval-time"><?php esc_html_e('API Service Status', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[rest_api][status]" name="wps_addon_settings[rest_api][status]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('status', 'rest_api')) ?>>
                    <label for="wps_addon_settings[rest_api][status]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Toggle to activate or deactivate WP-Statistics data endpoints within the WordPress REST API.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[rest_api][token_auth]"><?php esc_html_e('Authentication Token', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" name="wps_addon_settings[rest_api][token_auth]" id="wps_addon_settings[rest_api][token_auth]" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('token_auth', 'rest_api')) ?>"/>
                    <p class="description"><?php esc_html_e('Secure your API with a unique token. Enter your personal token here to authorize REST API requests.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

<?php
if ($isRestApiActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='rest-api-settings'"));
}
?>