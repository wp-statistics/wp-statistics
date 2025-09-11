<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Resources\ResourceManager;

/**
 * Decorator for a record from the 'resource_uris' table.
 *
 * This class provides a clean interface for accessing resource URL data
 * by wrapping the ResourceManager and exposing formatted accessors for
 * each column in the resource_uris table.
 *
 * @since 15.0.0
 */
class ResourceUriDecorator
{
    /**
     * The resource manager instance that handles resource URL identification.
     *
     * @var ResourceManager
     */
    private $identifier;

    /**
     * Constructs a new ResourceUriDecorator instance.
     *
     * Initializes the decorator with an optional record parameter that can be
     * a resource URL record object, record ID, or null for current context.
     *
     * @param mixed $record Optional. Resource URL record object, record ID (int), or null
     */
    public function __construct($record = null)
    {
        $this->identifier = new ResourceManager($record);
    }

    /**
     * Get the record ID.
     *
     * @return int The primary key ID from the resource_uris table or 0 if not set.
     */
    public function getId()
    {
        return empty($this->identifier->resourceUri->record->ID) ? 0 : (int)$this->identifier->resourceUri->record->ID;
    }

    /**
     * Get the resource ID.
     *
     * @return int|null
     */
    public function getResourceId()
    {
        return empty($this->identifier->resourceUri->record->resource_id) ? null : (int)$this->identifier->resourceUri->record->resource_id;
    }

    /**
     * Get the URL.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return empty($this->identifier->resourceUri->record->url) ? null : (string)$this->identifier->resourceUri->record->url;
    }
}