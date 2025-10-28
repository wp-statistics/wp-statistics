<?php

namespace WP_Statistics\CLI;

use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Models\OnlineModel;

class CliDataProvider
{
    /**
     * Visitor model instance.
     *
     * @var VisitorsModel
     */
    protected $visitorsModel;

    /**
     * Online model instance.
     *
     * @var OnlineModel
     */
    protected $onlineModel;

    /**
     * CliDataProvider constructor.
     *
     * Initializes model instances for visitors and online data.
     */
    public function __construct()
    {
        $this->visitorsModel = new VisitorsModel();
        $this->onlineModel   = new OnlineModel();
    }

    /**
     * Get summary data for visitors and online users.
     *
     * @param array $args
     *
     * @return array
     */
    public function getSummaryData($args = [])
    {
        $data = $this->visitorsModel->getVisitorsHitsSummary(array_merge($args, [
            'ignore_post_type' => true,
            'include_total'    => true,
            'exclude'          => ['last_week', 'last_month', '7days', '30days', '90days', '6months']
        ]));

        return [
            'visitors' => array_values(wp_list_pluck($data, 'visitors')),
            'hits'     => array_values(wp_list_pluck($data, 'hits')),
            'labels'   => array_values(wp_list_pluck($data, 'label')),
            'online'   => $this->onlineModel->countOnlines(),
        ];
    }

    /**
     * Get detailed data about currently online visitors.
     *
     * @param array $args
     *
     * @return array
     */
    public function getOnlineData($args = [])
    {
        return $this->onlineModel->getOnlineVisitorsData(array_merge($args, ['page' => 1]));
    }

    /**
     * Get detailed visitor data.
     *
     * @param array $args
     *
     * @return array
     */
    public function getVisitorsData($args = [])
    {
        return $this->visitorsModel->getVisitorsData(array_merge($args, ['ignore_date' => true, 'page' => 1]));
    }
}