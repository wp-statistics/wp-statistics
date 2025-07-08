<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Decorators\ResourceDecorator;
use WP_Statistics\Decorators\ResourceUriDecorator;
use WP_Statistics\Records\RecordFactory;

/**
 * Factory class for creating and retrieving resource objects and decorators.
 *
 * This factory provides a centralized way to create and retrieve resource-related objects,
 * including ResourceDecorator and ResourceUriDecorator instances. It serves as an abstraction
 * layer that simplifies resource management by offering various methods to retrieve resources
 * based on different identifiers and contexts.
 *
 * @package WP_Statistics\Service\Resources
 * @since 15.0.0
 */
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
        $record = RecordFactory::resource()->get([
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
     * @param string $resourceUri The URL of the resource.
     *
     * @return ResourceDecorator|null A decorator for the resource record, or null if not found.
     */
    public static function getByUrl($resourceUri)
    {
        if (empty($resourceUri)) {
            return null;
        }

        $resourceUriRecord = RecordFactory::resourceUri()->get(['url' => $resourceUri]);

        if (empty($resourceUriRecord->resource_id)) {
            return null;
        }

        $record = RecordFactory::resource()->get([
            'ID' => $resourceUriRecord->resource_id,
        ]);

        return !empty($record) ? new ResourceDecorator($record) : null;
    }

    /**
     * Retrieves a resource by its URL ID.
     *
     * This method searches the 'url' table for a resource URL that matches the provided URL ID,
     * and wraps the resulting resource record in a ResourceDecorator if a match is found.
     *
     * @param int $resourceUriId The ID of the resource URL.
     * @return ResourceDecorator|null A decorator for the resource record, or null if not found.
     */
    public static function getByUrlId($resourceUriId)
    {
        $record = RecordFactory::resourceUri()->get(['ID' => $resourceUriId]);

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
     * Retrieves the current resource URL based on the current request.
     *
     * This method creates a ResourceUriDecorator without a specific identifier,
     * allowing it to determine the resource URL context from the current request.
     *
     * @return ResourceUriDecorator A decorator representing the current resource URL.
     */
    public static function getCurrentResourceUri()
    {
        return new ResourceUriDecorator();
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
