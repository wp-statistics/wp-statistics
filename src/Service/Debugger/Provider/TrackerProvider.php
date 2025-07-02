<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Components\Assets;
use WP_Statistics\Components\AssetNameObfuscator;
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
     *
     * @var string
     */
    private $trackerPath;

    /**
     * Stores tracker status information
     * Contains file existence, path, and cache status
     *
     * @var array
     */
    private $trackerStatus;

    /**
     * Arguments array for making remote requests.
     * Contains configuration options and parameters used when performing requests.
     *
     * @var array Configuration arguments for remote requests
     */
    private $args = [];

    /**
     * Initialize tracker provider with necessary setup
     */
    public function __construct()
    {
        $this->args = [
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ];

        $this->trackerPath = Assets::getSrc('js/tracker.js', Option::get('bypass_ad_blockers'), WP_STATISTICS_URL);
        $this->initializeData();
    }

    /**
     * Check if AJAX hit recording is blocked.
     *
     * @return bool
     */
    public function checkHitRecording()
    {
        $adBlocker = Option::get('bypass_ad_blockers', false);

        return $adBlocker ? $this->checkAjaxHit() : $this->checkRestHit();
    }

    /**
     * Check AJAX endpoint for hit recording
     *
     * @return bool Returns true if request works, false if blocked or invalid
     */
    private function checkAjaxHit()
    {
        $ajax_url      = admin_url('admin-ajax.php');
        $remoteRequest = new RemoteRequest(
            $ajax_url,
            'POST',
            [
                'action' => 'wp_statistics_hit_record'
            ],
            $this->args
        );

        $remoteRequest->execute(false, false);

        $response     = $remoteRequest->getResponse();
        $responseCode = $remoteRequest->getResponseCode();

        if ($this->isCloudflareChallenge($response) && 403 === $responseCode) {
            return true;
        }

        return $remoteRequest->isValidJsonResponse();
    }

    /**
     * Check REST API endpoint for hit recording
     *
     * @return bool Returns true if request works, false if blocked or invalid
     */
    private function checkRestHit()
    {
        $rest_url      = site_url('index.php?rest_route=/wp-statistics/v2/hit');
        $remoteRequest = new RemoteRequest(
            $rest_url,
            'POST',
            [],
            $this->args
        );

        $remoteRequest->execute(false, false);

        $response     = $remoteRequest->getResponse();
        $responseCode = $remoteRequest->getResponseCode();

        if ($this->isCloudflareChallenge($response) && 403 === $responseCode) {
            return true;
        }

        return $remoteRequest->isValidJsonResponse();
    }

    /**
     * Determines if a response indicates a Cloudflare challenge page.
     *
     * @param mixed $response The response array containing headers
     * @return bool True if response indicates a Cloudflare challenge, false otherwise
     */
    private function isCloudflareChallenge($response)
    {
        if (!isset($response['headers']) || !is_object($response['headers'])) {
            return false;
        }

        $server = $response['headers']->offsetGet('server') ?? '';
        if ($server !== 'cloudflare') {
            return false;
        }

        $cfMitigated = $response['headers']->offsetGet('cf-mitigated') ?? '';

        return $cfMitigated === 'challenge';
    }

    /**
     * Get tracker status information
     *
     * @return array Array containing tracker existence, path and cache status
     */
    public function getTrackerStatus()
    {
        return $this->trackerStatus;
    }

    /**
     * Initialize tracker status data
     * Sets up the initial tracker status array with all required information
     */
    private function initializeData()
    {
        $fileExists = $this->executeTrackerCheck();

        $this->trackerStatus = [
            'exists'             => $fileExists,
            'path'               => $this->trackerPath,
            'cacheStatus'        => $this->getCacheStatus(),
            'hitRecordingStatus' => $this->checkHitRecording()
        ];
    }

    /**
     * Execute remote request to check tracker file
     * Performs a HEAD request to verify file accessibility
     *
     * @return bool Whether tracker file exists and is accessible
     */
    public function executeTrackerCheck()
    {
        $parsedUrl = parse_url($this->trackerPath);

        if (empty($parsedUrl['path'])) {
            return false;
        }

        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);

            $assetNameObfuscator = new AssetNameObfuscator();
            $dynamicAssetKey     = $assetNameObfuscator->getDynamicAssetKey();

            if (isset($queryParams[$dynamicAssetKey])) {
                $response = wp_safe_remote_head($this->trackerPath, ['sslverify' => false]);

                if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        $urlPath        = $parsedUrl['path'];
        $trimmedUrlPath = ltrim($urlPath, '/');

        $wpContentPosition = strpos($trimmedUrlPath, 'wp-content/');

        if ($wpContentPosition === false) {
            return false;
        }

        $relativeFilePath = substr($trimmedUrlPath, $wpContentPosition + strlen('wp-content/'));
        $absoluteFilePath = WP_CONTENT_DIR . '/' . $relativeFilePath;

        return file_exists($absoluteFilePath) && is_readable($absoluteFilePath);
    }

    /**
     * Check if cache plugin is active
     *
     * @return bool True if cache plugin is enabled, false otherwise
     */
    public function getCacheStatus()
    {
        $cacheInfo = Helper::checkActiveCachePlugin();
        return $cacheInfo['status'] ?? false;
    }

    /**
     * Checks if a cache plugin is active and returns its name.
     *
     * @return string The active cache plugin name, or an empty string if none is active.
     */
    public function getCachePlugin()
    {
        $cacheInfo = Helper::checkActiveCachePlugin();
        return $cacheInfo['plugin'] ?? '';
    }
}
