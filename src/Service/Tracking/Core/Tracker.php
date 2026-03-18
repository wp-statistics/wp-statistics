<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use WP_Statistics\Entity\EntityFactory;

/**
 * Hit recording pipeline.
 *
 * Parses the request, resolves visitor data, checks exclusions,
 * then records all entities in order.
 *
 * Must work in WordPress SHORTINIT mode.
 */
class Tracker
{
    /**
     * Record a hit: visitor, device, geo, locale, referrer, session, view.
     *
     * @return array Exclusion metadata.
     * @throws Exception If visitor is excluded by rules.
     */
    public function record()
    {
        $payload = Payload::parse();
        $visitor = new Visitor($payload);

        $exclusion = Exclusions::check($visitor);

        if (!empty($exclusion['exclusion_match'])) {
            Exclusions::record($exclusion);
            throw new Exception($exclusion['exclusion_reason'], 200);
        }

        $visitorId  = EntityFactory::visitor($visitor)->record();
        $deviceIds  = EntityFactory::device($visitor)->record();
        $geoIds     = EntityFactory::geo($visitor)->record();
        $localeIds  = EntityFactory::locale($visitor)->record();
        $referrerId = EntityFactory::referrer($visitor)->record();

        $sessionId  = EntityFactory::session($visitor)->record(
            $visitorId, $deviceIds, $geoIds, $localeIds, $referrerId
        );

        EntityFactory::view($visitor)->record($sessionId);

        return $exclusion;
    }
}
