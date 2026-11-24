<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_STATISTICS\Option;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Models\EventsModel;
use WP_STATISTICS\Visitor;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class VisitorInsightsDataProvider
{
    /**
     * Most other visits (same IP or user) read for the single visitor page.
     */
    const OTHER_VISITS_LIMIT = 50;

    protected $args;
    protected $visitorsModel;
    protected $onlineModel;
    protected $viewsModel;

    protected $isTrackLoggedInUsersEnabled;

    protected $chartData;

    public function __construct($args)
    {
        $this->args = $args;

        $this->isTrackLoggedInUsersEnabled = Option::get('visitors_log') ? true : false;

        $this->visitorsModel = new VisitorsModel();
        $this->onlineModel   = new OnlineModel();
        $this->viewsModel    = new ViewsModel();
    }

    public function getOverviewData()
    {
        $overviewChartData = $this->getOverviewChartsData();

        $summary = ChartDataProviderFactory::summaryChart(['include_total' => true])->getData();
        $online  = $this->onlineModel->countOnlines();

        $visitors       = $this->visitorsModel->countVisitors();
        $prevVisitors   = $this->visitorsModel->countVisitors(['date' => DateRange::getPrevPeriod()]);
        $views          = $this->visitorsModel->countHits();
        $prevViews      = $this->visitorsModel->countHits(['date' => DateRange::getPrevPeriod()]);

        $loggedIn           = $this->visitorsModel->countVisitors(['logged_in' => true]);
        $prevLoggedIn       = $this->visitorsModel->countVisitors(['logged_in' => true, 'date' => DateRange::getPrevPeriod()]);
        $loggedInShare      = Helper::calculatePercentage($loggedIn, $visitors);
        $prevLoggedInShare  = Helper::calculatePercentage($prevLoggedIn, $prevVisitors);

        $bounceRate     = $this->visitorsModel->getBounceRate();
        $prevBounceRate = $this->visitorsModel->getBounceRate(['date' => DateRange::getPrevPeriod()]);

        $referrers      = $this->visitorsModel->getReferrers(['decorate' => true, 'per_page' => 5]);
        $topVisitors    = $this->visitorsModel->getVisitorsData(['order_by' => 'hits', 'order' => 'DESC', 'page' => 1, 'per_page' => 5]);
        $entryPages     = $this->visitorsModel->getEntryPages(['per_page' => 5]);

        $glance = [
            'visitors'  => [
                'value'     => $visitors,
                'change'    => Helper::calculatePercentageChange($prevVisitors, $visitors)
            ],
            'views'     => [
                'value'     => $views,
                'change'    => Helper::calculatePercentageChange($prevViews, $views)
            ],
            'bounce_rate' => [
                'value'     => $bounceRate . '%',
                // Percentage metrics report an absolute point difference, not a relative change.
                'change'    => ($prevVisitors == 0) ? null : round($bounceRate - $prevBounceRate, 1)
            ],
            'country'   => $overviewChartData['countries']['labels'][0] ?? '',
            'referrer'  => isset($referrers[0]) ? $referrers[0]->getRawReferrer() : '',
        ];

        if ($this->isTrackLoggedInUsersEnabled) {
            // Only show change if we have previous visitor data to calculate share from
            $loggedInChange = ($prevVisitors == 0) ? null : ($loggedInShare - $prevLoggedInShare);
            $glance['logged_in'] = [
                'value'     => $loggedInShare . '%',
                'change'    => $loggedInChange
            ];
        }

        return [
            'glance'        => $glance,
            'summary'       => $summary,
            'online'        => $online,
            'referrers'     => $referrers,
            'entry_pages'   => $entryPages,
            'map_chart'     => $overviewChartData['map'],
            'visitors'      => $topVisitors,
        ];
    }

    public function getOverviewChartsData()
    {
        if (!empty($this->chartData)) {
            return $this->chartData;
        }

        $platformsChart = ChartDataProviderFactory::platformCharts();
        $countryChart   = ChartDataProviderFactory::countryChart();
        $trafficChart   = ChartDataProviderFactory::trafficChart();
        $mapChart       = ChartDataProviderFactory::mapChart();

        $this->chartData = [
            'devices'   => $platformsChart->getDeviceData(),
            'browsers'  => $platformsChart->getBrowserData(),
            'countries' => $countryChart->getData(),
            'traffic'   => $trafficChart->getData(),
            'map'       => $mapChart->getData()
        ];

        if ($this->isTrackLoggedInUsersEnabled) {
            $this->chartData['logged_in_users'] = ChartDataProviderFactory::loggedInUsers()->getData();
        }

        return $this->chartData;
    }

    public function getViewsChartsData()
    {
        return [
            'traffic_chart_data' => ChartDataProviderFactory::trafficChart($this->args)->getData()
        ];
    }

    public function getLoggedInChartsData()
    {
        return [
            'logged_in_chart_data' => ChartDataProviderFactory::usersTrafficChart($this->args)->getData()
        ];
    }

    public function getVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'user_info' => true,
                'order_by'  => VisitorsModel::LATEST_ORDER_BY,
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors($this->args)
        ];
    }

    public function getViewsData()
    {
        return [
            'data'  => $this->viewsModel->getViewsData(array_merge($this->args, [
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->viewsModel->countViewRecords($this->args)
        ];
    }

    public function getOnlineVisitorsData()
    {
        return [
            'data'  => $this->onlineModel->getOnlineVisitors(array_merge($this->args, [
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page
            ])),
            'total' => $this->onlineModel->countOnlines($this->args)
        ];
    }

    public function getTopVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'user_info' => true,
                'order_by'  => 'hits',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors($this->args)
        ];
    }

    public function getVisitorData()
    {
        $visitor = $this->visitorsModel->getVisitorData($this->args);

        // The visitor may have been purged or never existed
        if (empty($visitor)) {
            return [
                'visitor'  => null,
                'journey'  => [],
                'sessions' => [],
                'visits'   => [],
                'summary'  => VisitorJourney::summarize([])
            ];
        }

        $journey  = $this->visitorsModel->getVisitorJourney($this->args);
        $sessions = [];

        if (!$visitor->isAnonymous()) {
            $args = [
                'ignore_date' => true,
                'exclude_ids' => [$visitor->getId()],
                // Newest visits first (day, then last view), so the 50-row limit keeps the latest ones.
                'order_by'    => VisitorsModel::LATEST_ORDER_BY,
                'order'       => 'DESC',
                'page'        => 1,
                'per_page'    => self::OTHER_VISITS_LIMIT
            ];

            // Link visits by user only while user tracking is on, like the profile card shows it.
            if (!empty($visitor->getVisibleUser())) {
                $args['user_id'] = $visitor->getUserId();
            } else {
                $args['ip']       = $visitor->getIP();
                $args['exact_ip'] = true;
            }

            $sessions = $this->visitorsModel->getVisitorsData($args);
        }

        $visitors = array_merge([$visitor], is_array($sessions) ? $sessions : []);

        return [
            'visitor'        => $visitor,
            'journey'        => $journey,
            'sessions'       => $sessions,
            // Every visit (this one + other visits from the same IP or user), newest first, with pages and events.
            'visits'         => $this->getVisitorVisits($visitors, $visitor->getId()),
            'summary'        => VisitorJourney::summarize($visitors),
            'sessions_limit' => self::OTHER_VISITS_LIMIT,
        ];
    }

    /**
     * Visits with their steps for the single visitor journey.
     *
     * @param \WP_Statistics\Decorators\VisitorDecorator[] $visitors
     * @param int $currentId
     * @return array
     */
    public function getVisitorVisits($visitors, $currentId)
    {
        $ids = array_map(function ($visitor) {
            return (int)$visitor->getId();
        }, $visitors);

        $views  = $this->visitorsModel->getVisitorsJourneys(['visitor_ids' => $ids]);
        $events = $this->getVisitorEvents($ids);
        $pages  = [];

        $resolver = function ($pageId) use (&$pages) {
            if (!isset($pages[$pageId])) {
                $pages[$pageId] = Visitor::get_page_by_id($pageId);
            }

            return $pages[$pageId];
        };

        return VisitorJourney::buildVisits($visitors, $views, $events, $currentId, $resolver);
    }

    /**
     * Link clicks and downloads of the given visits. Events are tracked by Data Plus, so they are only read while
     * it is active. Data Plus (or anything else) can supply them through `wp_statistics_visitor_journey_events`.
     *
     * @param int[] $visitorIds
     * @return array Normalized events ({@see VisitorJourney::normalizeEvent()}).
     */
    public function getVisitorEvents($visitorIds)
    {
        /**
         * Events shown in the single visitor journey.
         *
         * Return an array of rows (visitor_id, date, event_name, event_data) or of already normalized steps
         * (visitor_id, date, event_name, label, target) to replace the default query; null keeps it.
         *
         * @param array|null $events
         * @param int[] $visitorIds
         */
        $events = apply_filters('wp_statistics_visitor_journey_events', null, $visitorIds);

        if ($events === null) {
            $events = [];

            if (!empty($visitorIds) && Helper::isAddOnActive('data-plus')) {
                $events = (new EventsModel())->getVisitorEvents(['visitor_ids' => $visitorIds]);
            }
        }

        return array_values(array_filter(array_map([VisitorJourney::class, 'normalizeEvent'], (array)$events)));
    }

    public function getLoggedInUsersData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'logged_in' => true,
                'order_by'  => VisitorsModel::LATEST_ORDER_BY,
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors(array_merge($this->args, [
                'logged_in' => true,
                'user_role' => Request::get('role', '')
            ]))
        ];
    }
}