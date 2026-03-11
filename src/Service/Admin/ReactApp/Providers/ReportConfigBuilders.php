<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

/**
 * Reusable builders for PHP-driven report configs.
 *
 * Provides public static methods for common query, widget, and column patterns
 * used by both core (ReportConfigDataProvider) and premium modules.
 *
 * All builders accept an $overrides array merged last, so callers can customize any field.
 */
class ReportConfigBuilders
{
    // -----------------------------------------------------------------------
    // Query builders
    // -----------------------------------------------------------------------

    /**
     * Build a "top N by visitors" query for any dimension.
     */
    public static function topByVisitorsQuery(string $id, array $groupBy, array $columns, array $overrides = []): array
    {
        return array_merge([
            'id'          => $id,
            'sources'     => ['visitors'],
            'group_by'    => $groupBy,
            'columns'     => $columns,
            'per_page'    => 5,
            'order_by'    => 'visitors',
            'order'       => 'DESC',
            'format'      => 'table',
            'show_totals' => true,
        ], $overrides);
    }

    public static function topCountriesQuery(string $id = 'top_countries', array $overrides = []): array
    {
        return self::topByVisitorsQuery($id, ['country'], ['country_code', 'country_name', 'visitors'], $overrides);
    }

    public static function topBrowsersQuery(string $id = 'top_browsers', array $overrides = []): array
    {
        return self::topByVisitorsQuery($id, ['browser'], ['browser_name', 'visitors'], $overrides);
    }

    public static function topOsQuery(string $id = 'top_operating_systems', array $overrides = []): array
    {
        return self::topByVisitorsQuery($id, ['os'], ['os_name', 'visitors'], $overrides);
    }

    public static function topDeviceCategoriesQuery(string $id = 'top_device_categories', array $overrides = []): array
    {
        return self::topByVisitorsQuery($id, ['device_type'], ['device_type_name', 'visitors'], $overrides);
    }

