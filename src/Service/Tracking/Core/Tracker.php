<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use WP_Statistics\Abstracts\BaseTracking;
use WP_Statistics\Entity\EntityFactory;

/**
 * Handles hit tracking for visitors via the JS tracker's REST/AJAX requests.
 *
 * SHORTINIT compatibility
 * -----------------------
 * This class and everything it calls must work in WordPress SHORTINIT mode.
 */
class Tracker extends BaseTracking
{
    /**
     * Record a hit including visitor, device, geo, locale, referrer, session, view, and parameter tracking.
     *
     * @return array Exclusion data if visitor was excluded.
     * @throws Exception If visitor is excluded by rules.
     */
    public function record()
    {
        $hitRequest = HitRequest::create();
        $context    = new HitContext($hitRequest);

        $exclusion = $this->checkAndThrowIfExcluded($context);

        $visitorId  = EntityFactory::visitor($context)->record();

        $deviceIds  = EntityFactory::device($context)->record();

        $geoIds     = EntityFactory::geo($context)->record();

        $localeIds  = EntityFactory::locale($context)->record();

        $referrerId = EntityFactory::referrer($context)->record();

        $sessionId  = EntityFactory::session($context)->record(
            $visitorId, $deviceIds, $geoIds, $localeIds, $referrerId
        );

        EntityFactory::view($context)->record($sessionId);

        return $exclusion;
    }
}
