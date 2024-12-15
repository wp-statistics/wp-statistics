<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;

class UsersTrafficChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    public $args;
    protected $visitorsModel;
    protected $viewsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();
    }

    public function getData()
    {
        // Init chart data
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        // Get previous data only if previous chart data option is enabled
        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $currentPeriod  = $this->args['date'] ?? DateRange::get();
        $currentDates   = array_keys(TimeZone::getListDays($currentPeriod));

        $loggedInData   = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['logged_in' => true, 'user_role' => Request::get('role', '')]));
        $anonymousData  = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['user_id' => '0']));

        $data = $this->parseData($currentDates, ['logged_in' => $loggedInData, 'anonymous' => $anonymousData]);

        $this->setChartLabels($data['labels']);
        $this->addChartDataset(esc_html__('User Visitors', 'wp-statistics'), $data['users']);
        $this->addChartDataset(esc_html__('Anonymous Visitors', 'wp-statistics'), $data['anonymous']);
    }

    protected function setPrevPeriodData()
    {
        $currentPeriod  = $this->args['date'] ?? DateRange::get();
        $prevPeriod     = DateRange::getPrevPeriod($currentPeriod);
        $prevDates      = array_keys(TimeZone::getListDays($prevPeriod));

        $loggedInData   = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['logged_in' => true, 'user_role' => Request::get('role', ''), 'date' => $prevPeriod]));
        $anonymousData  = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['user_id' => '0', 'date' => $prevPeriod]));

        $data = $this->parseData($prevDates, ['logged_in' => $loggedInData, 'anonymous' => $anonymousData]);

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
                'day'               => date_i18n('l', strtotime($date))
            ];
            $parsedData['users'][]  = isset($loggedIn[$date]) ? intval($loggedIn[$date]) : 0;
            $parsedData['anonymous'][] = isset($anonymous[$date]) ? intval($anonymous[$date]) : 0;
        }

        return $parsedData;
    }
}
