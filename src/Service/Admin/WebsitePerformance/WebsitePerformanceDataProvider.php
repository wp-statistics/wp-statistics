<?php

namespace WP_Statistics\Service\Admin\WebsitePerformance;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Traits\ObjectCacheTrait;

/**
 * This class is used to get data needed for "Your performance at a glance" section (mostly used in e-mail reports).
 */
class WebsitePerformanceDataProvider
{
    use ObjectCacheTrait;

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
        $this->setCache('currentPeriodVisitors', null);
        $this->setCache('previousPeriodVisitors', null);
        $this->setCache('currentPeriodViews', null);
        $this->setCache('previousPeriodViews', null);
        $this->setCache('currentPeriodReferrals', null);
        $this->setCache('previousPeriodReferrals', null);
        $this->setCache('currentPeriodContents', null);
        $this->setCache('previousPeriodContents', null);
        $this->setCache('percentageChangeVisitors', null);
        $this->setCache('percentageChangeViews', null);
        $this->setCache('percentageChangeReferrals', null);
        $this->setCache('percentageChangeContents', null);
        $this->setCache('topAuthor', null);
        $this->setCache('topPost', null);
        $this->setCache('topReferral', null);
        $this->setCache('topCategory', null);
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
        if (!is_numeric($this->getCache('currentPeriodVisitors'))) {
            $this->setCache('currentPeriodVisitors', $this->getVisitors());
        }

        return intval($this->getCache('currentPeriodVisitors'));
    }

    /**
     * Returns visitors for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodVisitors()
    {
        if (!is_numeric($this->getCache('previousPeriodVisitors'))) {
            $this->setCache('previousPeriodVisitors', $this->getVisitors(false));
        }

        return intval($this->getCache('previousPeriodVisitors'));
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
        if (!is_numeric($this->getCache('currentPeriodViews'))) {
            $this->setCache('currentPeriodViews', $this->getViews());
        }

        return intval($this->getCache('currentPeriodViews'));
    }

    /**
     * Returns views for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodViews()
    {
        if (!is_numeric($this->getCache('previousPeriodViews'))) {
            $this->setCache('previousPeriodViews', $this->getViews(false));
        }

        return intval($this->getCache('previousPeriodViews'));
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
        if (!is_array($this->getCache('currentPeriodReferrals'))) {
            $this->setCache('currentPeriodReferrals', $this->getReferrals());
        }

        if (empty($this->getCache('currentPeriodReferrals'))) {
            return 0;
        }

        $count = 0;
        foreach ($this->getCache('currentPeriodReferrals') as $referral) {
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
        if (!is_array($this->getCache('previousPeriodReferrals'))) {
            $this->setCache('previousPeriodReferrals', $this->getReferrals(false));
        }

        if (empty($this->getCache('previousPeriodReferrals'))) {
            return 0;
        }

        $count = 0;
        foreach ($this->getCache('previousPeriodReferrals') as $referral) {
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
        if (!is_numeric($this->getCache('currentPeriodContents'))) {
            $this->setCache('currentPeriodContents', $this->getContents());
        }

        return intval($this->getCache('currentPeriodContents'));
    }

    /**
     * Returns contents for previous period.
     *
     * @return int
     */
    public function getPreviousPeriodContents()
    {
        if (!is_numeric($this->getCache('previousPeriodContents'))) {
            $this->setCache('previousPeriodContents', $this->getContents(false));
        }

        return intval($this->getCache('previousPeriodContents'));
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

        if (!is_numeric($this->getCache('percentageChangeVisitors'))) {
            $this->setCache('percentageChangeVisitors', intval(Helper::calculatePercentageChange($this->getPreviousPeriodVisitors(), $this->getCurrentPeriodVisitors())));
        }

        return $this->getCache('percentageChangeVisitors');
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

        if (!is_numeric($this->getCache('percentageChangeViews'))) {
            $this->setCache('percentageChangeViews', intval(Helper::calculatePercentageChange($this->getPreviousPeriodViews(), $this->getCurrentPeriodViews())));
        }

        return $this->getCache('percentageChangeViews');
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

        if (!is_numeric($this->getCache('percentageChangeReferrals'))) {
            $this->setCache('percentageChangeReferrals', intval(Helper::calculatePercentageChange($this->getPreviousPeriodReferralsCount(), $this->getCurrentPeriodReferralsCount())));
        }

        return $this->getCache('percentageChangeReferrals');
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

        if (!is_numeric($this->getCache('percentageChangeContents'))) {
            $this->setCache('percentageChangeContents', intval(Helper::calculatePercentageChange($this->getPreviousPeriodContents(), $this->getCurrentPeriodContents())));
        }

        return $this->getCache('percentageChangeContents');
    }


    /**
     * Returns the name of the author with the most published posts in current period.
     *
     * @return string
     */
    public function getTopAuthor()
    {
        if ($this->getCache('topAuthor') !== null) {
            return $this->getCache('topAuthor');
        }

        if (empty($this->authorsModel)) {
            $this->authorsModel = new AuthorsModel();
        }

        $topAuthor = $this->authorsModel->getAuthorsByPostPublishes($this->argsCurrentPeriod);
        $this->setCache('topAuthor', !empty($topAuthor) ? $topAuthor[0]->name : '');

        return $this->getCache('topAuthor');
    }

    /**
     * Returns the name of the post that had the most views in current period.
     *
     * @return string
     */
    public function getTopPost()
    {
        if ($this->getCache('topPost') !== null) {
            return $this->getCache('topPost');
        }

        if (empty($this->postsModel)) {
            $this->postsModel = new PostsModel();
        }

        $topPost = $this->postsModel->getPostsViewsData($this->argsCurrentPeriod);
        $this->setCache('topPost', !empty($topPost) ? $topPost[0]->post_title : '');

        return $this->getCache('topPost');
    }

    /**
     * Returns the URL of the website that referred the most users in current period.
     *
     * @return string
     */
    public function getTopReferral()
    {
        if ($this->getCache('topReferral') !== null) {
            return $this->getCache('topReferral');
        }

        if (!is_array($this->getCache('currentPeriodReferrals'))) {
            $this->setCache('currentPeriodReferrals', $this->getReferrals());
        }

        $topReferral = null;
        foreach ($this->getCache('currentPeriodReferrals') as $referral) {
            if (!empty($referral->visitors) && !empty($referral->referred)) {
                $topReferral = str_replace('www.', '', $referral->referred);
                $topReferral = wp_parse_url($topReferral);
                $topReferral = !empty($topReferral['host']) ? trim($topReferral['host']) : '';
                $this->setCache('topReferral', ucfirst($topReferral));

                // We only need the first referral
                break;
            }
        }

        return $this->getCache('topReferral');
    }

    /**
     * Returns the name of the category/taxonomy that had the most views in its posts in current period.
     *
     * @return string
     */
    public function getTopCategory()
    {
        if ($this->getCache('topCategory') !== null) {
            return $this->getCache('topCategory');
        }

        if (empty($this->taxonomiesModel)) {
            $this->taxonomiesModel = new TaxonomyModel();
        }

        $topCategory = $this->taxonomiesModel->getTermsData([
            'date'     => $this->getCurrentPeriod(),
            'order_by' => 'views',
            'order'    => 'DESC',
            'taxonomy' => array_keys(Helper::get_list_taxonomy()),
        ]);
        $this->setCache('topCategory', (!empty($topCategory) && is_array($topCategory) && !empty($topCategory[0]->term_name)) ? $topCategory[0]->term_name : '');

        return $this->getCache('topCategory');
    }
}
