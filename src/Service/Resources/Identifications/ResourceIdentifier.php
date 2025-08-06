<?php

namespace WP_Statistics\Service\Resources\Identifications;

use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Records\ResourceRecord;
use WP_Statistics\Service\Resources\Core\ResourceDetector;

/**
 * Identifies and manages resource-related data.
 */
class ResourceIdentifier
{
    /**
     * Holds an instance of ResourceDetector used to gather detailed resource information.
     *
     * @var ResourceDetector|null
     */
    private $detector;

    /**
     * The row ID associated with the resource.
     *
     * @var int|null
     */
    private $rowId = null;


    /**
     * The "raw" resource ID extracted from the input (e.g. WP_Post or WP_User).
     *
     * @var int|null
     */
    private $resourceId = null;

    /**
     * The resource type (e.g. post type or 'user').
     *
     * @var string|null
     */
    private $resourceType = null;

    /**
     * The resource data retrieved from the database.
     *
     * @var object|null
     */
    public $record = null;

    /**
     * Initializes the resource with its properties.
     *
     * Accepts either a resource record object, a "raw" object (e.g., WP_Post or WP_User),
     * a record ID, or nothing. Based on the input, it sets internal properties and then either
     * loads an existing resource record or creates a new one.
     *
     * @param mixed $record Either a resource record object, a record ID (int), or empty.
     */
    public function __construct($record = null)
    {
        $this->set($record);

        if (!empty($this->rowId)) {
            $this->get();
            return;
        }

        $this->insert();
    }

    /**
     * Sets the internal record from the provided resource data.
     *
     * Accepts one of the following:
     * - A **resource record object**: an object already stored in the resources table (i.e. it contains properties like
     *   `resource_type` and `resource_id`), in which case its `ID` is used as the row identifier.
     * - A **raw object**: for example, a WP_Post, WP_User, or any other object that contains an `ID` (and possibly a `post_type`).
     *   In this case, the resource URL is generated (using `get_the_permalink()` for WP_Post objects), and the object's
     *   ID and type (e.g. `post_type`) are extracted for later insertion.
     * - A **record ID**: an integer representing the primary key of a resource record.
     *
     * Depending on the input type, the method extracts and sets the appropriate internal properties:
     * - For a resource record object, it stores the object in `$this->resource` and extracts its row ID.
     * - For a raw object, it extracts the resource URL, resource ID, and resource type.
     * - For a record ID, it simply assigns that value to `$this->rowId`.
     *
     * @param mixed $record Either a resource record object, a raw object (e.g. WP_Post, WP_User), or a record ID (int).
     */
    private function set($record)
    {
        if (empty($record)) {
            return;
        }

        if (!is_object($record)) {
            $this->rowId = $record;
            return;
        }

        if (!empty($record->resource_type) && isset($record->resource_id)) {
            $this->record = $record;
            $this->rowId  = isset($record->ID) ? $record->ID : null;
            return;
        }

        if (isset($record->ID)) {
            $this->resourceId   = $record->ID;
            $this->resourceType = isset($record->post_type) ? $record->post_type : null;
        }
    }

    /**
     * Retrieves the resource data from the database using the row ID.
     *
     * If the resource data is already loaded, no further action is taken.
     *
     * @return void
     */
    private function get()
    {
        if (!empty($this->record)) {
            return;
        }

        $this->record = $this->getRecord()->get(['ID' => $this->rowId]);
    }

    /**
     * Returns a ResourceRecord instance based on the current resource data.
     *
     * @return ResourceRecord
     */
    public function getRecord()
    {
        return RecordFactory::resource($this->record);
    }

    /**
     * Inserts a new resource record into the database.
     *
     * Uses a ResourceDetector (instantiated with resourceId and resourceType, if available)
     * to gather additional details for the resource, then inserts the record and retrieves it.
     *
     * @return void
     */
    private function insert()
    {
        if (!empty($this->record)) {
            return;
        }

        if (empty($this->detector)) {
            $this->detector = new ResourceDetector($this->resourceId, $this->resourceType);
        }

        $this->record = $this->getRecord()->get([
            'resource_id'   => $this->detector->getResourceId(),
            'resource_type' => $this->detector->getResourceType(),
        ]);

        if (!empty($this->record)) {
            return;
        }

        $insertId = $this->getRecord()->insert([
            'resource_id'        => $this->detector->getResourceId(),
            'resource_type'      => $this->detector->getResourceType(),
            'cached_title'       => $this->detector->getCachedTitle(),
            'cached_terms'       => $this->detector->getCachedTerms(),
            'cached_author_id'   => $this->detector->getCachedAuthorId(),
            'cached_author_name' => $this->detector->getCachedAuthorName(),
            'cached_date'        => $this->detector->getCachedDate(),
            'resource_meta'      => $this->detector->getResourceMeta(),
        ]);

        if (empty($insertId)) {
            return;
        }

        $this->record = $this->getRecord()->get(['ID' => $insertId]);
    }
}
