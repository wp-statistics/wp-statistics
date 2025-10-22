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
     * Retrieves visitors data for the summary widget.
     * Data includes current, previous and trend information.
     *
     * @return array
     */
    public function getVisitorsData()
    {
        $periods = $this->getPeriods();

        foreach ($periods as $key => $period) {
            $args = array_merge(['date' => $period['date']], $this->args);

            $visitors = !empty($args['include_hits'])
                ? $this->visitorsModel->getVisitorsHits($args)
                : $this->visitorsModel->countVisitors($args);

            $periods[$key]['data']['current'] = $visitors;

            if (!empty($period['comparison'])) {
                $args['date'] = DateRange::getPrevPeriod($period['date']);

                $prevVisitors = !empty($args['include_hits'])
                    ? $this->visitorsModel->getVisitorsHits($args)
                    : $this->visitorsModel->countVisitors($args);

                $periods[$key]['data']['prev'] = $prevVisitors;

                $periods[$key]['data']['trend']['visitors'] = $this->calculateTrend($visitors['visitors'], $prevVisitors['visitors']);

                if (!empty($args['include_hits'])) {
                    $periods[$key]['data']['trend']['hits'] = $this->calculateTrend($visitors['hits'], $prevVisitors['hits']);
                }
            }
        }

        return $periods;
    }

    /**
     * Retrieves views data for the summary widget.
     * Data includes current, previous and trend information.
     *
     * @return array
     */
    public function getViewsData()
    {
        $periods = $this->getPeriods();

        foreach ($periods as $key => $period) {
            $args = array_merge(['date' => $period['date']], $this->args);

            $views = $this->viewsModel->countViews($args);

            $periods[$key]['data']['current'] = $views;

            if (!empty($period['comparison'])) {
                $args['date'] = DateRange::getPrevPeriod($period['date']);

                $prevViews = $this->viewsModel->countViews($args);

                $periods[$key]['data']['trend'] = $this->calculateTrend($views, $prevViews);

                $periods[$key]['data']['prev'] = $prevViews;
            }
        }

        return $periods;
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