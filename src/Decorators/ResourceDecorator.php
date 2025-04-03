<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Models\ResourceModel;
use WP_Statistics\Service\Resources\Core\ResourcesIdentifier;

class ResourceDecorator
{
    /**
     * The underlying ResourcesIdentifier instance.
     *
     * @var ResourcesIdentifier
     */
    private $resourcesIdentifier;

    /**
     * Constructor.
     *
     * Initializes the ResourceDecorator with an optional resource record.
     * If a resource record (object) or record ID is provided, a ResourcesIdentifier is
     * instantiated using that data. Otherwise, the ResourcesIdentifier will attempt to
     * determine the resource context based on the current request (e.g., via the current URL).
     *
     * @param mixed $record A resource record object or resource record ID.
     */
    public function __construct($record = null)
    {
        $this->resourcesIdentifier = new ResourcesIdentifier($record);
    }

    /**
     * Retrieves the underlying ResourcesIdentifier instance.
     *
     * @return ResourcesIdentifier The underlying ResourcesIdentifier instance.
     */
    public function getResource()
    {
        return $this->resourcesIdentifier;
    }

    /**
     * Retrieves the row ID that was derived from the current URL.
     *
     * @return int|null
     */
    public function getId()
    {
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

    /**
     * Retrieves the resource model.
     *
     * @return ResourceModel The resource model instance.
     */
    public function getModel()
    {
        return $this->resourcesIdentifier->getModel();
    }
}
