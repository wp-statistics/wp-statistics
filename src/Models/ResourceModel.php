<?php

namespace WP_Statistics\Models;

use WP_Statistics;
use WP_STATISTICS\DB;
use WP_Statistics\Utils\Query;

class ResourceModel
{
    /**
     * The resource record retrieved from the resources table.
     *
     * @var object
     */
    private $resource;

    /**
     * Constructor.
     *
     * Initializes the ResourceModel with the given resource record.
     *
     * @param object|null $resource The resource record object.
     */
    public function __construct($resource = null)
    {
        $this->resource = $resource;
    }

    /**
     * Parses resource arguments by merging the provided arguments with default values.
     *
     * Uses wp_parse_args to merge the given $args with $defaults and applies the
     * 'wp_statistics_data_resource_args' filter to allow modifications.
     *
     * @param array $args     The arguments to parse.
     * @param array $defaults The default values.
     * @return array The merged and filtered arguments.
     */
    private function parseResourceArgs($args, $defaults = [])
    {
        $args = wp_parse_args($args, $defaults);

        return apply_filters('wp_statistics_data_resource_args', $args);
    }

    /**
     * Updates the resource record with new values.
     *
     * This method accepts an associative array of fields to update and merges it with default keys.
     * It then filters out any empty values (except for the 'date' key, which is ignored) and performs
     * an update on the "resources" table for the record identified by its primary key (ID).
     *
     * @param array $args An associative array of fields and their new values. Expected keys include:
     *                    - 'cached_title'
     *                    - 'cached_terms'
     *                    - 'resource_url'
     *                    - 'cached_author_id'
     *                    - 'cached_author_name'
     *                    - 'cached_date'
     *                    - 'resource_meta'
     *
     * @return void
     */
    public function update($args)
    {
        $args = $this->parseResourceArgs($args, [
            'cached_title'       => '',
            'cached_terms'       => '',
            'resource_url'       => '',
            'cached_author_id'   => '',
            'cached_author_name' => '',
            'cached_date'        => '',
            'resource_meta'      => ''
        ]);

        $values = [];

        foreach ($args as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $values[$key] = $value;
        }

        if (empty($values)) {
            return;
        }

        Query::update('resources')
            ->set($values)
            ->where('ID', '=', $this->resource->ID)
            ->execute();
    }

    /**
     * Retrieves a resource record that matches the provided criteria.
     *
     * This method accepts an associative array with keys such as 'ID' and 'resource_url'
     * to build a query that returns a single resource record from the "resources" table.
     *
     * @param array $args An associative array of query parameters. Expected keys:
     *                    - 'ID'
     *                    - 'resource_id'
     *                    - 'resource_type'
     *                    - 'resource_url'
     * @return object|null The resource record object if found, or null if not found.
     */
    public function get($args)
    {
        $args = $this->parseResourceArgs($args, [
            'ID'            => '',
            'resource_url'  => '',
        ]);

        $row = Query::select('*')
            ->from('resources')
            ->where('ID', '=',  $args['ID'])
            ->where('resource_url', '=', $args['resource_url'])
            ->getRow();

        return $row;
    }

    /**
     * Retrieves the primary key (row ID) for a resource record based on provided criteria.
     *
     * This method builds a query to fetch the "ID" column from the "resources" table,
     * filtering by 'resource_id', 'resource_type', and 'resource_url' values.
     *
     * @param array $args An associative array of query parameters. Expected keys:
     *                    - 'resource_id'
     *                    - 'resource_type'
     *                    - 'resource_url'
     * @return int|null The primary key of the resource record if found, or null if not found.
     */
    public function getId($args)
    {
        $args = $this->parseResourceArgs($args, [
            'resource_id'   => '',
            'resource_type' => '',
            'resource_url'  => '',
        ]);

        $rowId = Query::select('ID')
            ->from('resources')
            ->where('resource_id', '=',  $args['resource_id'])
            ->where('resource_type', '=', $args['resource_type'])
            ->where('resource_url', '=', $args['resource_url'])
            ->getVar();

        return $rowId;
    }

    /**
     * Removes the resource record.
     *
     * @return void
     * @todo the functionality of remove should be moved to Query class.
     */
    public function remove()
    {
        global $wpdb;
        $table = DB::table('resources');

        $deleted = $wpdb->delete($table, ['ID' => $this->resource->ID]);

        if ($deleted === false) {
            WP_Statistics::log('Failed to delete resource with ID ' . $this->resource->ID . ': ' . $wpdb->last_error);
        }
    }

    /**
     * Inserts a new resource record into the "resources" table.
     *
     * This method accepts an associative array of values, parses the arguments,
     * and performs an insert operation using $wpdb->insert(). If the insert is successful,
     * it returns the inserted row's primary key. Otherwise, it logs an error.
     *
     * Expected keys in $args:
     *  - 'resource_id'
     *  - 'resource_type'
     *  - 'resource_url'
     *  - 'cached_title'
     *  - 'cached_terms'
     *  - 'cached_author_id'
     *  - 'cached_author_name'
     *  - 'cached_date'
     *
     * @todo the functionality of remove should be moved to Query class( its already exist in another branch so no need to add it ).
     * @param array $args An associative array of values to insert.
     * @return int|void The inserted row ID on success, or nothing if the insert fails.
     */
    public function insert($args)
    {
        $args = $this->parseResourceArgs($args, [
            'resource_id'        => '',
            'resource_type'      => '',
            'resource_url'       => '',
            'cached_title'       => '',
            'cached_terms'       => '',
            'cached_author_id'   => '',
            'cached_author_name' => '',
            'cached_date'        => '',
        ]);

        global $wpdb;

        $insert = $wpdb->insert(
            DB::table('resources'),
            $args
        );

        if ($insert === false) {
            WP_Statistics::log('Insert into resources failed: ' . $wpdb->last_error);
            return;
        }

        return $wpdb->insert_id;
    }
}
