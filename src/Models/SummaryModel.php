<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\PostType;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to traffic summaries.
 *
 * Provides methods to query and aggregate data from the summary table.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with page groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 * @since 15.0.0
 */
class SummaryModel extends BaseModel
{
    /**
     * Get top resources by total views within a date range.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date  Date range (Y-m-d).
     *   @type int                           $limit Number of rows to return. Default 10.
     * }
     * @return array<int, array{views:int, resource_id:int, resource_type:string}> Top resources
     */
    public function getTopViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'limit' => 10,
        ]);

        $rows = Query::select([
                'SUM(summary.views) AS views',
                'resource_uris.resource_id',
            ])
            ->from('summary')
            ->join('resource_uris', ['summary.resource_uri_id', 'resource_uris.id'])
            ->join('resources', ['resource_uris.resource_id', 'resources.id'])
            ->where('summary.date', '>=', $args['date']['from'])
            ->where('summary.date', '<=', $args['date']['to'])
            ->where('resources.resource_type', 'IN', PostType::getQueryableTypes())
            ->groupBy('summary.resource_uri_id')
            ->orderBy('views', 'DESC')
            ->perPage(1, (int) $args['limit'])
            ->allowCaching()
            ->getAll();

        return $rows;
    }
}
