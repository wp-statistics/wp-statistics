<?php
namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class EventActivityChartDataProvider extends AbstractChartDataProvider
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
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $period = $this->args['date'] ?? DateRange::get();
        $dates  = array_keys(TimeZone::getListDays($period));

        $filters = $this->buildFilters();

        $result = $this->queryHandler->handle([
            'sources'   => ['events'],
            'group_by'  => ['date'],
            'date_from' => $period['from'] ?? null,
            'date_to'   => $period['to'] ?? null,
            'filters'   => $filters,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $parsedData = $this->parseData($dates, $result['data']['rows'] ?? []);

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
        $prevDates  = array_keys(TimeZone::getListDays($prevPeriod));

        $filters = $this->buildFilters();

        $result = $this->queryHandler->handle([
            'sources'   => ['events'],
            'group_by'  => ['date'],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'filters'   => $filters,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $parsedData = $this->parseData($prevDates, $result['data']['rows'] ?? []);

        $this->setChartPreviousLabels($parsedData['labels']);

        $this->addChartPreviousDataset(
            $this->getEventLabel(),
            $parsedData['events']
        );
    }

    protected function parseData($dates, $data)
    {
        $events = wp_list_pluck($data, 'events', 'date');

        $parsedData = [
            'labels' => [],
            'events' => []
        ];
        foreach ($dates as $date) {
            $parsedData['labels'][] = [
                'formatted_date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'           => date_i18n('Y-m-d', strtotime($date)),
                'day'            => date_i18n('D', strtotime($date))
            ];
            $parsedData['events'][] = isset($events[$date]) ? intval($events[$date]) : 0;
        }

        return $parsedData;
    }

    /**
     * Build filters for the query handler based on args.
     *
     * @return array
     */
    protected function buildFilters()
    {
        $filters = [];

        if (!empty($this->args['event_name'])) {
            $eventName = $this->args['event_name'];
            $eventName = is_string($eventName) ? [$eventName] : $eventName;
            $filters['event_name'] = ['in' => $eventName];
        }

        if (!empty($this->args['post_id'])) {
            $filters['event_page_id'] = $this->args['post_id'];
        }

        return $filters;
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
