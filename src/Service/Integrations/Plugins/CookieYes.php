<?php

namespace WP_Statistics\Service\Integrations\Plugins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CookieYes extends AbstractIntegration
{
    protected $key = 'cookieyes';
    protected $path = 'cookie-law-info/cookie-law-info.php';

    /**
     * Returns the name of the integration.
     *
     * @return  string
     */
    public function getName()
    {
        return esc_html__('CookieYes', 'wp-statistics');
    }

    /**
     * CookieYes needs no registration, consent is read from its cookie.
     *
     * @return  void
     */
    public function register()
    {
    }

    /**
     * Checks the "analytics" category in the CookieYes consent cookie.
     * The cookie looks like "consentid:abc,consent:yes,action:yes,necessary:yes,analytics:yes".
     *
     * @return bool
     */
    public function hasConsent()
    {
        if (!array_key_exists('cookieyes-consent', $_COOKIE)) {
            return $this->hasLegacyConsent();
        }

        $consent = sanitize_text_field(wp_unslash($_COOKIE['cookieyes-consent']));

        foreach (explode(',', $consent) as $pair) {
            $parts = explode(':', $pair, 2);

            if (count($parts) === 2 && trim($parts[0]) === 'analytics') {
                return trim($parts[1]) === 'yes';
            }
        }

        return false;
    }

    /**
     * Return an array of js handles for this integration.
     * The result will be used as dependencies for the tracker js file
     *
     * CookieYes loads its banner from its own CDN when the site is connected
     * to the CookieYes web app, so there is no reliable handle to depend on.
     * The tracker reads the consent cookie when getCkyConsent() is missing.
     *
     * @return  array
     */
    public function getJsHandles()
    {
        return [];
    }

    /**
     * Tells the tracker whether CookieYes runs in legacy mode, so it only reads the
     * legacy cookies there.
     *
     * @return array
     */
    public function getStatus()
    {
        return array_merge(parent::getStatus(), [
            'legacy' => $this->isLegacyMode(),
        ]);
    }

    /**
     * CookieYes runs the older "GDPR Cookie Consent" banner in legacy mode.
     *
     * @return bool
     */
    private function isLegacyMode()
    {
        return function_exists('cky_is_legacy') && cky_is_legacy();
    }

    /**
     * CookieYes in its legacy mode (the older "GDPR Cookie Consent" banner) has no
     * cookieyes-consent cookie. It stores one cookie per category, such as
     * cookielawinfo-checkbox-analytics=yes, and viewed_cookie_policy for the
     * visitor's answer to the banner.
     *
     * The category cookie is written on the first page load from the category's
     * default state, before the visitor answers, so it only counts after the banner
     * was accepted (viewed_cookie_policy=yes), as CookieYes's own script blocker
     * checks. A banner without an Analytics category sets no analytics cookie, so
     * accepting the banner is enough there. Outside legacy mode these cookies are
     * leftovers and are ignored.
     *
     * @return bool
     */
    private function hasLegacyConsent()
    {
        if (!$this->isLegacyMode() || !isset($_COOKIE['viewed_cookie_policy'])) {
            return false;
        }

        if (sanitize_text_field(wp_unslash($_COOKIE['viewed_cookie_policy'])) !== 'yes') {
            return false;
        }

        return !isset($_COOKIE['cookielawinfo-checkbox-analytics'])
            || sanitize_text_field(wp_unslash($_COOKIE['cookielawinfo-checkbox-analytics'])) === 'yes';
    }
}
