<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Service\Tracking\Pipeline\Visitor;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;

/**
 * Base entity class for tracking-related entities.
 *
 * Provides a common constructor accepting a read-only Visitor,
 * and initializes a UserAgentService when available.
 */
abstract class BaseEntity
{
    /**
     * Resolved visitor data for the current hit.
     *
     * @var Visitor
     */
    protected $visitor;

    /**
     * Service for retrieving user agent details (browser/device).
     * May be null if not available.
     *
     * @var UserAgentService|null
     */
    protected $userAgent;

    /**
     * BaseEntity constructor.
     *
     * @param Visitor $visitor Resolved visitor data for the current hit.
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor   = $visitor;
        $this->userAgent = $visitor->getUserAgent();
    }

    /**
     * Checks whether the specified tracking entity is enabled via a WordPress filter.
     *
     * Constructs a dynamic filter name based on the entity name and returns the filtered result.
     * This allows plugins or themes to conditionally disable tracking for specific data types.
     *
     * @param string $entityName The name of the tracking entity (e.g., 'device_types', 'device_resolutions').
     * @return bool
     */
    protected function isActive($entityName)
    {
        $filterName = 'wp_statistics_active_' . esc_html($entityName);

        return (has_filter($filterName)) ? apply_filters($filterName, true) : true;
    }
}
