<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid = LicenseHelper::isPluginLicenseValid('wp-statistics-marketing');
$isMarketingActive = Helper::isAddOnActive('marketing');
?>
    <h2 class="wps-settings-box__title">
        <span><?php esc_html_e('Marketing', 'wp-statistics'); ?></span>
    </h2>
<?php


if (!$isMarketingActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug' => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp/?utm_source=wp-statistics&utm_medium=link&utm_campaign=marketing'),
        'addon_title' => __('Marketing Add-On', 'wp-statistics'),
        'addon_modal_target' => 'wp-statistics-marketing',
        'addon_campaign' => 'marketing',
        'addon_description' => sprintf(
            __('The settings on this page are part of the %s add-on, which upgrades WP Statistics from simple analytics to a full-fledged growth dashboard.', 'wp-statistics'),
            '<b>' . esc_html__('Marketing', 'wp-statistics') . '</b>'
        ),
        'addon_features' => [
            sprintf(
                __('Pull %s keywords and traffic for every page without leaving WordPress.', 'wp-statistics'),
                '<b>' . esc_html__('Google Search Console', 'wp-statistics') . '</b>'
            ),
            sprintf(
                __('Track %s and instantly see which channels bring the best visitors.', 'wp-statistics'),
                '<b>' . esc_html__('campaigns & UTM links', 'wp-statistics') . '</b>'
            ),
            sprintf(
                __('%s on buttons, links, and other elements with a quick toggle.', 'wp-statistics'),
                '<b>' . esc_html__('Record click events', 'wp-statistics') . '</b>'
            ),
            sprintf(
                __('Create unlimited %s for custom interactions or funnels.', 'wp-statistics'),
                '<b>' . esc_html__('code-based events ', 'wp-statistics') . '</b>'
            ),
            sprintf(
                __('Set %s and watch progress toward your targets in real-time.', 'wp-statistics'),
                '<b>' . esc_html__('PageView goals', 'wp-statistics') . '</b>'
            )
        ],
    ], true);


if ($isMarketingActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}

$isAuthenticated = apply_filters('wp_statistics_oath_authentication_status', false);
?>
    <div class="postbox wps-addon-settings--marketing">
        <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
            <tbody>
            <tr valign="top" class="wps-settings-box_head">
                <?php if (!$isAuthenticated) : ?>
                    <th scope="row">
                        <h3><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></h3>
                    </th>
                <?php else : ?>
                    <th scope="row" colspan="2">
                        <div class="wps-addon-settings--marketing__title">
                            <div>
                                <h3><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></h3>
                            </div>
                            <div>
                                <a href="<?php echo apply_filters('wp_statistics_google_auth_url', '') ?>"
                                   class="wps-addon-settings--marketing__reconnect"><?php esc_html_e('Reconnect', 'wp-statistics'); ?></a>
                                <a href="<?php echo apply_filters('wp_statistics_google_auth_disconnect_url', '') ?>"
                                   class="wps-addon-settings--marketing__disconnect"><?php esc_html_e('Disconnect', 'wp-statistics'); ?></a>
                            </div>
                        </div>
                    </th>
                <?php endif; ?>
            </tr>
            <tr data-id="wps_addon_settings-marketing-search-console">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Show Google Search tab', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <?php
                    $gscReport = Option::getByAddon('gsc_report', 'marketing', '1');
                    $site = Option::getByAddon('site', 'marketing');
                    ?>
                    <input type="hidden" name="wps_addon_settings[marketing][gsc_report]" value="0"/>
                    <input id="wps_addon_settings[marketing][gsc_report]"
                           name="wps_addon_settings[marketing][gsc_report]" type="checkbox"
                           value="1" <?php disabled(!empty($site)); ?> <?php checked($gscReport || $site); ?>>
                    <label for="wps_addon_settings[marketing][gsc_report]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Display the Google Search Console report tab when no Google property is connected.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            <?php if (!$isAuthenticated) : ?>
                <tr>
                    <td colspan="2" scope="row" class="wps-addon-settings--marketing__row">
                        <div class="wps-alert--marketing">
                            <a href="<?php echo apply_filters('wp_statistics_google_auth_url', '') ?>"
                               class="button button-primary"><?php esc_html_e('Connect to Google Search Console', 'wp-statistics'); ?></a>
                            <div class="wps-alert--setting--title">
                                <h1>
                                    <?php esc_html_e('Benefits of Connecting', 'wp-statistics'); ?>
                                    <span><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></span>
                                </h1>
                            </div>
                            <div>
                                <ul>
                                    <li><?php esc_html_e('View your Search Console data in WordPress—no more switching tabs.', 'wp-statistics'); ?></li>
                                    <li><?php esc_html_e('Get key metrics in your overview page for quick insights.', 'wp-statistics'); ?></li>
                                    <li><?php esc_html_e('Track traffic and keywords for each page or post at a glance.', 'wp-statistics'); ?></li>
                                    <li><?php esc_html_e('Unlock detailed search data to make smarter content decisions.', 'wp-statistics'); ?></li>
                                </ul>
                            </div>
                            <?php View::load("components/objects/google-data-policy-alert"); ?>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <tr data-id="wps_addon_settings-marketing-site">
                    <th scope="row">
                        <label for="wps_addon_settings[marketing][site]"><?php esc_html_e('Site', 'wp-statistics'); ?></label>
                    </th>

                    <td class="wps_addon_settings__site">
                        <?php $selectedSite = Option::getByAddon('site', 'marketing'); ?>

                        <select dir="ltr" id="wps_addon_settings[marketing][site]"
                                name="wps_addon_settings[marketing][site]">
                            <?php if (!empty($selectedSite)) : ?>
                                <option selected
                                        value="<?php echo esc_attr($selectedSite) ?>"><?php echo esc_html(str_replace('sc-domain:', '', $selectedSite)); ?></option>
                            <?php else : ?>
                                <option disabled selected
                                        value=""><?php esc_html_e('Select site', 'wp-statistics'); ?></option>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><span class="screen-reader-text"><?php echo esc_html__('Google data policy alert', 'wp-statistics') ?></span></th>
                    <td class="wps_addon_settings__site">
                        <?php View::load("components/objects/google-data-policy-alert"); ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="postbox wps-addon-settings--marketing">
        <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
            <tbody>
            <tr valign="top" class="wps-settings-box_head">
                <th scope="row">
                    <h3><?php esc_html_e('Campaign Builder', 'wp-statistics'); ?></h3>
                </th>
            </tr>
            <tr data-id="wps_addon_settings-marketing-campaign-builder">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Campaign Builder', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <input type="hidden" name="wps_addon_settings[marketing][campaign_builder]" value="0"/>
                    <input id="wps_addon_settings[marketing][campaign_builder]"
                           name="wps_addon_settings[marketing][campaign_builder]" type="checkbox"
                           value="1" <?php checked(Option::getByAddon('campaign_builder', 'marketing', '1')); ?>>
                    <label for="wps_addon_settings[marketing][campaign_builder]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Generate and validate UTM-tagged links.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
<?php
if ($isMarketingActive) {
    submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='marketing-settings'"));
}
?>