<?php

namespace WP_Statistics\Service\Admin\WebsitePerformance;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;

/**
 * This class is used to get data needed for "Your performance at a glance" section (mostly used in e-mail reports).
 */
class WebsitePerformanceDataProvider
{
    /**
     * Current period dates.
     *
     * @var array Format: `['from' => {Y-m-d}, 'to' => {Y-m-d}]`.
     */
    private $currentPeriod;

    /**
     * Previous period dates.
     *
     * @var array Format: `['from' => {Y-m-d}, 'to' => {Y-m-d}]`.
     */
    private $previousPeriod;

    /**
     * Should calculate percentage change between current period and previous period stats?
     *
     * @var bool
     */
    private $calculatePercentageChanges = true;

    /**
     * Arguments to use in the visitors and views models when fetching current period's stats.
     *
     * @var array
     */
    private $argsCurrentPeriod = [];

    /**
     * Arguments to use in the visitors and views models when fetching previous period's stats.
     *
     * @var array
     */
    private $argsPreviousPeriod = [];

    /**
     * @var VisitorsModel
     */
    private $visitorsModel;

    /**
     * @var ViewsModel
     */
    private $viewsModel;

    /**
     * @var PostsModel
     */
    private $postsModel;

    /**
     * @var AuthorsModel
     */
    private $authorsModel;

    /**
     * @var TaxonomyModel
     */
    private $taxonomiesModel;

    // Cached attributes (to prevent duplicate queries)
    private $currentPeriodVisitors     = null;
    private $previousPeriodVisitors    = null;
    private $currentPeriodViews        = null;
    private $previousPeriodViews       = null;
    private $currentPeriodReferrals    = null;
    private $previousPeriodReferrals   = null;
    private $currentPeriodContents     = null;
    private $previousPeriodContents    = null;
    private $percentageChangeVisitors  = null;
    private $percentageChangeViews     = null;
    private $percentageChangeReferrals = null;
    private $percentageChangeContents  = null;
    private $topAuthor                 = null;
    private $topPost                   = null;
    private $topReferral               = null;
    private $topCategory               = null;

    /**
     * @param string $fromDate Start date of the report in `Y-m-d` format.
     * @param string $toDate End date of the report in `Y-m-d` format. Default: Yesterday.
     */
    public function __construct($fromDate, $toDate = '')
    {
        $this->setArgs($fromDate, $toDate);

        $this->visitorsModel = new VisitorsModel();
        $this->viewsModel    = new ViewsModel();
    }

    /**
     * Sets arguments for current period and previous period.
     *
     * @param string $fromDate Start date of the report in `Y-m-d` format.
     * @param string $toDate End date of the report in `Y-m-d` format. Default: Yesterday.
     *
     * @return void
     */
    public function setArgs($fromDate, $toDate = '')
    {
        $this->setPeriods($fromDate, $toDate);
        $this->setArguments();
        $this->resetCachedData();
    }

    /**
     * Sets current and previous period dates.
     *
     * @param string $fromDate Start date of the report in `Y-m-d` format.
     * @param string $toDate End date of the report in `Y-m-d` format. Default: Yesterday.
     *
     * @return void
     */
    private function setPeriods($fromDate, $toDate = '')
    {
        if (!TimeZone::isValidDate($fromDate)) {
            $fromDate = TimeZone::getTimeAgo(7);
            $toDate   = TimeZone::getTimeAgo();
        } else if (!TimeZone::isValidDate($toDate)) {
            $toDate = TimeZone::getTimeAgo();
        }

        if ($fromDate == TimeZone::getTimeAgo()) {
            // Current period: Yesterday - Previous period: The day before yesterday
            $this->setCurrentAndPreviousPeriods(TimeZone::getTimeAgo(), TimeZone::getTimeAgo(), TimeZone::getTimeAgo(2), TimeZone::getTimeAgo(2));
        } else if ($fromDate == TimeZone::getTimeAgo(7)) {
            // Current period: From 7 days ago to yesterday - Previous period: From 2 weeks ago to 8 days ago
            $this->setCurrentAndPreviousPeriods(TimeZone::getTimeAgo(7), TimeZone::getTimeAgo(), TimeZone::getTimeAgo(14), TimeZone::getTimeAgo(8));
        } else if ($fromDate == TimeZone::getTimeAgo(14)) {
            // Current period: From 2 weeks ago to yesterday - Previous period: From 4 weeks ago to 15 days ago
            $this->setCurrentAndPreviousPeriods(TimeZone::getTimeAgo(14), TimeZone::getTimeAgo(), TimeZone::getTimeAgo(28), TimeZone::getTimeAgo(15));
        } else if ($fromDate == date('Y-m-d', strtotime('First day of previous month'))) {
            // Current period: Last month - Previous period: Previous month
            $this->setCurrentAndPreviousPeriods(
                date('Y-m-d', strtotime('First day of previous month')),
                date('Y-m-d', strtotime('Last day of previous month')),
                date('Y-m-d', strtotime('First day of -2 months')),
                date('Y-m-d', strtotime('Last day of -2 months'))
            );
        } else if (!empty($fromDate)) {
            // Current period: From the `$fromDate` to `$toDate` - Previous period: From twice the `$fromDate` to one day before the `$fromDate`
            $this->setCurrentAndPreviousPeriods(
                $fromDate,
                $toDate,
                TimeZone::getTimeAgo(TimeZone::getNumberDayBetween($fromDate) * 2),
                TimeZone::getTimeAgo(TimeZone::getNumberDayBetween($fromDate) + 1)
            );
        } else {
            // Current period: Total (including today) - Skip previous period (and the percentage change number)
            $this->setCurrentAndPreviousPeriods(date('Y-m-d', 0), TimeZone::getTimeAgo(0));
            $this->calculatePercentageChanges = false;
        }
    }

