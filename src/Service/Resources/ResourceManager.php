<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Service\Resources\Identifications\ResourceIdentifier;
use WP_Statistics\Service\Resources\Identifications\ResourceUriIdentifier;

/**
 * ResourceManager Class
 *
 * Manages resource identification and URL handling.
 * This class serves as a factory and coordinator for resource-related operations,
 * determining the appropriate resource type and URL based on provided records.
 *
 * @package WP_Statistics\Service\Resources
 * @since 15.0.0
 */
class ResourceManager
{
    /**
     * Resource identifier instance
     *
     * @var ResourceIdentifier|null
     */
    public $resource = null;

    /**
     * Resource URL identifier instance
     *
     * @var ResourceUriIdentifier|null
     */
    public $resourceUri = null;

    /**
     * ResourceManager constructor
     *
     * Initializes the resource manager with optional record data.
     * Determines the appropriate resource type based on the provided record
     * or resolves resources automatically if no record is provided.
     *
     * @param object|null $record Optional record object containing resource data
     */
    public function __construct($record = null)
    {
        if (empty($record)) {
            $this->resolveResource();
            return;
        }

        if (!empty($record->resource_type)) {
            $this->resource = new ResourceIdentifier($record);
            return;
        }

        if (!empty($record->url)) {
            $this->resourceUri = new ResourceUriIdentifier($record);
            return;
        }
    }

    /**
     * Resolve resource automatically
     *
     * Creates a new ResourceIdentifier instance and, if successful,
     * creates a corresponding ResourceUriIdentifier. This method handles
     * the automatic resolution of resources when no specific record is provided.
     *
     * @return void
     */
    private function resolveResource()
    {
        $this->resource = new ResourceIdentifier();

        if (empty($this->resource->record)) {
            return;
        }

        $this->resourceUri = new ResourceUriIdentifier($this->resource->record);
    }
}