<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics;
use WP_STATISTICS\DB;
use WP_Statistics\Utils\Query;

/**
 * BaseRecord provides common database operations for model classes.
 *
 * This abstract class handles the core CRUD operations for a given database table
 * including fetching, inserting, updating, and deleting records.
 * All table-specific models should extend this class and implement the `setTableName` method.
 */
abstract class BaseRecord
{

    /**
     * The current record, if any.
     *
     * @var object|null
     */
    protected $record;

    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * The fully processed table name.
     *
     * @var string
     */
    protected $fullTableName = '';

    /**
     * Constructor.
     *
     * @param object|null $record The initial record object.
     */
    public function __construct($record = null)
    {
        $this->record = $record;

        $this->setFullTableName();
    }

    /**
     * Processes and caches the fully qualified table name.
     *
     * @return void
     */
    protected function setFullTableName()
    {
        if (!empty($this->fullTableName)) {
            return;
        }
        $this->fullTableName = DB::table($this->tableName);
    }

    /**
     * Parses arguments using wp_parse_args and applies a filter.
     *
     * @param array $args The arguments to parse.
     * @param array $defaults The default values.
     * @return array The merged and filtered arguments.
     */
    protected function parseArgs($args, $defaults = [])
    {
        $args = wp_parse_args($args, $defaults);

        return apply_filters('wp_statistics_base_record_args', $args);
    }

    /**
     * Retrieves a record based on given criteria.
     *
     * This generic get method builds where clauses for each non-empty parameter.
     *
     * @param array $args The query parameters.
     * @return object|null The record if found, or null otherwise.
     */
    public function get($args)
    {
        $args = $this->parseArgs($args, []);

        $query = Query::select('*')
            ->from($this->tableName);

        foreach ($args as $key => $value) {
            if (is_null($value)) {
                $query->whereNull($key);
            } elseif(!empty($value)) {
                $query->where($key, '=', $value);
            }
        }

        return $query->getRow();
    }

    /**
     * Retrieves multiple records based on given criteria.
     *
     * @param array $args The query parameters.
     * @return array An array of matching records.
     */
    public function getAll($args)
    {
        $args = $this->parseArgs($args, []);

        $query = Query::select('*')->from($this->tableName);

        foreach ($args as $key => $value) {
            if ($value !== '' && $value !== null) {
                $query->where($key, '=', $value);
            }
        }

        return $query->getAll() ?: [];
    }

    /**
     * Inserts a new record into the table.
     *
     * @param array $args The values to insert.
     * @return int|void The inserted record's ID on success, or void if failed.
     */
    public function insert($args)
    {
        $args = $this->parseArgs($args, []);
        global $wpdb;

        $insert = $wpdb->insert(
            $this->fullTableName,
            $args
        );

        if ($insert === false) {
            WP_Statistics::log('Insert into ' . $this->fullTableName . ' failed: ' . $wpdb->last_error);
            return;
        }

        return $wpdb->insert_id;
    }

    /**
     * Updates an existing record.
     *
     * Only non-empty values in the provided array are used, and the record must have an existing ID.
     *
     * @param array $args The values to update.
     * @return void
     */
    public function update($args)
    {
        $args = $this->parseArgs($args, []);

        $values = [];

        foreach ($args as $key => $value) {
            if ($value === '' || is_null($value)) {
                continue;
            }
            $values[$key] = $value;
        }

        if (empty($values) || empty($this->record->ID)) {
            return;
        }

        Query::update($this->tableName)
            ->set($values)
            ->where('ID', '=', $this->record->ID)
            ->execute();
    }

    /**
     * Retrieves a record's primary key (ID) based on given criteria.
     *
     * This method expects that the passed $args contains the key-value pairs that
     * uniquely identify a record (e.g., ['resource_id' => '...', 'resource_type' => '...', 'resource_url' => '...']).
     *
     * @param array $args Associative array of columns and their values.
     * @return int|null The record's ID if found, or null otherwise.
     */
    public function getId($args)
    {
        if (empty($args)) {
            return 0;
        }

        $args = $this->parseArgs($args, []);

        $query = Query::select('ID')
            ->from($this->tableName);

        $hasCondition = false;

        foreach ($args as $key => $value) {
            if ($value === '' || is_null($value)) {
                continue;
            }

            $hasCondition = true;
            $query->where($key, '=', $value);
        }

        if (!$hasCondition) {
            return 0;
        }

        return $query->getVar();
    }

    /**
     * Removes a record from the table.
     *
     * @return void
     */
    public function remove()
    {
        if (empty($this->record->ID)) {
            return;
        }

        global $wpdb;

        $deleted = $wpdb->delete($this->fullTableName, ['ID' => $this->record->ID]);
        if ($deleted === false) {
            WP_Statistics::log('Failed to delete record from ' . $this->tableName . ' with ID ' . $this->record->ID . ': ' . $wpdb->last_error);
        }
    }
}