    /**
     * Sets the current and previous period dates.
     *
     * @param string $currentFrom Start date of the current period.
     * @param string $currentTo End date of the current period.
     * @param string $previousFrom Start date of the previous period.
     * @param string $previousTo End date of the previous period.
     *
     * @return void
     */
    private function setCurrentAndPreviousPeriods($currentFrom, $currentTo, $previousFrom = '', $previousTo = '')
    {
        $this->currentPeriod  = ['from' => $currentFrom, 'to' => $currentTo];
        $this->previousPeriod = ['from' => $previousFrom, 'to' => $previousTo];
    }

    /**
     * Configures the arguments for fetching data.
     *
     * @return void
     */
    private function setArguments()
    {
        $this->argsCurrentPeriod = [
            'date'     => $this->getCurrentPeriod(),
            'page'     => 0,
            'per_page' => 0,
        ];

        if ($this->calculatePercentageChanges) {
            $this->argsPreviousPeriod = [
                'date'     => $this->getPreviousPeriod(),
                'page'     => 0,
                'per_page' => 0,
            ];
        }
    }

    /**
     * Resets cached data.
     *
     * @return void
     */
    private function resetCachedData()
    {
        $this->currentPeriodVisitors     = null;
        $this->previousPeriodVisitors    = null;
        $this->currentPeriodViews        = null;
        $this->previousPeriodViews       = null;
        $this->currentPeriodReferrals    = null;
        $this->previousPeriodReferrals   = null;
        $this->currentPeriodContents     = null;
        $this->previousPeriodContents    = null;
        $this->percentageChangeVisitors  = null;
        $this->percentageChangeViews     = null;
        $this->percentageChangeReferrals = null;
        $this->percentageChangeContents  = null;
        $this->topAuthor                 = null;
        $this->topPost                   = null;
        $this->topReferral               = null;
        $this->topCategory               = null;
    }

    /**
     * Returns current period dates.
     *
     * @return array Format: `['from' => {Y-m-d}, 'to' => {Y-m-d}]`.
     */
    public function getCurrentPeriod()
    {
        return $this->currentPeriod;
    }

    /**
     * Returns previous period dates.
     *
     * @return array Format: `['from' => {Y-m-d}, 'to' => {Y-m-d}]`.
     */
    public function getPreviousPeriod()
    {
        return $this->previousPeriod;
    }

    /**
     * Should calculate percentage change between current period and previous period stats?
     *
     * @return bool
     */
    public function shouldCalculatePercentageChanges()
    {
        return $this->calculatePercentageChanges;
    }

