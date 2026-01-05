<?php

namespace WP_Statistics\Service\Admin\WebsitePerformance;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Traits\ObjectCacheTrait;

/**
 * This class is used to get data needed for "Your performance at a glance" section (mostly used in e-mail reports).
 *
 * @since 15.0.0 Refactored to use AnalyticsQueryHandler instead of legacy models.
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
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    private $queryHandler;

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

        $this->queryHandler = new AnalyticsQueryHandler(false);
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
     * Sets current and previous period dates using DateRange component.
     *
     * @param string $fromDate Start date of the report in `Y-m-d` format.
     * @param string $toDate End date of the report in `Y-m-d` format. Default: Yesterday.
     *
     * @return void
     */
    private function setPeriods($fromDate, $toDate = '')
    {
        $yesterday = DateTime::get('-1 day');

        if (!DateTime::isValidDate($fromDate)) {
            $fromDate = DateTime::get('-7 days');
            $toDate   = $yesterday;
        } elseif (!DateTime::isValidDate($toDate)) {
            $toDate = $yesterday;
        }

        // Try to match to a predefined period for proper prev_period calculation
        $periodName = $this->detectPeriodName($fromDate, $toDate);

        if ($periodName) {
            // Use DateRange for predefined periods
            $range     = DateRange::get($periodName, true);
            $prevRange = DateRange::getPrevPeriod($periodName, true);

            $this->setCurrentAndPreviousPeriods(
                $range['from'],
                $range['to'],
                $prevRange['from'],
                $prevRange['to']
            );
        } elseif (!empty($fromDate)) {
            // Custom date range - calculate previous period dynamically
            $prevRange = DateRange::getPrevPeriod(['from' => $fromDate, 'to' => $toDate]);

            $this->setCurrentAndPreviousPeriods(
                $fromDate,
                $toDate,
                $prevRange['from'],
                $prevRange['to']
            );
        } else {
            // Total period - skip percentage changes
            $range = DateRange::get('total');
            $this->setCurrentAndPreviousPeriods($range['from'], $range['to']);
            $this->calculatePercentageChanges = false;
        }
    }

    /**
     * Detect the period name from date range.
     *
     * @param string $fromDate Start date.
     * @param string $toDate End date.
     * @return string|false Period name or false if not matched.
     */
    private function detectPeriodName($fromDate, $toDate)
    {
        $yesterday = DateTime::get('-1 day');

        // Check for 'yesterday' (single day)
        if ($fromDate === $yesterday && $toDate === $yesterday) {
            return 'yesterday';
        }

        // Check for '7days' (excludeToday = true shifts range)
        $sevenDaysRange = DateRange::get('7days', true);
        if ($fromDate === $sevenDaysRange['from'] && $toDate === $sevenDaysRange['to']) {
            return '7days';
        }

        // Check for '14days'
        $fourteenDaysRange = DateRange::get('14days', true);
        if ($fromDate === $fourteenDaysRange['from'] && $toDate === $fourteenDaysRange['to']) {
            return '14days';
        }

        // Check for 'last_month'
        $lastMonthRange = DateRange::get('last_month');
        if ($fromDate === $lastMonthRange['from'] && $toDate === $lastMonthRange['to']) {
            return 'last_month';
        }

        return false;
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
     * Returns visitors for the selected period using AnalyticsQueryHandler.
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

        $period = $isCurrentPeriod ? $this->getCurrentPeriod() : $this->getPreviousPeriod();

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $period['from'],
            'date_to'   => $period['to'],
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['visitors'] ?? 0);
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
     * Returns views for the selected period using AnalyticsQueryHandler.
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

        $period = $isCurrentPeriod ? $this->getCurrentPeriod() : $this->getPreviousPeriod();

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'date_from' => $period['from'],
            'date_to'   => $period['to'],
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['views'] ?? 0);
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
     * Returns referrals for the selected period using AnalyticsQueryHandler.
     *
     * @param bool $isCurrentPeriod Whether return current period's data or previous period's.
     *
     * @return array Format: `[['visitors' => {COUNT}, 'referrer' => {URL}], ...]`
     */
    public function getReferrals($isCurrentPeriod = true)
    {
        // Skip if `$isCurrentPeriod` is false and previous period is not calculated
        if (!$isCurrentPeriod && !$this->shouldCalculatePercentageChanges()) {
            return [];
        }

        $period = $isCurrentPeriod ? $this->getCurrentPeriod() : $this->getPreviousPeriod();

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'date_from' => $period['from'],
            'date_to'   => $period['to'],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $referrals = [];
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                // Convert to object format for backward compatibility
                $referrals[] = (object) [
                    'visitors' => intval($row['visitors'] ?? 0),
                    'referred' => $row['referrer'] ?? '',
                ];
            }
        }

        return $referrals;
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

        $period = $this->getCurrentPeriod();

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['page'],
            'date_from' => $period['from'],
            'date_to'   => $period['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);

        $title = '';
        if (!empty($result['data']['rows'][0]['page_title'])) {
            $title = $result['data']['rows'][0]['page_title'];
        }

        $this->setCache('topPost', $title);

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
