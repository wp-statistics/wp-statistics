<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Url;

class HistoricalModel
{
    /**
     * Store the current resource ID.
     *
     * @var int|null
     */
    private $resourceId = null;

    /**
     * Store the current resource type.
     *
     * @var string|null
     */
    private $type = null;

    /**
     * Parse and cache the arguments for reuse.
     *
     * @param array $args Arguments to be parsed. Must contain either 'historical' or 'ignore_date'.
     * @param array $defaults Optional. Default values to merge with provided arguments.
     *
     * @return array|null Parsed and enhanced arguments, or null if required arguments are missing.
     */
    private function parseArgs($args, $defaults = [])
    {
        if (empty($args['historical']) && empty($args['ignore_date'])) {
            return null;
        }

        $args = wp_parse_args($args, $defaults);

        $args['resource_id'] = $this->getResourceId($args);
        $args['uri']         = $this->getResourceUri($args);

        if (! empty($args['uri']) && ! empty($args['resource_id'])) {
            $args['category'] = 'uri';
        }

        return $args;
    }

    /**
     * Get the ID value from a resource array argument.
     *
     * @param array $args {
     *     Arguments containing resource ID.
     *
     *     @type int $post_id    Optional. Post ID to retrieve.
     *     @type int $term       Optional. Term ID to retrieve.
     *     @type int $author_id  Optional. Author ID to retrieve.
     * }
     *
     * @return int|null The ID of the first found resource, or null if no resource ID specified
     */
    public function getResourceId($args)
    {
        if (!empty($args['post_id'])) {
            $this->resourceId = $args['post_id'];
            $this->type       = 'post';

            return $this->resourceId;
        }

        if (!empty($args['term'])) {
            $this->resourceId = $args['term'];
            $this->type       = 'taxonomy';

            return $this->resourceId;
        }

        if (!empty($args['author_id'])) {
            $this->resourceId = $args['author_id'];
            $this->type       = 'author';

            return $this->resourceId;
        }

        $this->resourceId = null;
        $this->type       = null;

        return null;
    }

    /**
     * Get relative URI for a WordPress resource (post, term, or author).
     *
     * @param array $args {
     *     Arguments for determining the resource URI.
     *
     *     @type int    $post_id    Optional. Post ID to get its URI.
     *     @type int    $term       Optional. Term ID to get its URI.
     *     @type string $taxonomy   Required if $term is set. The taxonomy name.
     *     @type int    $author_id  Optional. Author ID to get their archive URI.
     * }
     * @return string|null Relative path to the resource, or null if no valid resource specified
     */
    public function getResourceUri($args)
    {
        if (! empty($args['uri'])) {
            return $args['uri'];
        }

        if ($this->type === 'taxonomy') {
            return Url::getPath($this->resourceId, $args['taxonomy']);
        }

        if ($this->type === 'author') {
            return Url::getPath($this->resourceId, 'author');
        }

        if ($this->type === 'post') {
            $postType = strtolower(Helper::getPostTypeName(get_post_type($this->resourceId), true));
            return Url::getPath($this->resourceId, $postType);
        }

        return null;
    }

    /**
     * Get the total number of historical visitors.
     *
     * @param array $args {
     *     Optional. An array of arguments for filtering historical visitor data.
     *
     *     @type bool $ignore_date  Whether to ignore date constraints when counting visitors.
     *                             Default false. When false, returns 0.
     * }
     * @return int Total number of historical visitors.
     */
    public function getVisitors($args)
    {
        $args = $this->parseArgs($args);

        if (is_null($args)) {
            return 0;
        }

        foreach ($args as $key => $value) {
            if ('historical' === $key || 'ignore_date' === $key) {
                continue;
            }

            if (! empty($value)) {
                return 0;
            }
        }

        $result = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('category', '=', 'visitors')
            ->getVar();

        return $result ?? 0;
    }

    /**
     * Get the total number of historical views based on provided arguments.
     *
     * @param array $args {
     *     Arguments for retrieving views.
     *
     *     @type string $type        Optional. Type of view count to retrieve ('uri' for specific URI views).
     *     @type int    $post_id     Optional. Post ID to get views for.
     *     @type string $uri         Optional. URI to get views for.
     *     @type bool   $ignore_date Optional. Whether to ignore date in URI comparison.
     * }
     * @return int Total number of historical views.
     */
    public function getViews($args)
    {
        $args = $this->parseArgs($args, [
            'resource_id' => '',
            'uri'         => '',
            'category'    => 'visits',
        ]);

        if (is_null($args)) {
            return 0;
        }

        $result = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('page_id', '=', $args['resource_id'])
            ->where('uri', '=', $args['uri'])
            ->where('category', '=', $args['category'])
            ->getVar();

        return $result ?? 0;
    }
}
