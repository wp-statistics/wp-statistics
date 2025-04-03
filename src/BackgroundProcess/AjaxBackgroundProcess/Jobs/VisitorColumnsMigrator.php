<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\AbstractAjaxBackgroundProcess;
use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Handles the migration of visitor column data, ensuring first and last page visits
 * are correctly stored in the `visitor` table.
 */
class VisitorColumnsMigrator extends AbstractAjaxBackgroundProcess
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
     * Retrieves the total count of visitors to be migrated.
     *
     * If the total is already set, the method exits early.
     */
    protected function getTotal()
    {
        $inspect = DatabaseFactory::table('inspect')
            ->setName('visitor')
            ->execute();

        if (!$inspect->getResult()) {
            return;
        }

        $allVisitors = DatabaseFactory::table('select')
            ->setName('visitor_relationships')
            ->setArgs([
                'columns'  => ['DISTINCT visitor_id'],
                'order_by' => 'visitor_id ASC',
            ])
            ->execute()
            ->getResult();

        $this->total   = count($allVisitors);
        $this->batches = ceil($this->total / $this->batchSize);
    }

    /**
     * Calculates the migration offset based on already processed visitors.
     *
     * Retrieves the number of visitors where the first and last visit data is already set.
     */
    protected function calculateOffset()
    {
        $visitors = DatabaseFactory::table('select')
            ->setName('visitor')
            ->setArgs([
                'columns'   => ['first_page', 'first_view', 'last_page', 'last_view'],
                'raw_where' => [
                    "first_page IS NOT NULL AND first_page != ''",
                    "first_view IS NOT NULL AND first_view > '0000-00-00 00:00:00'",
                    "last_page IS NOT NULL AND last_page != ''",
                    "last_view IS NOT NULL AND last_view > '0000-00-00 00:00:00'"
                ]
            ])
            ->execute()
            ->getResult();

        $this->done   = count($visitors);
        $currentBatch = ceil($this->done / $this->batchSize);
        $this->offset = $currentBatch * $this->batchSize;
    }

    /**
     * Executes the migration process for visitor data.
     *
     * This method fetches visitor session data and inserts missing first and last page visits.
     */
    protected function migrate()
    {
        $this->getTotal();
        $this->calculateOffset();

        if ($this->isCompleted()) {
            return;
        }

        $visitorBatch = DatabaseFactory::table('select')
            ->setName('visitor_relationships')
            ->setArgs([
                'columns'  => ['visitor_id', 'MIN(ID) as min_id', 'MAX(ID) as max_id'],
                'group_by' => 'visitor_id',
                'limit'    => [
                    $this->batchSize,
                    $this->offset,
                ]
            ])
            ->execute()
            ->getResult();

        if (empty($visitorBatch)) {
            return;
        }

        foreach ($visitorBatch as $visitor) {
            $visitorId = $visitor['visitor_id'];
            $minId     = $visitor['min_id'];
            $maxId     = $visitor['max_id'];

            $firstPage = DatabaseFactory::table('select')
                ->setName('visitor_relationships')
                ->setArgs([
                    'columns' => ['page_id', 'date'],
                    'where'   => ['ID' => $minId],
                ])
                ->execute()
                ->getResult();

            $lastPage = DatabaseFactory::table('select')
                ->setName('visitor_relationships')
                ->setArgs([
                    'columns' => ['page_id', 'date'],
                    'where'   => ['ID' => $maxId],
                ])
                ->execute()
                ->getResult();

            if (!empty($firstPage) && !empty($lastPage)) {
                DatabaseFactory::table('insert')
                    ->setName('visitor')
                    ->setArgs([
                        'conditions' => [
                            'ID' => $visitorId,
                        ],
                        'mapping'    => [
                            'first_page' => $firstPage[0]['page_id'],
                            'first_view' => $firstPage[0]['date'],
                            'last_page'  => $lastPage[0]['page_id'],
                            'last_view'  => $lastPage[0]['date'],
                        ],
                    ])
                    ->execute();
            }
        }
    }
}
