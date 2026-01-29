<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\UrlBuilder;

/**
 * This class is used to get summary stats about a post (e.g. visitors, views, referrers, etc.).
 *
 * @since 15.0.0 Refactored to use AnalyticsQueryHandler instead of legacy models.
 * @since 15.x.x Refactored to use getAllData() with batched queries and immutable date ranges.
 */
class PostSummaryDataProvider
{
    private $postId = 0;

    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

    /**
     * Post type cache.
     *
     * @var string
     */
    private $postType;

    /**
     * Common filters for all queries.
     *
     * @var array
     */
    private $filters;

    /**
     * Initializes the class.
     *
     * @param int $postId
     *
     * @throws \Exception
     */
    public function __construct($postId)
    {
        if (empty($postId)) {
            throw new \Exception('Invalid post!');
        }

        $this->postId       = $postId;
        $this->postType     = get_post_type($postId);
        $this->queryHandler = new AnalyticsQueryHandler(false);
        $this->filters      = [
            'resource_id' => $this->postId,
            'post_type'   => $this->postType,
        ];
    }

    /**
     * Returns all summary data for this post in batched queries (5 queries instead of 8).
     *
     * @param array  $periodRange Date range for the period summary. Format: ['from' => 'Y-m-d', 'to' => 'Y-m-d'].
     * @param array  $totalRange  Date range for total/lifetime stats. Format: ['from' => 'Y-m-d', 'to' => 'Y-m-d'].
     * @param array  $chartRange  Date range for the chart data. Format: ['from' => 'Y-m-d', 'to' => 'Y-m-d'].
     * @param string $chartMetric Either 'views' or 'visitors'.
     *
     * @return array
     */
    public function getAllData(array $periodRange, array $totalRange, array $chartRange, $chartMetric = 'visitors')
    {
        // Query A: Total visitors + views (batched)
        $totals = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $totalRange['from'],
            'date_to'   => $totalRange['to'],
            'filters'   => $this->filters,
            'format'    => 'flat',
        ]);

        // Query B: Period visitors + views (batched)
        $period = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'date_from' => $periodRange['from'],
            'date_to'   => $periodRange['to'],
            'filters'   => $this->filters,
            'format'    => 'flat',
        ]);

        // Query C: Daily chart data (single source based on metric)
        $source = ($chartMetric === 'views') ? 'views' : 'visitors';
        $daily  = $this->queryHandler->handle([
            'sources'   => [$source],
            'group_by'  => ['date'],
            'date_from' => $chartRange['from'],
            'date_to'   => $chartRange['to'],
            'filters'   => $this->filters,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        // Query D: Top referrer (total)
        $topRefTotal = $this->queryTopReferrer($totalRange);

        // Query E: Top referrer (period)
        $topRefPeriod = $this->queryTopReferrer($periodRange);

        return [
            'totalVisitors'        => intval($totals['data']['totals']['visitors'] ?? 0),
            'totalViews'           => intval($totals['data']['totals']['views'] ?? 0),
            'periodVisitors'       => intval($period['data']['totals']['visitors'] ?? 0),
            'periodViews'          => intval($period['data']['totals']['views'] ?? 0),
            'dailyHits'            => $this->parseDailyRows($daily, $source),
            'topReferrerTotal'     => $topRefTotal,
            'topReferrerPeriod'    => $topRefPeriod,
            'contentAnalyticsUrl'  => $this->getContentAnalyticsUrl(),
        ];
    }

    /**
     * Queries the top referrer for a given date range.
     *
     * @param array $dateRange Format: ['from' => 'Y-m-d', 'to' => 'Y-m-d'].
     *
     * @return array Format: ['url' => '{URL}', 'count' => {COUNT}].
     */
    private function queryTopReferrer(array $dateRange)
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => $this->filters,
            'format'    => 'table',
            'per_page'  => 1,
        ]);

        if (empty($result['data']['rows']) || empty($result['data']['rows'][0]['referrer'])) {
            return [
                'url'   => '',
                'count' => 0,
            ];
        }

        $topReferrer = $result['data']['rows'][0];

        return [
            'url'   => esc_url($topReferrer['referrer']),
            'count' => intval($topReferrer['visitors'] ?? 0),
        ];
    }

    /**
     * Parses daily rows from the query result.
     *
     * @param array  $result Query result.
     * @param string $source The source key ('views' or 'visitors').
     *
     * @return array Format: [['date' => '{DATE}', 'hits' => {COUNT}], ...].
     */
    private function parseDailyRows(array $result, $source)
    {
        $rows = [];

        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $rows[] = [
                    'date' => $row['date'] ?? '',
                    'hits' => intval($row[$source] ?? 0),
                ];
            }
        }

        return $rows;
    }

    /**
     * Returns post publish date as a string.
     *
     * @param string $format Returns the date with this format. Default: 'Y-m-d'.
     *
     * @return string|int|false
     */
    public function getPublishDate($format = 'Y-m-d')
    {
        return get_the_date($format, $this->postId);
    }

    /**
     * Returns the url to content analytics page for this post.
     *
     * @return string
     */
    public function getContentAnalyticsUrl()
    {
        return esc_url(UrlBuilder::pageAnalytics($this->postId));
    }

    /**
     * @deprecated Use getAllData() instead.
     */
    public function getVisitors($isTotal = false)
    {
        $dateRange = $isTotal
            ? ['from' => $this->getPublishDate(), 'to' => date('Y-m-d')]
            : ['from' => DateTime::get('-7 days'), 'to' => DateTime::get()];

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => $this->filters,
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['visitors'] ?? 0);
    }

    /**
     * @deprecated Use getAllData() instead.
     */
    public function getViews($isTotal = false)
    {
        $dateRange = $isTotal
            ? ['from' => $this->getPublishDate(), 'to' => date('Y-m-d')]
            : ['from' => DateTime::get('-7 days'), 'to' => DateTime::get()];

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => $this->filters,
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['views'] ?? 0);
    }

    /**
     * @deprecated Use getAllData() instead.
     */
    public function getTopReferrerAndCount($isTotal = false)
    {
        $dateRange = $isTotal
            ? ['from' => $this->getPublishDate(), 'to' => date('Y-m-d')]
            : ['from' => DateTime::get('-7 days'), 'to' => DateTime::get()];

        return $this->queryTopReferrer($dateRange);
    }
}
