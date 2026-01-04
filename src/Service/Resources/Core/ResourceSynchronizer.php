<?php

namespace WP_Statistics\Service\Resources\Core;

use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Utils\PostType;
use WP_Statistics\Utils\Query;

/**
 * ResourceSynchronizer Class
 *
 * Handles synchronization of WordPress resources (posts, pages, custom post types) with the WP Statistics database.
 * This class ensures that resource data is kept up-to-date when WordPress content is modified, deleted, or when
 * related entities (authors, terms) are changed.
 *
 * @package WP_Statistics\Service\Resources\Core
 * @since 15.0.0
 */
class ResourceSynchronizer
{
    /**
     * Holds the ResourcesIdentifier for the current resource.
     *
     * @var object|null
     */
    private $resource = null;

    /**
     * Constructor.
     *
     * Hooks into WordPress actions to update resource data when a post is inserted/updated,
     * remove resource records when a post is deleted, update resource author information,
     * detect term removals, and reassign resource author when a user is deleted.
     */
    public function __construct()
    {
        add_action('wp_after_insert_post', [$this, 'addOrUpdateResource'], 10, 2);
        add_action('delete_post', [$this, 'setResourceAsDeleted'], 10, 2);
        add_action('delete_user', [$this, 'reassignResourceAuhtor'], 10, 2);
        add_action('delete_term', [$this, 'removeResourceTerm'], 10, 5);
        
        /**
         * @todo: we need implementation to check the resource by background process and also on single content page.
         */
    }

    /**
     * Initializes the ResourcesIdentifier object if it hasn't been set yet.
     *
     * @param int $resourceId The ID of the post (resource).
     * @param string $resourceType The post type of the resource.
     * @return void
     */
    private function setResourcesIdentifier($resourceId, $resourceType)
    {
        $decorator = ResourcesFactory::getByResourceId($resourceId, $resourceType);

        if (empty($decorator)) {
            return;
        }

        $this->resource = $decorator->getResource();
    }

    /**
     * Removes the resource associated with a deleted post.
     *
     * @param int $postId The ID of the post being deleted.
     * @param \WP_Post $post The post object being deleted.
     *
     * @return void
     */
    public function setResourceAsDeleted($postId, $post)
    {
        if (!is_object($post)) {
            $post = get_post($postId);
        }

        if (is_null($post)) {
            return;
        }

        if ('revision' === $post->post_type) {
            return;
        }

        
        $this->setResourcesIdentifier($postId, $post->post_type);

        if (empty($this->resource)) {
            return;
        }

        $this->resource->getRecord()->markAsDeleted();
    }

    /**
     * Updates the resource data when a WordPress post is updated.
     *
     * This method is hooked to the 'wp_after_insert_post' action and performs
     * several validation checks before processing:
     * - Ensures the post type is publicly viewable
     * - Checks the post exists and is not in trash
     * - Verifies the post is published
     *
     * If the resource doesn't exist, it creates a new one. If it exists, it updates
     * the cached resource data including title, author, terms, and date.
     *
     * @param int $postId The ID of the post
     * @param \WP_Post|null $post The post object or null to fetch it
     *
     * @return void
     */
    public function addOrUpdateResource($postId, $post)
    {
        $post = get_post($postId);

        if (!is_post_type_viewable($post->post_type)) {
            return;
        }

        if (is_null($post) || $post->post_status === 'trash') {
            return;
        }

        if ($post->post_status !== 'publish') {
            return;
        }

        $this->setResourcesIdentifier($postId, $post->post_type);

        if (empty($this->resource)) {
            ResourcesFactory::setResource($post);
            return;
        }

        $this->resource->getRecord()->update([
            'cached_title'       => $post->post_title,
            'cached_author_id'   => $post->post_author,
            'cached_terms'       => $this->getTerms($postId),
            'cached_date'        => get_post_field('post_date', $postId)
        ]);
    }

    /**
     * Retrieves and formats the taxonomy term IDs for the specified resource.
     *
     * Collects all taxonomy terms associated with a post and returns them as a
     * comma-separated string of term IDs. This method:
     * - Gets all taxonomies associated with the post type
     * - Retrieves terms for each taxonomy
     * - Formats them into a comma-separated list
     *
     * @param int $postId The ID of the post
     *
     * @return string|null Comma-separated string of term IDs, or null if no terms found
     */
    private function getTerms($postId)
    {
        $postType = get_post_type($postId);

        if (!$postType) {
            return;
        }

        $taxonomies = get_object_taxonomies($postType, 'WP_Statistics_names');

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

        return !empty($formattedTerms) ? implode(', ', $formattedTerms) : null;
    }

    /**
     * Updates resource term information when a term is deleted.
     *
     * This method is triggered when a WordPress taxonomy term is deleted.
     * It should update all resource records that reference the deleted term.
     *
     * @param int $termId The ID of the term that was removed
     * @param int $termTaxonomyId The term taxonomy ID
     * @param string $taxonomy The taxonomy slug
     * @param \WP_Term $deletedTerm The deleted term object
     * @param int|int[] $objectIds The IDs of objects (posts) that
     *
     * @return void
     */
    public function removeResourceTerm($termId, $termTaxonomyId, $taxonomy, $deletedTerm, $objectIds)
    {
        if (empty($objectIds)) {
            return;
        }

        $affectedIds = array_values(array_filter(array_map('intval', (array) $objectIds)));
        if (empty($affectedIds)) {
            return;
        }

        $derivedQuery = Query::select([
                'term_relationships.object_id',
                "GROUP_CONCAT(DISTINCT terms.term_id ORDER BY terms.term_id SEPARATOR ', ') AS term_ids"
            ])
            ->from('term_relationships')
            ->join('term_taxonomy', ['term_taxonomy.term_taxonomy_id', 'term_relationships.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->where('term_relationships.object_id', 'IN', $affectedIds)
            ->groupBy('term_relationships.object_id')
            ->getQuery();

        Query::update('resources')
            ->joinQuery($derivedQuery, ['resources.resource_id', 'x.object_id'], 'x', 'LEFT')
            ->setRaw('resources.cached_terms', "IFNULL(x.term_ids, '')")
            ->where('resources.is_deleted', '=', 0)
            ->where('resources.resource_id', 'IN', $affectedIds)
            ->where('resources.resource_type', 'IN', PostType::getQueryableTypes())
            ->execute();
    }

    /**
     * Reassigns resource author information when a user is deleted.
     *
     * This method is triggered when a WordPress user is deleted.
     * It should reassign all resource records from the deleted user to the new user.
     *
     * @param int $userId The ID of the user being deleted
     * @param int $reassignId The new user ID to reassign the posts to
     *
     * @return void
     */
    public function reassignResourceAuhtor($userId, $reassignId)
    {
        Query::update('resources')
            ->set([
                'cached_author_id' => $reassignId,
            ])
            ->where('cached_author_id', '=', $userId)
            ->execute();
    }
}