    public static function topReferrersQuery(string $id = 'top_referrers', array $overrides = []): array
    {
        return self::topByVisitorsQuery($id, ['referrer'], ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'], $overrides);
    }

    /**
     * Build a traffic trends chart query.
     */
    public static function trafficTrendsQuery(string $id = 'traffic_trends', array $overrides = []): array
    {
        return array_merge([
            'id'               => $id,
            'sources'          => ['visitors', 'views'],
            'group_by'         => ['date'],
            'format'           => 'chart',
            'show_totals'      => false,
            'compare'          => true,
            'timeframeGroupBy' => true,
        ], $overrides);
    }

    /**
     * Build a flat metrics query (aggregates with comparison support).
     */
    public static function metricsQuery(string $id, array $sources, array $overrides = []): array
    {
        return array_merge([
            'id'          => $id,
            'sources'     => $sources,
            'group_by'    => [],
            'format'      => 'flat',
            'show_totals' => true,
            'compare'     => true,
        ], $overrides);
    }

    /**
     * Build a "top 1" metric query (single row, no comparison).
     */
    public static function topOneQuery(string $id, array $groupBy, array $columns): array
    {
        return [
            'id'          => $id,
            'sources'     => ['visitors'],
            'group_by'    => $groupBy,
            'columns'     => $columns,
            'per_page'    => 1,
            'order_by'    => 'visitors',
            'order'       => 'DESC',
            'format'      => 'flat',
            'show_totals' => false,
            'compare'     => false,
        ];
    }

    // -----------------------------------------------------------------------
    // Widget builders
    // -----------------------------------------------------------------------

    public static function topCountriesWidget(array $overrides = []): array
    {
        return array_merge([
            'id'             => 'top-countries',
            'type'           => 'bar-list',
            'label'          => __('Top Countries', 'wp-statistics'),
            'defaultSize'    => 4,
            'queryId'        => 'top_countries',
            'labelField'     => 'country_name',
            'valueField'     => 'visitors',
            'iconType'       => 'country',
            'iconSlugField'  => 'country_code',
            'linkTo'         => '/country/$countryCode',
            'linkParamField' => 'country_code',
            'columnHeaders'  => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
            'link'           => ['to' => '/countries'],
        ], $overrides);
    }

    public static function topBrowsersWidget(array $overrides = []): array
    {
        return array_merge([
            'id'            => 'top-browsers',
            'type'          => 'bar-list',
            'label'         => __('Top Browsers', 'wp-statistics'),
            'defaultSize'   => 4,
            'queryId'       => 'top_browsers',
            'labelField'    => 'browser_name',
            'valueField'    => 'visitors',
            'iconType'      => 'browser',
            'iconSlugField' => 'browser_name',
            'columnHeaders' => ['left' => __('Browser', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
            'link'          => ['to' => '/browsers'],
        ], $overrides);
    }

    public static function topOsWidget(array $overrides = []): array
    {
        return array_merge([
            'id'            => 'top-operating-systems',
            'type'          => 'bar-list',
            'label'         => __('Top Operating Systems', 'wp-statistics'),
            'defaultSize'   => 4,
            'queryId'       => 'top_operating_systems',
            'labelField'    => 'os_name',
            'valueField'    => 'visitors',
            'iconType'      => 'os',
            'iconSlugField' => 'os_name',
            'columnHeaders' => ['left' => __('Operating System', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
            'link'          => ['to' => '/operating-systems'],
        ], $overrides);
    }

    public static function topDeviceCategoriesWidget(array $overrides = []): array
    {
        return array_merge([
            'id'            => 'top-device-categories',
            'type'          => 'bar-list',
            'label'         => __('Top Device Categories', 'wp-statistics'),
            'defaultSize'   => 4,
            'queryId'       => 'top_device_categories',
            'labelField'    => 'device_type_name',
            'valueField'    => 'visitors',
            'columnHeaders' => ['left' => __('Device Category', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
            'link'          => ['to' => '/device-categories'],
        ], $overrides);
    }

    public static function topReferrersWidget(array $overrides = []): array
    {
        return array_merge([
            'id'                  => 'top-referrers',
            'type'                => 'bar-list',
            'label'               => __('Top Referrers', 'wp-statistics'),
            'defaultSize'         => 4,
            'queryId'             => 'top_referrers',
            'labelField'          => 'referrer_name',
            'labelFallbackFields' => ['referrer_domain'],
            'valueField'          => 'visitors',
            'columnHeaders'       => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
            'link'                => ['to' => '/referrers'],
        ], $overrides);
    }

    public static function trafficTrendsWidget(array $overrides = []): array
    {
        return array_merge([
            'id'          => 'traffic-trends',
            'type'        => 'chart',
            'label'       => __('Traffic Trends', 'wp-statistics'),
            'defaultSize' => 12,
            'queryId'     => 'traffic_trends',
            'chartConfig' => [
                'metrics'          => [
                    ['key' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'color' => 'var(--chart-1)'],
                    ['key' => 'views', 'label' => __('Views', 'wp-statistics'), 'color' => 'var(--chart-2)'],
                ],
                'timeframeSupport' => true,
            ],
        ], $overrides);
    }

    public static function mapWidget(string $queryId = 'top_countries', array $overrides = []): array
    {
        return array_merge([
            'id'          => 'map',
            'label'       => __('Map', 'wp-statistics'),
            'type'        => 'map',
            'defaultSize' => 12,
            'queryId'     => $queryId,
            'mapConfig'   => [
                'title'  => __('Visitors', 'wp-statistics'),
                'metric' => 'visitors',
            ],
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // Column optimization helper
    // -----------------------------------------------------------------------

    /**
     * Generate columnConfig + defaultApiColumns from a columns array.
     *
     * Auto-derives column dependencies from each column's dataField, numerator/denominator,
     * and linkParamField. Columns with complex implicit dependencies (e.g., location, referrer)
     * can provide overrides via $overrideDeps.
     *
     * @param array $columns       The columns array (same format as report 'columns' config).
     * @param array $baseColumns   Base API columns always fetched (e.g., primary key fields).
     * @param array $overrideDeps  Override dependencies for specific column keys.
     *                             Example: ['country' => ['country_code', 'country_name']]
     * @return array With keys 'columnConfig' and 'defaultApiColumns'.
     */
    public static function columnOptimization(array $columns, array $baseColumns = [], array $overrideDeps = []): array
    {
        $deps    = [];
        $apiCols = $baseColumns;

        foreach ($columns as $col) {
            $key = $col['key'];

            if (isset($overrideDeps[$key])) {
                $fields = $overrideDeps[$key];
            } elseif (isset($col['numerator'])) {
                $fields = [$col['numerator'], $col['denominator']];
            } else {
                $field  = $col['dataField'] ?? $key;
                $fields = [$field];
            }

            if (isset($col['linkParamField']) && !in_array($col['linkParamField'], $fields)) {
                $fields[] = $col['linkParamField'];
            }

            $deps[$key] = $fields;
            array_push($apiCols, ...$fields);
        }

        return [
            'columnConfig'      => [
                'baseColumns'        => $baseColumns,
                'columnDependencies' => $deps,
            ],
            'defaultApiColumns' => array_values(array_unique($apiCols)),
        ];
    }

    // -----------------------------------------------------------------------
    // Column builders
    // -----------------------------------------------------------------------

    public static function visitorsColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'visitors',
            'title'        => __('Visitors', 'wp-statistics'),
            'type'         => 'numeric',
            'comparable'   => true,
            'previousKey'  => 'previous.visitors',
            'size'         => 'views',
            'cardPosition' => 'body',
        ], $overrides);
    }

    public static function viewsColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'views',
            'title'        => __('Views', 'wp-statistics'),
            'type'         => 'numeric',
            'comparable'   => true,
            'previousKey'  => 'previous.views',
            'size'         => 'views',
            'cardPosition' => 'body',
        ], $overrides);
    }

    public static function bounceRateColumn(array $overrides = []): array
    {
        return array_merge([
            'key'         => 'bounceRate',
            'dataField'   => 'bounce_rate',
            'title'       => __('Bounce Rate', 'wp-statistics'),
            'type'        => 'percentage',
            'priority'    => 'secondary',
            'comparable'  => true,
            'size'        => 'bounceRate',
            'mobileLabel' => __('Bounce', 'wp-statistics'),
            'decimals'    => 0,
        ], $overrides);
    }

    public static function sessionDurationColumn(array $overrides = []): array
    {
        return array_merge([
            'key'         => 'sessionDuration',
            'dataField'   => 'avg_session_duration',
            'title'       => __('Avg. Duration', 'wp-statistics'),
            'type'        => 'duration',
            'priority'    => 'secondary',
            'comparable'  => true,
            'size'        => 'duration',
            'mobileLabel' => __('Duration', 'wp-statistics'),
        ], $overrides);
    }

    public static function viewsPerVisitorColumn(array $overrides = []): array
    {
        return array_merge([
            'key'                 => 'viewsPerVisitor',
            'title'               => __('Views/Visitor', 'wp-statistics'),
            'type'                => 'computed-ratio',
            'priority'            => 'secondary',
            'sortable'            => false,
            'comparable'          => true,
            'mobileLabel'         => __('V/Visitor', 'wp-statistics'),
            'numerator'           => 'views',
            'denominator'         => 'visitors',
            'previousNumerator'   => 'previous.views',
            'previousDenominator' => 'previous.visitors',
            'decimals'            => 1,
        ], $overrides);
    }

    public static function pagesPerSessionColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'pagesPerSession',
            'dataField'    => 'pages_per_session',
            'title'        => __('Pages/Session', 'wp-statistics'),
            'type'         => 'numeric',
            'priority'     => 'secondary',
            'comparable'   => true,
            'size'         => 'viewsPerSession',
            'cardPosition' => 'body',
            'decimals'     => 1,
        ], $overrides);
    }
}
