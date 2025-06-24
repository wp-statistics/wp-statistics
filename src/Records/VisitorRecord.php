<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;
use WP_STATISTICS\TimeZone;

/**
 * Handles database interactions for the `visitors` table.
 *
 * This class relies on BaseRecord for core database operations.
 *
 * @since 15.0.0
 */
class VisitorRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'visitors';

    /**
     * Retrieve a visitor record by hash and created date.
     *
     * This method checks for a visitor entry that matches the given hash
     * and was created on the current date (date portion only, time ignored).
     *
     * @param array $args {
     *     Optional. Arguments to match the visitor.
     *
     * @type string $hash Visitor hash identifier.
     * @type string $DATE (created_at) Creation date (default is today's date).
     * }
     * @return object|false The visitor record if found, false otherwise.
     */
    public function getByHashAndDate($args)
    {
        $args = $this->parseArgs($args, [
            'hash'             => '',
            'DATE(created_at)' => TimeZone::getCurrentDate('Y-m-d')
        ]);

        return $this->get($args);
    }
}
