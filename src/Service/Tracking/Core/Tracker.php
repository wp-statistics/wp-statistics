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
     * @throws Exception If visitor is excluded by rules.
     */
    public function record(): void
    {
        RateLimiter::check();

        $payload = Payload::parse();
        $visitor = new Visitor($payload);

        $this->checkExclusions($visitor);

        $visitorId  = EntityFactory::visitor($visitor)->record();
        $deviceIds  = EntityFactory::device($visitor)->record();
        $geoIds     = EntityFactory::geo($visitor)->record();
        $localeIds  = EntityFactory::locale($visitor)->record();
        $referrerId = EntityFactory::referrer($visitor)->record();

        $sessionId  = EntityFactory::session($visitor)->record(
            $visitorId, $deviceIds, $geoIds, $localeIds, $referrerId
        );

        EntityFactory::view($visitor)->record($sessionId);
    }

    /**
     * Record engagement time for the current visitor's active session.
     *
     * @param int $engagementTimeMs Engagement time in milliseconds.
     * @return bool True if a session was found and updated.
     */
    public function recordEngagement(int $engagementTimeMs): bool
    {
        $visitor = new Visitor();

        return EntityFactory::session($visitor)->updateEngagement($engagementTimeMs);
    }

    /**
     * Check exclusion rules and record/throw if visitor is excluded.
     *
     * @throws Exception If visitor matches an exclusion rule.
     */
    private function checkExclusions(Visitor $visitor): void
    {
        $exclusion = Exclusions::check($visitor);

        if (!empty($exclusion['exclusion_match'])) {
            Exclusions::record($exclusion);
            throw new Exception($exclusion['exclusion_reason'], 200);
        }
    }
}
