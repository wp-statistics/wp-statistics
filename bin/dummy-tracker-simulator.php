#!/usr/bin/env php
<?php
/**
 * WP Statistics Tracker.js Simulator
 *
 * Generates realistic test data by simulating Tracker.js requests to admin-ajax.php.
 * This exercises the full tracking pipeline (validation, signature, recording).
 *
 * Usage: php bin/dummy-tracker-simulator.php [options]
 *
 * Prerequisites:
 * - 'use_cache_plugin' option must be enabled (Client-side tracking)
 * - 'bypass_ad_blockers' option must be enabled (AJAX tracking)
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/../../../../wp-load.php');

// Ensure WP Statistics is loaded
if (!class_exists('WP_Statistics\Bootstrap')) {
    die("Error: WP Statistics plugin not found.\n");
}

use WP_Statistics\Components\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Signature;

/**
 * TrackerSimulator - Simulates Tracker.js requests to admin-ajax.php
 */
class TrackerSimulator
{
    /** @var array Configuration options */
    private $config = [
        'days' => 7,
        'visitors_per_day' => 50,
        'from' => null,
        'to' => null,
        'delay_ms' => 50,
        'verbose' => false,
        'dry_run' => false,
        'url' => null,
    ];

    /** @var string WordPress admin-ajax.php URL */
    private $ajaxUrl;

    /** @var string Site URL */
    private $siteUrl;

    /** @var array Loaded data files */
    private $countries;
    private $devices;
    private $referrers;
    private $userAgents;
    private $timezones;
    private $languages;

    /** @var array Prepared resources (posts with resource_uri_id) */
    private $resources = [];

    /** @var array Category archive resources */
    private $categoryResources = [];

    /** @var array Author archive resources */
    private $authorResources = [];

    /** @var array Tag archive resources */
    private $tagResources = [];

    /** @var array 404 error resources */
    private $notFoundResources = [];

    /** @var array Search term resources */
    private $searchResources = [];

    /** @var array|null Home page resource */
    private $homeResource = null;

    /** @var array Sample 404 URLs */
    private $notFoundUrls = [
        '/non-existent-page/',
        '/old-post-deleted/',
        '/wp-content/uploads/missing-file.pdf',
        '/broken-link-from-external/',
        '/typo-in-url/',
        '/page/999/',
        '/2019/old-article/',
        '/products/discontinued-item/',
        '/download/expired-file.zip',
        '/members/restricted-area/',
    ];

    /** @var array Sample search terms */
    private $searchTerms = [
        'wordpress tutorial',
        'how to create plugin',
        'best practices',
        'seo tips',
        'performance optimization',
        'theme development',
        'rest api',
        'gutenberg blocks',
        'woocommerce setup',
        'contact form',
    ];

    /** @var array Statistics */
    private $stats = [
        'requests_sent' => 0,
        'requests_successful' => 0,
        'requests_failed' => 0,
        'errors' => [],
    ];

    /** @var string Path to temporary mu-plugin for signature bypass */
    private $signatureBypassPlugin;

    /**
     * Constructor - Initialize the simulator
     */
    public function __construct(array $options = [])
    {
        $this->config = array_merge($this->config, $options);

        // Set date range
        $this->config['to'] = $this->config['to'] ?? date('Y-m-d');
        $this->config['from'] = $this->config['from'] ?? date('Y-m-d', strtotime("-{$this->config['days']} days"));

        // Get URLs (use custom URL if provided)
        if (!empty($this->config['url'])) {
            $this->siteUrl = $this->config['url'];
            $this->ajaxUrl = $this->config['url'] . '/wp-admin/admin-ajax.php';
        } else {
            $this->ajaxUrl = admin_url('admin-ajax.php');
            $this->siteUrl = home_url();
        }

        // Disable signature validation by creating a temporary mu-plugin
        $this->createSignatureBypassPlugin();

        // Verify requirements
        $this->verifyRequirements();

        // Load data files
        $this->loadDataFiles();

        // Prepare resources
        $this->prepareResources();
    }

