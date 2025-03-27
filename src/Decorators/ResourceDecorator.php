<?php

namespace WP_Statistics\Decorators;

class ResourceDecorator
{
    private $postId;

    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    public function getId()
    {
        return $this->postId;
    }

    public function getTitle()
    {
        return get_the_title($this->postId);
    }

    public function getUrl()
    {
        return get_the_permalink($this->postId);
    }

    public function getPostType()
    {
        return get_post_type($this->postId);
    }
}
