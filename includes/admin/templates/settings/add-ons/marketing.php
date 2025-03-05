<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid     = LicenseHelper::isPluginLicenseValid('marketing');
$isMarketingActive  = Helper::isAddOnActive('marketing');

if (!$isMarketingActive) {
    echo Admin_Template::get_template(
        'layout/partials/addon-premium-feature',
        [
            'addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-marketing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
            'addon_title'        => __('Marketing Add-On', 'wp-statistics'),
            'addon_modal_target' => 'wp-statistics-marketing',
            'addon_description'  => __('The settings on this page are part of the Marketing add-on, which enhances WP Statistics by expanding tracking capabilities and providing detailed visitor insights.', 'wp-statistics'),
            'addon_features'     => [
                // ...
            ],
            'addon_info'        => __('Unlock deeper insights into your website\'s performance with Marketing.', 'wp-statistics'),
        ],
        true
    );
}

if ($isMarketingActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}

$isAuthenticated = apply_filters('wp_statistics_oath_authentication_status', false);
?>

<?php if (!$isAuthenticated) : ?>
    <h2 class="wps_title">
        <?php esc_html_e('Marketing', 'wp-statistics'); ?>
        <span class="wps-tooltip" title="<?php esc_html_e('Marketing tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
    </h2>
    <div class="postbox wps-addon-settings--marketing">
        <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2">
                    <h3><?php esc_html_e('Search Console', 'wp-statistics'); ?></h3>
                </th>
            </tr>
            <tr>
                <td  scope="row" colspan="2">
                    <div class="wps-alert wps-alert--setting wps-alert--success">
                        <a href="<?php echo apply_filters('wp_statistics_google_auth_url', '') ?>" class="button button-primary"><?php esc_html_e('Connect to Google Search Console', 'wp-statistics'); ?></a>
                        <div class="wps-alert--setting--title">
                            <h1>
                                <?php esc_html_e('Benefits of Connecting', 'wp-statistics'); ?>
                                <span><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></span>
                            </h1>
                            <p><?php esc_html_e('The settings on this page are part of the REST API add-on, which enables the following endpoints in the WordPress REST API:', 'wp-statistics'); ?></p>
                        </div>
                        <div>
                            <ul>
                                <li><?php esc_html_e('Verify site ownership on Google Search Console in a single click', 'wp-statistics'); ?></li>
                                <li><?php esc_html_e('Track page and keyword rankings with the Advanced Analytics module', 'wp-statistics'); ?></li>
                                <li><?php esc_html_e('Easily set up Google Analytics without using another 3rd party plugin', 'wp-statistics'); ?></li>
                                <li><?php esc_html_e('Automatically submit sitemaps to the Google Search Console', 'wp-statistics'); ?></li>
                                <li><?php esc_html_e('Free keyword suggestions when entering a focus keyword', 'wp-statistics'); ?></li>
                                <li><?php esc_html_e('Use our revolutionary SEO Analyzer to scan your website for SEO errors', 'wp-statistics'); ?></li>
                            </ul>
                            <a href="" class="wps-link-underline"
                            target="_blank"><?php esc_html_e('Learn more about the benefits of connecting your account here.', 'wp-statistics'); ?>
                            </a>
                        </div>
                    </div>
                </td>

            </tr>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="postbox wps-addon-settings--marketing">
        <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2">
                    <div class="wps-addon-settings--marketing__title">
                        <div>
                            <h3><?php esc_html_e('Search Console', 'wp-statistics'); ?></h3>
                        </div>
                        <div>
                            <a href="<?php echo apply_filters('wp_statistics_google_auth_url', '') ?>" class="wps-addon-settings--marketing__reconnect"><?php esc_html_e('Reconnect', 'wp-statistics'); ?></a>
                            <a href="<?php echo apply_filters('wp_statistics_google_auth_disconnect_url', '') ?>" class="wps-addon-settings--marketing__disconnect"><?php esc_html_e('Disconnect', 'wp-statistics'); ?></a>
                        </div>
                    </div>
                </th>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[marketing][site]"><?php esc_html_e('Site', 'wp-statistics'); ?></label>
                </th>

                <td class="wps_addon_settings__site">
                    <select dir="ltr" id="wps_addon_settings[marketing][site]" name="wps_addon_settings[marketing][site]">
                        <option disabled selected value=""><?php esc_html_e('Select site', 'wp-statistics'); ?></option>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Set a threshold for daily robot visits. Robots exceeding this number daily will be identified as bots.', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
if ($isMarketingActive) {
    submit_button(__('Save Settings', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='marketing-settings'"));
}
?>