    /**
     * Verify WP Statistics settings are correct for tracker simulation
     */
    private function verifyRequirements()
    {
        if (!Option::getValue('use_cache_plugin')) {
            echo "Warning: Client-side tracking (use_cache_plugin) is not enabled.\n";
            echo "The AJAX endpoint may not be registered. Attempting anyway...\n\n";
        }

        if (!Option::getValue('bypass_ad_blockers')) {
            echo "Warning: Bypass ad blockers option is not enabled.\n";
            echo "This simulator uses admin-ajax.php which requires this setting.\n";
            echo "Attempting anyway...\n\n";
        }

        // Ensure IP method reads X-Forwarded-For header (simulator sends IPs this way)
        $currentIpMethod = Option::getValue('ip_method', 'REMOTE_ADDR');
        if ($currentIpMethod !== 'HTTP_X_FORWARDED_FOR') {
            Option::updateValue('ip_method', 'HTTP_X_FORWARDED_FOR');
            echo "Auto-configured: ip_method set to HTTP_X_FORWARDED_FOR for geo lookup.\n";
        }

        // Ensure GeoIP is enabled
        if (!Option::getValue('geoip_enable')) {
            Option::updateValue('geoip_enable', true);
            echo "Auto-configured: GeoIP enabled.\n";
        }

        // Check if the site is reachable (skip in dry-run mode)
        if (!$this->config['dry_run']) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->ajaxUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if ($httpCode === 0 || $httpCode >= 500) {
                echo "Error: Cannot connect to {$this->ajaxUrl} (HTTP {$httpCode})\n";
                echo "Please ensure your local web server is running.\n";
                echo "Use --dry-run to test without making HTTP requests.\n\n";
                exit(1);
            }

            // If we were redirected, update the AJAX URL
            if ($finalUrl !== $this->ajaxUrl) {
                echo "Note: Redirected to {$finalUrl}\n";
                $this->ajaxUrl = $finalUrl;
                // Also update site URL based on the redirect
                $this->siteUrl = preg_replace('#/wp-admin/admin-ajax\.php$#', '', $finalUrl);
            }
        }
    }

    /**
     * Load JSON data files
     */
    private function loadDataFiles()
    {
        $dataDir = __DIR__ . '/data/';

        $this->countries = json_decode(file_get_contents($dataDir . 'countries.json'), true);
        $this->devices = json_decode(file_get_contents($dataDir . 'devices.json'), true);
        $this->referrers = json_decode(file_get_contents($dataDir . 'referrers.json'), true);
        $this->userAgents = json_decode(file_get_contents($dataDir . 'user-agents.json'), true);
        $this->timezones = json_decode(file_get_contents($dataDir . 'timezones.json'), true);
        $this->languages = json_decode(file_get_contents($dataDir . 'languages.json'), true);

        if (!$this->countries || !$this->devices || !$this->referrers) {
            die("Error: Failed to load data files from {$dataDir}\n");
        }
    }

    /**
     * Prepare WordPress posts as trackable resources
     */
    private function prepareResources()
    {
        $posts = get_posts([
            'posts_per_page' => 50,
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
        ]);

        if (empty($posts)) {
            die("Error: No published posts found. Create some posts first.\n");
        }

        foreach ($posts as $post) {
            // Get post terms (categories + tags) for cached_terms
            $termIds = [];
            $postTerms = wp_get_post_terms($post->ID, ['category', 'post_tag'], ['fields' => 'ids']);
            if (!is_wp_error($postTerms)) {
                $termIds = $postTerms;
            }
            $cachedTerms = !empty($termIds) ? implode(',', $termIds) : '';

            // Create or get resource record
            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => $post->post_type,
                'resource_id' => $post->ID,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type' => $post->post_type,
                    'resource_id' => $post->ID,
                    'cached_title' => $post->post_title,
                    'cached_author_id' => $post->post_author,
                    'cached_date' => $post->post_date,
                    'cached_terms' => $cachedTerms,
                ]);
            } else {
                // Update existing resource with cached_terms if needed
                RecordFactory::resource()->update(
                    ['cached_terms' => $cachedTerms],
                    ['ID' => $resourceId]
                );
            }

            // Get permalink path (without domain)
            $uri = str_replace(home_url(), '', get_permalink($post->ID));

            // Create or get resource_uri record
            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->resources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => $post->ID,
                'post_type' => $post->post_type,
                'uri' => $uri,
                'title' => $post->post_title,
            ];
        }

        echo "Prepared " . count($this->resources) . " post/page resources.\n";

        // Prepare additional resource types
        $this->prepareCategoryResources();
        $this->prepareAuthorResources();
        $this->prepareTagResources();
        $this->prepare404Resources();
        $this->prepareSearchResources();
        $this->prepareHomeResource();
    }

    /**
     * Prepare category archive pages as trackable resources
     */
    private function prepareCategoryResources()
    {
        $categories = get_categories(['hide_empty' => false]);

        foreach ($categories as $category) {
            $uri = str_replace(home_url(), '', get_category_link($category->term_id));

            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => 'category',
                'resource_id' => $category->term_id,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type' => 'category',
                    'resource_id' => $category->term_id,
                    'cached_title' => $category->name,
                    'cached_author_id' => 0,
                    'cached_date' => current_time('mysql'),
                ]);
            }

            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->categoryResources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => $category->term_id,
                'post_type' => 'category',
                'uri' => $uri,
                'title' => $category->name,
            ];
        }

        echo "Prepared " . count($this->categoryResources) . " category resources.\n";
    }

    /**
     * Prepare author archive pages as trackable resources
     */
    private function prepareAuthorResources()
    {
        $authors = get_users(['role__in' => ['administrator', 'editor', 'author']]);

        foreach ($authors as $author) {
            $uri = str_replace(home_url(), '', get_author_posts_url($author->ID));

            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => 'author',
                'resource_id' => $author->ID,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type' => 'author',
                    'resource_id' => $author->ID,
                    'cached_title' => $author->display_name,
                    'cached_author_id' => $author->ID,
                    'cached_date' => $author->user_registered,
                ]);
            }

            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->authorResources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => $author->ID,
                'post_type' => 'author',
                'uri' => $uri,
                'title' => $author->display_name,
            ];
        }

        echo "Prepared " . count($this->authorResources) . " author resources.\n";
    }

    /**
     * Prepare tag archive pages as trackable resources
     */
    private function prepareTagResources()
    {
        $tags = get_tags(['hide_empty' => false]);

        if (empty($tags) || is_wp_error($tags)) {
            echo "Prepared 0 tag resources (no tags found).\n";
            return;
        }

        foreach ($tags as $tag) {
            $uri = str_replace(home_url(), '', get_tag_link($tag->term_id));

            $resourceId = RecordFactory::resource()->getId([
                'resource_type' => 'post_tag',
                'resource_id' => $tag->term_id,
            ]);

            if (!$resourceId) {
                $resourceId = RecordFactory::resource()->insert([
                    'resource_type' => 'post_tag',
                    'resource_id' => $tag->term_id,
                    'cached_title' => $tag->name,
                    'cached_author_id' => 0,
                    'cached_date' => current_time('mysql'),
                ]);
            }

            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->tagResources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => $tag->term_id,
                'post_type' => 'post_tag',
                'uri' => $uri,
                'title' => $tag->name,
            ];
        }

        echo "Prepared " . count($this->tagResources) . " tag resources.\n";
    }

    /**
     * Prepare 404 error pages as trackable resources
     */
    private function prepare404Resources()
    {
        // Create a single 404 resource (all 404 URLs point to same resource_type)
        $resourceId = RecordFactory::resource()->getId([
            'resource_type' => '404',
            'resource_id' => 0,
        ]);

        if (!$resourceId) {
            $resourceId = RecordFactory::resource()->insert([
                'resource_type' => '404',
                'resource_id' => 0,
                'cached_title' => '404 Not Found',
                'cached_author_id' => 0,
                'cached_date' => current_time('mysql'),
            ]);
        }

        foreach ($this->notFoundUrls as $uri) {
            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->notFoundResources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => 0,
                'post_type' => '404',
                'uri' => $uri,
                'title' => '404 Not Found',
            ];
        }

        echo "Prepared " . count($this->notFoundResources) . " 404 resources.\n";
    }

    /**
     * Prepare search result pages as trackable resources
     */
    private function prepareSearchResources()
    {
        $resourceId = RecordFactory::resource()->getId([
            'resource_type' => 'search',
            'resource_id' => 0,
        ]);

        if (!$resourceId) {
            $resourceId = RecordFactory::resource()->insert([
                'resource_type' => 'search',
                'resource_id' => 0,
                'cached_title' => 'Search Results',
                'cached_author_id' => 0,
                'cached_date' => current_time('mysql'),
            ]);
        }

        foreach ($this->searchTerms as $term) {
            $uri = '/?s=' . urlencode($term);

            $resourceUriId = RecordFactory::resourceUri()->getId([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);

            if (!$resourceUriId) {
                $resourceUriId = RecordFactory::resourceUri()->insert([
                    'resource_id' => $resourceId,
                    'uri' => $uri,
                ]);
            }

            $this->searchResources[] = [
                'resource_id' => $resourceId,
                'resource_uri_id' => $resourceUriId,
                'post_id' => 0,
                'post_type' => 'search',
                'uri' => $uri,
                'title' => "Search: $term",
            ];
        }

        echo "Prepared " . count($this->searchResources) . " search resources.\n";
    }

    /**
     * Prepare home page as trackable resource
     */
    private function prepareHomeResource()
    {
        $uri = '/';
        $frontPageId = get_option('page_on_front') ?: 0;

        $resourceId = RecordFactory::resource()->getId([
            'resource_type' => 'home',
            'resource_id' => $frontPageId,
        ]);

        if (!$resourceId) {
            $resourceId = RecordFactory::resource()->insert([
                'resource_type' => 'home',
                'resource_id' => $frontPageId,
                'cached_title' => get_bloginfo('name'),
                'cached_author_id' => 0,
                'cached_date' => current_time('mysql'),
            ]);
        }

        $resourceUriId = RecordFactory::resourceUri()->getId([
            'resource_id' => $resourceId,
            'uri' => $uri,
        ]);

        if (!$resourceUriId) {
            $resourceUriId = RecordFactory::resourceUri()->insert([
                'resource_id' => $resourceId,
                'uri' => $uri,
            ]);
        }

        $this->homeResource = [
            'resource_id' => $resourceId,
            'resource_uri_id' => $resourceUriId,
            'post_id' => $frontPageId,
            'post_type' => 'home',
            'uri' => $uri,
            'title' => get_bloginfo('name'),
        ];

        echo "Prepared home page resource.\n";
    }

    /**
     * Create temporary mu-plugin to disable signature validation
     */
    private function createSignatureBypassPlugin()
    {
        $muPluginsDir = WP_CONTENT_DIR . '/mu-plugins';

        if (!is_dir($muPluginsDir)) {
            mkdir($muPluginsDir, 0755, true);
        }

        $this->signatureBypassPlugin = $muPluginsDir . '/wps-simulator-signature-bypass.php';

        $content = "<?php\n// Temporary file created by WP Statistics Tracker Simulator\n// This file will be automatically deleted when the simulation completes\nadd_filter('wp_statistics_request_signature_enabled', '__return_false');\n";

        file_put_contents($this->signatureBypassPlugin, $content);
    }

    /**
     * Remove temporary mu-plugin
     */
    private function removeSignatureBypassPlugin()
    {
        if ($this->signatureBypassPlugin && file_exists($this->signatureBypassPlugin)) {
            unlink($this->signatureBypassPlugin);
        }
    }

    /**
     * Find a resource by its resource_uri_id across all resource arrays
     */
    private function findResourceByUriId($resourceUriId)
    {
        // Search across all resource arrays
        $allResources = array_merge(
            $this->resources,
            $this->categoryResources,
            $this->authorResources,
            $this->tagResources,
            $this->notFoundResources,
            $this->searchResources,
            $this->homeResource ? [$this->homeResource] : []
        );

        foreach ($allResources as $resource) {
            if ($resource['resource_uri_id'] == $resourceUriId) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * Get a random resource with weighted distribution across all types
     */
    private function getRandomResource()
    {
        // Weighted distribution of page types (realistic traffic patterns)
        $weights = [
            'post_page' => 55,      // 55% - Regular posts/pages
            'home' => 15,           // 15% - Homepage (high traffic)
            'category' => 10,       // 10% - Category archives
            'author' => 5,          // 5%  - Author archives
            'search' => 5,          // 5%  - Search results
            'tag' => 5,             // 5%  - Tag archives
            '404' => 5,             // 5%  - 404 errors
        ];

        $type = $this->weightedRandom($weights);

        switch ($type) {
            case 'home':
                return $this->homeResource ?? $this->resources[array_rand($this->resources)];
            case 'category':
                return !empty($this->categoryResources)
                    ? $this->categoryResources[array_rand($this->categoryResources)]
                    : $this->resources[array_rand($this->resources)];
            case 'author':
                return !empty($this->authorResources)
                    ? $this->authorResources[array_rand($this->authorResources)]
                    : $this->resources[array_rand($this->resources)];
            case 'tag':
                return !empty($this->tagResources)
                    ? $this->tagResources[array_rand($this->tagResources)]
                    : $this->resources[array_rand($this->resources)];
            case 'search':
                return !empty($this->searchResources)
                    ? $this->searchResources[array_rand($this->searchResources)]
                    : $this->resources[array_rand($this->resources)];
            case '404':
                return !empty($this->notFoundResources)
                    ? $this->notFoundResources[array_rand($this->notFoundResources)]
                    : $this->resources[array_rand($this->resources)];
            default:
                return $this->resources[array_rand($this->resources)];
        }
    }

    /**
     * Generate a valid signature for the request
     */
    private function generateSignature($resourceType, $resourceId)
    {
        return Signature::generate([
            $resourceType,
            $resourceId,
        ]);
    }

    /**
     * Generate visitor profile (device, browser, location, etc.)
     */
    private function generateVisitorProfile()
    {
        // Select country
        $countryCode = $this->weightedRandom($this->countries['distribution']);

        // Select device type
        $deviceType = $this->weightedRandom($this->devices['types']);

        // Select browser
        $browser = $this->weightedRandom($this->devices['browsers']);

        // Select OS based on device type
        $osOptions = $this->devices['os'][$deviceType];
        $os = $this->weightedRandom($osOptions);

        // Select browser version
        $browserVersions = $this->devices['browser_versions'][$browser] ?? ['100.0'];
        $browserVersion = $browserVersions[array_rand($browserVersions)];

        // Get User-Agent string
        $userAgent = $this->getUserAgentString($deviceType, $os, $browser, $browserVersion);

        // Get screen resolution
        $resolutions = $this->devices['resolutions'][$deviceType] ?? ['1920x1080'];
        $resolution = $resolutions[array_rand($resolutions)];
        list($width, $height) = explode('x', $resolution);

        // Get timezone for country
        $timezones = $this->timezones[$countryCode] ?? ['UTC'];
        $timezone = $timezones[array_rand($timezones)];

        // Get language for country
        $langData = $this->languages[$countryCode] ?? ['code' => 'en-US', 'name' => 'English'];

        // Generate referrer
        $referrer = $this->generateReferrer();

        // Generate IP address for country
        $ip = $this->generateIpForCountry($countryCode);

        return [
            'country_code' => $countryCode,
            'device_type' => $deviceType,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'user_agent' => $userAgent,
            'screen_width' => $width,
            'screen_height' => $height,
            'timezone' => $timezone,
            'language_code' => $langData['code'],
            'language_name' => $langData['name'],
            'referrer' => $referrer,
            'ip' => $ip,
        ];
    }

    /**
     * Get User-Agent string for device/browser/OS combination
     */
    private function getUserAgentString($deviceType, $os, $browser, $version)
    {
        // Map OS to user-agent key
        $osKey = $os;
        if ($deviceType === 'Mobile' && in_array($os, ['iOS'])) {
            $osKey = 'iOS';
        } elseif ($deviceType === 'Mobile' && in_array($os, ['Android'])) {
            $osKey = 'Android';
        } elseif ($deviceType === 'Tablet' && in_array($os, ['iOS'])) {
            $osKey = 'iOS';
        } elseif ($deviceType === 'Tablet' && in_array($os, ['Android'])) {
            $osKey = 'Android';
        }

        // Try to get template from user-agents.json
        $templates = $this->userAgents[$deviceType][$osKey][$browser] ?? [];

        if (!empty($templates)) {
            $template = $templates[array_rand($templates)];
            return str_replace('{version}', $version, $template);
        }

        // Fallback to a generic User-Agent
        return $this->buildFallbackUserAgent($deviceType, $browser, $version, $os);
    }

    /**
     * Build fallback User-Agent string
     */
    private function buildFallbackUserAgent($deviceType, $browser, $version, $os)
    {
        if ($deviceType === 'Desktop') {
            switch ($os) {
                case 'Windows':
                    return "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
                case 'macOS':
                    return "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
                case 'Linux':
                    return "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
            }
        } elseif ($deviceType === 'Mobile') {
            if ($os === 'iOS') {
                return "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$version} Mobile/15E148 Safari/604.1";
            } else {
                return "Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Mobile Safari/537.36";
            }
        } elseif ($deviceType === 'Tablet') {
            if ($os === 'iOS') {
                return "Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$version} Mobile/15E148 Safari/604.1";
            } else {
                return "Mozilla/5.0 (Linux; Android 14; SM-X910) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
            }
        }

        return "Mozilla/5.0 (compatible; WPStatisticsSimulator/1.0)";
    }

    /**
     * Generate referrer URL based on channel distribution
     */
    private function generateReferrer()
    {
        $channel = $this->weightedRandom($this->referrers['channels']);

        if ($channel === 'direct') {
            return '';
        }

        if (!isset($this->referrers[$channel])) {
            return '';
        }

        // Get weighted referrer from channel
        $channelReferrers = $this->referrers[$channel];
        $weights = array_column($channelReferrers, 'weight', 'domain');
        $domain = $this->weightedRandom($weights);

        // Build referrer URL
        switch ($channel) {
            case 'search':
                return "https://www.{$domain}/search?q=" . urlencode('sample search');
            case 'social':
                return "https://www.{$domain}/";
            case 'referral':
                return "https://{$domain}/article/sample";
            case 'email':
                return "https://{$domain}/";
            case 'paid':
                return "https://www.{$domain}/aclk?sa=l";
            default:
                return "https://{$domain}/";
        }
    }

    /**
     * Generate IP address for a country (using realistic ranges)
     */
    private function generateIpForCountry($countryCode)
    {
        // Simplified IP ranges per country
        $countryIpRanges = [
            'US' => ['24', '63', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '96', '97', '98', '99', '100'],
            'GB' => ['2', '5', '31', '46', '81', '82', '86', '87', '90', '91', '92', '109'],
            'CA' => ['24', '64', '65', '66', '67', '68', '69', '70', '71', '72', '74', '75', '76', '99', '142', '184', '192', '198', '199', '204', '205', '206', '207'],
            'DE' => ['46', '77', '78', '79', '80', '84', '85', '87', '88', '89', '91', '93', '94', '95', '130', '134', '138', '139'],
            'FR' => ['37', '46', '78', '80', '81', '82', '83', '84', '85', '86', '88', '90', '91', '92', '93', '109', '176', '178', '185', '193'],
            'AU' => ['1', '14', '27', '43', '49', '58', '59', '60', '101', '103', '110', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125'],
            'IN' => ['14', '27', '36', '43', '49', '59', '61', '101', '103', '106', '110', '112', '115', '116', '117', '119', '120', '121', '122', '123', '124', '125'],
            'BR' => ['131', '138', '139', '143', '146', '152', '168', '177', '179', '186', '187', '189', '191', '200', '201'],
            'JP' => ['14', '27', '36', '42', '43', '49', '58', '59', '60', '61', '101', '106', '110', '111', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '126'],
            'NL' => ['2', '5', '31', '37', '46', '77', '78', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '145', '146', '176', '178', '185', '188', '193', '194', '195'],
            'ES' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'IT' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '109', '151', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'MX' => ['131', '138', '143', '148', '152', '177', '187', '189', '200', '201'],
            'SE' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'CH' => ['31', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'PL' => ['31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'BE' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'NO' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'DK' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'FI' => ['2', '5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            // Additional countries with city-level GeoIP data
            'IR' => ['2', '5', '31', '37', '46', '78', '80', '85', '91', '93', '151', '176', '178', '185', '188', '217'],
            'RU' => ['5', '31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'CN' => ['14', '27', '36', '42', '43', '49', '58', '59', '60', '61', '101', '106', '110', '111', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '180', '182', '183'],
            'KR' => ['14', '27', '36', '42', '43', '49', '58', '59', '61', '106', '110', '112', '115', '116', '117', '118', '119', '121', '122', '123', '124', '125', '175', '180', '182', '183', '203', '210', '211'],
            'TR' => ['31', '37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'SA' => ['37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'AE' => ['37', '46', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '109', '176', '178', '185', '188', '193', '194', '195', '212', '213'],
            'EG' => ['41', '102', '105', '154', '156', '163', '196', '197'],
            'ZA' => ['41', '102', '105', '154', '156', '163', '196', '197'],
            'NG' => ['41', '102', '105', '154', '156', '163', '196', '197'],
            'ID' => ['36', '103', '110', '112', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '180', '182', '202', '203'],
            'TH' => ['27', '36', '49', '58', '61', '101', '103', '106', '110', '112', '115', '116', '117', '118', '119', '121', '122', '124', '125', '180', '182', '183', '202', '203'],
            'VN' => ['14', '27', '36', '42', '49', '58', '59', '60', '61', '101', '103', '106', '110', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125'],
            'PH' => ['27', '36', '49', '58', '61', '101', '103', '110', '112', '115', '116', '117', '118', '119', '120', '121', '122', '124', '125', '180', '182', '202', '203'],
            'MY' => ['27', '36', '49', '58', '60', '61', '101', '103', '110', '112', '113', '115', '116', '117', '118', '119', '121', '122', '124', '125', '175', '180', '182', '202', '203', '210', '211'],
            'SG' => ['27', '36', '49', '58', '61', '101', '103', '110', '112', '115', '116', '117', '118', '119', '121', '122', '124', '125', '175', '180', '182', '202', '203', '210', '211'],
            'HK' => ['14', '27', '36', '42', '43', '49', '58', '59', '60', '61', '101', '103', '106', '110', '112', '113', '114', '115', '116', '117', '118', '119', '121', '122', '123', '124', '125', '175', '180', '182', '202', '203', '210', '211'],
            'TW' => ['14', '27', '36', '42', '49', '58', '59', '60', '61', '101', '106', '110', '111', '112', '113', '114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '175', '180', '182', '202', '203', '210', '211'],
            'AR' => ['131', '138', '143', '152', '168', '177', '179', '181', '186', '190', '191', '200', '201'],
            'CL' => ['131', '138', '143', '152', '168', '177', '179', '181', '186', '190', '191', '200', '201'],
            'CO' => ['131', '138', '143', '152', '168', '177', '179', '181', '186', '190', '191', '200', '201'],
            'PE' => ['131', '138', '143', '152', '168', '177', '179', '181', '186', '190', '191', '200', '201'],
        ];

        $firstOctet = $countryIpRanges[$countryCode] ?? ['100', '101', '102'];
        $first = $firstOctet[array_rand($firstOctet)];

        return $first . '.' . rand(1, 254) . '.' . rand(1, 254) . '.' . rand(1, 254);
    }

    /**
     * Generate request parameters (matching Tracker.js format)
     */
    private function generateRequestData($profile)
    {
        // Select resource using weighted distribution across all resource types
        $resource = $this->getRandomResource();

        return [
            // AJAX action
            'action' => 'wp_statistics_hit_record',

            // Required tracking params
            'resourceUriId' => $resource['resource_uri_id'],
            'resourceUri' => base64_encode($resource['uri']),
            'resource_type' => $resource['post_type'],
            'resource_id' => $resource['post_id'],
            'signature' => $this->generateSignature($resource['post_type'], $resource['post_id']),

            // Locale info (from browser)
            'timezone' => $profile['timezone'],
            'language' => $profile['language_code'],
            'languageFullName' => $profile['language_name'],
            'screenWidth' => $profile['screen_width'],
            'screenHeight' => $profile['screen_height'],

            // Referrer (base64 encoded)
            'referred' => base64_encode($profile['referrer']),

            // Additional (for compatibility)
            'page_uri' => base64_encode($resource['uri']),
        ];
    }

    /**
     * Send HTTP request to admin-ajax.php
     */
    private function sendTrackingRequest($params, $profile)
    {
        if ($this->config['dry_run']) {
            $this->stats['requests_sent']++;
            $this->stats['requests_successful']++;
            return true;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->ajaxUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_POSTREDIR => CURL_REDIR_POST_ALL, // Preserve POST data through redirects
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: ' . $profile['user_agent'],
                'X-Forwarded-For: ' . $profile['ip'],
                'Referer: ' . ($profile['referrer'] ?: $this->siteUrl . '/'),
            ],
            // For local development
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->stats['requests_sent']++;

        if ($error) {
            $this->stats['requests_failed']++;
            $this->stats['errors'][] = "CURL Error: $error";
            return false;
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['status']) && $result['status'] === true) {
            $this->stats['requests_successful']++;
            return true;
        }

        $this->stats['requests_failed']++;
        $errorMsg = $result['data'] ?? $response;
        if (!in_array($errorMsg, $this->stats['errors'])) {
            $this->stats['errors'][] = "HTTP $httpCode: $errorMsg";
        }
        return false;
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom($weights)
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, (int)($total * 100)) / 100;

        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    /**
     * Get current max IDs from tracking tables
     */
    private function getMaxIds()
    {
        global $wpdb;

        return [
            'visitors' => (int) $wpdb->get_var("SELECT COALESCE(MAX(ID), 0) FROM {$wpdb->prefix}statistics_visitors"),
            'sessions' => (int) $wpdb->get_var("SELECT COALESCE(MAX(ID), 0) FROM {$wpdb->prefix}statistics_sessions"),
            'views'    => (int) $wpdb->get_var("SELECT COALESCE(MAX(ID), 0) FROM {$wpdb->prefix}statistics_views"),
        ];
    }

    /**
     * Update dates for records created after the given max IDs
     */
    private function backdateRecords($maxIds, $targetDate, $hourDistribution)
    {
        global $wpdb;

        // Get records created since max IDs
        $newVisitorIds = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}statistics_visitors WHERE ID > %d",
            $maxIds['visitors']
        ));

        $newSessionIds = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}statistics_sessions WHERE ID > %d",
            $maxIds['sessions']
        ));

        $newViewIds = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}statistics_views WHERE ID > %d",
            $maxIds['views']
        ));

        // Update each record with a random time on the target date
        foreach ($newVisitorIds as $id) {
            $hour = $this->weightedRandom($hourDistribution);
            $datetime = $targetDate . ' ' . sprintf('%02d:%02d:%02d', $hour, rand(0, 59), rand(0, 59));
            $wpdb->update(
                $wpdb->prefix . 'statistics_visitors',
                ['created_at' => $datetime],
                ['ID' => $id]
            );
        }

        foreach ($newSessionIds as $id) {
            $hour = $this->weightedRandom($hourDistribution);
            $datetime = $targetDate . ' ' . sprintf('%02d:%02d:%02d', $hour, rand(0, 59), rand(0, 59));
            $wpdb->update(
                $wpdb->prefix . 'statistics_sessions',
                ['started_at' => $datetime, 'ended_at' => $datetime],
                ['ID' => $id]
            );
        }

        foreach ($newViewIds as $id) {
            $hour = $this->weightedRandom($hourDistribution);
            $datetime = $targetDate . ' ' . sprintf('%02d:%02d:%02d', $hour, rand(0, 59), rand(0, 59));
            $wpdb->update(
                $wpdb->prefix . 'statistics_views',
                ['viewed_at' => $datetime],
                ['ID' => $id]
            );
        }

        return [
            'visitors' => count($newVisitorIds),
            'sessions' => count($newSessionIds),
            'views'    => count($newViewIds),
        ];
    }

    /**
     * Get weekday multiplier
     */
    private function getWeekdayMultiplier($dayOfWeek)
    {
        $multipliers = [
            1 => 1.0,   // Monday
            2 => 1.1,   // Tuesday
            3 => 1.2,   // Wednesday (peak)
            4 => 1.1,   // Thursday
            5 => 0.9,   // Friday
            6 => 0.6,   // Saturday
            7 => 0.5,   // Sunday
        ];
        return $multipliers[$dayOfWeek] ?? 1.0;
    }

    /**
     * Get hour distribution weights
     */
    private function getHourDistribution()
    {
        return [
            0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
            6 => 2, 7 => 3, 8 => 4,
            9 => 6, 10 => 7, 11 => 8, 12 => 7, 13 => 7, 14 => 8, 15 => 7, 16 => 6, 17 => 5,
            18 => 4, 19 => 3, 20 => 3, 21 => 2, 22 => 2, 23 => 1
        ];
    }

    /**
     * Main execution loop
     */
    public function run()
    {
        $this->printHeader();

        $currentDate = new DateTime($this->config['from']);
        $endDate = new DateTime($this->config['to']);
        $totalDays = $currentDate->diff($endDate)->days + 1;
        $dayNum = 0;

        $hourDistribution = $this->getHourDistribution();
        $totalHourWeight = array_sum($hourDistribution);

        while ($currentDate <= $endDate) {
            $date = $currentDate->format('Y-m-d');
            $dayNum++;

            // Calculate visitors for this day
            $dayOfWeek = (int)$currentDate->format('N');
            $weekdayMultiplier = $this->getWeekdayMultiplier($dayOfWeek);
            $visitorsToday = (int)round($this->config['visitors_per_day'] * $weekdayMultiplier);

            echo "Day {$dayNum}/{$totalDays} ({$date}): {$visitorsToday} visitors";

            // Capture current max IDs before this day's batch
            $maxIdsBefore = $this->config['dry_run'] ? null : $this->getMaxIds();

            // Distribute visitors across hours
            for ($i = 0; $i < $visitorsToday; $i++) {
                // Select hour based on distribution
                $hour = $this->weightedRandom($hourDistribution);

                // Generate visitor profile
                $profile = $this->generateVisitorProfile();

                // Generate request data
                $params = $this->generateRequestData($profile);

                // Send request
                $success = $this->sendTrackingRequest($params, $profile);

                if ($this->config['verbose']) {
                    $status = $success ? 'OK' : 'FAIL';
                    $resource = $this->findResourceByUriId($params['resourceUriId']);
                    $uri = $resource ? $resource['uri'] : 'unknown';
                    echo "\n  [{$status}] {$profile['ip']} - {$profile['browser']}/{$profile['os']} - {$uri}";
                }

                // Delay between requests
                if ($this->config['delay_ms'] > 0 && !$this->config['dry_run']) {
                    usleep($this->config['delay_ms'] * 1000);
                }
            }

            // Backdate records created during this day's batch
            if (!$this->config['dry_run'] && $maxIdsBefore !== null) {
                $backdated = $this->backdateRecords($maxIdsBefore, $date, $hourDistribution);
                echo " → backdated {$backdated['views']} views";
            }

            echo "\n";
            $currentDate->modify('+1 day');
        }

        $this->printSummary();
    }

    /**
     * Print header
     */
    private function printHeader()
    {
        echo "=== WP Statistics Tracker.js Simulator ===\n\n";
        echo "Date Range: {$this->config['from']} to {$this->config['to']}\n";
        echo "Visitors/Day: {$this->config['visitors_per_day']}\n";
        echo "AJAX URL: {$this->ajaxUrl}\n";
        if ($this->config['dry_run']) {
            echo "Mode: DRY RUN (no actual requests)\n";
        }
        echo "\nResources prepared:\n";
        echo "  - Posts/Pages: " . count($this->resources) . "\n";
        echo "  - Categories:  " . count($this->categoryResources) . "\n";
        echo "  - Authors:     " . count($this->authorResources) . "\n";
        echo "  - Tags:        " . count($this->tagResources) . "\n";
        echo "  - 404 URLs:    " . count($this->notFoundResources) . "\n";
        echo "  - Searches:    " . count($this->searchResources) . "\n";
        echo "  - Home:        " . ($this->homeResource ? '1' : '0') . "\n";
        $totalResources = count($this->resources) + count($this->categoryResources) +
            count($this->authorResources) + count($this->tagResources) +
            count($this->notFoundResources) + count($this->searchResources) +
            ($this->homeResource ? 1 : 0);
        echo "  - Total:       " . $totalResources . "\n";
        echo "\n";
    }

    /**
     * Print summary
     */
    private function printSummary()
    {
        // Clean up temporary mu-plugin
        $this->removeSignatureBypassPlugin();

        echo "\n=== Summary ===\n";
        echo "Requests sent: " . number_format($this->stats['requests_sent']) . "\n";
        echo "Successful: " . number_format($this->stats['requests_successful']) . "\n";
        echo "Failed: " . number_format($this->stats['requests_failed']) . "\n";

        if (!empty($this->stats['errors'])) {
            echo "\nErrors:\n";
            $uniqueErrors = array_unique($this->stats['errors']);
            foreach (array_slice($uniqueErrors, 0, 10) as $error) {
                echo "  - {$error}\n";
            }
            if (count($uniqueErrors) > 10) {
                echo "  ... and " . (count($uniqueErrors) - 10) . " more\n";
            }
        }
    }
}

