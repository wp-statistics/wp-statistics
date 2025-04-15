<?php

namespace WP_STATISTICS\Abstracts;

use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\Exclusion;
use Exception;

/**
 * Abstract base class for tracking implementations such as Hits and UserOnline.
 *
 * Provides shared utility methods to support timestamping, profile resolution,
 * REST API key handling, and exclusion logic.
 */
abstract class BaseTracking
{
    /**
     * Key used to identify REST-based tracking requests.
     * Subclasses can override this value.
     *
     * @var string
     */
    protected $restHitsKey = '';

    /**
     * Retrieve the REST hit request key for tracking identification.
     *
     * @return string The key used to detect REST API tracking calls.
     */
    public function getRestHitsKey()
    {
        return $this->restHitsKey;
    }

    /**
     * Ensure a valid VisitorProfile object is available.
     *
     * @param VisitorProfile|null $profile Optional profile instance to use.
     * @return VisitorProfile A valid visitor profile instance.
     */
    protected function resolveProfile($profile = null)
    {
        if ($profile instanceof VisitorProfile) {
            return $profile;
        }

        return new VisitorProfile();
    }

    /**
     * Get the current timestamp according to the WordPress timezone.
     *
     * @return int Current local timestamp.
     */
    protected function getCurrentTimestamp()
    {
        return TimeZone::getCurrentTimestamp();
    }

    /**
     * Get the current date according to the WordPress timezone.
     *
     * @return string Current local date in 'Y-m-d' format.
     */
    protected function getCurrentDate()
    {
        return TimeZone::getCurrentDate();
    }

    /**
     * Check whether the visitor is excluded from tracking and throw an exception if so.
     *
     * @param VisitorProfile $profile The visitor profile being evaluated.
     * @return array Exclusion metadata if not excluded.
     * @throws Exception If the visitor is excluded from tracking.
     */
    protected function checkAndThrowIfExcluded(VisitorProfile $profile)
    {
        $exclusion = Exclusion::check($profile);

        if (! empty($exclusion['exclusion_match'])) {
            Exclusion::record($exclusion);
            throw new Exception($exclusion['exclusion_reason'], 200);
        }

        return $exclusion;
    }
}
