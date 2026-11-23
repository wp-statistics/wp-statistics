<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Url;
use WP_Statistics\Utils\Query;
use WP_Statistics\Decorators\VisitorDecorator;

class MetaboxDataProvider
{
    protected $taxonomyModel;
    protected $authorsModel;
    protected $visitorsModel;
    protected $viewsModel;
    protected $onlineModel;
    protected $postsModel;

    public function __construct()
    {
        $this->visitorsModel = new VisitorsModel();
        $this->authorsModel  = new AuthorsModel();
        $this->viewsModel    = new ViewsModel();
        $this->onlineModel   = new OnlineModel();
        $this->postsModel    = new PostsModel();
        $this->taxonomyModel = new TaxonomyModel();
    }

    public function getTrafficSummaryData($args = [])
    {
        $data = [
            'online'  => $this->onlineModel->countOnlines($args),
            'summary' => ChartDataProviderFactory::summaryChart(['include_total' => true])->getData()
        ];

        return $data;
    }

    /**
     * Get traffic overview data
     *
     * @param array $args Filter arguments for the queries
     *
     * @return array
     */
    public function getTrafficOverviewData($args = [])
    {
        $data = [
            'online'  => $this->onlineModel->countOnlines($args),
            'summary' => ChartDataProviderFactory::summaryChart(['include_total' => true])->getData()
        ];

        return $data;
    }

    public function getReferrersData($args = [])
    {
        $args = array_merge($args, [
            'decorate' => true,
            'per_page' => 5,
            'page'     => 1
        ]);

        return $this->visitorsModel->getReferrers($args);
    }

    public function getTopVisitorsData($args = [], $limit = 10)
    {
        $visitors = $this->visitorsModel->getVisitorsData(array_merge($args, [
            'user_info' => true,
            'order_by'  => 'hits',
            'order'     => 'DESC',
            'per_page'  => max(1, intval($limit)),
            'page'      => 1
        ]));

        return $visitors;
    }

    /**
     * Most active visitors with a logged-in user counted once (WP Dashboard widget).
     *
     * A visitor row covers one visitor on one day, so the same user fills several rows
     * of the plain list. Here rows of the same user are summed into one: views are the
     * sum of hits, `visits` the number of rows, and the visitor shown is the user's
     * newest row (its last view, last page and device). Guests stay one row each, since
     * hashed IPs change daily and an IP alone is not one person.
     *
     * @param array $args  Filters, `date` => ['from', 'to'].
     * @param int   $limit Groups to return.
     *
     * @return VisitorDecorator[] Each with `hits` = total views and `visits` = rows merged.
     */
    public function getTopVisitorsGroupedData($args = [], $limit = 5)
    {
        $limit = max(1, intval($limit));
        $group = "CASE WHEN visitor.user_id > 0 THEN CONCAT('u', visitor.user_id) ELSE CONCAT('v', visitor.ID) END";

        $groups = Query::select([
            "{$group} AS visitor_group",
            'SUM(visitor.hits) AS total_hits',
            'COUNT(visitor.ID) AS visits',
            'MAX(visitor.ID) AS latest_id',
        ])
            ->from('visitor')
            ->whereDate('visitor.last_counter', $args['date'] ?? '')
            ->groupBy('visitor_group')
            ->orderBy(['total_hits', 'latest_id'])
            ->perPage(1, $limit)
            ->getAll();

        if (empty($groups)) {
            return [];
        }

        $ids  = array_map('intval', wp_list_pluck($groups, 'latest_id'));
        $rows = Query::select('*')
            ->from('visitor')
            ->where('visitor.ID', 'IN', $ids)
            ->getAll();

        $rowsById = [];
        foreach ((array) $rows as $row) {
            $rowsById[intval($row->ID)] = $row;
        }

        $visitors = [];
        foreach ($groups as $group) {
            $row = $rowsById[intval($group->latest_id)] ?? null;

            if (!$row) {
                continue;
            }

            $row->hits   = intval($group->total_hits);
            $row->visits = intval($group->visits);
            $visitors[]  = new VisitorDecorator($row);
        }

        return $visitors;
    }

