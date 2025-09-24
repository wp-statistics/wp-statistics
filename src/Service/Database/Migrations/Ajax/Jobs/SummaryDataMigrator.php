<?php
namespace WP_Statistics\Service\Database\Migrations\Ajax\Jobs;

use WP_Statistics\Service\Database\Migrations\Ajax\AbstractAjax;

class SummaryDataMigrator extends AbstractAjax
{
    /**
     * Total number of batches required for migration.
     *
     * @var int
     */
    protected $batches = 0;

    /**
     * Offset for batch processing.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Retrieves the total count of data to be migrated.
     *
     * @param bool $needCaching Whether to load/save from cache. Defaults to true.
     * @return void
     */
    protected function getTotal($needCaching = true)
    {

    }

    /**
     * Calculates the migration offset based on already processed data.
     */
    protected function calculateOffset()
    {

    }

    /**
     * Checks whether the migration has already been completed based on existing data.
     *
     * @return bool|null Returns true if data is already migrated and job is marked as completed, null otherwise.
     */
    protected function isAlreadyDone()
    {

    }

    /**
     * Executes the migration process for visitor data.
     */
    protected function migrate()
    {

    }
}
