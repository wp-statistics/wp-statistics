<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Components\Assets;
use WP_Statistics\Components\AssetNameObfuscator;
use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\Option;
use WP_STATISTICS\RestAPI;
use WP_Statistics\Utils\Signature;
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
     * Details of the last hit endpoint check.
     *
     * @var array
     */
    private $hitRecordingDetails = [];

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
     * Check whether the hit endpoint accepts a visit.
     *
     * Sends a signed test hit with the same parameters tracker.js sends, marked
     * as a debugger test so it is not recorded. The check passes only when the
     * endpoint answers with a success status and `"status": true`.
     *
     * @return bool
     */
    public function checkHitRecording()
    {
        $adBlocker = Option::get('bypass_ad_blockers', false);

        return $adBlocker ? $this->checkAjaxHit() : $this->checkRestHit();
    }

    /**
     * Returns the details of the last hit endpoint check.
     *
     * @return array {
     *     @type string   $url          The endpoint that was called.
     *     @type int|null $responseCode The HTTP status code, or null when there was no response.
     *     @type string   $message      The error reported by the endpoint or the HTTP client, if any.
     * }
     */
    public function getHitRecordingDetails()
    {
        return $this->hitRecordingDetails;
    }

    /**
     * Check AJAX endpoint for hit recording ("Bypass Ad Blockers" enabled).
     *
     * @return bool Returns true if a test hit is accepted, false otherwise.
     */
    private function checkAjaxHit()
    {
        $params = array_merge($this->getTestHitParams(), [
            'action' => 'wp_statistics_hit_record',
        ]);

        return $this->sendTestHit(admin_url('admin-ajax.php'), $params);
    }

    /**
     * Check REST API endpoint for hit recording.
     *
     * Uses the same URL that tracker.js posts to.
     *
     * @return bool Returns true if a test hit is accepted, false otherwise.
     */
    private function checkRestHit()
    {
        return $this->sendTestHit(get_rest_url(null, RestAPI::$namespace . '/hit'), $this->getTestHitParams());
    }

    /**
     * Builds the parameters of a test hit, the same way the frontend builds them for tracker.js.
     *
     * @return array
     */
    private function getTestHitParams()
    {
        $sourceType = 'home';
        $sourceId   = 0;

        $params = [
            Hits::$rest_hits_key     => 1,
            Hits::$debugger_test_key => 1,
            'source_type'            => $sourceType,
            'source_id'              => $sourceId,
            'search_query'           => '',
            'page_uri'               => base64_encode('/'),
            'referred'               => '',
        ];

        if (Helper::isRequestSignatureEnabled()) {
            $params['signature'] = Signature::generate([$sourceType, $sourceId]);
        }

        return $params;
    }

    /**
     * Sends a test hit and checks that the endpoint accepted it.
     *
     * @param string $url
     * @param array $params
     * @return bool
     */
    private function sendTestHit($url, $params)
    {
        $remoteRequest = new RemoteRequest(
            $url,
            'POST',
            [],
            array_merge($this->args, ['body' => $params])
        );

        $remoteRequest->execute(false, false);

        $response     = $remoteRequest->getResponse();
        $responseCode = $remoteRequest->getResponseCode();

        $this->hitRecordingDetails = [
            'url'          => $url,
            'responseCode' => $responseCode ? (int)$responseCode : null,
            'message'      => '',
        ];

        if (is_wp_error($response)) {
            $this->hitRecordingDetails['message'] = $response->get_error_message();
            return false;
        }

        // A Cloudflare challenge blocks this server-side request, but real visitors pass it.
        if ($this->isCloudflareChallenge($response) && 403 === $responseCode) {
            return true;
        }

        $body = json_decode((string)$remoteRequest->getResponseBody(), true);

        if (!is_array($body)) {
            $this->hitRecordingDetails['message'] = __('The endpoint did not return a valid JSON response.', 'wp-statistics');
            return false;
        }

        $isAccepted = $responseCode >= 200 && $responseCode < 300 && isset($body['status']) && true === $body['status'];

        if (!$isAccepted) {
            $this->hitRecordingDetails['message'] = $this->getErrorMessage($body);
        }

        return $isAccepted;
    }

    /**
     * Reads the error message from a hit endpoint reply.
     *
     * Handles both the WP Statistics format (`data`) and the WordPress REST error format (`message`).
     *
     * @param array $body
     * @return string
     */
    private function getErrorMessage($body)
    {
        if (!empty($body['message']) && is_string($body['message'])) {
            return $body['message'];
        }

        if (!empty($body['data']) && is_string($body['data'])) {
            return $body['data'];
        }

        return '';
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
            'exists'              => $fileExists,
            'path'                => $this->trackerPath,
            'frontendStatus'      => $this->checkFrontendTracker(),
            'cacheStatus'         => $this->getCacheStatus(),
            'hitRecordingStatus'  => $this->checkHitRecording(),
            'hitRecordingDetails' => $this->getHitRecordingDetails(),
        ];
    }

    /**
     * Checks the tracker URL included in the public homepage response.
     *
     * @return array
     */
    private function checkFrontendTracker()
    {
        $status = [
            'checked'      => false,
            'found'        => false,
            'url'          => '',
            'responseCode' => null,
            'isAccessible' => null,
        ];

        $response = wp_safe_remote_get(home_url('/'), array_merge($this->args, [
            'redirection' => 3,
        ]));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $status;
        }

        $status['checked'] = true;
        $trackerUrl        = $this->extractFrontendTrackerUrl(wp_remote_retrieve_body($response));

        if (empty($trackerUrl)) {
            if (!Option::get('use_cache_plugin')) {
                $status['isAccessible'] = true;
            }

            return $status;
        }

        $status['found'] = true;
        $status['url']   = $trackerUrl;

        $trackerResponse = wp_safe_remote_get($trackerUrl, array_merge($this->args, [
            'redirection'         => 3,
            'limit_response_size' => 1024,
        ]));

        if (is_wp_error($trackerResponse)) {
            return $status;
        }

        $status['responseCode'] = wp_remote_retrieve_response_code($trackerResponse);
        $status['isAccessible'] = $status['responseCode'] === 200;

        return $status;
    }

    /**
     * Extracts the WP Statistics tracker URL from frontend HTML.
     *
     * @param string $html
     * @return string
     */
    private function extractFrontendTrackerUrl($html)
    {
        if (empty($html) || !class_exists('WP_HTML_Tag_Processor')) {
            return '';
        }

        $processor            = new \WP_HTML_Tag_Processor($html);
        $assetNameObfuscator  = new AssetNameObfuscator();
        $dynamicAssetKey      = $assetNameObfuscator->getDynamicAssetKey();

        while ($processor->next_tag(['tag_name' => 'SCRIPT'])) {
            $id  = $processor->get_attribute('id');
            $src = $processor->get_attribute('src');

            if (!is_string($src) || empty($src)) {
                continue;
            }

            $isTrackerHandle = $id === 'wp-statistics-tracker-js';
            $isTrackerPath   = strpos($src, '/wp-statistics/assets/js/tracker.js') !== false;
            $isObfuscated    = strpos($src, '?' . $dynamicAssetKey . '=') !== false || strpos($src, '&' . $dynamicAssetKey . '=') !== false;

            if (!$isTrackerHandle && !$isTrackerPath && !$isObfuscated) {
                continue;
            }

            return $this->resolveFrontendTrackerUrl($src);
        }

        return '';
    }

    /**
     * Resolves a frontend script source to an absolute URL.
     *
     * @param string $src
     * @return string
     */
    private function resolveFrontendTrackerUrl($src)
    {
        $homeUrl = home_url('/');

        if (strpos($src, '//') === 0) {
            $scheme = wp_parse_url($homeUrl, PHP_URL_SCHEME);
            $src    = $scheme . ':' . $src;
        } elseif (strpos($src, '/') === 0) {
            $parts = wp_parse_url($homeUrl);

            if (empty($parts['scheme']) || empty($parts['host'])) {
                return '';
            }

            $host = $parts['host'];
            if (strpos($host, ':') !== false && strpos($host, '[') !== 0) {
                $host = '[' . $host . ']';
            }

            $port = isset($parts['port']) ? ':' . $parts['port'] : '';
            $src  = $parts['scheme'] . '://' . $host . $port . $src;
        } elseif (empty(wp_parse_url($src, PHP_URL_SCHEME))) {
            $src = home_url('/' . ltrim($src, '/'));
        }

        return esc_url_raw($src);
    }

    /**
     * Execute remote request to check tracker file
     * Performs a HEAD request to verify file accessibility
     *
     * @return bool Whether tracker file exists and is accessible
     */
    public function executeTrackerCheck()
    {
        $parsedUrl = wp_parse_url($this->trackerPath);

        if (empty($parsedUrl['path'])) {
            return false;
        }

        // If the file is dynamic (obfuscated query string), try remote HEAD check
        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);

            $assetNameObfuscator = new AssetNameObfuscator();
            $dynamicAssetKey     = $assetNameObfuscator->getDynamicAssetKey();

            if (isset($queryParams[$dynamicAssetKey])) {
                $response = wp_safe_remote_head($this->trackerPath, ['sslverify' => false]);

                return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
            }
        }

        // Normalize path by removing leading slash
        $urlPath = ltrim($parsedUrl['path'], '/');

        // Try matching with actual wp-content directory
        $contentDirName  = basename(WP_CONTENT_DIR); // usually 'wp-content'
        $contentPosition = strpos($urlPath, $contentDirName . '/');

        if ($contentPosition === false) {
            return false;
        }

        $relativeFilePath = substr($urlPath, $contentPosition + strlen($contentDirName . '/'));
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
