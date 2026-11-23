<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid  = LicenseHelper::isPluginLicenseValid('wp-statistics-rest-api');
$isRestApiActive = WP_STATISTICS\Helper::isAddOnActive('rest-api');
?>
    <h2 class="wps-settings-box__title"><span><?php esc_html_e('REST API', 'wp-statistics'); ?></span></h2>

<?php
if (!$isRestApiActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature', [
    'addon_title'        => __('Rest API Add-on', 'wp-statistics'),
    'addon_modal_target' => 'wp-statistics-rest-api',
    'addon_campaign'     => 'rest-api',
], true);

if ($isRestApiActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}
?>


    <div class="postbox">
        <table class="form-table <?php echo !$isRestApiActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('WordPress REST API Integration', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="api_service_status_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('API Service Status', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="wps_addon_settings[rest_api][status]" name="wps_addon_settings[rest_api][status]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('status', 'rest_api')) ?>>
                    <label for="wps_addon_settings[rest_api][status]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php /* translators: %1$s: string value */ echo wp_kses_post(sprintf(__('Enable or disable WP Statistics API endpoints. For more information, visit the %1$s.', 'wp-statistics'), '<a href="https://documenter.getpostman.com/view/3239688/2s8Z6vZER4" target="_blank">API documentation</a>')); ?></p>
                </td>
            </tr>

            <tr data-id="authentication_token_tr">
                <th scope="row">
                    <label for="wps_addon_settings[rest_api][token_auth]"><?php esc_html_e('Authentication Token', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <div class="wps-input-group wps-input-group__action">
                        <input type="text" name="wps_addon_settings[rest_api][token_auth]" id="wps_addon_settings[rest_api][token_auth]" class="regular-text wps-input-group__field" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('token_auth', 'rest_api')) ?>"/>
                        <button type="button"  class="button has-icon wps-input-group__label wps-input-group__copy"  style="margin: 0; "><?php esc_html_e('Copy', 'wp-statistics'); ?></button>
                    </div>
                    <p class="description"><?php esc_html_e('Enter your unique token here to secure and authorize API requests.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

<?php
/**
 * The REST API add-on prints its "AI agents (MCP)" section here, inside the settings form.
 */
if ($isRestApiActive && has_action('wp_statistics_settings_rest_api_mcp')) {
    do_action('wp_statistics_settings_rest_api_mcp');
} elseif (!$isRestApiActive) {
    ?>
    <div class="postbox">
        <table class="form-table form-table--preview">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('AI agents (MCP)', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr>
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('AI Agent Access', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <input id="wps-mcp-preview-status" type="checkbox" disabled>
                    <label for="wps-mcp-preview-status"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Connect ChatGPT, Claude and other AI agents with MCP, and ask them about your visitors, top pages, referrers and countries. Read only, and you can cut off any agent at any time.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php
}

if ($isRestApiActive) {
    submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('id' => 'rest_api_submit', 'OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='rest-api-settings'"));
}
?>