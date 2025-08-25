<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid    = LicenseHelper::isPluginLicenseValid('wp-statistics-marketing');
$isMarketingActive = Helper::isAddOnActive('marketing');
?>
    <h2 class="wps-settings-box__title">
        <span><?php esc_html_e('Marketing', 'wp-statistics'); ?></span>
    </h2>
<?php


if (!$isMarketingActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp/?utm_source=wp-statistics&utm_medium=link&utm_campaign=marketing'),
     'addon_title'        => __('Marketing Add-On', 'wp-statistics'),
     'addon_modal_target' => 'wp-statistics-marketing',
     'addon_campaign'     => 'marketing',
     'addon_description'  => sprintf(
         __('The settings on this page are part of the %s add-on, which upgrades WP Statistics from simple analytics to a full-fledged growth dashboard.', 'wp-statistics'),
         '<b>' . esc_html__('Marketing', 'wp-statistics') . '</b>'
     ),
     'addon_features'     => [
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

$authUrl         = apply_filters('wp_statistics_google_auth_url', '');
$testUrl         = apply_filters('wp_statistics_google_auth_test_url', '');
$redirectUrl     = apply_filters('wp_statistics_google_auth_redirect_url', '');
$disconnectUrl   = apply_filters('wp_statistics_google_auth_disconnect_url', '');
$isAuthenticated = apply_filters('wp_statistics_oath_authentication_status', false);
?>
    <div class="postbox wps-addon-settings--marketing">
        <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
            <tbody>
            <tr class="js-wps-show_if_gsc-connection-method_equal_middleware wps-settings-box_head">
                <th scope="row" colspan="2">
                    <?php if (!$isAuthenticated) : ?>
                        <h3><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></h3>
                    <?php else : ?>
                        <div class="wps-addon-settings--marketing__title">
                            <div>
                                <h3><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></h3>
                            </div>
                            <div>
                                <a href="<?php echo esc_url(add_query_arg(['method' => 'middleware'], $authUrl)); ?>"
                                   class="wps-addon-settings--marketing__reconnect"><?php esc_html_e('Reconnect', 'wp-statistics'); ?></a>
                                <a href="<?php echo esc_url($disconnectUrl); ?>"
                                   class="wps-addon-settings--marketing__disconnect"><?php esc_html_e('Disconnect', 'wp-statistics'); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </th>
            </tr>
            <tr class="js-wps-show_if_gsc-connection-method_equal_direct wps-settings-box_head">
                <th scope="row" colspan="2">
                    <div class="wps-addon-settings--marketing__title">
                        <div>
                            <h3><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></h3>
                        </div>
                        <div>
                            <?php if ($isAuthenticated) : ?>
                                <a href="<?php echo esc_url(add_query_arg(['method' => 'direct'], $authUrl)); ?>" class="wps-addon-settings--marketing__reconnect"><?php esc_html_e('Reconnect', 'wp-statistics'); ?></a>
                                <a href="<?php echo esc_url($testUrl); ?>" class="wps-addon-settings--marketing__reconnect"><?php esc_html_e('Test Connection', 'wp-statistics'); ?></a>
                                <a href="<?php echo esc_url($disconnectUrl); ?>" class="wps-addon-settings--marketing__disconnect"><?php esc_html_e('Disconnect', 'wp-statistics'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </th>
            </tr>
            <tr data-id="wps_addon_settings-marketing-search-console">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Show Google Search tab', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <?php
                    $gscReport = Option::getByAddon('gsc_report', 'marketing', '1');
                    $site      = Option::getByAddon('site', 'marketing');
                    ?>
                    <input type="hidden" name="wps_addon_settings[marketing][gsc_report]" value="0"/>
                    <input id="wps_addon_settings[marketing][gsc_report]"
                           name="wps_addon_settings[marketing][gsc_report]" type="checkbox"
                           value="1" <?php disabled(!empty($site)); ?> <?php checked($gscReport || $site); ?>>
                    <label for="wps_addon_settings[marketing][gsc_report]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Display the Google Search Console report tab when no Google property is connected.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="wps_addon_settings-gsc-connection-method">
                <th scope="row">
                    <label for="wps_settings[gsc-connection-method]">
                        <?php esc_html_e('Connection Method', 'wp-statistics'); ?>
                        <?php if ($isAuthenticated): ?>
                            <span class="wps-tooltip" title="<?php echo esc_attr__('This option is locked. Click “Disconnect” to enable it.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></span>
                        <?php endif; ?>
                    </label>
                </th>
                <td>
                    <?php $connectionMethod = Option::getByAddon('gsc_connection_method', 'marketing', 'middleware'); ?>
                    <select id="wps_settings[gsc-connection-method]" class="<?php echo $isAuthenticated ? 'disabled' : ''; ?>" name="wps_addon_settings[marketing][gsc_connection_method]">
                        <option <?php selected($connectionMethod, 'middleware'); ?> value="middleware">
                            <?php esc_html_e('WP Statistics Credentials', 'wp-statistics'); ?>
                        </option>
                        <option <?php selected($connectionMethod, 'direct'); ?> value="direct">
                            <?php esc_html_e('Direct (Your Credentials)', 'wp-statistics'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php echo esc_html__('Select how to connect to Google Search Console: WP Statistics Credentials (no setup) or Direct (your Google OAuth app).', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>
            <tr class="js-wps-show_if_gsc-connection-method_equal_direct" data-id="wps_addon_settings-gsc-client-id">
                <th scope="row">
                    <label for="gsc-client-id"><?php esc_html_e('Google Client ID', 'wp-statistics'); ?><span class="u-wps-required">*</span></label>
                </th>
                <td>
                    <?php $gscClientId = Option::getByAddon('gsc_client_id', 'marketing'); ?>
                    <input type="text" size="3" id="gsc-client-id" name="wps_addon_settings[marketing][gsc_client_id]" placeholder="1234567890-abc123def456.apps.googleusercontent.com" value="<?php echo esc_attr($gscClientId); ?>">
                    <p class="description">
                        <?php echo esc_html__('Client ID from your Google Cloud OAuth 2.0 Web application.', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>
            <tr class="js-wps-show_if_gsc-connection-method_equal_direct" data-id="wps_addon_settings-gsc-client-secret">
                <th scope="row">
                    <label for="gsc-client-secret"><?php esc_html_e('Google Client Secret', 'wp-statistics'); ?><span class="u-wps-required">*</span></label>
                </th>
                <td>
                    <div class="c-password-field">
                        <?php $gscClientSecret = Option::getByAddon('gsc_client_secret', 'marketing'); ?>
                        <input type="password" size="3" class="js-password-toggle" id="gsc-client-secret" name="wps_addon_settings[marketing][gsc_client_secret]" value="<?php echo esc_attr($gscClientSecret); ?>">
                        <button type="button" class="c-password-field__btn" aria-label="Toggle password visibility">
                            <span class="icon-eye"></span>
                        </button>
                    </div>
                    <p class="description">
                        <?php echo esc_html__('Client Secret from the same OAuth app; private on your site.', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>

            <tr class="js-wps-show_if_gsc-connection-method_equal_direct" data-id="wps_addon_settings-gsc-auth-redirect-url">
                <th scope="row">
                    <label for="gsc-auth-redirect-url"><?php esc_html_e('Authorized Redirect URI', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <div class="wps-input-group wps-input-group__action">
                        <input readonly type="text" id="gsc-auth-redirect-url" class="regular-text wps-input-group__field" value="<?php echo esc_url($redirectUrl); ?>"/>
                        <button type="button" id="copy-text" class="button has-icon wps-input-group__label wps-input-group__copy"  style="margin: 0; "><?php esc_html_e('Copy', 'wp-statistics'); ?></button>
                    </div>
                     <p class="description">
                        <?php echo esc_html__('URL to register under “Authorized redirect URIs” in your OAuth client.', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>
            <?php if (!$isAuthenticated) : ?>
                <tr>
                    <td colspan="2" class="wps-addon-settings--marketing__row">
                        <div class="wps-alert--marketing">
                            <div class="js-wps-show_if_gsc-connection-method_equal_middleware">
                                <a href="<?php echo esc_url(add_query_arg(['method' => 'middleware'], $authUrl)); ?>"
                                   class="button button-primary">
                                    <?php esc_html_e('Connect to Google Search Console', 'wp-statistics'); ?></a>
                            </div>

                            <div class="js-wps-show_if_gsc-connection-method_equal_direct">
                                <span class="wps-tooltip wps-d-inline-block"
                                      data-disable-tooltip="<?php echo esc_html__('To connect, add your Client ID and Client Secret, save changes, then click Connect.', 'wp-statistics')?>"
                                      data-enable-tooltip="<?php echo esc_html__('Continues to Google for approval. You can disconnect anytime.', 'wp-statistics')?>"
                                      title="<?php echo empty($gscClientId) || empty($gscClientSecret)
                                          ? esc_html__('To connect, add your Client ID and Client Secret, save changes, then click Connect.', 'wp-statistics')
                                          : esc_html__('Continues to Google for approval. You can disconnect anytime.', 'wp-statistics'); ?>"
                                >

                                <a id="wps-gsc-connect-btn" href="<?php echo esc_url(add_query_arg(['method' => 'direct'], $authUrl)); ?>"
                                   class="button button-primary" <?php disabled(empty($gscClientId) || empty($gscClientSecret)) ?>
                                >
                                    <?php esc_html_e('Connect to Google Search Console', 'wp-statistics'); ?>
                                </a>
                                </span>

                            </div>

                            <div class="wps-alert--setting--title">
                                <h1>
                                    <?php esc_html_e('Benefits of Connecting', 'wp-statistics'); ?>
                                    <span><?php esc_html_e('Google Search Console', 'wp-statistics'); ?></span>
                                </h1>
                            </div>
                            <div>
                                <ul>
                                    <li><?php esc_html_e('View your Search Console data in WordPress.', 'wp-statistics'); ?></li>
                                    <li><?php esc_html_e('Get key metrics in your overview page for quick insights.', 'wp-statistics'); ?></li>
                                    <li><?php esc_html_e('Track traffic and keywords for each page or post at a glance.', 'wp-statistics'); ?></li>
                                    <li><?php esc_html_e('Unlock detailed search data to make smarter content decisions.', 'wp-statistics'); ?></li>
                                </ul>
                            </div>

                            <div class="js-wps-show_if_gsc-connection-method_equal_middleware">
                                <?php
                                View::load(
                                    "components/objects/google-data-policy-alert",
                                    [
                                        'content' => sprintf(
                                            '%s <a href="%s" target="_blank" rel="noopener">%s</a>.',
                                            esc_html__('We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused.', 'wp-statistics'),
                                            esc_url(WP_STATISTICS_SITE_URL . '/resources/google-search-console-integration-privacy-data-handling/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'), esc_html__('Learn more', 'wp-statistics')
                                        )
                                    ]
                                );
                                ?>
                            </div>


                            <div class="js-wps-show_if_gsc-connection-method_equal_direct">
                                <?php
                                View::load(
                                    "components/objects/google-data-policy-alert",
                                    ['content' => sprintf(
                                        '%s <a href="%s" target="_blank" rel="noopener">%s</a>.',
                                        esc_html__('Client ID, Secret, and tokens are stored only on your site. Disconnect removes the tokens. Step-by-step setup:', 'wp-statistics'),
                                        esc_url(WP_STATISTICS_SITE_URL . '/resources/connect-google-search-console-with-your-own-google-oauth-app-direct-method?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'),
                                        esc_html__('Direct integration guide', 'wp-statistics')
                                    )]
                                );
                                ?>
                            </div>
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

                        <select dir="ltr" class="wps-marketing-site" id="wps_addon_settings[marketing][site]" name="wps_addon_settings[marketing][site]">
                            <?php if (!empty($selectedSite)) : ?>
                                <option selected value="<?php echo esc_attr($selectedSite) ?>"><?php echo esc_html(str_replace('sc-domain:', '', $selectedSite)); ?></option>
                            <?php else : ?>
                                <option disabled selected value=""><?php esc_html_e('Select site', 'wp-statistics'); ?></option>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr class="js-wps-show_if_gsc-connection-method_equal_middleware">
                    <th scope="row"><span class="screen-reader-text"><?php echo esc_html__('Google data policy alert', 'wp-statistics') ?></span></th>
                    <td class="wps_addon_settings__site">
                        <?php
                        View::load(
                            "components/objects/google-data-policy-alert",
                            [
                                'content' => sprintf(
                                    '%s <a href="%s" target="_blank" rel="noopener">%s</a>.',
                                    esc_html__('We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused.', 'wp-statistics'),
                                    esc_url(WP_STATISTICS_SITE_URL . '/resources/google-search-console-integration-privacy-data-handling/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'), esc_html__('Learn more', 'wp-statistics')
                                )
                            ]
                        );
                        ?>
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
    <div class="postbox wps-addon-settings--marketing">
        <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head wps-settings-box_marketing">
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