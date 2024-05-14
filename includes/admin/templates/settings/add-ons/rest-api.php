<?php
$isRestApiActive = WP_STATISTICS\Helper::isAddOnActive('rest-api');
?>
<?php if (!$isRestApiActive) : ?>
    <div class="wps-premium-feature">
        <div>
            <h1><?php esc_html_e('This feature is currently restricted in your current version.', 'wp-statistics'); ?></h1>
            <p><?php esc_html_e('Unlock premium features to gain a deeper insight into your website.', 'wp-statistics'); ?></p>
        </div>
        <a target="_blank" class="button button-primary" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'); ?>"><?php esc_html_e('Upgrade Now', 'wp-statistics') ?></a>
    </div>
<?php endif; ?>

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