<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use WP_Statistics\Abstracts\BaseTracking;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Tracking\HitRequest;

/**
 * Handles hit tracking for visitors via the JS tracker's REST/AJAX requests.
 *
 * Integrates with the exclusion system to respect rules such as user roles and IP blocks.
 *
 * SHORTINIT compatibility
 * -----------------------
 * This class and everything it calls must work in WordPress SHORTINIT mode
 * (used by the direct file endpoint in mu-plugins). SHORTINIT only loads:
 * $wpdb, plugin.php (hooks), formatting.php (sanitization), functions.php
 * (options/utilities), and the object cache.
 *
 * NOT available: l10n.php, pluggable.php, link-template.php, http.php,
 * user.php, WP_User, REST API, themes, or other plugins.
 *
 * Rules for code in this pipeline:
 * - No translation wrappers (__(), esc_html__(), etc.) — use plain strings.
 * - No WP_User or get_user_by() — use User::getRolesById() for role lookups.
 * - No wp_salt() or wp_generate_password() — use AUTH constants or random_bytes().
 * - No is_user_logged_in() or get_current_user_id() — user ID comes from HitRequest.
 * - No admin-only code paths (DateRange, UserPreferences, etc.).
 * - Remaining polyfills (home_url, wp_parse_url) are in MuPlugin/polyfills.php.
 */
class Hits extends BaseTracking
{
    /**
     * Record a hit including visitor, device, geo, locale, referrer, session, view, and parameter tracking.
     *
     * @param VisitorProfile|null $visitorProfile Optional profile object.
     * @return array Exclusion data if visitor was excluded.
     * @throws Exception If visitor is excluded by rules.
     *
     * @todo UserAgent has very bad performance we need to discuss about it.
     */
    public function record($visitorProfile = null)
    {
        $visitorProfile = $this->resolveProfile($visitorProfile);

        $hitRequest = HitRequest::create();
        $visitorProfile->setHitRequest($hitRequest);

        $exclusion = $this->checkAndThrowIfExcluded($visitorProfile);

        EntityFactory::visitor($visitorProfile)
            ->record();

        EntityFactory::device($visitorProfile)
            ->recordType()
            ->recordOs()
            ->recordBrowser()
            ->recordBrowserVersion()
            ->recordResolution();

        EntityFactory::geo($visitorProfile)
            ->recordCountry()
            ->recordCity();

        EntityFactory::locale($visitorProfile)
            ->recordLanguage()
            ->recordTimezone();

        EntityFactory::referrer($visitorProfile)
            ->record();

        EntityFactory::session($visitorProfile)
            ->record();

        EntityFactory::view($visitorProfile)
            ->record();

        return $exclusion;
    }
}
