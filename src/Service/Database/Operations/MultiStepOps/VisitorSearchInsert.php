<?php

namespace WP_Statistics\Service\Database\Operations\MultiStepOps;

use Exception;
use RuntimeException;
use WP_Statistics\Async\BackgroundProcessMonitor;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\DatabaseFactory;
use WP_Statistics\Service\Database\Operations\AbstractTableOperation;

/**
 * Handles inserting first and last page data for visitors.
 *
 * This operation processes the visitor data immediately.
 */
class VisitorSearchInsert extends AbstractTableOperation
{
    /**
     * Sets up the visitor ID batch for processing.
     *
     * @param array $visitorIds The list of visitor IDs.
     * @return $this
     */
    public function setVisitorIds(array $visitorIds)
    {
        $this->args['visitor_ids'] = $visitorIds;
        return $this;
    }

    /**
     * Executes the process for each visitor batch.
     *
     * @return void
     * @throws RuntimeException
     */
    public function execute()
    {
        try {
            $this->ensureConnection();

            $this->setRunTimeError();

            if (empty($this->args['visitor_ids'])) {
                throw new RuntimeException("Batch insert process requires visitor IDs.");
            }

            $visitorIds = $this->args['visitor_ids'];

            // Step 1: Fetch min & max ID per visitor
            $visitorData = DatabaseFactory::table('select')
                ->setName('visitor_relationships')
                ->setArgs([
                    'columns'  => ['visitor_id', 'MIN(ID) as min_id', 'MAX(ID) as max_id'],
                    'where_in' => ['visitor_id' => $visitorIds],
                    'group_by' => 'visitor_id',
                ])
                ->execute()
                ->getResult();

            if (!$visitorData) {
                return; // No data found, nothing to process
            }
            
            BackgroundProcessMonitor::setCompletedRecords('data_migration_process', count($visitorData));

            foreach ($visitorData as $visitor) {
                try {
                    $visitorId = $visitor['visitor_id'];
                    $minId     = $visitor['min_id'];
                    $maxId     = $visitor['max_id'];

                    // Step 2: Fetch first page visit
                    $firstPage = DatabaseFactory::table('select')
                        ->setName('visitor_relationships')
                        ->setArgs([
                            'columns' => ['page_id', 'date'],
                            'where' => ['ID' => $minId],
                        ])
                        ->execute()
                        ->getResult();

                    // Step 3: Fetch last page visit
                    $lastPage = DatabaseFactory::table('select')
                        ->setName('visitor_relationships')
                        ->setArgs([
                            'columns' => ['page_id', 'date'],
                            'where' => ['ID' => $maxId],
                        ])
                        ->execute()
                        ->getResult();

                    // Step 4: Validate and insert data
                    if (!empty($firstPage) && !empty($lastPage)) {
                        DatabaseFactory::table('insert')
                            ->setName('visitor')
                            ->setArgs([
                                'conditions' => [
                                    'ID'  => $visitorId,
                                ],
                                'mapping' => [
                                    'first_page' => $firstPage[0]['page_id'],
                                    'first_view' => $firstPage[0]['date'],
                                    'last_page'  => $lastPage[0]['page_id'],
                                    'last_view'  => $lastPage[0]['date'],
                                ],
                            ])
                            ->execute();
                    }
                } catch (Exception $e) {
                    Option::saveOptionGroup('migration_status_detail', [
                        'status' => 'failed',
                        'message' => "Batch aborted due to visitor processing failure: " . $e->getMessage()
                    ], 'db');

                    \WP_Statistics::log("Batch aborted due to visitor processing failure: " . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            Option::saveOptionGroup('migration_status_detail', [
                'status' => 'failed',
                'message' => "Visitor first and last log Insert failed: " . $e->getMessage()
            ], 'db');

            \WP_Statistics::log("Visitor first and last log Insert failed: " . $e->getMessage());
        }
    }
}
