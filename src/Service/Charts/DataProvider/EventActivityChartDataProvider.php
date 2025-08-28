<?php
namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\EventsModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class EventActivityChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    protected $eventsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->eventsModel = new EventsModel();
    }

    public function getData()
    {
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $data = $this->eventsModel->countDailyEvents($this->args);

        $period     = $this->args['date'] ?? DateRange::get();
        $parsedData = $this->parseData($data, $period);

        $this->setChartLabels($parsedData['labels']);

        $this->addChartDataset(
            $this->getEventLabel(),
            $parsedData['events']
        );
    }

    protected function setPrevPeriodData()
    {
        $thisPeriod = $this->args['date'] ?? DateRange::get();
        $prevPeriod = DateRange::getPrevPeriod($thisPeriod);

        $data       = $this->eventsModel->countDailyEvents(array_merge($this->args, ['date' => $prevPeriod]));
        $parsedData = $this->parseData($data, $prevPeriod);

        $this->setChartPreviousLabels($parsedData['labels']);

        $this->addChartPreviousDataset(
            $this->getEventLabel(),
            $parsedData['events']
        );
    }

    protected function parseData($data, $period)
    {
        $dates = array_keys(TimeZone::getListDays($period));

        $events = wp_list_pluck($data, 'count', 'date');

        $parsedData = [
            'labels' => [],
            'events' => []
        ];
        foreach ($dates as $date) {
            $parsedData['labels'][] = [
                'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'              => date_i18n('Y-m-d', strtotime($date)),
                'day'               => date_i18n('D', strtotime($date))
            ];
            $parsedData['events'][] = isset($events[$date]) ? intval($events[$date]) : 0;
        }

        return $parsedData;
    }

    protected function getEventLabel()
    {
        $eventName = $this->args['event_name'];
        $eventName = is_string($eventName) ? [$eventName] : $eventName;

        if (array_intersect(['click', 'mouseup'], $eventName)) {
            return esc_html__('Clicks', 'wp-statistics');
        } else if (in_array('file_download', $eventName)) {
            return esc_html__('Downloads', 'wp-statistics');
        }

        if (!empty($this->args['event_label'])) {
            return $this->args['event_label'];
        }
    }
}
