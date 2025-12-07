<?php
namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;

class SummaryChartDataProvider extends AbstractChartDataProvider
{
    protected $visitorsModel;
    protected $viewsModel;

    public function __construct($args = [])
    {
        parent::__construct($args);

        $this->visitorsModel = new VisitorsModel();
        $this->viewsModel    = new ViewsModel();
    }

    /**
     * Retrieves data for the summary widget.
     *
     * @return array
     */
    public function getData()
    {
        $periods = $this->getPeriods();

        foreach ($periods as $key => $period) {
            $data = [];

            $args = array_merge($this->args, ['date' => $period['date']]);

            // Fetch current data
            $data['current'] = $this->fetchData($args);

            if (!empty($period['comparison'])) {
                $args['date'] = DateRange::getPrevPeriod($period['date']);

                // Fetch previous data
                $data['prev'] = $this->fetchData($args);

                // Calculate trends
                $data['trend'] = [
                    'visitors' => $this->calculateTrend($data['current']['visitors'], $data['prev']['visitors']),
                    'views'    => $this->calculateTrend($data['current']['views'], $data['prev']['views'])
                ];
            }

            $periods[$key]['data'] = $data;
        }

        return $periods;
    }

    /**
     * Fetch data for a specific period.
     * Uses separate queries when filters are applied, combined query otherwise.
     *
     * @param array $args
     * @return array
     */
    protected function fetchData($args)
    {
        $result = ['visitors' => 0, 'views' => 0];

        if ($this->isFilterApplied()) {
            // Get visitors and views separately when filtering
            $result['visitors'] = $this->visitorsModel->countVisitors($args);
            $result['views']    = $this->viewsModel->countViews($args);
        } else {
            // Get visitors and hits combined for better performance
            $data = $this->visitorsModel->getVisitorsHits($args);

            $result['visitors'] = $data['visitors'];
            $result['views']    = $data['hits'];
        }

        return $result;
    }

    /**
     * Get the time periods for the summary widget data.
     *
     * @return array
     */
    protected function getPeriods()
    {
        $periods =  [
            'today' => [
                'label'      => esc_html__('Today', 'wp-statistics'),
                'tooltip'    => null,
                'comparison' => false,
                'date'       => DateRange::get('today')
            ],
            'yesterday' => [
                'label'      => esc_html__('Yesterday', 'wp-statistics'),
                'tooltip'    => null,
                'comparison' => true,
                'date'       => DateRange::get('yesterday'),
            ],
            '7days' => [
                'label'      => esc_html__('Last 7 days', 'wp-statistics'),
                'tooltip'    => esc_html__('Totals from the last 7 complete days (excludes today).', 'wp-statistics'),
                'comparison' => true,
                'date'       => DateRange::get('7days', true),
            ],
            '28days' => [
                'label'      => esc_html__('Last 28 days', 'wp-statistics'),
                'tooltip'    => esc_html__('Totals from the last 28 complete days (excludes today).', 'wp-statistics'),
                'comparison' => true,
                'date'       => DateRange::get('28days', true),
            ]
        ];

        if (!empty($this->args['include_total'])) {
            $periods['total'] = [
                'label'      => esc_html__('Total', 'wp-statistics'),
                'tooltip'    => null,
                'comparison' => false,
                'date'       => DateRange::get('total'),
            ];
        }

        return $periods;
    }

    /**
     * Calculate the trend between current and previous values.
     *
     * @param int $current
     * @param int $previous
     * @return array
     */
    protected function calculateTrend($current, $previous)
    {
        $difference = $current - $previous;
        $percentage = Helper::calculatePercentageChange($previous, $current, 1, true);

        if ($difference > 0) {
            $direction = 'up';
        } elseif ($difference < 0) {
            $direction = 'down';
        } else {
            $direction = 'neutral';
        }

        return [
            'direction'  => $direction,
            'difference' => $difference,
            'percentage' => $percentage
        ];
    }
}