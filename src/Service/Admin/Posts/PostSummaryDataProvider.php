<?php

namespace WP_Statistics\Service\Admin\Posts;

use WP_STATISTICS\Menus;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;

/**
 * This class is used to get summary stats about a post (e.g. visitors, views, referrers, etc.).
 */
class PostSummaryDataProvider
{
    private $postId = 0;

    /**
     * Arguments to use in the visitors and views models.
     *
     * @var array
     */
    private $args = [];

    private $fromDate = '';
    private $toDate = '';

    private $visitorsModel;
    private $viewsModel;

    /**
     * Initializes the class.
     *
     * @param   \WP_Post    $post
     *
     * @throws  \Exception
     */
    public function __construct($post)
    {
        if (empty($post) || !$post instanceof \WP_Post) {
            throw new \Exception('Invalid post!');
        }

        $this->postId = $post->ID;

        $this->setFrom(TimeZone::getTimeAgo(7));
        $this->setTo(TimeZone::getTimeAgo());

        $this->visitorsModel = new VisitorsModel();
        $this->viewsModel    = new ViewsModel();
    }

    /**
     * Sets/Resets `$args` array based on the class attributes.
     *
     * @return  void
     */
    private function setArgs()
    {
        $this->args = [
            'post_id' => $this->postId,
            'date'    => [
                'from' => $this->fromDate,
                'to'   => $this->toDate,
            ],
        ];
    }

    /**
     * Sets a new value for the `$fromDate` attribute.
     *
     * @param   string  $from
     * @param   bool    $checkPublishDate   Make sure the input date is after (or equal) the post's publish date.
     *
     * @return  void
     */
    public function setFrom($from, $checkPublishDate = true)
    {
        if (!TimeZone::isValidDate($from)) {
            return;
        }

        if ($checkPublishDate) {
            $publishDate = get_the_date('Y-m-d', $this->postId);
            if ($from < $publishDate) {
                $from = $publishDate;
            }
        }

        $this->fromDate = $from;
        $this->setArgs();
    }

    /**
     * Sets a new value for the `$toDate` attribute.
     *
     * @param   string  $to
     *
     * @return  void
     */
    public function setTo($to)
    {
        if (!TimeZone::isValidDate($to)) {
            return;
        }

        $this->toDate = $to;
        $this->setArgs();
    }

    /**
     * Returns `$fromDate` as a string.
     *
     * @param   string          $format     Returns the date with this format. If left empty, the format in WordPress settings will be used.
     *
     * @return  string|false
     */
    public function getFromString($format = '')
    {
        if (empty($format)) {
            $format = get_option('date_format');
        }

        return date($format, strtotime($this->fromDate));
    }

    /**
     * Returns `$toDate` as a string.
     *
     * @param   string          $format     Returns the date with this format. If left empty, the format in WordPress settings will be used.
     *
     * @return  string|false
     */
    public function getToString($format = '')
    {
        if (empty($format)) {
            $format = get_option('date_format');
        }

        return date($format, strtotime($this->toDate));
    }

    /**
     * Returns the number of visitors for this post.
     *
     * @param   bool    $isTotal    Should return total numbers? Or use `$fromDate` and `$toDate` as date range?
     *
     * @return  int
     */
    public function getVisitors($isTotal = false)
    {
        $args = $isTotal ? ['post_id' => $this->postId] : $this->args;
        return intval($this->visitorsModel->countVisitors($args));
    }

    /**
     * Returns the number of views for this post.
     *
     * @param   bool    $isTotal    Should return total numbers? Or use `$fromDate` and `$toDate` as date range?
     *
     * @return  int
     */
    public function getViews($isTotal = false)
    {
        $args = $isTotal ? ['post_id' => $this->postId] : $this->args;
        return intval($this->viewsModel->countViews($args));
    }

    /**
     * Returns daily views for this post for the past x days.
     *
     * @return  array   Format: `[['views' => {COUNT}, 'date' => '{DATE}'], ['views' => {COUNT}, 'date' => '{DATE}'], ...]`.
     */
    public function getDailyViews()
    {
        return $this->viewsModel->countDailyViews($this->args);
    }

    /**
     * Returns daily visitors for this post for the past x days.
     *
     * @return  array   Format: `[['date' => '{DATE}', 'visitors' => {COUNT}], ['date' => '{DATE}', 'visitors' => {COUNT}], ...]`.
     */
    public function getDailyVisitors()
    {
        return $this->visitorsModel->countDailyVisitors($this->args);
    }

    /**
     * Returns the top referrer website and its hit count for this post.
     *
     * @param   bool    $isTotal    Should return the top referrer of all time? Or use `$fromDate` and `$toDate` as date range?
     *
     * @return  array               Format: `['url' => '{URL}', 'count' => {COUNT}]`.
     */
    public function getTopReferrerAndCount($isTotal = false)
    {
        $args        = $isTotal ? ['post_id' => $this->postId] : $this->args;
        $topReferrer = $this->visitorsModel->getReferrers($args);

        if (empty($topReferrer) && empty($topReferrer[0]->referrer)) {
            return [
                'url'   => '',
                'count' => 0,
            ];
        }

        return [
            'url'   => esc_url($topReferrer[0]->referrer),
            'count' => intval($topReferrer[0]->visitors),
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
