<?php
namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;

class SummaryChartDataProvider extends AbstractChartDataProvider
{
    /**
     * Args that change how a query runs, not which visitors or views it counts.
     */
    const NON_FILTER_ARGS = ['include_total', 'date', 'ignore_date', 'historical', 'prev_data', 'bypass_cache', 'date_field', 'hide_post', 'ignore_post_type', 'decorate', 'page', 'per_page', 'order_by', 'order'];

    /**
     * Filters that point at one resource. Historical rows are stored per URI, so a single
     * post or URL keeps its own historical views; any other filter has no historical match.
     */
    const SINGLE_RESOURCE_ARGS = ['resource_id', 'post_id', 'query_param', 'resource_type', 'post_type'];

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

            // Total period: all time. Historical numbers are site-wide (or per URI), so they are
            // only added when the card is not filtered, or filtered to a single resource.
            if ($key === 'total') {
                if ($this->canIncludeHistorical()) {
                    unset($args['date']);
                    $args['ignore_date'] = true;
                    $args['historical']  = true;
                } else {
                    // A date range (not ignore_date) so no historical row is added by the models
                    $args['date'] = ['from' => DateTime::get(0), 'to' => DateTime::get()];
                }
            }

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

        if (!empty($this->args['referred_visitors'])) {
            // Referred views live on the visitor rows (hits); the pages table has no referrer
            $data = $this->visitorsModel->getVisitorsHits($args);

            $result['visitors'] = intval($data['visitors']);
            $result['views']    = intval($data['hits']);
        } elseif ($this->isFilterApplied()) {
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
     * Whether the Total row may add the stored historical numbers.
     *
     * Historical visitors and views are kept for the whole site (and views per URI), so a
     * card filtered by country, post type, referrer and so on must not add them.
     *
     * @return bool
     */
    protected function canIncludeHistorical()
    {
        $filters = array_keys(array_filter(array_diff_key($this->args, array_flip(self::NON_FILTER_ARGS))));

        if (empty($filters)) {
            return true;
        }

        $isSingleResource = array_intersect(['resource_id', 'post_id', 'query_param'], $filters);

        return $isSingleResource && !array_diff($filters, self::SINGLE_RESOURCE_ARGS);
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