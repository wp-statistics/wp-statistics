<?php
namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
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
            }
        }

        return $periods;
    }

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

                $periods[$key]['data']['prev'] = $prevViews;
            }
        }

        return $periods;
    }

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
}