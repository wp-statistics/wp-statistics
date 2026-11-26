<?php

namespace WP_Statistics\Service\Integrations;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

/**
 * "Only visitors who accept are counted" check.
 *
 * A consent plugin is selected and working, but nothing lets WP Statistics count the visitors who
 * do not accept (or never answer) the banner. On an opt-in banner that can be most visitors, so the
 * numbers drop and nothing looks broken. One rule, used by the admin notice (GeneralNotices) and the
 * Tracker Debugger consent card, so both always agree.
 *
 * The fix depends on the plugin:
 *  - Borlabs Cookie reads WP Statistics' own "Anonymous Tracking" setting, so the fix turns it on.
 *  - WP Consent API, CookieYes and Real Cookie Banner decide in the consent plugin itself; "Anonymous
 *    Tracking" does nothing there. The fix is to set Consent Plugin Integration to None, for sites
 *    where WP Statistics does not need consent (no cookies, hashed IPs).
 *
 * A selected plugin that is not active or not set up is not covered here: the "Consent Integration
 * Needs Attention" notice handles that, so the two never show together.
 */
class ConsentOnlyTracking
{
    const ACTION = 'wp_statistics_consent_only_tracking_fix';
    const NONCE  = 'wp_statistics_consent_only_tracking_fix';

    const FIX_ANONYMOUS = 'anonymous';
    const FIX_NONE      = 'none';

    /**
     * Registers the handler of the fix button.
     *
     * @return void
     */
    public static function register()
    {
        add_action('admin_post_' . self::ACTION, [self::class, 'handleFix']);
    }

    /**
     * What to tell the site owner, or null when every visitor can be counted.
     *
     * @return array|null ['plugin' => string, 'message' => string, 'fix' => string, 'button' => string, 'url' => string]
     */
    public static function getInfo()
    {
        $integration = IntegrationHelper::getActiveIntegration();

        if (empty($integration) || !empty($integration->getConfigurationWarning())) {
            return null;
        }

        $name = $integration->getName();

        if ($integration->getKey() === 'borlabs_cookie') {
            if ($integration->trackAnonymously()) {
                return null;
            }

            return [
                'plugin'  => $name,
                'message' => sprintf(
                    /* translators: %s: consent plugin name, for example "Borlabs Cookie". */
                    esc_html__('WP Statistics only counts visitors who accept %s. Visitors who decline or never answer the banner are not counted. Turn on Anonymous Tracking to count them without personal data.', 'wp-statistics'),
                    $name
                ),
                'fix'     => self::FIX_ANONYMOUS,
                'button'  => esc_html__('Turn on Anonymous Tracking', 'wp-statistics'),
                'url'     => self::getFixUrl(self::FIX_ANONYMOUS),
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
            'url'     => self::getFixUrl(self::FIX_NONE),
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
     * Link of the fix button.
     *
     * @param string $fix self::FIX_ANONYMOUS or self::FIX_NONE.
     * @return string
     */
    public static function getFixUrl($fix)
    {
        return wp_nonce_url(
            add_query_arg(['action' => self::ACTION, 'fix' => $fix], admin_url('admin-post.php')),
            self::NONCE,
            'nonce'
        );
    }

    /**
     * Applies the fix and goes back to the Privacy settings.
     *
     * @return void
     */
    public static function handleFix()
    {
        check_admin_referer(self::NONCE, 'nonce');

        if (!User::Access('manage')) {
            wp_die(esc_html__('You do not have permission to change WP Statistics settings.', 'wp-statistics'), '', ['response' => 403]);
        }

        $fix = Request::get('fix');

        if ($fix === self::FIX_ANONYMOUS) {
            Option::update('anonymous_tracking', true);
            Notice::addFlashNotice(esc_html__('Anonymous Tracking is on. Visitors who decline the banner are now counted without personal data.', 'wp-statistics'), 'success');
        } elseif ($fix === self::FIX_NONE) {
            Option::update('consent_integration', '');
            Notice::addFlashNotice(esc_html__('Consent Plugin Integration is set to None. WP Statistics now counts every visitor.', 'wp-statistics'), 'success');
        }

        wp_safe_redirect(Menus::admin_url('settings', ['tab' => 'privacy-settings']) . '#consent_integration');
        exit;
    }
}
