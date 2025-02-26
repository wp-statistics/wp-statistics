<?php

namespace WP_Statistics\Decorators;

class ResourceDecorator
{
    private $pageId;

    public function __construct($pageId)
    {
        $this->pageId = $pageId;
    }

    public function getId()
    {
        return $this->pageId;
    }

    public function getTitle()
    {
        return get_the_title($this->pageId);
    }

    public function getUrl()
    {
        return get_the_permalink($this->pageId);
    }
}