    public function getLatestVisitorsData($args = [], $limit = 10)
    {
        $args = array_merge($args, [
            'user_info' => true,
            'order_by'  => VisitorsModel::LATEST_ORDER_BY,
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => max(1, intval($limit))
        ]);

        // All-time list (Overview and WP Dashboard widgets): only sort the last days that hold the newest rows
        if (!empty($args['ignore_date']) && empty($args['date'])) {
            $args['since'] = $this->visitorsModel->getLatestVisitorsSince($args['per_page']);
        }

        return $this->visitorsModel->getVisitorsData($args);
    }

    public function getTopPages($args = [])
    {
        return $this->viewsModel->getResourcesViews($args);
    }

    /**
     * Top pages plus the total views of the range, for share bars (WP Dashboard widget).
     *
     * @param array $args  Filters, `date` => ['from', 'to'].
     * @param int   $limit Rows to fetch.
     *
     * @return array{rows: array, total: int}
     */
    public function getTopPagesShare($args = [], $limit = 5)
    {
        $rows = $this->getTopPages(array_merge($args, ['per_page' => max(1, intval($limit)), 'page' => 1]));

        if (empty($rows)) {
            return ['rows' => [], 'total' => 0];
        }

        $total = $this->viewsModel->countViewsFromPagesOnly(['date' => $args['date'] ?? '']);

        return ['rows' => $rows, 'total' => max($total, array_sum(wp_list_pluck($rows, 'views')))];
    }

    /**
     * Top referrers plus the referrals of the range, for share bars (WP Dashboard widget).
     *
     * @param array $args  Filters, `date` => ['from', 'to'].
     * @param int   $limit Rows to fetch.
     *
     * @return array{rows: array, total: int}
     */
    public function getReferrersShare($args = [], $limit = 5)
    {
        // Same grouping as the Referrers report: one row per domain and source channel.
        $rows = $this->visitorsModel->getReferrers(array_merge($args, [
            'decorate' => true,
            'group_by' => ['visitor.referred', 'visitor.source_channel'],
            'per_page' => max(1, intval($limit)),
            'page'     => 1
        ]));

        if (empty($rows)) {
            return ['rows' => [], 'total' => 0];
        }

        $total = intval($this->visitorsModel->countReferrers(['date' => $args['date'] ?? '', 'sum_visitors' => true]));
        $shown = 0;
        foreach ($rows as $row) {
            $shown += intval($row->getTotalReferrals(true));
        }

        return ['rows' => $rows, 'total' => max($total, $shown)];
    }

    /**
     * Top pages with visitors and bounce rate added to each row (Overview "Top Pages" card).
     * getTopPages() stays views-only, since the Widgets add-on calls it on the front end.
     *
     * @param array $args
     * @return array
     */
    public function getTopPagesWithEngagement($args = [])
    {
        $rows = $this->getTopPages($args);

        return $this->visitorsModel->attachPagesEngagement($rows, ['date' => $args['date'] ?? '']);
    }

    public function getOnlineVisitorsData($args = [], $limit = 10)
    {
        return [
            'visitors' => $this->onlineModel->getOnlineVisitors(array_merge($args, [
                'order_by' => 'last_view',
                'per_page' => max(1, intval($limit))
            ])),
            'total'    => $this->onlineModel->countOnlines($args)
        ];
    }

    /**
     * How many visitors are online now (the badge on the Visitors card's "Online now" switch).
     *
     * @param array $args
     *
     * @return int
     */
    public function getOnlineVisitorsCount($args = [])
    {
        return intval($this->onlineModel->countOnlines($args));
    }

    public function getPostSummaryData($args = [])
    {
        $postId = Request::get('post', '', 'number');

        if (empty($postId) && Request::has('current_page')) {
            $postId = Request::get('current_page', [], 'array')['ID'] ?? 0;
        }

        return PostsManager::getPostStatisticsSummary($postId);
    }

