<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\Legacy\LegacyViewsModel;
use WP_STATISTICS\TimeZone;

class ViewsModel extends BaseModel
{
    private $legacy;

    public function __construct()
    {
        $this->legacy = new LegacyViewsModel();
    }

    /**
     * Retrieve the number of page views for a specific resource.
     *
     * @return int Total number of matching views.
     * @since 15.0.0
     */
    public function getPagesViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_id'   => '',
            'resource_type' => '',
            'resource_url'  => '',
            'date'          => '',
        ]);

        $query = Query::select(['COUNT(*) AS count'])
            ->from('views')
            ->where('resources.resource_id', '=', $args['resource_id'])
            ->where('resources.resource_url', '=', $args['resource_url'])
            ->where('resources.resource_type', 'IN', $args['resource_type']);

        if (!empty($args['resource_type'])) {
            $query->join('resources', ['views.resource_id', 'resources.ID']);
        }

        if (!empty($args['date'])) {
            $start = $args['date']['from'] . ' 00:00:00';
            $end   = $args['date']['to'] . ' 23:59:59';

            $query->where('views.viewed_at', '>=', $start)
                ->where('views.viewed_at', '<', $end);
        }

        return (int)$query->getVar();
    }

    /**
     * Retrieve the most recent view record for a given session ID.
     *
     * @param array $args {
     * @type int $session_id Required. The session ID to fetch the latest view for.
     * }
     *
     * @return object|null
     * @since 15.0.0
     */
    public function getLastViewBySessionId($args = [])
    {
        $args = $this->parseArgs($args, [
            'session_id' => 0
        ]);

        if (empty($args['session_id'])) {
            return null;
        }

        $query = Query::select('*')
            ->from('views')
            ->where('session_id', '=', $args['session_id'])
            ->orderBy('ID', 'DESC')
            ->perPage(1);

        return $query->getRow();
    }

    /**
     * Get the number of visits for a specific day or date range.
     *
     * @param array $args {
     *     Optional. Array of arguments.
     *
     * @type string|array $time Time range ('today', 'yesterday', or ['start' => 'Y-m-d', 'end' => 'Y-m-d']).
     * @type bool $daily Whether to fetch visits for a single day.
     * }
     *
     * @return int Total number of visits.
     *
     * @since 15.0.0
     */
    public function getViewsByTime($args = [])
    {
        $args = $this->parseArgs($args, [
            'time'  => 'today',
            'daily' => false,
        ]);

        $query = Query::select(['COUNT(*) AS count'])
            ->from('views');

        if ($args['daily']) {
            $date = TimeZone::isValidDate($args['time'])
                ? $args['time']
                : TimeZone::getCurrentDate('Y-m-d', $args['time']);

            $query->where('viewed_at', '>=', $date . ' 00:00:00')
                ->where('viewed_at', '<=', $date . ' 23:59:59');
        } else {
            $range = is_array($args['time']) && isset($args['time']['start'], $args['time']['end'])
                ? $args['time']
                : DateRange::get($args['time']);

            $query->where('viewed_at', '>=', $range['from'] . ' 00:00:00')
                ->where('viewed_at', '<=', $range['to'] . ' 23:59:59');
        }

        return (int)$query->getVar();
    }

    public function countViews($args = [])
    {
        return $this->legacy->countViews($args);
    }

    /**
     * Returns views from `pages` table without joining with other tables.
     *
     * Used for calculating taxonomies views (Unlike `countViews()` which is suited for calculating posts/pages/cpt views).
     *
     * @param array $args Arguments to include in query (e.g. `post_id`, `resource_type`, `query_param`, `date`, etc.).
     *
     * @return  int
     */
    public function countViewsFromPagesOnly($args = [])
    {
        return $this->legacy->countViewsFromPagesOnly($args);
    }

    public function countDailyViews($args = [])
    {
        return $this->legacy->countDailyViews($args);
    }

    public function getHourlyViews($args = [])
    {
        return $this->legacy->getHourlyViews($args);
    }

    public function getViewsSummary($args = [])
    {
        return $this->legacy->getViewsSummary($args);
    }

    public function getViewedPageUri($args = [])
    {
        return $this->legacy->getViewedPageUri($args);
    }

    public function getResourcesViews($args = [])
    {
        if (false) {
            return $this->legacy->getResourcesViews($args);
        }

        $args = $this->parseArgs($args, [
            /* default select list mirrors the legacy output */
            'fields'        => [
                'resources.resource_id   AS id',
                'resources.resource_url  AS uri',
                'resources.resource_type AS type',
                'COUNT(*)                AS views',
            ],
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'page'          => 1,
            'per_page'      => 10,
        ]);

        // base query: join views → resources
        $query = Query::select($args['fields'])
            ->from('views')
            ->join('resources', ['views.resource_id', 'resources.ID'])
            ->where('resources.resource_id', '=', $args['resource_id'])
            ->where('resources.resource_type', 'IN', $args['resource_type']);;

        if (!empty($args['date'])) {
            $start = $args['date']['from'] . ' 00:00:00';
            $end   = $args['date']['to'] . ' 23:59:59';

            $query->where('views.viewed_at', '>=', $start)
                ->where('views.viewed_at', '<=', $end);
        }

        if (empty($args['resource_id']) && empty($args['resource_type'])) {
            $query->groupBy('resources.ID');
        } else {
            $query->groupBy('resources.resource_id');
        }

        $results = $query->orderBy('views', 'DESC')
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $results ?: [];
    }
}
