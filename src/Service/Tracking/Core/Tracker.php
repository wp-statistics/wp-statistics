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
     * When a returning visitor has an active session, dimension resolution
     * (device, geo, locale, referrer) is skipped because the session already
     * holds those FK references and they are not updated on subsequent views.
     *
     * @throws Exception If visitor is excluded by rules.
     */
    public function record(): void
    {
        RateLimiter::check();

        $payload = Payload::parse();
        $visitor = new Visitor($payload);

        $this->checkExclusions($visitor);

        $visitorId = EntityFactory::visitor($visitor)->record();

        $sessionEntity = EntityFactory::session($visitor);
        $activeSession = $sessionEntity->getActive($visitorId);

        // Resolve dimensions when creating a new session, or when last-touch
        // attribution is enabled for returning visitors. Skipped by default
        // on warm hits because the session already holds these FK references.
        $needsDimensions = !$activeSession || $this->isLastTouchEnabled();

        $deviceIds  = $needsDimensions ? EntityFactory::device($visitor)->record()   : null;
        $geoIds     = $needsDimensions ? EntityFactory::geo($visitor)->record()      : null;
        $localeIds  = $needsDimensions ? EntityFactory::locale($visitor)->record()   : null;
        $referrerId = $needsDimensions ? EntityFactory::referrer($visitor)->record() : null;

        $sessionId = $activeSession
            ? (int) $activeSession->ID
            : $sessionEntity->create($visitorId, $deviceIds, $geoIds, $localeIds, $referrerId);

        $viewId = EntityFactory::view($visitor)->record($sessionId);

        $sessionEntity->update(
            $sessionId,
            $viewId,
            $activeSession ?: null,
            $deviceIds,
            $geoIds,
            $localeIds,
            $referrerId
        );
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
     * Whether last-touch session attribution is enabled.
     *
     * When true, dimension resolution (device, geo, locale, referrer) runs on
     * every warm hit and the session FKs are updated to the latest values.
     * Default is false (first-touch) — dimensions are only set when the session
     * is created, saving ~10 DB queries per warm hit.
     *
     * @since 15.1.0
     * @return bool
     */
    private function isLastTouchEnabled(): bool
    {
        return (bool) apply_filters('wp_statistics_last_touch_attribution', false);
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
