<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * This class is used to get summary stats about a post (e.g. visitors, views, referrers, etc.).
 *
 * @since 15.0.0 Refactored to use AnalyticsQueryHandler instead of legacy models.
 */
class PostSummaryDataProvider
{
    private $postId = 0;

    private $fromDate = '';
    private $toDate = '';

    /**
     * Total period date range (from publish date to today).
     *
     * @var array Format: ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     */
    private $totalDateRange = [];

    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

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

        $this->postId = $postId;

        $this->setFrom(DateTime::get('-7 days'));
        $this->setTo(DateTime::get());

        $this->totalDateRange = [
            'from' => $this->getPublishDate(),
            'to'   => date('Y-m-d'),
        ];

        $this->queryHandler = new AnalyticsQueryHandler(false);
    }

    /**
     * Sets a new value for the `$fromDate` attribute.
     *
     * @param string $from
     * @param bool   $checkPublishDate Make sure the input date is after (or equal) the post's publish date.
     *
     * @return void
     */
    public function setFrom($from, $checkPublishDate = true)
    {
        if (!DateTime::isValidDate($from)) {
            return;
        }

        if ($checkPublishDate) {
            $publishDate = get_the_date('Y-m-d', $this->postId);
            if ($from < $publishDate) {
                $from = $publishDate;
            }
        }

        $this->fromDate = $from;
    }

    /**
     * Sets a new value for the `$toDate` attribute.
     *
     * @param string $to
     *
     * @return void
     */
    public function setTo($to)
    {
        if (!DateTime::isValidDate($to)) {
            return;
        }

        $this->toDate = $to;
    }

    /**
     * Returns `$fromDate` as a string.
     *
     * @param   string          $format         Returns the date with this format. If left empty, the format in WordPress settings will be used.
     * @param   bool            $shortFormat    Make the returned date format shorter.
     *
     * @return  string|false
     */
    public function getFromString($format = '', $shortFormat = false)
    {
        if (empty($format)) {
            $format = get_option('date_format');
        }

        if ($shortFormat) {
            $format = $this->makeDateFormatShorter($format);
        }

        return date($format, strtotime($this->fromDate));
    }

    /**
     * Returns `$toDate` as a string.
     *
     * @param   string          $format         Returns the date with this format. If left empty, the format in WordPress settings will be used.
     * @param   bool            $shortFormat    Make the returned date format shorter.
     *
     * @return  string|false
     */
    public function getToString($format = '', $shortFormat = false)
    {
        if (empty($format)) {
            $format = get_option('date_format');
        }

        if ($shortFormat) {
            $format = $this->makeDateFormatShorter($format);
        }

        return date($format, strtotime($this->toDate));
    }

    /**
     * Removes year from the given date format and make the month shorter.
     *
     * @param   string  $dateFormat
     *
     * @return  string
     */
    private function makeDateFormatShorter($dateFormat)
    {
        // Remove year
        $dateFormat = str_replace(['o', 'X', 'x', 'Y', 'y'], '', $dateFormat);

        // Trim extra charaters
        $dateFormat = trim($dateFormat, ' ,./\\-_');

        // Replace full representation of a month with its short one
        $dateFormat = str_replace('F', 'M', $dateFormat);

        // Trim extra charaters
        $dateFormat = trim($dateFormat, ' ,./\\-_');

        return $dateFormat;
    }

    /**
     * Returns post publish date as a string.
     *
     * @param   string              $format     Returns the date with this format. Default: 'Y-m-d'.
     *
     * @return  string|int|false
     */
    public function getPublishDate($format = 'Y-m-d')
    {
        return get_the_date($format, $this->postId);
    }

    /**
     * Returns the number of visitors for this post.
     *
     * @param bool $isTotal Should return total numbers? Or use `$fromDate` and `$toDate` as date range?
     *
     * @return int
     */
    public function getVisitors($isTotal = false)
    {
        $dateRange = $isTotal ? $this->totalDateRange : ['from' => $this->fromDate, 'to' => $this->toDate];

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => [
                'resource_id' => $this->postId,
                'post_type'   => get_post_type($this->postId),
            ],
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['visitors'] ?? 0);
    }

    /**
     * Returns the number of views for this post.
     *
     * @param bool $isTotal Should return total numbers? Or use `$fromDate` and `$toDate` as date range?
     *
     * @return int
     */
    public function getViews($isTotal = false)
    {
        $dateRange = $isTotal ? $this->totalDateRange : ['from' => $this->fromDate, 'to' => $this->toDate];

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => [
                'resource_id' => $this->postId,
                'post_type'   => get_post_type($this->postId),
            ],
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['views'] ?? 0);
    }

    /**
     * Returns daily views for this post for the past x days.
     *
     * @return array Format: `[['views' => {COUNT}, 'date' => '{DATE}'], ['views' => {COUNT}, 'date' => '{DATE}'], ...]`.
     */
    public function getDailyViews()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['date'],
            'date_from' => $this->fromDate,
            'date_to'   => $this->toDate,
            'filters'   => [
                'resource_id' => $this->postId,
                'post_type'   => get_post_type($this->postId),
            ],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $dailyViews = [];
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $dailyViews[] = [
                    'views' => intval($row['views'] ?? 0),
                    'date'  => $row['date'] ?? '',
                ];
            }
        }

        return $dailyViews;
    }

    /**
     * Returns daily visitors for this post for the past x days.
     *
     * @return array Format: `[['date' => '{DATE}', 'visitors' => {COUNT}], ['date' => '{DATE}', 'visitors' => {COUNT}], ...]`.
     */
    public function getDailyVisitors()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => $this->fromDate,
            'date_to'   => $this->toDate,
            'filters'   => [
                'resource_id' => $this->postId,
                'post_type'   => get_post_type($this->postId),
            ],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $dailyVisitors = [];
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $dailyVisitors[] = [
                    'date'     => $row['date'] ?? '',
                    'visitors' => intval($row['visitors'] ?? 0),
                ];
            }
        }

        return $dailyVisitors;
    }

    /**
     * Returns the top referrer website and its hit count for this post.
     *
     * @param bool $isTotal Should return the top referrer of all time? Or use `$fromDate` and `$toDate` as date range?
     *
     * @return array Format: `['url' => '{URL}', 'count' => {COUNT}]`.
     */
    public function getTopReferrerAndCount($isTotal = false)
    {
        $dateRange = $isTotal ? $this->totalDateRange : ['from' => $this->fromDate, 'to' => $this->toDate];

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => [
                'resource_id' => $this->postId,
                'post_type'   => get_post_type($this->postId),
            ],
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
     * Returns the url to content analytics page for this post.
     *
     * @return  string
     */
    public function getContentAnalyticsUrl()
    {
        return esc_url(Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $this->postId]));
    }
}
