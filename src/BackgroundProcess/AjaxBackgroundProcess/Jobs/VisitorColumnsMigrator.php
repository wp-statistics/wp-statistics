<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\AbstractAjaxBackgroundProcess;
use WP_STATISTICS\Option;
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
     * @param bool $needCaching Whether to load/save from cache. Defaults to true.
     * @return void
     */
    protected function getTotal($needCaching = true)
    {
        $total = $needCaching ? $this->getCachedTotal(self::$currentProcessKey) : false;

        if (!empty($total)) {
            $this->total   = $total;
            $this->batches = ceil($this->total / $this->batchSize);

            return;
        }

        $inspect = DatabaseFactory::table('inspect')
            ->setName('visitor')
            ->execute();

        if (!$inspect->getResult()) {
            return;
        }

        $allVisitors = DatabaseFactory::table('select')
            ->setName('visitor_relationships AS vr')
            ->setArgs([
                'columns'  => ['DISTINCT vr.visitor_id'],
                'joins'    => [
                    [
                        'table' => 'visitor',
                        'alias' => 'v',
                        'on'    => 'vr.visitor_id = v.ID',
                        'type'  => 'INNER'
                    ]
                ],
                'order_by' => 'vr.visitor_id ASC',
            ])
            ->execute()
            ->getResult();

        $this->total   = count($allVisitors);
        $this->batches = ceil($this->total / $this->batchSize);

        if ($needCaching) {
            $this->saveTotal(self::$currentProcessKey, $this->total);
        }
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
                ],
                'raw_where_type' => 'OR',
            ])
            ->execute()
            ->getResult();

        $this->done   = count($visitors);
        $currentBatch = ceil($this->done / $this->batchSize);
        $this->offset = $currentBatch * $this->batchSize;

        $maxOffset = max(0, floor(($this->total - 1) / $this->batchSize) * $this->batchSize);

        if ($this->offset > $maxOffset) {
            $this->offset = $maxOffset;
        }
    }

    /**
     * Checks whether the migration has already been completed based on existing data.
     *
     * @return bool|null Returns true if data is already migrated and job is marked as completed, null otherwise.
     */
    protected function isAlreadyDone()
    {
        $status = Option::getOptionGroup('ajax_background_process', 'status', false);

        if ($status === false || in_array($status, ['progress', 'done'], true)) {
            return;
        }

        $visitors = DatabaseFactory::table('select')
            ->setName('visitor')
            ->setArgs([
                'columns'   => ['first_page', 'first_view', 'last_page', 'last_view'],
                'raw_where' => [
                    "first_page IS NOT NULL AND first_page != ''",
                    "first_view IS NOT NULL AND first_view > '0000-00-00 00:00:00'",
                    "last_page IS NOT NULL AND last_page != ''",
                    "last_view IS NOT NULL AND last_view > '0000-00-00 00:00:00'"
                ],
                'raw_where_type' => 'OR',
            ])
            ->execute()
            ->getResult();

        $this->getTotal(false);

        if (count($visitors) >= $this->total) {
            $this->markAsCompleted(get_class($this));

            return true;
        }

        return;
    }

    /**
     * Executes the migration process for visitor data.
     *
     * This method fetches visitor session data and inserts missing first and last page visits.
     */
    protected function migrate()
    {
        $this->setBatchSize(100);
        $this->getTotal(false);
        $this->calculateOffset();

        if ($this->isCompleted()) {
            $this->saveTotal(self::$currentProcessKey, $this->total);
            return;
        }

        $inspect = DatabaseFactory::table('inspect')
            ->setName('visitor_relationships')
            ->execute();

        if (!$inspect->getResult()) {
            return;
        }

        $visitorBatch = DatabaseFactory::table('select')
            ->setName('visitor_relationships AS vr')
            ->setArgs([
                'columns'  => ['vr.visitor_id', 'MIN(vr.ID) as min_id', 'MAX(vr.ID) as max_id'],
                'group_by' => 'vr.visitor_id',
                'joins'    => [
                    [
                        'table' => 'visitor',
                        'alias' => 'v',
                        'on'    => 'vr.visitor_id = v.ID',
                        'type'  => 'INNER'
                    ]
                ],
                'order_by' => 'vr.visitor_id ASC',
                'limit'    => [
                    $this->batchSize,
                    $this->offset,
                ]
            ])
            ->execute()
            ->getResult();

        if (empty($visitorBatch)) {
            $this->done = $this->total;
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