    /**
     * Returns visitors for the selected period.
     *
     * @param bool $isCurrentPeriod Whether return current period's data or previous period's.
     *
     * @return int
     */
    public function getVisitors($isCurrentPeriod = true)
    {
        // Skip if `$isCurrentPeriod` is false and previous period is not calculated
        if (!$isCurrentPeriod && !$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        return $this->visitorsModel->countVisitors($isCurrentPeriod ? $this->argsCurrentPeriod : $this->argsPreviousPeriod);
    }

    /**
     * Returns visitors for current period.
     *
     * @return int
     */
    public function getCurrentPeriodVisitors()
    {
        if (!is_numeric($this->currentPeriodVisitors)) {
            $this->currentPeriodVisitors = $this->getVisitors();
        }

        return intval($this->currentPeriodVisitors);
    }

    /**
     * Returns visitors for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodVisitors()
    {
        if (!is_numeric($this->previousPeriodVisitors)) {
            $this->previousPeriodVisitors = $this->getVisitors(false);
        }

        return intval($this->previousPeriodVisitors);
    }

    /**
     * Returns visitors for the selected period.
     *
     * @param bool $isCurrentPeriod Whether return current period's data or previous period's.
     *
     * @return int
     */
    public function getViews($isCurrentPeriod = true)
    {
        // Skip if `$isCurrentPeriod` is false and previous period is not calculated
        if (!$isCurrentPeriod && !$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        return $this->viewsModel->countViewsFromPagesOnly($isCurrentPeriod ? $this->argsCurrentPeriod : $this->argsPreviousPeriod);
    }

    /**
     * Returns views for current period.
     *
     * @return int
     */
    public function getCurrentPeriodViews()
    {
        if (!is_numeric($this->currentPeriodViews)) {
            $this->currentPeriodViews = $this->getViews();
        }

        return intval($this->currentPeriodViews);
    }

    /**
     * Returns views for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodViews()
    {
        if (!is_numeric($this->previousPeriodViews)) {
            $this->previousPeriodViews = $this->getViews(false);
        }

        return intval($this->previousPeriodViews);
    }

    /**
     * Returns referrals for the selected period.
     *
     * @param bool $isCurrentPeriod Whether return current period's data or previous period's.
     *
     * @return array Format: `['visitors' => {COUNT}, 'referrer' => {URL}, 'visitors' => {COUNT}, 'referrer' => {URL}, ...]`
     */
    public function getReferrals($isCurrentPeriod = true)
    {
        // Skip if `$isCurrentPeriod` is false and previous period is not calculated
        if (!$isCurrentPeriod && !$this->shouldCalculatePercentageChanges()) {
            return [];
        }

        return $this->visitorsModel->getReferrers($isCurrentPeriod ? $this->argsCurrentPeriod : $this->argsPreviousPeriod);
    }

    /**
     * Returns referrals count for current period.
     *
     * @return int
     */
    public function getCurrentPeriodReferralsCount()
    {
        if (!is_array($this->currentPeriodReferrals)) {
            $this->currentPeriodReferrals = $this->getReferrals();
        }

        if (empty($this->currentPeriodReferrals)) {
            return 0;
        }

        $count = 0;
        foreach ($this->currentPeriodReferrals as $referral) {
            if (!empty($referral->visitors)) {
                $count += intval($referral->visitors);
            }
        }

        return intval($count);
    }

    /**
     * Returns referrals count for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodReferralsCount()
    {
        if (!is_array($this->previousPeriodReferrals)) {
            $this->previousPeriodReferrals = $this->getReferrals(false);
        }

        if (empty($this->previousPeriodReferrals)) {
            return 0;
        }

        $count = 0;
        foreach ($this->previousPeriodReferrals as $referral) {
            if (!empty($referral->visitors)) {
                $count += intval($referral->visitors);
            }
        }

        return intval($count);
    }

    /**
     * Returns number of published contents for the selected period.
     *
     * @param bool $isCurrentPeriod Whether return current period's data or previous period's.
     *
     * @return int
     */
    public function getContents($isCurrentPeriod = true)
    {
        // Skip if `$isCurrentPeriod` is false and previous period is not calculated
        if (!$isCurrentPeriod && !$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        if (empty($this->postsModel)) {
            $this->postsModel = new PostsModel();
        }

        return $this->postsModel->countPosts($isCurrentPeriod ? $this->argsCurrentPeriod : $this->argsPreviousPeriod);
    }

    /**
     * Returns contents for current period.
     *
     * @return int
     */
    public function getCurrentPeriodContents()
    {
        if (!is_numeric($this->currentPeriodContents)) {
            $this->currentPeriodContents = $this->getContents();
        }

        return intval($this->currentPeriodContents);
    }

    /**
     * Returns contents for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodContents()
    {
        if (!is_numeric($this->previousPeriodContents)) {
            $this->previousPeriodContents = $this->getContents(false);
        }

        return intval($this->previousPeriodContents);
    }

    /**
     * Returns percentage change between current and previous period's visitors.
     *
     * @return int
     */
    public function getPercentageChangeVisitors()
    {
        if (!$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        if (!is_numeric($this->percentageChangeVisitors)) {
            $this->percentageChangeVisitors = intval(Helper::calculatePercentageChange($this->getPreviousPeriodVisitors(), $this->getCurrentPeriodVisitors()));
        }

        return $this->percentageChangeVisitors;
    }

    /**
     * Returns percentage change between current and previous period's views.
     *
     * @return int
     */
    public function getPercentageChangeViews()
    {
        if (!$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        if (!is_numeric($this->percentageChangeViews)) {
            $this->percentageChangeViews = intval(Helper::calculatePercentageChange($this->getPreviousPeriodViews(), $this->getCurrentPeriodViews()));
        }

        return $this->percentageChangeViews;
    }

    /**
     * Returns percentage change between current and previous period's referrals.
     *
     * @return int
     */
    public function getPercentageChangeReferrals()
    {
        if (!$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        if (!is_numeric($this->percentageChangeReferrals)) {
            $this->percentageChangeReferrals = intval(Helper::calculatePercentageChange($this->getPreviousPeriodReferralsCount(), $this->getCurrentPeriodReferralsCount()));
        }

        return $this->percentageChangeReferrals;
    }

    /**
     * Returns percentage change between current and previous period's contents.
     *
     * @return int
     */
    public function getPercentageChangeContents()
    {
        if (!$this->shouldCalculatePercentageChanges()) {
            return 0;
        }

        if (!is_numeric($this->percentageChangeContents)) {
            $this->percentageChangeContents = intval(Helper::calculatePercentageChange($this->getPreviousPeriodContents(), $this->getCurrentPeriodContents()));
        }

        return $this->percentageChangeContents;
    }


    /**
     * Returns the name of the author with the most published posts in current period.
     *
     * @return string
     */
    public function getTopAuthor()
    {
        if ($this->topAuthor !== null) {
            return $this->topAuthor;
        }

        if (empty($this->authorsModel)) {
            $this->authorsModel = new AuthorsModel();
        }

        $this->topAuthor = $this->authorsModel->getAuthorsByPostPublishes($this->argsCurrentPeriod);
        $this->topAuthor = !empty($this->topAuthor) ? $this->topAuthor[0]->name : '';

        return $this->topAuthor;
    }

    /**
     * Returns the name of the post that had the most views in current period.
     *
     * @return string
     */
    public function getTopPost()
    {
        if ($this->topPost !== null) {
            return $this->topPost;
        }

        if (empty($this->postsModel)) {
            $this->postsModel = new PostsModel();
        }

        $this->topPost = $this->postsModel->getPostsViewsData($this->argsCurrentPeriod);
        $this->topPost = !empty($this->topPost) ? $this->topPost[0]->post_title : '';

        return $this->topPost;
    }

    /**
     * Returns the URL of the website that referred the most users in current period.
     *
     * @return string
     */
    public function getTopReferral()
    {
        if ($this->topReferral !== null) {
            return $this->topReferral;
        }

        if (!is_array($this->currentPeriodReferrals)) {
            $this->currentPeriodReferrals = $this->getReferrals();
        }

        $this->topReferral = null;
        foreach ($this->previousPeriodReferrals as $referral) {
            if (!empty($referral->visitors) && !empty($referral->referred)) {
                $this->topReferral = str_replace('www.', '', $referral->referred);
                $this->topReferral = wp_parse_url($this->topReferral);
                $this->topReferral = !empty($this->topReferral['host']) ? trim($this->topReferral['host']) : '';
                $this->topReferral = ucfirst($this->topReferral);

                // We only need the first referral
                break;
            }
        }

        return $this->topReferral;
    }

    /**
     * Returns the name of the category/taxonomy that had the most views in its posts in current period.
     *
     * @return string
     */
    public function getTopCategory()
    {
        if ($this->topCategory !== null) {
            return $this->topCategory;
        }

        if (empty($this->taxonomiesModel)) {
            $this->taxonomiesModel = new TaxonomyModel();
        }

        $this->topCategory = $this->taxonomiesModel->getTermsData([
            'date'     => $this->getCurrentPeriod(),
            'order_by' => 'views',
            'order'    => 'DESC',
            'taxonomy' => array_keys(Helper::get_list_taxonomy()),
        ]);
        $this->topCategory = (!empty($this->topCategory) && is_array($this->topCategory) && !empty($this->topCategory[0]->term_name)) ? $this->topCategory[0]->term_name : '';

        return $this->topCategory;
    }
}
