<?php

namespace WP_Statistics\Service\Admin\Referrals;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Request;

class ReferralsDataProvider
{
    protected $args;
    private $visitorsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    public function getReferredVisitors()
    {
        return [
            'visitors' => $this->visitorsModel->getReferredVisitors($this->args),
            'total'    => $this->visitorsModel->countReferredVisitors($this->args)
        ];
    }

    public function getReferrers()
    {
        return [
            'referrers' => $this->visitorsModel->getReferrers($this->args),
            'total'     => $this->visitorsModel->countReferrers($this->args)
        ];
    }

    public function getSearchEngineReferrals()
    {
        return [
            'referrers' => $this->visitorsModel->getReferrers(array_merge($this->args, ['source_channel' => Request::get('source_channel', 'search')])),
        ];
    }

    public function getChartsData()
    {
        $args = [
            'source_channel'    => Request::get('source_channel', 'search'),
            'group_by'          => ['visitor.referred', 'visitor.last_counter']
        ];

        $searchEngineChart = ChartDataProviderFactory::searchEngineChart(array_merge($this->args, $args));

        return [
            'search_engine_chart_data' => $searchEngineChart->getData()
        ];
    }
}