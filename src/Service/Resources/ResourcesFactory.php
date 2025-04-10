<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Decorators\ResourceDecorator;
use WP_Statistics\Models\ResourceModel;

class ResourcesFactory
{
    /**
     * Retrieves a resource by its logical resource ID and resource type.
     *
     * This method queries the 'resources' table using the specified resource ID and type,
     * and then wraps the resulting resource record (if found) in a ResourceDecorator.
     *
     * @param int $resourceId The unique identifier for the resource.
     * @param string $resourceType The type of the resource (e.g., 'post', 'page').
     *
     * @return ResourceDecorator|null A decorator for the resource record, or null if not found.
     */
    public static function getByResourceId($resourceId, $resourceType)
    {
        $record = (new ResourceModel)->get([
            'resource_id'   => $resourceId,
            'resource_type' => $resourceType,
        ]);

        return !empty($record) ? new ResourceDecorator($record) : null;
    }

    /**
     * Retrieves a resource by its URL.
     *
     * This method searches the 'resources' table for a resource that matches the provided URL,
     * and wraps the resulting resource record in a ResourceDecorator if a match is found.
     *
     * @param string $resourceUrl The URL of the resource.
     *
     * @return ResourceDecorator|null A decorator for the resource record, or null if not found.
     */
    public static function getByUrl($resourceUrl)
    {
        $record = (new ResourceModel)->get([
            'resource_url' => $resourceUrl,
        ]);

        return !empty($record) ? new ResourceDecorator($record) : null;
    }

    /**
     * Retrieves a resource by its database record ID.
     *
     * This method instantiates a ResourceDecorator for the resource corresponding to the given record ID.
     *
     * @param int $rowId The database record ID for the resource.
     *
     * @return ResourceDecorator|null A decorator for the resource record, or null if the ID is empty.
     */
    public static function getById($rowId)
    {
        return !empty($rowId) ? new ResourceDecorator($rowId) : null;
    }

    /**
     * Retrieves the current resource based on the current URL or context.
     *
     * This method creates a ResourceDecorator without a specific identifier,
     * allowing it to determine the resource context from the current request.
     *
     * @return ResourceDecorator A decorator representing the current resource.
     */
    public static function getCurrentResource()
    {
        return new ResourceDecorator();
    }

    /**
     * Wraps the given resource data in a ResourceDecorator.
     *
     * This method accepts any type of resource data—such as a WP_Post object, WP_User object, a custom resource record,
     * or even a record ID—and returns a new ResourceDecorator instance that encapsulates the provided data.
     * This allows the resource data to be further manipulated or queried in a consistent manner.
     *
     * @param mixed $data Resource data, which may be a WP_Post, WP_User, a custom resource object, or a record ID.
     *
     * @return ResourceDecorator A decorator instance encapsulating the provided resource.
     */
    public static function setResource($post)
    {
        return new ResourceDecorator($post);
    }
}
