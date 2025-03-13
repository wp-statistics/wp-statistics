<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Resources\Core\ResourcesIdentifier;

class ResourceDecorator {
    /**
     * The underlying ResourcesIdentifier instance.
     *
     * @var ResourcesIdentifier
     */
    private $resourcesIdentifier;

    /**
     * Constructor.
     *
     * Initializes the decorator with a given row ID. If a row ID is provided,
     * it creates a ResourcesIdentifier object using that ID. Otherwise, it will
     * attempt to determine the resource from the current context (e.g., current URL).
     *
     * @param int|null $rowId Optional. The database row identifier for the resource.
     */
    public function __construct($rowId = null)
    {
        $this->resourcesIdentifier = new ResourcesIdentifier($rowId);
    }

    /**
     * Retrieves the underlying ResourcesIdentifier instance.
     *
     * @return ResourcesIdentifier The underlying ResourcesIdentifier instance.
     */
    public function getResource() {
        return $this->resourcesIdentifier;
    }

    /**
     * Retrieves the row ID that was derived from the current URL.
     *
     * @return int|null
     */
    public function getId() {
        return $this->resourcesIdentifier->resource->ID ?? null;
    }

    /**
     * Gets the resource's unique identifier.
     *
     * @return int|null
     */
    public function getResourceId()
    {
        return $this->resourcesIdentifier->resource->resource_id ?? null;
    }

    /**
     * Gets the resource's unique identifier.
     *
     * @return int|null
     */
    public function getTitle()
    {
        return $this->resourcesIdentifier->resource->cached_title ?? null;
    }

    /**
     * Retrieves the resource type.
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->resourcesIdentifier->resource->resource_type ?? null;
    }

    /**
     * Retrieves the URL of the resource.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->resourcesIdentifier->resource->resource_url ?? null;
    }

     /**
     * Retrieves the cached terms associated with the resource.
     *
     * @return string|null
     */
    public function getCachedTerms()
    {
        return $this->resourcesIdentifier->resource->cached_terms ?? [];
    }

    /**
     * Retrieves the cached author ID for the resource.
     *
     * @return int|null
     */
    public function getCachedAuthorId()
    {
        return $this->resourcesIdentifier->resource->cached_author_id ?? 0;
    }

    /**
     * Retrieves the cached author name for the resource.
     *
     * @return string|null
     */
    public function getCachedAuthorName()
    {
        return $this->resourcesIdentifier->resource->cached_author_name ?? 0;
    }

    /**
     * Retrieves the cached date of the resource.
     *
     * @return string|null
     */
    public function getCachedDate()
    {
        return $this->resourcesIdentifier->resource->cached_date ?? '';
    }

    /**
     * Retrieves the resource metadata.
     *
     * @return string|null
     */
    public function getResourceMeta()
    {
        return $this->resourcesIdentifier->resource->resource_meta ?? '';
    }
}