    public function getSinglePostData($args = [])
    {
        $currentPage = Request::get('current_page', [], 'array');
        $postId      = $currentPage['ID'] ?? 0;

        $args = [
            'resource_id'   => $postId,
            'resource_type' => get_post_type($postId) ?? '',
            'ignore_date'   => true,
            'user_info'     => true,
            'page'          => 1,
            'per_page'      => 15,
        ];

        return $this->visitorsModel->getVisitorsData($args);
    }

    public function getWeeklyPerformanceData($args = [])
    {
        $currentPeriod = DateRange::get('7days', true);
        $prevPeriod    = DateRange::getPrevPeriod('7days', true);

        $data = [
            'visitors'  => [
                'current_period' => $this->visitorsModel->countVisitors(['date' => $currentPeriod]),
                'prev_period'    => $this->visitorsModel->countVisitors(['date' => $prevPeriod])
            ],
            'visits'    => [
                'current_period' => $this->visitorsModel->countHits(['date' => $currentPeriod]),
                'prev_period'    => $this->visitorsModel->countHits(['date' => $prevPeriod])
            ],
            'posts'     => [
                'current_period' => $this->postsModel->countPosts(['date' => $currentPeriod]),
                'prev_period'    => $this->postsModel->countPosts(['date' => $prevPeriod])
            ],
            'referrals' => [
                'current_period' => $this->visitorsModel->countReferrers(['date' => $currentPeriod]),
                'prev_period'    => $this->visitorsModel->countReferrers(['date' => $prevPeriod])
            ]
        ];

        foreach ($data as $key => $value) {
            $percentageChange = Helper::calculatePercentageChange($value['prev_period'], $value['current_period']);
            $data[$key]['diff_percentage'] = $percentageChange;

            // Only set diff_type if we have valid percentage data
            if ($percentageChange === null) {
                $data[$key]['diff_type'] = null;
            } elseif ($percentageChange > 0) {
                $data[$key]['diff_type'] = 'plus';
            } elseif ($percentageChange < 0) {
                $data[$key]['diff_type'] = 'minus';
            } else {
                $data[$key]['diff_type'] = 'equal';
            }

            if ($data[$key]['diff_percentage'] !== null) {
                $data[$key]['diff_percentage'] = abs($data[$key]['diff_percentage']);
            }
        }

        $topReferrer = $this->visitorsModel->getReferrers(['per_page' => 1, 'decorate' => true, 'date' => $currentPeriod]);
        $topAuthor   = $this->authorsModel->getTopViewingAuthors(['date' => $currentPeriod, 'per_page' => 1]);
        $topCategory = $this->taxonomyModel->getTermsData(['date' => $currentPeriod, 'per_page' => 5, 'taxonomy' => Helper::get_list_taxonomy()]);
        $topContent  = $this->postsModel->getPostsViewsData(['date' => $currentPeriod, 'per_page' => 1]);

        $data['top_author']   = $topAuthor[0] ?? '';
        $data['top_referrer'] = $topReferrer[0] ?? '';
        $data['top_category'] = $topCategory[0] ?? '';
        $data['top_content']  = $topContent[0] ?? '';

        return $data;
    }

    public function getSourceCategoriesData($args = [])
    {
        return ChartDataProviderFactory::topSourceCategories($args)->getData();
    }

    public function getTrafficChartData($args = [])
    {
        return ChartDataProviderFactory::trafficChart($args)->getData();
    }

    public function getSearchEnginesChartData($args = [])
    {
        return ChartDataProviderFactory::searchEngineChart($args)->getData();
    }

    public function getBrowsersChartData($args = [])
    {
        return ChartDataProviderFactory::browserChart($args)->getData();
    }

    public function getDeviceChartData($args = [])
    {
        return ChartDataProviderFactory::deviceChart($args)->getData();
    }

    public function getOsChartData($args = [])
    {
        return ChartDataProviderFactory::osChart($args)->getData();
    }

    public function getModelChartData($args = [])
    {
        return ChartDataProviderFactory::modelChart($args)->getData();
    }

    public function getMapChartData($args = [])
    {
        return ChartDataProviderFactory::mapChart($args)->getData();
    }
}