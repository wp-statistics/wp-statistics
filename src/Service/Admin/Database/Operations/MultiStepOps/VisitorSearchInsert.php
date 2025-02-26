<?php

namespace WP_Statistics\Service\Admin\Database\Operations\MultiStepOps;

use Exception;
use RuntimeException;
use WP_Statistics\Service\Admin\Database\DatabaseFactory;
use WP_Statistics\Service\Admin\Database\Operations\AbstractTableOperation;

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
                } catch(Exception $e) {
                    throw new RuntimeException("Batch aborted due to visitor processing failure.");
                }
            }
        } catch(Exception $e) {
            throw new RuntimeException("Visitor first and last log Insert failed: " . $e->getMessage());
        }
        
    }
}
