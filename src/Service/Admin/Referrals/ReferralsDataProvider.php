<?php

namespace WP_Statistics\Service\Admin\Referrals;
use WP_Statistics\Decorators\ReferralDecorator;
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
            'referrers' => $this->visitorsModel->getReferrers(array_merge($this->args, [
                'decorate' => true,
                'group_by' => ['visitor.referred', 'visitor.source_channel']
            ])),
            'total'     => $this->visitorsModel->countReferrers($this->args)
        ];
    }

    public function getSourceCategories()
    {
        $sourceCategories = $this->visitorsModel->getReferrers(array_merge($this->args, [
            'group_by' => ['visitor.source_channel'],
            'decorate' => true,
            'not_null' => false
        ]));

        $total = 0;
        foreach ($sourceCategories as $sourceCategory) {
            $total += $sourceCategory->getTotalReferrals(true);
        }

        return [
            'categories' => $sourceCategories,
            'total'      => $total
        ];
    }

    public function getSearchEngineReferrals()
    {
        return [
            'referrers' => $this->visitorsModel->getReferrers(array_merge($this->args, [
                'source_channel'    => Request::get('source_channel', ['search', 'paid_search']),
                'decorate'          => true,
                'group_by'          => ['visitor.referred', 'visitor.source_channel']
            ])),
            'total'     => $this->visitorsModel->countReferrers(array_merge($this->args, ['source_channel' => Request::get('source_channel', ['search', 'paid_search'])]))
        ];
    }

    public function getSocialMediaReferrals()
    {
        return [
            'referrers' => $this->visitorsModel->getReferrers(array_merge($this->args, [
                'source_channel'    => Request::get('source_channel', ['social', 'paid_social']),
                'decorate'          => true,
                'group_by'          => ['visitor.referred', 'visitor.source_channel']
            ])),
            'total'     => $this->visitorsModel->countReferrers(array_merge($this->args, ['source_channel' => Request::get('source_channel', ['social', 'paid_social'])]))
        ];
    }

    public function getSearchEnginesChartsData()
    {
        $args = [
            'source_channel' => Request::get('source_channel', ['search', 'paid_search'])
        ];

        $searchEngineChart = ChartDataProviderFactory::searchEngineChart(array_merge($this->args, $args));

        return [
            'search_engine_chart_data' => $searchEngineChart->getData()
        ];
    }

    public function getSocialMediaChartsData()
    {
        $args = [
            'source_channel' => Request::get('source_channel', ['social', 'paid_social'])
        ];

        $socialMediaChart = ChartDataProviderFactory::socialMediaChart(array_merge($this->args, $args));

        return [
            'social_media_chart_data' => $socialMediaChart->getData()
        ];
    }

    public function getSourceCategoryChartsData()
    {
        $searchEngineChart = ChartDataProviderFactory::sourceCategoryChart($this->args);

        return [
            'source_category_chart_data' => $searchEngineChart->getData()
        ];
    }
}