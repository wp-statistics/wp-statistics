<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Components\Assets;
use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Debugger\AbstractDebuggerProvider;

/**
 * Provider for handling tracker file status and cache information
 * 
 * This class implements DebuggerServiceProviderInterface to provide information
 * about the WP Statistics tracker file and caching status. It checks for the
 * existence and accessibility of the tracker file and cache plugin status.
 */
class TrackerProvider extends AbstractDebuggerProvider
{
    /**
     * Path to the tracker JavaScript file
     * Stores the complete URL to the tracker.js file
     */
    private string $trackerPath;

    /**
     * Stores tracker status information
     * Contains file existence, path, and cache status
     */
    private array $trackerStatus;

    /**
     * RemoteRequest instance for checking tracker file
     * Used to perform HEAD requests to verify file accessibility.
     * 
     * @var RemoteRequest
     */
    private $remoteRequest;

    /**
     * Initialize tracker provider with necessary setup
     */
    public function __construct()
    {
        $this->trackerPath = Assets::getSrc('js/tracker.js', Option::get('bypass_ad_blockers'));
        $this->remoteRequest = new RemoteRequest($this->trackerPath, 'HEAD');
        $this->initializeData();
    }

    /**
     * Get tracker status information
     * 
     * @return array Array containing tracker existence, path and cache status
     */
    public function getTrackerStatus(): array
    {
        return $this->trackerStatus;
    }

    /**
     * Initialize tracker status data
     * Sets up the initial tracker status array with all required information
     */
    private function initializeData(): void
    {
        $this->trackerStatus = [
            'exists' => $this->executeTrackerCheck(),
            'path' => $this->trackerPath,
            'cacheStatus' => $this->getCacheStatus()
        ];
    }

    /**
     * Execute remote request to check tracker file
     * Performs a HEAD request to verify file accessibility
     *
     * @return bool Whether tracker file exists and is accessible
     */
    public function executeTrackerCheck(): bool
    {
        $trackerFile = $this->remoteRequest->execute(false, false, HOUR_IN_SECONDS, true);
        return !empty($trackerFile);
    }

    /**
     * Check if cache plugin is active
     * 
     * @return bool True if cache plugin is enabled, false otherwise
     */
    public function getCacheStatus(): bool
    {
        $cacheInfo = Helper::checkActiveCachePlugin();

        return $cacheInfo['status'] ?? false;
    }

    /**
     * Checks if a cache plugin is active and returns its name.
     *
     * @return string The active cache plugin name, or an empty string if none is active.
     */
    public function getCachePlugin(): string
    {
        $cacheInfo = Helper::checkActiveCachePlugin();
        return $cacheInfo['plugin'] ?? '';
    }
}
