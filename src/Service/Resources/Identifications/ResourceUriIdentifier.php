<?php

namespace WP_Statistics\Service\Resources\Identifications;

use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Url;

/**
 * Class ResourceUriIdentifier
 *
 * Handles identification and management of resource URIs in the WP Statistics system.
 * This class is responsible for creating, retrieving, and managing URI records
 * associated with specific resources.
 *
 * @package WP_Statistics\Service\Resources\Identifications
 * @since 15.0.0
 */
class ResourceUriIdentifier
{
    /**
     * The database row ID of the resource URI record.
     *
     * @var int|null
     */
    private $rowId = null;

    /**
     * The resource URI record object.
     *
     * @var object|null
     */
    public $record = null;

    /**
     * The cleaned URI string.
     *
     * @var string|null
     */
    private $uri = null;

    /**
     * The ID of the associated resource.
     *
     * @var int|null
     */
    private $resourceId = null;

    /**
     * ResourceUriIdentifier constructor.
     *
     * Initializes the identifier with a record and handles the workflow
     * of either retrieving an existing record or creating a new one.
     *
     * @param mixed $record Optional. Can be a row ID (int) or record object
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
     * Gets the ResourceUri record instance.
     *
     * Creates and returns a ResourceUri record factory instance
     * using the current record data.
     *
     * @return object The ResourceUri record instance
     */
    public function getRecord()
    {
        return RecordFactory::resourceUri($this->record);
    }

    /**
     * Sets the internal properties based on the provided record.
     *
     * Analyzes the input record and sets appropriate properties
     * such as rowId, record object, and resourceId based on
     * the structure and content of the input.
     *
     * @param mixed $record The record to process (int, object, or null)
     * @return void
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

        if (!empty($record->resource_id)) {
            if (!empty($record->uri)) {
                $this->record = $record;
                $this->rowId  = isset($record->ID) ? $record->ID : null;
                return;
            }

            if (!empty($record->resource_type)) {
                $this->resourceId = $record->ID;
                return;
            }
        }

        if (
            empty($record->resource_id) &&
            !empty($record->resource_type) &&
            ($record->resource_type === 'home' || $record->resource_type === '404' || $record->resource_type === 'search')
        ) {
            $this->resourceId = $record->ID;
            return;
        }
    }

    /**
     * Retrieves the resource URI record from the database.
     *
     * Fetches the complete record data using the stored row ID
     * if the record is not already loaded.
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
     * Inserts a new resource URI record into the database.
     *
     * Creates a new record if one doesn't exist for the current
     * resource ID and URI combination. The URI is cleaned before
     * insertion to remove tracking parameters.
     *
     * @return void
     */
    private function insert()
    {
        $this->uri = !empty($this->uri) ? $this->uri : home_url(add_query_arg(null, null));
        $this->uri = Url::getRelativePath($this->uri);
        $this->uri = apply_filters('wp_statistics_page_uri', $this->uri);

        if (empty($this->resourceId)) {
            return;
        }

        $this->record = RecordFactory::resourceUri()->get([
            'resource_id' => $this->resourceId,
            'uri'         => $this->uri,
        ]);

        if (!empty($this->record)) {
            return;
        }

        $rowId = $this->getRecord()->insert([
            'resource_id' => $this->resourceId,
            'uri'         => $this->uri,
        ]);

        $this->record = $this->getRecord()->get(['ID' => $rowId]);
    }
}