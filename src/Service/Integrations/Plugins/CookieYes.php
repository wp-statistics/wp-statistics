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
        if (empty($_COOKIE['cookieyes-consent'])) {
            return false;
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
}
