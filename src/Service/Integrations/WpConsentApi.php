<?php

namespace WP_Statistics\Service\Integrations;

use WP_CONSENT_API;

class WpConsentApi
{
    /**
     * Checks if "WP Consent API" plugin is activated.
     *
     * @return  bool
     */
    public static function isWpConsentApiActive()
    {
        return class_exists(WP_CONSENT_API::class);
    }
}
