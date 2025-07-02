<?php

namespace WP_Statistics\Service\Resources\Identifications;

use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\QueryParams;

/**
 * Class ResourceUrlIdentifier
 *
 * Handles identification and management of resource URLs in the WP Statistics system.
 * This class is responsible for creating, retrieving, and managing URL records
 * associated with specific resources.
 *
 * @package WP_Statistics\Service\Resources\Identifications
 * @since 15.0.0
 */
class ResourceUrlIdentifier
{
    /**
     * The database row ID of the resource URL record.
     *
     * @var int|null
     */
    private $rowId = null;

    /**
     * The resource URL record object.
     *
     * @var object|null
     */
    public $record = null;

    /**
     * The cleaned URL string.
     *
     * @var string|null
     */
    private $url = null;

    /**
     * The ID of the associated resource.
     *
     * @var int|null
     */
    private $resourceId = null;

    /**
     * ResourceUrlIdentifier constructor.
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
     * Gets the ResourceUrl record instance.
     *
     * Creates and returns a ResourceUrl record factory instance
     * using the current record data.
     *
     * @return object The ResourceUrl record instance
     */
    public function getRecord()
    {
        return RecordFactory::resourceUrl($this->record);
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
            if (!empty($record->url)) {
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
     * Retrieves the resource URL record from the database.
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
     * Inserts a new resource URL record into the database.
     *
     * Creates a new record if one doesn't exist for the current
     * resource ID and URL combination. The URL is cleaned before
     * insertion to remove tracking parameters.
     *
     * @return void
     */
    private function insert()
    {
        $this->url = !empty($this->url) ? $this->url : home_url(add_query_arg(null, null));
        $this->url = $this->cleanUrl($this->url);

        if (empty($this->resourceId)) {
            return;
        }

        $this->record = RecordFactory::resourceUrl()->get([
            'resource_id' => $this->resourceId,
            'url'         => $this->url,
        ]);

        if (!empty($this->record)) {
            return;
        }

        $rowId = $this->getRecord()->insert([
            'resource_id' => $this->resourceId,
            'url'         => $this->url,
        ]);

        $this->record = $this->getRecord()->get(['ID' => $rowId]);
    }

    /**
     * Cleans a URL by removing specific tracking parameters.
     *
     * Removes tracking and analytics parameters from URLs to create
     * a canonical version for storage. This helps prevent duplicate
     * entries for the same logical resource.
     *
     * @param string $url The URL to clean
     * @return string The cleaned URL without tracking parameters
     */
    private function cleanUrl($url)
    {
        $trackingParams = QueryParams::getAllowedList('array', true);

        $parts = wp_parse_url($url);

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);

            foreach ($trackingParams as $param) {
                unset($query[$param]);
            }
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host   = isset($parts['host']) ? $parts['host'] : '';
        $path   = isset($parts['path']) ? $parts['path'] : '/';

        $newUrl = $scheme . $host . $path;

        if (!empty($query)) {
            $newUrl .= '?' . http_build_query($query);
        }

        return $newUrl;
    }
}