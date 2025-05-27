<?php
use WP_Statistics\Service\Integrations\IntegrationHelper;
?>

<script type="text/javascript">
    const toggleConsentIntegration = () => {
        const selectElement = document.getElementById('consent_integration');
        const consentCategories = document.getElementById('wps-consent-categories');
        const anonymousTracking = document.getElementById('wps-anonymous-tracking');
        const updateVisibility = (element, shouldShow) => {
            element.classList.toggle('wps-hide', !shouldShow);
        };
        switch (selectElement.value) {
            case 'wp_consent_api':
                updateVisibility(consentCategories, true);
                updateVisibility(anonymousTracking, true);
                break;
            default:
                updateVisibility(consentCategories, false);
                updateVisibility(anonymousTracking, false);
                break;
        }
    };
    jQuery(document).ready(() => {
        jQuery('#consent_integration').on('change', toggleConsentIntegration);
        toggleConsentIntegration(); // Initialize on page load
    });

</script>
<h2 class="wps-settings-box__title">
    <span>
        <?php esc_html_e('Privacy & Data Protection', 'wp-statistics'); ?>
     </span>
    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/resources/data-protection-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings') ?>" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
</h2>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top" class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Data Protection', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top" data-id="anonymize_ip_addresses_tr">
            <th scope="row">
                <label for="anonymize_ips">
                    <span>
                        <?php esc_html_e('Anonymize IP Addresses', 'wp-statistics'); ?>
                        <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                            <a class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                        <?php endif ?>
                    </span>
                </label>
            </th>
            <td>
                <input id="anonymize_ips" type="checkbox" value="1" name="wps_anonymize_ips" <?php echo WP_STATISTICS\Option::get('anonymize_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="anonymize_ips"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('Masks the last segment of a user\'s IP address for privacy, complying with GDPR and preventing the full IP from being stored. More details can be found at <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Avoiding PII Data Collection.</a>', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped		 ?></p>
            </td>
        </tr>

        <tr valign="top" data-id="hash_ip_addresses_tr">
            <th scope="row">
                <label for="hash_ips">
                    <span>
                        <?php esc_html_e('Hash IP Addresses', 'wp-statistics'); ?>
                        <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                             <a class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                        <?php endif ?>
                    </span>
                </label>
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
        <tr valign="top" class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Privacy Compliance', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top" data-id="privacy_audit_tr">
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
        <tr valign="top" class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('User Preferences', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top" data-id="consent_level_integration_tr">
            <th scope="row">
                <label for="consent_integration"><?php esc_html_e('Consent Plugin Integration', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select id="consent_integration" name="wps_consent_integration">
                    <option value="" <?php selected(WP_STATISTICS\Option::get('consent_integration')); ?>><?php esc_html_e('None', 'wp-statistics'); ?></option>

                    <?php foreach (IntegrationHelper::getAllIntegrations() as $integration) : ?>
                        <option <?php disabled(!$integration->isActive()) ?> value="<?php echo esc_attr($integration->getKey()); ?>" <?php selected(WP_STATISTICS\Option::get('consent_integration'), $integration->getKey()); ?>><?php echo esc_html($integration->getName()); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e("Enable integration with supported consent management plugins, such as WP Consent API and Real Cookie Banner, to ensure WP Statistics respects user privacy preferences. When enabled, WP Statistics will only track data based on the consent settings provided by your active consent management plugin.", 'wp-statistics'); ?></p>
                <p class="description"><?php esc_html_e("Note: To use this feature, you must install and activate one of the supported consent management plugins.", 'wp-statistics'); ?></p>
                <p class="description"><?php _e("For step-by-step setup, refer to our <a target='_blank' href='https://wp-statistics.com/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'>detailed guide</a>.", 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="wps-consent-categories">
            <th scope="row">
                <label for="consent_level_integration"><?php esc_html_e('Consent Categories', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select id="consent_level_integration" name="wps_consent_level_integration">
                    <option value="disabled" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'disabled'); ?>><?php esc_html_e('Disabled', 'wp-statistics'); ?></option>
                    <option value="functional" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'functional'); ?>><?php esc_html_e('Functional', 'wp-statistics'); ?></option>
                    <option value="statistics-anonymous" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'statistics-anonymous'); ?>><?php esc_html_e('Statistics-Anonymous', 'wp-statistics'); ?></option>
                    <option value="statistics" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'statistics'); ?>><?php esc_html_e('Statistics', 'wp-statistics'); ?></option>
                    <option value="marketing" <?php selected(WP_STATISTICS\Option::get('consent_level_integration'), 'marketing'); ?>><?php esc_html_e('Marketing', 'wp-statistics'); ?></option>
                </select>
                <p class="description"><?php esc_html_e("When using WP Consent API, select the consent categories that WP Statistics should track. Only visitors who have consented to the selected categories will be tracked.", 'wp-statistics'); ?></p>

                <?php
                    $compatiblePlugins = IntegrationHelper::getIntegration('wp_consent_api')->getCompatiblePlugins();

                    if (empty($compatiblePlugins)) {
                        ?><p class="description">
                        <?php
                        printf(
                            __('%1$s: No recognized consent management plugin detected. Please install or activate a compatible plugin to ensure compliance with user consent.', 'wp-statistics'),
                            '<b>' . __('No Plugin Found', 'wp-statistics') . '</b>'
                        );
                        ?>
                        </p><?php
                    } else {
                        ?>
                        <p class="description">
                            <?php
                            echo sprintf(
                                __('<b>Plugin Found:</b> A recognized consent management plugin is active: %s. WP Statistics will comply with the user’s consent settings provided by this plugin.', 'wp-statistics'),
                                implode(', ', $compatiblePlugins)
                            );
                            ?>
                        </p>
                        <?php
                    }
                ?>

                <?php if (\WP_STATISTICS\Option::get('privacy_audit', false)) : ?>
                    <p class="description">
                        <?php echo sprintf(
                        // translators: %s: Consent option.
                            __('Recommended Category: <b>%s</b>', 'wp-statistics'),
                            \WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent::getStatus() === 'success' ? esc_html__('Functional or Statistics-Anonymous', 'wp-statistics') : esc_html__('Statistics', 'wp-statistics')
                        ); ?>
                    </p>
                <?php endif; ?>

                <p class="description">
                    <?php _e('More Information: Learn more about configuring WP Consent API and the available categories in our <a target="_blank" href="https://wp-statistics.com/resources/wp-consent-level-integration/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings">WP Consent API documentation</a>.', 'wp-statistics'); ?>
                    <br/>
                    <?php _e('Note: Selecting the correct categories ensures compliance with privacy laws and demonstrates respect for user preferences.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr valign="top" id="wps-anonymous-tracking">
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

        <tr valign="top" data-id="do_not_track_tr">
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
<?php submit_button(esc_html__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='privacy-settings'")); ?>