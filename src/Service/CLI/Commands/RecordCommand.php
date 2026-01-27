<?php

namespace WP_Statistics\Service\CLI\Commands;

use Exception;
use WP_CLI;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Tracking\Core\Exclusion;

/**
 * Record a hit manually using v15 tracking services.
 *
 * @since 15.0.0
 */
class RecordCommand
{
    /**
     * Record a hit.
     *
     * ## OPTIONS
     *
     * [--uri=<uri>]
     * : The request URI to record the hit for (e.g., /sample-page/).
     * ---
     * default: /
     * ---
     *
     * [--post_id=<post_id>]
     * : The WordPress post/page ID. Use 0 for non-post pages (home, 404, etc.).
     * ---
     * default: 0
     * ---
     *
     * [--ip=<ip>]
     * : The IP address of the visitor.
     *
     * [--user_agent=<user_agent>]
     * : The HTTP user agent of the visitor.
     *
     * [--referrer=<referrer>]
     * : The referrer URL.
     *
     * [--user_id=<user_id>]
     * : The WordPress user ID of the visitor (0 for anonymous).
     * ---
     * default: 0
     * ---
     *
     * [--skip-exclusion]
     * : Skip exclusion checks (record hit even if it would normally be excluded).
     *
     * ## EXAMPLES
     *
     *      # Record a hit for the homepage
     *      $ wp statistics record --uri="/"
     *
     *      # Record a hit for a specific page
     *      $ wp statistics record --uri="/sample-page/" --post_id=2
     *
     *      # Record a hit with a specific IP address
     *      $ wp statistics record --uri="/blog/" --ip="192.168.1.100"
     *
     *      # Record a hit with full visitor details
     *      $ wp statistics record --uri="/contact/" --post_id=5 --ip="10.0.0.1" \
     *        --user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0" \
     *        --referrer="https://google.com"
     *
     *      # Record a hit for a logged-in user
     *      $ wp statistics record --uri="/dashboard/" --user_id=1
     *
     *      # Force record even if excluded
     *      $ wp statistics record --uri="/" --ip="127.0.0.1" --skip-exclusion
     *
     * ## BASH SCRIPT EXAMPLE
     *
     *      #!/bin/bash
     *      # Generate 10 hits from different IPs
     *      for i in {1..10}; do
     *         wp statistics record --uri="/test-page/" --ip="192.168.1.$i" \
     *           --user_agent="Mozilla/5.0 TestBot/$i"
     *      done
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        // Extract options
        $uri           = $assoc_args['uri'] ?? '/';
        $postId        = (int) ($assoc_args['post_id'] ?? 0);
        $userId        = (int) ($assoc_args['user_id'] ?? 0);
        $skipExclusion = isset($assoc_args['skip-exclusion']);

        // Set server variables for IP and user agent if provided
        if (isset($assoc_args['ip'])) {
            $_SERVER['REMOTE_ADDR'] = $assoc_args['ip'];
        }

        if (isset($assoc_args['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $assoc_args['user_agent'];
        }

        if (isset($assoc_args['referrer'])) {
            $_SERVER['HTTP_REFERER'] = $assoc_args['referrer'];
        }

        try {
            // Create visitor profile
            $visitorProfile = new VisitorProfile();

            // Check exclusion rules unless skipped
            if (!$skipExclusion) {
                $exclusion = Exclusion::check($visitorProfile);
                if (!empty($exclusion['exclusion_match'])) {
                    Exclusion::record($exclusion);
                    WP_CLI::error(sprintf('Hit excluded: %s', $exclusion['exclusion_reason']));
                    return;
                }
            }

            // Step 1: Create or get ResourceUri record
            $resourceUriId = $this->getOrCreateResourceUri($uri, $postId);
            if (!$resourceUriId) {
                WP_CLI::error('Failed to create resource URI record.');
                return;
            }

            // Step 2: Set up visitor profile metadata
            $visitorProfile->setResourceUriId($resourceUriId);
            $visitorProfile->setResourceUri($uri);
            $visitorProfile->setResourceId($postId);

            // Override user ID if provided
            if ($userId > 0) {
                $visitorProfile->setMeta('user_id_override', $userId);
            }

            // Step 3: Record visitor
            EntityFactory::visitor($visitorProfile)->record();

            // Step 4: Record device information
            EntityFactory::device($visitorProfile)
                ->recordType()
                ->recordOs()
                ->recordBrowser()
                ->recordBrowserVersion();

            // Step 5: Record geo information
            EntityFactory::geo($visitorProfile)
                ->recordCountry()
                ->recordCity();

            // Step 6: Record locale information
            EntityFactory::locale($visitorProfile)
                ->recordLanguage()
                ->recordTimezone();

            // Step 7: Record referrer
            EntityFactory::referrer($visitorProfile)->record();

            // Step 8: Record session
            EntityFactory::session($visitorProfile)->record();

            // Step 9: Record view
            EntityFactory::view($visitorProfile)->record();

            // Build success message with details
            $details = [
                'URI'        => $uri,
                'Post ID'    => $postId,
                'Visitor ID' => $visitorProfile->getVisitorIdMeta(),
                'Session ID' => $visitorProfile->getSessionId(),
                'View ID'    => $visitorProfile->getViewId(),
            ];

            WP_CLI::success('Hit recorded successfully.');
            WP_CLI::line('');

            foreach ($details as $key => $value) {
                WP_CLI::line(sprintf('  %s: %s', $key, $value ?: 'N/A'));
            }
        } catch (Exception $e) {
            WP_CLI::error(sprintf('Failed to record hit: %s', $e->getMessage()));
        }
    }

    /**
     * Get or create a ResourceUri record.
     *
     * @param string $uri    The request URI.
     * @param int    $postId The WordPress post ID (0 for non-post pages).
     * @return int|false The ResourceUri ID or false on failure.
     */
    private function getOrCreateResourceUri(string $uri, int $postId)
    {
        // First, ensure the Resource record exists (if post_id > 0)
        $resourceId = null;
        if ($postId > 0) {
            $resource = RecordFactory::resource()->get(['resource_id' => $postId]);
            if (!$resource) {
                // Create resource record
                $post = get_post($postId);
                $resourceId = RecordFactory::resource()->insert([
                    'resource_id'   => $postId,
                    'resource_type' => $post ? $post->post_type : 'post',
                    'cached_title'  => $post ? $post->post_title : '',
                ]);
            } else {
                $resourceId = $resource->ID;
            }
        }

        // Check if ResourceUri already exists
        $existing = RecordFactory::resourceUri()->get(['uri' => $uri]);
        if ($existing && isset($existing->ID)) {
            return (int) $existing->ID;
        }

        // Create new ResourceUri
        return RecordFactory::resourceUri()->insert([
            'uri'         => $uri,
            'resource_id' => $resourceId,
            'created_at'  => DateTime::getUtc(),
        ]);
    }
}
