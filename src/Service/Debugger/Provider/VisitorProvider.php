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
     * Stores visitor statistics data.
     *
     * @var array|null
     */
    private $visitors;

    /**
     * Instance of VisitorsModel for data retrieval.
     *
     * @var VisitorsModel
     */
    private $visitorsModel;

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
    public function getVisitor()
    {
        return $this->visitors ?? [];
    }

    /**
     * Get latest visitors data with pagination
     *
     * @return array Array of latest visitor data
     */
    public function getLatestVisitors()
    {
        $this->visitors = $this->visitorsModel->getVisitorsData([
            'page_info' => true,
            'user_info' => true,
            'order_by' => 'visitor.ID',
            'order' => 'DESC',
            'page' => 1,
            'per_page' => 5,
        ]);

        return $this->visitors;
    }
}
