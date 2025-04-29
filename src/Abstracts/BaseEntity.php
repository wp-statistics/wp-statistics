<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;
use WP_Statistics\Traits\ObjectCacheTrait;

/**
 * Base entity class for tracking-related entities.
 *
 * Provides a common constructor and profile assignment,
 * and initializes a UserAgentService when available.
 */
abstract class BaseEntity
{
    use ObjectCacheTrait;

    /**
     * VisitorProfile instance containing visitor/session metadata.
     *
     * @var VisitorProfile
     */
    protected $profile;

    /**
     * Service for retrieving user agent details (browser/device).
     * May be null if not provided by the profile.
     *
     * @var UserAgentService|null
     */
    protected $userAgent;

    /**
     * BaseEntity constructor.
     *
     * @param VisitorProfile $profile VisitorProfile to associate with this entity.
     */
    public function __construct($profile)
    {
        $this->setProfile($profile);
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

    /**
     * Assigns the VisitorProfile and initializes the UserAgentService.
     *
     * When the profile provides a getUserAgent() method, its return
     * is cached in $userAgent for efficient reuse.
     *
     * @param VisitorProfile $profile VisitorProfile instance.
     * @return void
     */
    protected function setProfile(VisitorProfile $profile)
    {
        $this->profile = $profile;

        if (!$this->userAgent && method_exists($profile, 'getUserAgent')) {
            $this->userAgent = $profile->getUserAgent();
        }
    }
}
