<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use WP_Statistics\Abstracts\BaseTracking;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Utils\Request;

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
        $exclusion      = $this->checkAndThrowIfExcluded($visitorProfile);

        $resourceUriId = Request::get('resourceUriId', 0);
        $resourceId    = Request::get('resource_id', null);

        // resourceUriId must be positive (auto-increment ID)
        // resource_id can be 0 for special pages like 404, search, home
        if (empty($resourceUriId) || $resourceId === null) {
            throw new Exception(esc_html__('Missing or invalid resource identifiers: resourceId and/or resourceUriId.', 'wp-statistics'), 200);
            return;
        }

        $resourceUri = Request::get('resourceUri', '');

        $visitorProfile->setResourceUriId($resourceUriId);
        $visitorProfile->setResourceUri($resourceUri);
        $visitorProfile->setResourceId($resourceId);

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

        // UTM parameters are now recorded in Session::record() on session creation

        return $exclusion;
    }
}
