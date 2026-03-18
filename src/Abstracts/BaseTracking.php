<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Tracking\Core\Exclusion;
use WP_Statistics\Service\Tracking\Core\HitContext;
use Exception;

/**
 * Abstract base class for tracking implementations such as Hits.
 *
 * Provides shared utility methods to support timestamping, profile resolution,
 * REST API key handling, and exclusion logic.
 *
 * @since 15.0.0
 */
abstract class BaseTracking
{
    /**
     * Get the current timestamp according to the WordPress timezone.
     *
     * @return int Current local timestamp.
     */
    protected function getCurrentTimestamp()
    {
        return DateTime::getCurrentTimestamp();
    }

    /**
     * Get the current date according to the WordPress timezone.
     *
     * @return string Current local date in 'Y-m-d' format.
     */
    protected function getCurrentDate()
    {
        return DateTime::get();
    }

    /**
     * Check whether the visitor is excluded from tracking and throw an exception if so.
     *
     * @param HitContext $context The read-only hit context being evaluated.
     * @return array Exclusion metadata if not excluded.
     * @throws Exception If the visitor is excluded from tracking.
     */
    protected function checkAndThrowIfExcluded(HitContext $context)
    {
        $exclusion = Exclusion::check($context);

        if (!empty($exclusion['exclusion_match'])) {
            Exclusion::record($exclusion);
            throw new Exception($exclusion['exclusion_reason'], 200);
        }

        return $exclusion;
    }
}
