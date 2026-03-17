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
