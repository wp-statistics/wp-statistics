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
     * Store the parsed arguments.
     *
     * @var array
     */
    private $parsedArgs = [];

    /**
     * Parse and cache the arguments for reuse.
     *
     * @param array $args Arguments to be parsed.
     * @return array Parsed arguments with default values.
     */
    private function parseArgs($args)
    {
        if (! empty($this->parsedArgs)) {
            return $this->parsedArgs;
        }

        $this->parsedArgs = wp_parse_args($args, [
            'post_id' => $this->getResourceId($args),
            'uri'     => $this->getResourceUri($args),
        ]);

        return $this->parsedArgs;
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
    public function getVisitors($args) {
        $this->parseArgs($args);
        
        if(empty($this->parsedArgs['ignore_date'])){
            return 0;
        }

        $query = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('category', '=', 'visitors');

        return ! empty($query->getVar()) ? intval($query->getVar()) : 0;
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
        $this->parseArgs($args);

        if (! empty($this->parsedArgs['type']) && 'uri' === $this->parsedArgs['type']) {
            if (
                empty($this->parsedArgs['post_id']) ||
                (empty($this->parsedArgs['ignore_date']) && empty($this->parsedArgs['uri']))
            ) {
                return 0;
            }

            return $this->countUris();
        }

        $query = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('category', '=', 'visits');

        return ! empty($query->getVar()) ? intval($query->getVar()) : 0;
    }

    /**
     * Returns historical views of a page by its URL.
     *
     * @return int Total number of historical views
     */
    private function countUris()
    {
        $query = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('page_id', '=', intval($this->parsedArgs['post_id']))
            ->where('uri', '=', $this->parsedArgs['uri']);

        return intval($query->getVar());
    }
}
