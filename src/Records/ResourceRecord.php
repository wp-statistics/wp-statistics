<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Utils\Query;

class ResourceRecord extends BaseRecord
{

    /**
     * Returns the table name for resource records.
     *
     * @return string
     */
    protected function setTableName()
    {
        $this->tableName = 'resources';
    }

    /**
     * Marks the record as deleted (soft delete) by setting an is_deleted flag.
     *
     * @param array $args Optional additional fields to update.
     * @return void
     */
    public function markAsDeleted($args = [])
    {
        if (empty($this->record->ID)) {
            return;
        }

        $defaults = [
            'is_deleted' => 1,
        ];

        $args = $this->parseArgs($args, $defaults);

        Query::update($this->tableName)
            ->set($args)
            ->where('ID', '=', $this->record->ID)
            ->execute();
    }
}
