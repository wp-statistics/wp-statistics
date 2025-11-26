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
     * Get total fields within a date range.
     *
     * Sums the fields like`views` column from the `summary_totals` table between
     * `date[from]` and `date[to]`. Defaults to today if no range is provided.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date Date range (Y-m-d).
     * }
     * @return int Total views in the range
     */
    public function getFieldsCount($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields' => [
                'SUM(views) as views',
                'SUM(sessions) as sessions',
                'SUM(visitors) as visitors',
            ],
            'date' => DateRange::get('today')
        ]);

        $query = Query::select($args['fields'])
            ->from('summary_totals')
            ->where('date', '>=', $args['date']['from'])
            ->where('date', '<=', $args['date']['to']); 
            
        $result = $query->getAll();

        if (! empty($result[0])) {
            return $result[0];
        }

        return new \stdClass();
    }

    /**
     * Get daily traffic (views & visitors) for a date range.
     *
     * Returns one row per day ordered by date ascending. Defaults to the last
     * 30 days (inclusive) when no range is provided.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date Date range (Y-m-d).
     *   @type array{from:string,to:string} $previous_date Optional previous date range for comparison (Y-m-d).
     *   @type string $range Grouping type: 'daily', 'weekly', 'monthly'. Defaults to 'daily'.
     * }
     * @return array List of rows with fields: date (Y-m-d), views, visitors, viewsPrevious, visitorsPrevious
     */
    public function getTrafficInRange($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'range' => 'daily'
        ]);

        $dateFormat = 'date';
        $groupBy = 'date';
        $dateFormatWithTable = 'summary_totals.date as date';
        $groupByWithTable = 'summary_totals.date';

        switch ($args['range']) {
            case 'weekly':
                $dateFormat = 'MIN(date) as date';
                $groupBy = 'FLOOR(DATEDIFF(date, \'' . $args['date']['from'] . '\') / 7)';
                $dateFormatWithTable = 'MIN(summary_totals.date) as date';
                $groupByWithTable = 'FLOOR(DATEDIFF(summary_totals.date, \'' . $args['date']['from'] . '\') / 7)';
                break;
            case 'monthly':
                $dateFormat = 'DATE_FORMAT(date, "%Y-%m-01") as date';
                $groupBy = 'DATE_FORMAT(date, "%Y-%m")';
                $dateFormatWithTable = 'DATE_FORMAT(summary_totals.date, "%Y-%m-01") as date';
                $groupByWithTable = 'DATE_FORMAT(summary_totals.date, "%Y-%m")';
                break;
            case 'daily':
            default:
                $dateFormat = 'date';
                $groupBy = 'date';
                $dateFormatWithTable = 'summary_totals.date as date';
                $groupByWithTable = 'summary_totals.date';
                break;
        }

        // If previous_date is not provided, return simple query.
        if (empty($args['previous_date'])) {
            return Query::select([$dateFormat, 'SUM(views) as views', 'SUM(visitors) as visitors'])
                ->from('summary_totals')
                ->where('date', '>=', $args['date']['from'])
                ->where('date', '<=', $args['date']['to'])
                ->groupBy($groupBy)
                ->orderBy('date', 'ASC')
                ->getAll();
        }

        // Calculate the number of days between the two periods.
        $currentStart = strtotime($args['date']['from']);
        $previousStart = strtotime($args['previous_date']['from']);
        $daysDiff = (int)(($currentStart - $previousStart) / 86400);

        // Get table name for subquery
        global $wpdb;
        $tableName = $wpdb->prefix . 'statistics_summary_totals';

        // Determine grouping for subquery.
        $subQueryDateFormat = 'MIN(date) as date';
        $subQueryGroupBy = $groupBy;
        $joinCondition = ["summary_totals.date", "DATE_ADD(previous.date, INTERVAL {$daysDiff} DAY)"];

        if ($args['range'] === 'daily') {
            $subQueryDateFormat = 'date';
            $subQueryGroupBy = 'date';
        } elseif ($args['range'] === 'weekly') {
            // For weekly subquery, group by 7-day periods from previous_date[from].
            $subQueryGroupBy = 'FLOOR(DATEDIFF(date, \'' . $args['previous_date']['from'] . '\') / 7)';
        } elseif ($args['range'] === 'monthly') {
            // For monthly subquery, return first day of month and join on month match.
            $subQueryDateFormat = 'DATE_FORMAT(date, "%Y-%m-01") as date';
            $joinCondition = ["DATE_FORMAT(summary_totals.date, '%Y-%m')", "DATE_FORMAT(previous.date, '%Y-%m')"];
        }

        // Create subquery for previous period data with grouping.
        $subQuery = "SELECT {$subQueryDateFormat}, SUM(visitors) as visitors, SUM(views) as views FROM {$tableName} WHERE date >= '{$args['previous_date']['from']}' AND date <= '{$args['previous_date']['to']}' GROUP BY {$subQueryGroupBy}";

        $results = Query::select([
                $dateFormatWithTable,
                'COALESCE(SUM(summary_totals.visitors), 0) as visitors',
                'COALESCE(previous.visitors, 0) as visitorsPrevious',
                'COALESCE(SUM(summary_totals.views), 0) as views',
                'COALESCE(previous.views, 0) as viewsPrevious'
            ])
            ->from('summary_totals')
            ->joinQuery(
                $subQuery,
                $joinCondition,
                'previous',
                'LEFT'
            )
            ->where('summary_totals.date', '>=', $args['date']['from'])
            ->where('summary_totals.date', '<=', $args['date']['to'])
            ->groupBy($groupByWithTable)
            ->orderBy('summary_totals.date', 'ASC')
            ->getAll();

        return $results;
    }
}
