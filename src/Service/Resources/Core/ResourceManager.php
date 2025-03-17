<?php

namespace WP_Statistics\Service\Resources\Core;

use WP_Statistics\Service\Resources\ResourcesFactory;

class ResourceManager
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

        /**
         * @todo: We have to remove them from here and move them to the admin page of the resouces.
         */
        add_action('profile_update', [$this, 'updateResourceAuthor']);
        add_action('delete_term', [$this, 'removeResourceTerm'], 10, 3);
        add_action('delete_user', [$this, 'reassignResourceAuhtor'], 10, 3);
        add_action('permalink_structure_changed', [$this, 'updateResourceUrl']);
    }

    /**
     * Initializes the ResourcesIdentifier object if it hasn't been set yet.
     *
     * @param int    $resourceId   The ID of the post (resource).
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
     * @param int      $postId The ID of the post being deleted.
     * @param \WP_Post $post   The post object being deleted.
     *
     * @return void
     */
    public function setResourceAsDeleted($postId, $post)
    {
        if (! is_object($post)) {
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

        $this->resource->getModel()->markAsDeleted();
    }

    /**
     * Updates the resource data when a WordPress post is updated.
     *
     * This method is hooked to the 'wp_after_insert_post' action and performs
     * several checks to ensure the post is valid before updating resource data.
     *
     * @param int      $postId The ID of the post.
     * @param \WP_Post $post   The post object.
     *
     * @return void
     */
    public function addOrUpdateResource($postId, $post)
    {
        $post = get_post($postId);

        if (! is_post_type_viewable($post->post_type)) {
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

        $this->resource->getModel()->update([
            'cached_title'       => $post->post_title,
            'resource_url'       => get_the_permalink($postId),
            'cached_author_id'   => $post->post_author,
            'cached_author_name' => $this->getAuthorName($post->post_author),
            'cached_terms'       => $this->getTerms($postId),
            'cached_date'        => get_post_field('post_date', $postId)
        ]);
    }

    /**
     * Retrieves and formats the taxonomy term IDs for the specified resource.
     * 
     * @param int $postId The ID of the post.
     * @return string|null
     */
    private function getTerms($postId)
    {
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

        return ! empty($formattedTerms) ? implode(', ', $formattedTerms) : null;
    }

    /**
     *  Retrieves the display name for a given author ID.
     * 
     * @param int $authorId The ID of the author to set.
     * @return string|null
     */
    public function getAuthorName($authorId)
    {
        if (empty($authorId)) {
            return;
        }

        $authorInfo = get_userdata($authorId);

        return ! empty($authorInfo) ? $authorInfo->display_name : '';
    }

    /**
     * Updates the resource author information.
     * 
     * @param int $userId The ID of the user whose profile is updated.
     * @todo It should be decided whether this should be handled as a background process,
     *       or directly query and update the table.
     */
    public function updateResourceAuthor($userId) {}

    /**
     * Updates the resource term information.
     * 
     * @param int    $termId         The ID of the term that was removed.
     * @param int    $termTaxonomyId The term taxonomy ID.
     * @param string $taxonomy       The taxonomy slug.
     * @todo It should be decided whether this should be handled as a background process,
     *       or directly query and update the table.
     */
    public function removeResourceTerm($termId, $termTaxonomyId, $taxonomy) {}

    /**
     * Reassign the resource author information.
     *
     * @param int    $userId     The ID of the user being deleted.
     * @param int    $reassignId The new user ID to reassign the posts to.
     * @param object $user       The deleted user object.
     * @todo It should be decided whether this should be handled as a background process,
     *       or directly query and update the table.
     */
    public function reassignResourceAuhtor($userId, $reassignId, $user) {}

    /**
     * Updates the resource url.
     *
     * @todo It should be decided whether this should be handled as a background process,
     *       or directly query and update the table.
     */
    public function updateResourceUrl() {}
}
