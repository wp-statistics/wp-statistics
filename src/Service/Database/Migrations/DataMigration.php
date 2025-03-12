<?php

namespace WP_Statistics\Service\Database\Migrations;

use Exception;
use WP_Statistics\Async\BackgroundProcessMonitor;
use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Manages migrations related to database data.
 */
class DataMigration extends AbstractMigrationOperation
{
    /**
     * The name of the migration operation.
     *
     * @var string
     */
    protected $name = 'data';

    /**
     * The list of migration steps for this operation.
     *
     * This array maps version numbers to their corresponding migration methods.
     * Each version key represents a database migration that needs to be applied
     * for that specific version. The associated value is an array of method names
     * that should be executed for the migration step.
     *
     * Example:
     * 'x.x.x' => [
     *     'FirstMethodName',
     *     'SecondMethodName',
     * ],
     *
     * The method names specified should exist within this class, as this class
     * is specifically designed for handling data migration and related operations.
     * Each method typically defines a set of tasks represented as an array,
     * which may include tasks like data transformation, data migration, or other
     * operations that do not involve schema changes such as modifying table
     * structures or column types.
     *
     * Note: This class should not be used for schema changes such as altering
     * table structures, changing column types, or adding/removing database columns.
     * Those operations should be handled through a dedicated schema migration system.
     *
     * @var array
     */
    protected $migrationSteps = [
        '14.12.6' => [
            'addFirstAndLastPageData',
        ],
    ];

    /**
     * Adds first and last page visit data for each visitor.
     *
     * @return array
     */
    public function addFirstAndLastPageData()
    {
        try {
            $this->ensureConnection();

            $tasks     = [];
            $batchSize = 50;

            $inspect = DatabaseFactory::table('inspect')
                ->setName('visitor')
                ->execute();

            if (!$inspect->getResult()) {
                return $tasks;
            }

            $allVisitors = DatabaseFactory::table('select')
                ->setName('visitor_relationships')
                ->setArgs([
                    'columns'  => ['DISTINCT visitor_id'],
                    'order_by' => 'visitor_id ASC',
                ])
                ->execute()
                ->getResult();

            if (!$allVisitors) {
                return $tasks;
            }

            $visitorIds = array_column($allVisitors, 'visitor_id');

            $totalVisitors = count($visitorIds);

            BackgroundProcessMonitor::setTotalRecords('data_migration_process', $totalVisitors);

            $batches = ceil($totalVisitors / $batchSize);

            for ($batch = 0; $batch < $batches; $batch++) {
                $offset = $batch * $batchSize;
                $currentBatch = array_slice($visitorIds, $offset, $batchSize);

                $tasks[] = [
                    'data'    => $currentBatch,
                    'setData' => 'setVisitorIds',
                    'class'   => 'visitor_search_insert'
                ];
            }

            return $tasks;
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }
}
