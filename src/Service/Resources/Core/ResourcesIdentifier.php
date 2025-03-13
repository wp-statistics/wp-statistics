<?php

namespace WP_Statistics\Service\Resources\Core;

use WP_Statistics;
use WP_STATISTICS\DB;
use WP_Statistics\Utils\Query;

/**
 * Identifies and manages resource-related data.
 */
class ResourcesIdentifier
{
    /**
     * Holds an instance of ResourceDetector used to gather detailed resource information.
     *
     * @var ResourceDetector|null
     */
    private $detector;

    /**
     * The row ID associated with the resource.
     *
     * @var int|null
     */
    private $rowId = null;

    /**
     * The resource data retrieved from the database.
     *
     * @var object|null
     */
    public $resource = null;

    /**
     * Initializes the resource with its properties.
     *
     * @param int|null $rowId Unique identifier for the resource.
     */
    public function __construct($rowId = null)
    {
        $this->rowId = $rowId;

        if (! empty($this->rowId)) {
            $this->getRestource();
            return;
        }

        $this->setRestource();
    }

    /**
     * Retrieves the resource data from the database using the row ID.
     *
     * @return void
     */
    private function getRestource()
    {
        if (empty($this->rowId)) {
            return;
        }

        $this->resource = Query::select('*')
            ->from('resources')
            ->where('ID', '=', $this->rowId)
            ->getRow();
    }

    /**
     * Sets the resource record.
     *
     * @return void
     */
    private function setRestource()
    {
        $currentPage = home_url(add_query_arg(null, null));

        $this->resource = Query::select('*')
            ->from('resources')
            ->where('resource_url', '=', $currentPage)
            ->getRow();

        if (! empty($this->resource)) {
            return;
        }

        if (empty($this->detector)) {
            $this->detector = new ResourceDetector();
        }

        global $wpdb;

        $insert = $wpdb->insert(
            DB::table('resources'),
            [
                'resource_id'        => $this->detector->getResourceId(),
                'resource_type'      => $this->detector->getResourceType(),
                'resource_url'       => $currentPage,
                'cached_title'       => $this->detector->getCachedTitle(),
                'cached_terms'       => $this->detector->getCachedTerms(),
                'cached_author_id'   => $this->detector->getCachedAuthorId(),
                'cached_author_name' => $this->detector->getCachedAuthorName(),
                'cached_date'        => $this->detector->getCachedDate(),
                'resource_meta'      => $this->detector->getResourceMeta(),
            ]
        );

        if ($insert === false) {
            WP_Statistics::log('Insert into resources failed: ' . $wpdb->last_error);
            return;
        }

        $this->resource = Query::select('*')
            ->from('resources')
            ->where('id', '=', $wpdb->insert_id)
            ->getRow();
    }

    /**
     * Updates the cached title for a resource.
     * 
     * @param string $title  The new title to be set for the resource.
     * @return void
     */
    public function updateTitle($title)
    {
        Query::update('resources')
            ->set(['cached_title' => $title])
            ->where('ID', '=', $this->rowId)
            ->execute();
    }

    /**
     * Updates the resource URL by fetching the permalink of the given post.
     * 
     * @param string|null $url Optional URL to use instead of fetching the permalink.
     * @return void
     */
    public function updateUrl($url = null)
    {
        $permalink = ! empty($url) ? $url : get_the_permalink($this->resource->resource_id);

        Query::update('resources')
            ->set(['resource_url' => $permalink])
            ->where('ID', '=', $this->rowId)
            ->execute();
    }

    /**
     * Updates the resource's author information (ID and display name).
     * 
     * @param int $authorId The ID of the author to set.
     * @return void
     */
    public function updateAuthor($authorId)
    {
        $authorInfo = get_userdata($authorId);
        $authorName = ! empty($authorInfo) ? $authorInfo->display_name : '';

        if (empty($authorName)) {
            return;
        }

        Query::update('resources')
            ->set([
                'cached_author_id'   => $authorId,
                'cached_author_name' => $authorName,
            ])
            ->where('resource_id', '=', $this->resource->resource_id)
            ->execute();
    }

    /**
     * Updates the cached taxonomy terms for the resource.
     * 
     * @param string $terms Optional JSON-encoded taxonomy terms.
     * @return void
     */
    public function updateTerms($terms = '')
    {
        if (! empty($terms)) {
            Query::update('resources')
                ->set(['cached_terms' => $terms])
                ->where('ID', '=', $this->rowId)
                ->execute();

            return;
        }

        $postId = $this->resource->resource_id;

        $postType = get_post_type($postId);

        if (! $postType) {
            return;
        }

        $taxonomies = get_object_taxonomies($postType, 'names');

        if (empty($taxonomies)) {
            return;
        }

        $formattedTerms = [];

        foreach ($taxonomies as $taxonomy) {
            $termList = get_the_terms($postId, $taxonomy);

            if (empty($termList) || is_wp_error($termList)) {
                continue;
            }

            foreach ($termList as $termObj) {
                $formattedTerms[] = $termObj->term_id;
            }
        }

        $terms = ! empty($formattedTerms) ? implode(', ', $formattedTerms) : null;

        Query::update('resources')
            ->set(['cached_terms' => $terms])
            ->where('ID', '=', $this->rowId)
            ->execute();
    }

    /**
     * Updates the cached publication date for the resource.
     *
     * @param string $date Optional date to update as the cached date.
     * @return void
     */
    public function updateDate($date = '')
    {
        $publishDate = ! empty($date) ? $date : get_post_field('post_date', $this->resource->resource_id);

        Query::update('resources')
            ->set(['cached_date' => $publishDate])
            ->where('ID', '=', $this->rowId)
            ->execute();
    }

    /**
     * Updates the resource metadata.
     *
     * @param string $meta Optional metadata to update.
     * @return void
     */
    public function updateMeta($meta = '')
    {
        if (empty($meta)) {
            return;
        }

        Query::update('resources')
            ->set(['resource_meta' => $meta])
            ->where('ID', '=', $this->rowId)
            ->execute();
    }

    /**
     * Removes the resource record.
     *
     * @return void
     */
    public function removeResource() 
    {
        global $wpdb;
        $table = DB::table('resources');
    
        $deleted = $wpdb->delete($table, ['ID' => $this->rowId]);
    
        if ($deleted === false) {
            WP_Statistics::log('Failed to delete resource with ID ' . $this->rowId . ': ' . $wpdb->last_error);
        }
    }
}
