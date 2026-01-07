<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to countries.
 *
 * Provides methods to query and aggregate metrics by country.
 *
 * @since 15.0.0
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with 'country' group_by instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class CountryModel extends BaseModel
{
    /**
     * Get top countries by views within a date range.
     *
     * Delegates to AnalyticsQueryHandler for consistent analytics access.
     *
     * Accepted arguments:
     * - 'date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     * - 'previous_date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d'] (optional, for comparison)
     * - 'limit' => int (optional, default: 4)
     *
     * @param array $args Query arguments.
     * @return array<int, array<string, mixed>> Top countries with views.
     */
    public function getTop($args = [])
    {
        $defaults = [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'previous_date' => null,
            'limit'         => 4,
        ];

        $args = $this->parseArgs($args, $defaults);

        $handler = new AnalyticsQueryHandler();

        $request = [
            'sources'  => ['views'],
            'group_by' => ['country'],
            'per_page' => (int) $args['limit'],
            'format'   => 'flat',
        ];

        // Set date range
        $request['date_from'] = $args['date']['from'];
        $request['date_to']   = $args['date']['to'];

        // Enable comparison if previous_date is provided
        if (!empty($args['previous_date']['from']) && !empty($args['previous_date']['to'])) {
            $request['compare']            = true;
            $request['previous_date_from'] = $args['previous_date']['from'];
            $request['previous_date_to']   = $args['previous_date']['to'];
        }

        $response = $handler->handle($request);

        // Transform response to match legacy format
        $rows = [];
        if (!empty($response['data']['rows'])) {
            foreach ($response['data']['rows'] as $row) {
                $item = [
                    'icon'  => strtolower($row['country_code'] ?? ''),
                    'label' => $row['country'] ?? '',
                    'value' => $row['views'] ?? 0,
                ];

                // Add previous_value if comparison was requested
                if (isset($row['previous']['views'])) {
                    $item['previous_value'] = $row['previous']['views'];
                }

                $rows[] = $item;
            }
        }

        return $rows;
    }
}
