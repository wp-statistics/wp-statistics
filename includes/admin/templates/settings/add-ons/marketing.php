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
?>
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
                    <a href="" class="button button-primary"><?php esc_html_e('Connect to Google Search Console', 'wp-statistics'); ?></a>
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
        <tr valign="top">
            <th scope="row">
                <label for="wps_addon_settings[marketing][database]"><?php esc_html_e('Analytics Database', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input dir="ltr" type="text" id="wps_addon_settings[marketing][database]" name="wps_addon_settings[marketing][database]" size="30" >
                <div class="wps-addon-settings--action">
                    <a href="" class="wps-button wps-button--outline-danger"><?php esc_html_e('Delete data', 'wp-statistics'); ?></a>
                    <a href="" class="wps-button wps-button--default"><?php esc_html_e('Update data manually', 'wp-statistics'); ?></a>
                </div>
                <p class="description">
                    <?php esc_html_e('Enter the number of days to keep Analytics data in your database. The maximum allowed days are 180. Though, 2x data will be stored in the DB for calculating the difference properly.', 'wp-statistics'); ?>
                </p>
                <?php View::load("components/objects/google-data-policy-alert"); ?>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox wps-addon-settings--marketing">
    <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h3><?php esc_html_e('Search Console', 'wp-statistics'); ?></h3>
            </th>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wps_addon_settings[marketing][site]"><?php esc_html_e('Site', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input dir="ltr" type="text" id="wps_addon_settings[marketing][site]" name="wps_addon_settings[marketing][site]" size="30" >
                <p class="description">
                    <?php esc_html_e('Set a threshold for daily robot visits. Robots exceeding this number daily will be identified as bots.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wps_addon_settings[marketing][status]"><?php esc_html_e('Enable the Index status tab', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="wps_addon_settings[marketing][status]" name="wps_addon_settings[marketing][status]" type="checkbox">
                <label for="wps_addon_settings[marketing][status]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                 <p class="description">
                    <?php esc_html_e('Enable this option to show the index status tab in the Analytics module.', 'wp-statistics'); ?>
                </p>
                <?php View::load("components/objects/google-data-policy-alert"); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="wps_addon_settings[marketing][database]"><?php esc_html_e('Analytics Database', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input dir="ltr" type="text" id="wps_addon_settings[marketing][database]" name="wps_addon_settings[marketing][database]" size="30" >
                <p class="description">
                    <?php esc_html_e('Enter the number of days to keep Analytics data in your database. The maximum allowed days are 180. Though, 2x data will be stored in the DB for calculating the difference properly.', 'wp-statistics'); ?>
                </p>
                <div class="wps-addon-settings-marketing-analytics">
                    <div class="wps-addon-settings-marketing-analytics--options">
                        <div class="wps-addon-settings-marketing-analytics--option">
                            <?php esc_html_e('Storage Days:', 'wp-statistics'); ?> <span>177</span>
                        </div>
                        <div class="wps-addon-settings-marketing-analytics--option">
                            <?php esc_html_e('Data rows:', 'wp-statistics'); ?> <span>192.9K</span>
                        </div>
                        <div class="wps-addon-settings-marketing-analytics--option">
                            <?php esc_html_e('Size:', 'wp-statistics'); ?> <span>59 MB</span>
                        </div>
                    </div>
                    <div class="wps-addon-settings--action">
                        <a href="" class="wps-button wps-button--outline-danger"><?php esc_html_e('Delete data', 'wp-statistics'); ?></a>
                        <a href="" class="wps-button wps-button--default"><?php esc_html_e('Update data manually', 'wp-statistics'); ?></a>
                    </div>
                </div>
                <?php View::load("components/objects/google-data-policy-alert"); ?>
            </td>
        </tr>
        </tbody>
    </table>

</div>

<?php
if ($isMarketingActive) {
    submit_button(__('Save Settings', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='marketing-settings'"));
}
?>