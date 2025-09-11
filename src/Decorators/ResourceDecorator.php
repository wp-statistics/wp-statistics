<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Records\ResourceRecord;
use WP_Statistics\Service\Resources\ResourceManager;

/**
 * Decorator for a record from the `resources` table.
 *
 * This class provides a clean interface for accessing resource data
 * by wrapping the ResourceManager and exposing formatted accessors for
 * various resource-related properties such as ID, URL, title, and metadata.
 * Helps abstract direct access to the raw resource data structure.
 *
 * @since 15.0.0
 */
class ResourceDecorator
{
    /**
     * The resource manager instance that handles resource identification.
     *
     * @var ResourceManager The ResourceManager instance for managing resource operations
     */
    private $identifier;

    /**
     * Constructs a new ResourceDecorator instance.
     *
     * Initializes the decorator with an optional record parameter that can be
     * a resource record object, record ID, or null for current context.
     * The ResourceManager will handle resource identification and data retrieval.
     *
     * @param mixed $record Optional. Resource record object, record ID (int), or null
     */
    public function __construct($record = null)
    {
        if (is_int($record)) {
            new PostDecorator($record);
            return;
        }

        $this->identifier = new ResourceManager($record);
    }

    /**
     * Retrieves the underlying ResourceManager instance.
     *
     * @return ResourceManager The underlying ResourceManager instance
     */
    public function getResource()
    {
        return $this->identifier->resource;
    }

    /**
     * Gets the record ID from the resources table.
     *
     * Returns the primary key ID from the resources table.
     *
     * @return int|null The record ID as an integer, or null if not available
     */
    public function getId()
    {
        return !empty($this->identifier->resource->record->ID) ? (int)$this->identifier->resource->record->ID : null;
    }

    /**
     * Gets the resource's unique identifier.
     *
     * Returns the resource_id field which identifies the actual WordPress object.
     *
     * @return int The resource ID as an integer, or zero if not available
     */
    public function getResourceId()
    {
        return !empty($this->identifier->resource->record->resource_id) ? (int)$this->identifier->resource->record->resource_id : 0;
    }

    /**
     * Gets the resource title.
     *
     * Returns the cached title for the resource, with special handling for home page.
     *
     * @return string|null The resource title, or null if not available
     */
    public function getTitle()
    {
        $title = $this->identifier->resource->record->cached_title ?? null;

        if (
            !empty($this->identifier->resource->record->resource_type) &&
            'home' === $this->identifier->resource->record->resource_type
        ) {
            $title = esc_html__('Home', 'wp-statistics');
        }

        return $title;
    }

    /**
     * Gets the resource type.
     *
     * Returns the type of resource (e.g., post type or 'user').
     *
     * @return string The resource type, or empty string if not available
     */
    public function getType()
    {
        return $this->identifier->resource->record->resource_type ?? '';
    }

    /**
     * Gets the cached terms associated with the resource.
     *
     * Returns the cached taxonomy terms for this resource.
     *
     * @return string|null The cached terms as a string, or empty array if not available
     */
    public function getCachedTerms()
    {
        return $this->identifier->resource->record->cached_terms ?? [];
    }

    /**
     * Gets the cached author ID for the resource.
     *
     * Returns the cached author ID associated with this resource.
     *
     * @return int|null The cached author ID, or 0 if not available
     */
    public function getCachedAuthorId()
    {
        return $this->identifier->resource->record->cached_author_id ?? 0;
    }

    /**
     * Gets the cached author name for the resource.
     *
     * Returns the cached author display name for this resource.
     *
     * @return string|null The cached author name, or 0 if not available
     */
    public function getCachedAuthorName()
    {
        return $this->identifier->resource->record->cached_author_name ?? 0;
    }

    /**
     * Gets the cached date of the resource.
     *
     * Returns the cached creation/publication date for this resource.
     *
     * @return string|null The cached date, or empty string if not available
     */
    public function getCachedDate()
    {
        return $this->identifier->resource->record->cached_date ?? '';
    }

    /**
     * Gets the resource metadata.
     *
     * Returns any additional metadata stored for this resource.
     *
     * @return string|null The resource metadata, or empty string if not available
     */
    public function getResourceMeta()
    {
        return $this->identifier->resource->record->resource_meta ?? '';
    }

    /**
     * Gets the resource language.
     *
     * Returns the language of the resource.
     *
     * @return string|null The resource language, or null if not available
     */
    public function getLanguage()
    {
        return $this->identifier->resource->record->language ?? null;
    }

    /**
     * Gets the resource model instance.
     *
     * Returns the ResourceRecord model for performing database operations.
     *
     * @return ResourceRecord The resource model instance
     */
    public function getRecord()
    {
        return $this->identifier->resource->getRecord();
    }
}
