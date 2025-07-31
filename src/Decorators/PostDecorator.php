<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a post record.
 *
 * This class provides a clean interface for accessing post data
 * by wrapping the post ID and exposing formatted accessors for
 * each column in the posts table.
 *
 * @since 15.0.0
 */
class PostDecorator
{
    /**
     * The ID of the post being decorated.
     *
     * @var int
     */
    private $postId;

    /**
     * PostDecorator Constructor.
     *
     * @param int $postId The ID of the post to decorate.
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * Get the ID of the post.
     *
     * @return int The ID of the post.
     */
    public function getId()
    {
        return $this->postId;
    }

    /**
     * Get the post title.
     *
     * @return string
     */
    public function getTitle()
    {
        return get_the_title($this->postId);
    }

    /**
     * Get the post url.
     *
     * @return string
     */
    public function getUrl()
    {
        return get_the_permalink($this->postId);
    }

    /**
     * Get the post type.
     *
     * @return string
     */
    public function getPostType()
    {
        return get_post_type($this->postId);
    }
}
