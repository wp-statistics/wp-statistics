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
        add_action('wp_after_insert_post', [$this, 'updateResource'], 10, 3);
        add_action('delete_post', [$this, 'removeResource'], 10, 2);

        // @todo: Decide if resource author updates should be handled in the background.
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
    public function removeResource($postId, $post)
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

        $this->resource->removeResource();
    }

    /**
     * Updates the resource data when a WordPress post is updated.
     *
     * This method is hooked to the 'wp_after_insert_post' action and performs
     * several checks to ensure the post is valid before updating resource data.
     *
     * @param int      $postId   The ID of the post.
     * @param \WP_Post $post     The post object.
     * @param bool     $isUpdate Whether the post is being updated.
     *
     * @return void
     */
    public function updateResource($postId, $post, $isUpdate)
    {
        if (! $isUpdate) {
            return;
        }

        if (! is_post_type_viewable($post->post_type)) {
            return;
        }

        $post = get_post($postId);

        if (is_null($post) || $post->post_status === 'trash') {
            return;
        }

        $this->setResourcesIdentifier($postId, $post->post_type);

        if (empty($this->resource)) {
            return;
        }

        $this->resource->updateTitle($post->post_title);
        $this->resource->updateAuthor($post->post_author);
        $this->resource->updateUrl();
        $this->resource->updateTerms();
        $this->resource->updateDate();
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
