<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Data Protection', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="anonymize_ips">
                    <?php esc_html_e('Anonymize IP Addresses', 'wp-statistics'); ?>
                    <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                        <a href="#" class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                    <?php endif ?>
                </label>
            </th>
            <td>
                <input id="anonymize_ips" type="checkbox" value="1" name="wps_anonymize_ips" <?php echo WP_STATISTICS\Option::get('anonymize_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="anonymize_ips"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('Masks the last segment of a user\'s IP address for privacy, complying with GDPR and preventing the full IP from being stored. More details can be found at <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Avoiding PII Data Collection.</a>', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped		 ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php esc_html_e('Hash IP Addresses', 'wp-statistics'); ?></label>
                <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                    <a href="#" class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                <?php endif ?>
            </th>
            <td>
                <input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo WP_STATISTICS\Option::get('hash_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="hash_ips"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('Transforms IP addresses into a unique, non-reversible string using a secure algorithm, enhancing privacy protection and complying with data privacy regulations. For an in-depth explanation, refer to <a href="https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="blank">Counting Unique Visitors Without Cookies</a>.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped		 ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Privacy Compliance', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="privacy_audit"><?php esc_html_e('Privacy Audit', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="privacy_audit" type="checkbox" value="1" name="wps_privacy_audit" <?php checked(WP_STATISTICS\Option::get('privacy_audit')) ?>>
                <label for="privacy_audit"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Checking WP Statistics settings for privacy compliance.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('User Preferences', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="consent_level_integration"><?php esc_html_e('WP Consent Level Integration', 'wp-statistics'); ?></label>
            </th>

            <td>
                <?php $isWpConsentApiActive = \WP_Statistics\Service\Integrations\WpConsentApi::isWpConsentApiActive(); ?>
                <select id="consent_level_integration" name="wps_consent_level_integration" <?php echo !$isWpConsentApiActive ? 'disabled' : ''; ?>>
                    <option value="disabled" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'disabled'); ?>><?php esc_html_e('Disabled', 'wp-statistics'); ?></option>
                    <?php if ($isWpConsentApiActive) : ?>
                        <option value="functional" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'functional'); ?>><?php esc_html_e('Functional', 'wp-statistics'); ?></option>
                        <option value="statistics-anonymous" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'statistics-anonymous'); ?>><?php esc_html_e('Statistics-Anonymous', 'wp-statistics'); ?></option>
                        <option value="statistics" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'statistics'); ?>><?php esc_html_e('Statistics', 'wp-statistics'); ?></option>
                        <option value="marketing" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'marketing'); ?>><?php esc_html_e('Marketing', 'wp-statistics'); ?></option>
                    <?php endif; ?>
                </select>
                <p class="description"><?php esc_html_e("Enable WP Consent Level API integration to ensure WP Statistics complies with user-selected privacy preferences. When enabled, WP Statistics will only trigger tracking based on the user's chosen consent category.", 'wp-statistics'); ?></p>
                <?php if ($isWpConsentApiActive) : ?>
                    <p class="description"><?php _e('<b>Note</b>: This integration requires a compatible consent management provider.', 'wp-statistics'); ?></p>
                    <?php if (\WP_STATISTICS\Option::get('privacy_audit', false)) : ?>
                        <p class="description">
                            <?php echo sprintf(
                            // translators: %s: Consent option.
                                __('Recommended Category: <b>%s</b>', 'wp-statistics'),
                                \WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent::getStatus() === 'success' ? esc_html__('Functional or Statistics-Anonymous', 'wp-statistics') : esc_html__('Statistics', 'wp-statistics')
                            ); ?>
                            <br/>
                            <?php echo \WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent::getStatus() === 'success' ?
                                esc_html__('WP Statistics, based on your settings, does not use cookies or other personally identifiable information (PII). Therefore, you could use it without notifying users. However, informing users about this can improve your transparency and demonstrate respect for their privacy.', 'wp-statistics') :
                                sprintf(
                                // translators: %s: Privacy Audit page link.
                                    __('WP Statistics, based on your settings, collects data that can be considered as personally identifiable information (PII). For more information, see the <a href="%s" target="_blank">Privacy Audit</a>.', 'wp-statistics'),
                                    esc_url(admin_url('admin.php?page=wps_privacy-audit_page'))
                                ); ?>
                        </p>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="description">
                        <?php echo sprintf(
                        // translators: %s: WP Consent API link.
                            __('<b>Notice: To use this feature, you need to install and activate the <a href="%s" target="_blank">WP Consent API</a> WordPress plugin.</b>', 'wp-statistics'),
                            'https://wordpress.org/plugins/wp-consent-api/'
                        ); ?>
                    </p>
                <?php endif; ?>
                <p class="description">
                    <?php echo sprintf(
                    // translators: %s: Documentation link.
                        __('For more details, please refer to our <a href="%s" target="_blank">documentation</a>.', 'wp-statistics'),
                        'https://wp-statistics.com/resources/wp-consent-level-integration/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'
                    ); ?>
                </p>
            </td>
        </tr>

        <?php if ($isWpConsentApiActive && WP_STATISTICS\Option::get('consent_level_integration', 'disabled') !== 'disabled') : ?>
            <tr valign="top">
                <th scope="row">
                    <label for="anonymous_tracking"><?php _e('Anonymous Tracking', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="anonymous_tracking" type="checkbox" value="1" name="wps_anonymous_tracking" <?php echo WP_STATISTICS\Option::get('anonymous_tracking', false) == true ? 'checked="checked"' : ''; ?> />
                    <label for="anonymous_tracking"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('When this option is enabled, all users will be tracked anonymously by default, without recording any Personally Identifiable Information (PII), regardless of consent. This anonymous tracking data is classified as "Functional" to align with privacy regulations. PII data will only be collected when explicit consent is provided by the website visitor.', 'wp-statistics'); ?></p>
                    <p class="description"><?php _e('<b>Note</b>: This feature is currently in beta and enables user tracking while adhering to privacy laws. Users are advised to review and ensure compliance with applicable legal requirements in their jurisdiction.', 'wp-statistics'); ?></p>
                </td>
            </tr>
        <?php endif; ?>

        <tr valign="top">
            <th scope="row">
                <label for="do_not_track"><?php esc_html_e('Do Not Track (DNT)', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="do_not_track" type="checkbox" value="1" name="wps_do_not_track" <?php echo WP_STATISTICS\Option::get('do_not_track') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="do_not_track"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e("Respects the visitor's browser setting to not track their web activity. Privacy laws like GDPR do not mandate this feature, but activating it demonstrates a commitment to privacy. Be aware that with DNT respected, information from visitors preferring not to be tracked will not be collected.", 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<?php submit_button(esc_html__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='privacy-settings'")); ?>
