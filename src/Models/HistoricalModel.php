<?php
namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Url;

class HistoricalModel extends BaseModel
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
    public function getResourceId($args) {
        if (!empty($args['post_id'])) {
            $this->resourceId = $args['post_id'];
            $this->type = 'post';

            return $this->resourceId;
        }

        if (!empty($args['term'])) {
            $this->resourceId = $args['term'];
            $this->type = 'taxonomy';

            return $this->resourceId;
        }

        if (!empty($args['author_id'])) {
            $this->resourceId = $args['author_id'];
            $this->type = 'author';

            return $this->resourceId;
        }

        $this->resourceId = null;
        $this->type = null;
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
    public function getResourceUri($args) {
        if (! empty($args['uri'])) {
            return $args['uri'];
        }

        if ($this->type === 'taxonomy') {
            return Url::getRelativePath($this->resourceId, $args['taxonomy']);
        }
        
        if ($this->type === 'author') {
            return Url::getRelativePath($this->resourceId, 'author');
        }
        
        if ($this->type === 'post') {
            $postType = strtolower(Helper::getPostTypeName(get_post_type($this->resourceId), true));
            return Url::getRelativePath($this->resourceId, $postType);
        }

        return null;
    }

    /**
     * Get total views for a specific resource.
     *
     * @param array $args {
     *     Arguments for retrieving views.
     *
     *     @type bool   $ignore_date Whether to ignore date restrictions
     *     @type int    $post_id     Optional. Post ID
     *     @type int    $term        Optional. Term ID
     *     @type string $taxonomy    Required if term is set
     *     @type int    $author_id   Optional. Author ID
     * }
     * @return int Total number of views
     */
    public function getViews($args) {
        if (empty($args['ignore_date'])) {
            return 0;
        }

        $this->getResourceId($args);
        $resourceUri = $this->getResourceUri($args);

        if (empty($resourceUri)) {
            return 0;
        }

        return $this->countUris([
            'post_id' => $this->resourceId,
            'uri' => $resourceUri
        ]);
    }

    /**
     * Returns historical views of a page by its URL.
     *
     * @param array $args {
     *     Arguments for counting URIs.
     *
     *     @type int    $post_id Post ID
     *     @type string $uri     Resource URI
     * }
     * @return int Total number of historical views
     */
    public function countUris($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_id' => '',
            'uri'     => '',
        ]);

        $query = Query::select('SUM(`value`) AS `historical_views`')
            ->from('historical')
            ->where('page_id', '=', intval($args['post_id']))
            ->where('uri', '=', $args['uri']);

        return intval($query->getVar());
    }
}
