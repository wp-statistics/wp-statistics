<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Utils\Query;

/**
 * Model for online visitors using v15 sessions table.
 *
 * Online visitors are determined by sessions with ended_at within the last 5 minutes.
 * This replaces the legacy useronline table approach.
 *
 * @since 15.0.0
 */
class OnlineModel extends BaseModel
{
    /**
     * Online threshold in seconds (5 minutes).
     */
    const ONLINE_THRESHOLD = 300;

    /**
     * Count online visitors.
     *
     * @param array $args Optional arguments.
     * @return int Count of online visitors.
     */
    public function countOnlines($args = [])
    {
        $args = $this->parseArgs($args, []);

        $fiveMinutesAgo = gmdate('Y-m-d H:i:s', time() - self::ONLINE_THRESHOLD);

        $result = Query::select('COUNT(DISTINCT sessions.visitor_id)')
            ->from('sessions')
            ->where('sessions.ended_at', '>=', $fiveMinutesAgo)
            ->getVar();

        return $result ? (int) $result : 0;
    }

    /**
     * Get online visitors data with full details.
     *
     * @param array $args {
     *     Optional. Query arguments.
     *     @type int    $page     Page number. Default 1.
     *     @type int    $per_page Items per page. Default all.
     *     @type string $order_by Order by column. Default 'last_visit'.
     *     @type string $order    Order direction. Default 'DESC'.
     * }
     * @return array Array of online visitor data.
     */
    public function getOnlineVisitorsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'page'     => 1,
            'per_page' => '',
            'order_by' => 'last_visit',
            'order'    => 'DESC',
        ]);

        $fiveMinutesAgo = gmdate('Y-m-d H:i:s', time() - self::ONLINE_THRESHOLD);

        // Map legacy order_by values to new column names
        $orderByMap = [
            'date'      => 'last_visit',
            'timestamp' => 'last_visit',
            'created'   => 'first_visit',
        ];
        $orderBy = $orderByMap[$args['order_by']] ?? $args['order_by'];

        $result = Query::select([
            'sessions.ID AS online_id',
            'sessions.visitor_id AS ID',
            'sessions.ip',
            'sessions.started_at AS created',
            'sessions.ended_at AS timestamp',
            'sessions.total_views AS hits',
            'sessions.user_id',
            'visitors.hash',
            'referrers.domain AS referred',
            'referrers.channel AS source_channel',
            'device_browsers.name AS agent',
            'device_browser_versions.version',
            'device_oss.name AS platform',
            'device_types.name AS device',
            'device_models.name AS model',
            'countries.code AS location',
            'countries.name AS country_name',
            'cities.region_name AS region',
            'cities.city_name AS city',
            'MAX(sessions.started_at) AS first_visit',
            'MAX(sessions.ended_at) AS last_visit',
            'users.display_name',
            'users.user_email',
        ])
            ->from('sessions')
            ->join('visitors', ['sessions.visitor_id', 'visitors.ID'])
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'], [], 'LEFT')
            ->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'], [], 'LEFT')
            ->join('device_browser_versions', ['sessions.device_browser_version_id', 'device_browser_versions.ID'], [], 'LEFT')
            ->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'], [], 'LEFT')
            ->join('device_types', ['sessions.device_type_id', 'device_types.ID'], [], 'LEFT')
            ->join('device_models', ['sessions.device_model_id', 'device_models.ID'], [], 'LEFT')
            ->join('countries', ['sessions.country_id', 'countries.ID'], [], 'LEFT')
            ->join('cities', ['sessions.city_id', 'cities.ID'], [], 'LEFT')
            ->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT')
            ->where('sessions.ended_at', '>=', $fiveMinutesAgo)
            ->groupBy('sessions.visitor_id')
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($orderBy, $args['order'])
            ->decorate(VisitorDecorator::class)
            ->getAll();

        return $result ? $result : [];
    }
}
