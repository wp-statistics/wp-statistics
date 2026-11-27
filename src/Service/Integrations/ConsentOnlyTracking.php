<?php

namespace WP_Statistics\Service\Integrations;


/**
 * "Only visitors who accept are counted" check.
 *
 * A consent plugin is selected and working, but nothing lets WP Statistics count the visitors who
 * do not accept (or never answer) the banner. On an opt-in banner that can be most visitors, so the
 * numbers drop and nothing looks broken. One rule, used by the admin notice (GeneralNotices) and the
 * Tracker Debugger consent card, so both always agree.
 *
 * The fix depends on the plugin:
 *  - Borlabs Cookie loads the tracker only after the visitor accepts, so the fix is in Borlabs (remove
 *    the WP Statistics service) plus setting the integration to None.
 *  - WP Consent API, CookieYes and Real Cookie Banner decide in the consent plugin itself; "Anonymous
 *    Tracking" does nothing there. The fix is to set Consent Plugin Integration to None, for sites
 *    where WP Statistics does not need consent (no cookies, hashed IPs).
 *
 * A selected plugin that is not active or not set up is not covered here: the "Consent Integration
 * Needs Attention" notice handles that, so the two never show together.
 */
class ConsentOnlyTracking
{
    const FIX_ANONYMOUS = 'anonymous';
    const FIX_NONE      = 'none';

    /**
     * What to tell the site owner, or null when every visitor can be counted.
     *
     * @return array|null ['plugin' => string, 'message' => string, 'fix' => string, 'button' => string, 'option' => string, 'value' => string]
     *   option/value feed the shared option updater (a.wps-option__updater, AjaxOptionUpdater).
     */
    public static function getInfo()
    {
        $integration = IntegrationHelper::getActiveIntegration();

        if (empty($integration) || !empty($integration->getConfigurationWarning())) {
            return null;
        }

        $name = $integration->getName();

        // Borlabs Cookie only loads the tracker for visitors who accept the WP Statistics service, so
        // nothing in WP Statistics (Anonymous Tracking included) can count the others. The fix is in
        // Borlabs: remove the service, then set the integration to None.
        if ($integration->getKey() === 'borlabs_cookie') {
            return [
                'plugin'  => $name,
                'message' => sprintf(
                    /* translators: %s: consent plugin name, for example "Borlabs Cookie". */
                    esc_html__('%1$s only loads WP Statistics for visitors who accept it, so visitors who decline or never answer the banner are not counted. To count everyone, remove the WP Statistics service in %1$s and set Consent Plugin Integration to None.', 'wp-statistics'),
                    $name
                ),
                'fix'     => self::FIX_NONE,
                'button'  => esc_html__('Set Consent Plugin Integration to None', 'wp-statistics'),
                'option'  => 'consent_integration',
                'value'   => '',
            ];
        }

        // Real Cookie Banner without the WP Statistics base service reports consent for every visitor
        // (the tracker reads the same thing), so everyone is counted. A service exists only when its
        // "cookie" value is set.
        if ($integration->getKey() === 'real_cookie_banner' && function_exists('wp_rcb_consent_given')) {
            $baseService = wp_rcb_consent_given('wp-statistics');
            if (empty($baseService['cookie'])) {
                return null;
            }
        }

        // An opt-out banner counts visitors until they decline, so nothing is missing by default.
        if ($integration->getKey() === 'wp_consent_api' && function_exists('wp_get_consent_type')
            && strpos((string)wp_get_consent_type(), 'optout') !== false) {
            return null;
        }

        return [
            'plugin'  => $name,
            'message' => sprintf(
                /* translators: %s: consent integration name, for example "WP Consent API". */
                esc_html__('WP Statistics only counts visitors who accept statistics cookies in your cookie banner (%s). Visitors who decline or never answer the banner are not counted. If WP Statistics does not need consent on your site, set Consent Plugin Integration to None.', 'wp-statistics'),
                $name
            ),
            'fix'     => self::FIX_NONE,
            'button'  => esc_html__('Set Consent Plugin Integration to None', 'wp-statistics'),
            'option'  => 'consent_integration',
            'value'   => '',
        ];
    }

    /**
     * Whether only visitors who accept the banner are counted.
     *
     * @return bool
     */
    public static function isActive()
    {
        return self::getInfo() !== null;
    }

    /**
     * The fix button: the shared option updater link (assets/js/option-updater.js + AjaxOptionUpdater),
     * which saves the option and reloads the page.
     *
     * @param array  $info  getInfo().
     * @param string $class Extra button classes.
     * @return string
     */
    public static function getFixButton($info, $class = 'button button-primary')
    {
        // Only offer "None" when WP Statistics records no personal data without the integration;
        // otherwise the notice keeps just its link to the settings.
        if (!self::isNoneSafe()) {
            return '';
        }

        return sprintf(
            '<a href="#" class="%1$s wps-option__updater" data-option="%2$s" data-value="%3$s">%4$s</a>',
            esc_attr($class),
            esc_attr($info['option']),
            esc_attr($info['value']),
            esc_html($info['button'])
        );
    }

    /**
     * Whether setting the integration to None leaves no setting that records personal data
     * (checked without the current integration's "every visit is anonymous" exemption).
     *
     * @return bool
     */
    public static function isNoneSafe()
    {
        return empty(\WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent::getPersonalDataSettings(false));
    }
}