// ============================================================================
// CLI HANDLING
// ============================================================================

// Parse command line arguments
$options = getopt('', [
    'days:',
    'from:',
    'to:',
    'visitors-per-day:',
    'delay:',
    'url:',
    'verbose',
    'dry-run',
    'help'
]);

if (isset($options['help'])) {
    echo "Usage: php bin/dummy-tracker-simulator.php [options]\n\n";
    echo "Simulates Tracker.js requests to admin-ajax.php for realistic test data.\n\n";
    echo "Options:\n";
    echo "  --days=<number>           Days to generate (default: 7)\n";
    echo "  --from=<YYYY-MM-DD>       Start date\n";
    echo "  --to=<YYYY-MM-DD>         End date (default: today)\n";
    echo "  --visitors-per-day=<num>  Average visitors per day (default: 50)\n";
    echo "  --delay=<ms>              Delay between requests in ms (default: 50)\n";
    echo "  --url=<url>               Custom site URL (e.g., https://wordpress.test)\n";
    echo "  --verbose                 Show detailed request output\n";
    echo "  --dry-run                 Generate data without sending requests\n";
    echo "  --help                    Show this help\n\n";
    echo "Examples:\n";
    echo "  php bin/dummy-tracker-simulator.php --days=7 --visitors-per-day=50\n";
    echo "  php bin/dummy-tracker-simulator.php --url=https://localhost:8080 --days=1\n";
    echo "  php bin/dummy-tracker-simulator.php --from=2024-12-01 --to=2024-12-31 --verbose\n";
    echo "  php bin/dummy-tracker-simulator.php --days=1 --visitors-per-day=10 --dry-run\n\n";
    echo "Prerequisites:\n";
    echo "  - 'use_cache_plugin' option should be enabled\n";
    echo "  - 'bypass_ad_blockers' option should be enabled\n";
    exit(0);
}

// Build configuration
$config = [
    'days' => (int)($options['days'] ?? 7),
    'visitors_per_day' => (int)($options['visitors-per-day'] ?? 50),
    'delay_ms' => (int)($options['delay'] ?? 50),
    'verbose' => isset($options['verbose']),
    'dry_run' => isset($options['dry-run']),
];

if (isset($options['from'])) {
    $config['from'] = $options['from'];
}

if (isset($options['to'])) {
    $config['to'] = $options['to'];
}

if (isset($options['url'])) {
    $config['url'] = rtrim($options['url'], '/');
}

// Run simulator
$simulator = new TrackerSimulator($config);
$simulator->run();
