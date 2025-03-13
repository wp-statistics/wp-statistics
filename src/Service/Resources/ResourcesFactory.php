<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Decorators\ResourceDecorator;
use WP_Statistics\Utils\Query;

class ResourcesFactory
{
    /**
     * Retrieves a resource by its logical resource ID and resource type.
     *
     * This method queries the 'resources' table using the given resource ID and type,
     * and then wraps the result in a ResourceDecorator if a matching row is found.
     *
     * @param int    $resourceId   The logical identifier for the resource.
     * @param string $resourceType The type of the resource (e.g., 'post', 'page').
     *
     * @return ResourceDecorator|null
     */
    public static function getByResourceId($resourceId, $resourceType)
    {
        $rowId = Query::select('ID')
            ->from('resources')
            ->where('resource_id', '=', $resourceId)
            ->where('resource_type', '=', $resourceType)
            ->getVar();
        
        return ! empty($rowId) ? new ResourceDecorator($rowId) : null;
    }

    /**
     * Retrieves a resource by its URL.
     *
     * This method searches the 'resources' table for a resource that matches the given URL,
     * and then wraps the result in a ResourceDecorator if a matching row is found.
     *
     * @param string $resourceUrl The URL of the resource.
     *
     * @return ResourceDecorator|null
     */
    public static function getByUrl($resourceUrl)
    {
        $rowId = Query::select('ID')
            ->from('resources')
            ->where('resource_url', '=', $resourceUrl)
            ->getVar();

        return ! empty($rowId) ? new ResourceDecorator($rowId) : null;
    }

    /**
     * Retrieves a resource by its database row ID.
     *
     * This method creates a new ResourceDecorator instance for the given row ID.
     *
     * @param int $rowId The database row identifier for the resource.
     *
     * @return ResourceDecorator|null
     */
    public static function getById($rowId)
    {
        return ! empty($rowId) ? new ResourceDecorator($rowId) : null;
    }

    /**
     * Retrieves the current resource based on the current URL or context.
     *
     * This method instantiates a ResourceDecorator without a specific identifier,
     * allowing it to determine the resource context from the current request.
     *
     * @return ResourceDecorator
     */
    public static function getCurrentResource()
    {
        return new ResourceDecorator();
    }
}
