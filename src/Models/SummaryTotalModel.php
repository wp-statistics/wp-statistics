<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to daily traffic totals.
 *
 * Provides methods to query and aggregate views and visitors from the summary_totals table.
 *
 * @since 15.0.0
 */
class SummaryTotalModel extends BaseModel
{
    /**
     * Get total visitors within a date range.
     *
     * Sums the `visitors` column from the `summary_totals` table between
     * `date[from]` and `date[to]`. Defaults to today if no range is provided.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date Date range (Y-m-d).
     * }
     * @return int Total visitors in the range
     */
    public function getVisitorsCount($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => DateRange::get('today')
        ]);

        $query = Query::select(['SUM(visitors) AS total'])
            ->from('summary_totals')
            ->where('date', '>=', $args['date']['from'])
            ->where('date', '<=', $args['date']['to']); 
           
        return (int)$query->getVar();
    }

    /**
     * Get total views within a date range.
     *
     * Sums the `views` column from the `summary_totals` table between
     * `date[from]` and `date[to]`. Defaults to today if no range is provided.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date Date range (Y-m-d).
     * }
     * @return int Total views in the range
     */
    public function getViewsCount($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => DateRange::get('today')
        ]);

        $query = Query::select(['SUM(views) AS total'])
            ->from('summary_totals')
            ->where('date', '>=', $args['date']['from'])
            ->where('date', '<=', $args['date']['to']); 
           
        return (int)$query->getVar();
    }

    /**
     * Get daily traffic (views & visitors) for a date range.
     *
     * Returns one row per day ordered by date ascending. Defaults to the last
     * 30 days (inclusive) when no range is provided.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date Date range (Y-m-d).
     * }
     * @return array List of rows with fields: date (Y-m-d), views, visitors
     */
    public function getTrafficInRange($args = [])
    {
        // Default to the last 30 days (inclusive)
        $args = $this->parseArgs($args, [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
        ]);

        $rows = Query::select(['date', 'views', 'visitors'])
            ->from('summary_totals')
            ->where('date', '>=', $args['date']['from'])
            ->where('date', '<=', $args['date']['to'])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getAll();

        return $rows;
    }
}
