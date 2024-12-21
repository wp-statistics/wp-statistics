<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Debugger\AbstractDebuggerProvider;
use WP_STATISTICS\TimeZone;

/**
 * Provider for handling visitor statistics data
 */
class VisitorProvider extends AbstractDebuggerProvider
{
    /**
     * Stores visitor statistics data
     */
    private array $visitors;

    /**
     * Instance of VisitorsModel for data retrieval
     */
    private VisitorsModel $visitorsModel;

    /**
     * Initialize provider
     */
    public function __construct()
    {
        $this->visitorsModel = new VisitorsModel();
    }

    /**
    * Get stored visitor data
    * 
    * @return array Array of visitor statistics data
    */
    public function getVisitorData(): array
    {
        return $this->visitors;
    }

    /**
     * Get latest visitors data with pagination
     */
    public function getLatestVisitors(): array
    {
        return $this->visitorsModel->getVisitorsData([
            'page_info' => true,
            'user_info' => true,
            'order_by' => 'visitor.ID',
            'order' => 'DESC',
            'page' => 1,
            'per_page' => 3,
            'date' => [
                'from' => TimeZone::getTimeAgo(0),
                'to' => TimeZone::getCurrentDate("Y-m-d")
            ]
        ]);
    }
}