<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;

class UsersTrafficChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    /**
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->queryHandler = new AnalyticsQueryHandler();
    }

    public function getData()
    {
        // Init chart data
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        // Get previous data only if comparison is enabled
        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $currentPeriod = $this->args['date'] ?? DateRange::get();
        $currentDates  = array_keys(TimeZone::getListDays($currentPeriod));

        // Build filters for logged-in users
        $loggedInFilters = ['logged_in' => true];
        $userRole = Request::get('role', '');
        if (!empty($userRole)) {
            $loggedInFilters['user_role'] = ['contains' => $userRole];
        }

        // Query for logged-in users
        $loggedInResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => $currentPeriod['from'] ?? null,
            'date_to'   => $currentPeriod['to'] ?? null,
            'filters'   => $loggedInFilters,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        // Query for anonymous users (user_id = 0)
        $anonymousResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => $currentPeriod['from'] ?? null,
            'date_to'   => $currentPeriod['to'] ?? null,
            'filters'   => ['logged_in' => false],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseData($currentDates, [
            'logged_in' => $loggedInResult['data']['rows'] ?? [],
            'anonymous' => $anonymousResult['data']['rows'] ?? [],
        ]);

        $this->setChartLabels($data['labels']);
        $this->addChartDataset(esc_html__('User Visitors', 'wp-statistics'), $data['users'], 'user-visitors');
        $this->addChartDataset(esc_html__('Anonymous Visitors', 'wp-statistics'), $data['anonymous'], 'anonymous-visitors');
    }

    protected function setPrevPeriodData()
    {
        $currentPeriod = $this->args['date'] ?? DateRange::get();
        $prevPeriod    = DateRange::getPrevPeriod($currentPeriod);
        $prevDates     = array_keys(TimeZone::getListDays($prevPeriod));

        // Build filters for logged-in users
        $loggedInFilters = ['logged_in' => true];
        $userRole = Request::get('role', '');
        if (!empty($userRole)) {
            $loggedInFilters['user_role'] = ['contains' => $userRole];
        }

        // Query for logged-in users (previous period)
        $loggedInResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'filters'   => $loggedInFilters,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        // Query for anonymous users (previous period)
        $anonymousResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'filters'   => ['logged_in' => false],
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseData($prevDates, [
            'logged_in' => $loggedInResult['data']['rows'] ?? [],
            'anonymous' => $anonymousResult['data']['rows'] ?? [],
        ]);

        $this->setChartPreviousLabels($data['labels']);
        $this->addChartPreviousDataset(esc_html__('User Visitors', 'wp-statistics'), $data['users']);
        $this->addChartPreviousDataset(esc_html__('Anonymous Visitors', 'wp-statistics'), $data['anonymous']);
    }

    protected function parseData($dates, $data)
    {
        $loggedIn   = wp_list_pluck($data['logged_in'], 'visitors', 'date');
        $anonymous  = wp_list_pluck($data['anonymous'], 'visitors', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][]   = [
                'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'              => date_i18n('Y-m-d', strtotime($date)),
                'day'               => date_i18n('D', strtotime($date))
            ];
            $parsedData['users'][]  = isset($loggedIn[$date]) ? intval($loggedIn[$date]) : 0;
            $parsedData['anonymous'][] = isset($anonymous[$date]) ? intval($anonymous[$date]) : 0;
        }

        return $parsedData;
    }
}