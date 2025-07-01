<?php

namespace WP_Statistics\Service\Admin\Referrals;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Visitor;

class ReferralsDataProvider
{
    protected $args;
    private $visitorsModel;
    private $overviewChartData;


    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    public function getReferralsOverview()
    {
        // Use cached chart data if available, to prevent additional queries
        $chartData = $this->getReferralsOverviewChartData();

        $visitors       = $this->visitorsModel->countReferredVisitors();
        $prevVisitors   = $this->visitorsModel->countReferredVisitors(['date' => DateRange::getPrevPeriod()]);

        $referrer = $this->visitorsModel->getReferrers(['per_page' => 1]);
        $referrer = $referrer[0]->referred ?? '';

        $browser = $chartData['browser_chart_data']['labels'][0] ?? '';
        $country = $chartData['countries_chart_data']['labels'][0] ?? '';

        $searchEngine   = isset($chartData['search_engine_chart_data']['data']['datasets'][0]['label']) && $chartData['search_engine_chart_data']['data']['datasets'][0]['slug'] != 'total'
            ? $chartData['search_engine_chart_data']['data']['datasets'][0]['label']
            : '';

        $socialMedia    = isset($chartData['social_media_chart_data']['data']['datasets'][0]['label']) && $chartData['social_media_chart_data']['data']['datasets'][0]['slug'] != 'total'
            ? $chartData['social_media_chart_data']['data']['datasets'][0]['label']
            : '';

        return [
            'summary'      => [
                'visitors'      => [
                    'value'     => $visitors,
                    'change'    => Helper::calculatePercentageChange($prevVisitors, $visitors),
                ],
                'referrer'      => $referrer,
                'browser'       => $browser,
                'country'       => $country,
                'search_engine' => $searchEngine,
                'social_media'  => $socialMedia
            ],
            'visitors'      => $this->visitorsModel->getReferredVisitors(array_merge($this->args, ['per_page' => 5, 'page' => 1])),
            'referrers'     => $this->visitorsModel->getReferrers(array_merge($this->args, ['decorate' => true, 'group_by' => ['visitor.referred'], 'per_page' => 5, 'page' => 1])),
        ];
    }

    public function getReferralsOverviewChartData()
    {
        if (!empty($this->overviewChartData)) return $this->overviewChartData;

        $args = array_merge($this->args, ['referred_visitors' => true]);

        $countryData        = ChartDataProviderFactory::countryChart($args)->getData();
        $browserData        = ChartDataProviderFactory::browserChart($args)->getData();
        $deviceData         = ChartDataProviderFactory::deviceChart($args)->getData();
        $trafficData        = ChartDataProviderFactory::trafficChart($args)->getData();
        $socialMediaData    = ChartDataProviderFactory::socialMediaChart(array_merge($this->args, ['source_channel' => Request::get('source_channel', ['social', 'paid_social'])]))->getData();
        $searchEngineData   = ChartDataProviderFactory::searchEngineChart(array_merge($this->args, ['source_channel' => Request::get('source_channel', ['search', 'paid_search'])]))->getData();

        $this->overviewChartData = [
            'countries_chart_data'      => $countryData,
            'browser_chart_data'        => $browserData,
            'device_chart_data'         => $deviceData,
            'traffic_chart_data'        => $trafficData,
            'social_media_chart_data'   => $socialMediaData,
            'search_engine_chart_data'  => $searchEngineData
        ];

        return $this->overviewChartData;
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