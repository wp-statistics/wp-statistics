<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

use WP_Statistics\Components\Country;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

/**
 * Report Configuration Data Provider
 *
 * Provides built-in report definitions and collects premium additions via filter.
 * Each definition describes a table report's columns, data source, and UI config.
 * React reads these to render reports without per-report JS files.
 *
 * @since 15.1.0
 */
class ReportConfigDataProvider implements LocalizeDataProviderInterface
{
    /**
     * @return string
     */
    public function getKey()
    {
        return 'reports';
    }

    /**
     * @return array
     */
    public function getData()
    {
        $builtinReports = $this->getBuiltinReports();

        return apply_filters('wp_statistics_report_definitions', $builtinReports);
    }

    /**
     * Built-in (free) report definitions.
     *
     * @return array
     */
    private function getBuiltinReports()
    {
        return [
            'devices-overview' => [
                'type'             => 'overview',
                'pageId'           => 'devices-overview',
                'title'            => __('Devices Overview', 'wp-statistics'),
                'filterGroup'      => 'devices',
                'hideFilters'      => true,
                'showFilterButton' => false,
                'queries'          => [
                    [
                        'id'          => 'metrics_top_browser',
                        'sources'     => ['visitors'],
                        'group_by'    => ['browser'],
                        'columns'     => ['browser_name', 'visitors'],
                        'per_page'    => 1,
                        'order_by'    => 'visitors',
                        'order'       => 'DESC',
                        'format'      => 'flat',
                        'show_totals' => false,
                        'compare'     => false,
                    ],
                    [
                        'id'          => 'metrics_top_os',
                        'sources'     => ['visitors'],
                        'group_by'    => ['os'],
                        'columns'     => ['os_name', 'visitors'],
                        'per_page'    => 1,
                        'order_by'    => 'visitors',
                        'order'       => 'DESC',
                        'format'      => 'flat',
                        'show_totals' => false,
                        'compare'     => false,
                    ],
                    [
                        'id'          => 'metrics_top_device',
                        'sources'     => ['visitors'],
                        'group_by'    => ['device_type'],
                        'columns'     => ['device_type_name', 'visitors'],
                        'per_page'    => 1,
                        'order_by'    => 'visitors',
                        'order'       => 'DESC',
                        'format'      => 'flat',
                        'show_totals' => false,
                        'compare'     => false,
                    ],
                    [
                        'id'          => 'top_browsers',
                        'sources'     => ['visitors'],
                        'group_by'    => ['browser'],
                        'columns'     => ['browser_name', 'visitors'],
                        'per_page'    => 5,
                        'order_by'    => 'visitors',
                        'order'       => 'DESC',
                        'format'      => 'table',
                        'show_totals' => true,
                    ],
                    [
                        'id'          => 'top_operating_systems',
                        'sources'     => ['visitors'],
                        'group_by'    => ['os'],
                        'columns'     => ['os_name', 'visitors'],
                        'per_page'    => 5,
                        'order_by'    => 'visitors',
                        'order'       => 'DESC',
                        'format'      => 'table',
                        'show_totals' => true,
                    ],
                    [
                        'id'          => 'top_device_categories',
                        'sources'     => ['visitors'],
                        'group_by'    => ['device_type'],
                        'columns'     => ['device_type_name', 'visitors'],
                        'per_page'    => 5,
                        'order_by'    => 'visitors',
                        'order'       => 'DESC',
                        'format'      => 'table',
                        'show_totals' => true,
                    ],
                ],
                'metrics'          => [
                    ['id' => 'top-browser', 'label' => __('Top Browser', 'wp-statistics'), 'queryId' => 'metrics_top_browser', 'valueField' => 'browser_name'],
                    ['id' => 'top-operating-system', 'label' => __('Top Operating System', 'wp-statistics'), 'queryId' => 'metrics_top_os', 'valueField' => 'os_name'],
                    ['id' => 'top-device-category', 'label' => __('Top Device Category', 'wp-statistics'), 'queryId' => 'metrics_top_device', 'valueField' => 'device_type_name'],
                ],
                'widgets'          => [
                    ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                    [
                        'id'            => 'top-browsers',
                        'type'          => 'bar-list',
                        'label'         => __('Top Browsers', 'wp-statistics'),
                        'defaultSize'   => 6,
                        'queryId'       => 'top_browsers',
                        'labelField'    => 'browser_name',
                        'valueField'    => 'visitors',
                        'iconType'      => 'browser',
                        'iconSlugField' => 'browser_name',
                        'columnHeaders' => ['left' => __('Browser', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                        'link'          => ['to' => '/browsers'],
                    ],
                    [
                        'id'            => 'top-operating-systems',
                        'type'          => 'bar-list',
                        'label'         => __('Top Operating Systems', 'wp-statistics'),
                        'defaultSize'   => 6,
                        'queryId'       => 'top_operating_systems',
                        'labelField'    => 'os_name',
                        'valueField'    => 'visitors',
                        'iconType'      => 'os',
                        'iconSlugField' => 'os_name',
                        'columnHeaders' => ['left' => __('Operating System', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                        'link'          => ['to' => '/operating-systems'],
                    ],
                    [
                        'id'            => 'top-device-categories',
                        'type'          => 'bar-list',
                        'label'         => __('Top Device Categories', 'wp-statistics'),
                        'defaultSize'   => 6,
                        'queryId'       => 'top_device_categories',
                        'labelField'    => 'device_type_name',
                        'valueField'    => 'visitors',
                        'columnHeaders' => ['left' => __('Device Category', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                        'link'          => ['to' => '/device-categories'],
                    ],
                ],
            ],

            'geographic-overview' => $this->getGeographicOverviewConfig(),

            'device-categories' => [
                'title'            => __('Device Categories', 'wp-statistics'),
                'context'          => 'device-categories',
                'filterGroup'      => 'visitors',
                'dataSource'       => [
                    'queryId' => 'device_categories',
                    'queries' => [
                        [
                            'id'          => 'device_categories',
                            'sources'     => ['visitors'],
                            'group_by'    => ['device_type'],
                            'columns'     => ['device_type_name', 'visitors'],
                            'format'      => 'table',
                            'show_totals' => false,
                        ],
                        [
                            'id'       => 'totals',
                            'sources'  => ['visitors'],
                            'group_by' => [],
                            'format'   => 'flat',
                            'compare'  => false,
                        ],
                    ],
                ],
                'columns'          => [
                    [
                        'key'      => 'device_type_name',
                        'title'    => __('Device Type', 'wp-statistics'),
                        'type'     => 'text',
                        'priority' => 'primary',
                        'sortable' => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'         => 'visitors',
                        'title'       => __('Visitors', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.visitors',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                ],
                'defaultSort'       => ['id' => 'visitors', 'desc' => true],
                'perPage'           => 25,
                'emptyStateMessage' => __('No device categories found for the selected period', 'wp-statistics'),
                'export'            => [
                    'sources'  => ['visitors'],
                    'group_by' => ['device_type'],
                    'columns'  => ['device_type_name'],
                ],
            ],

            'countries' => [
                'title'               => __('Countries', 'wp-statistics'),
                'context'             => 'countries',
                'filterGroup'         => 'visitors',
                'dataSource'          => [
                    'queryId'       => 'countries',
                    'queries'       => [
                        [
                            'id'          => 'countries',
                            'sources'     => ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
                            'group_by'    => ['country'],
                            'format'      => 'table',
                            'show_totals' => false,
                        ],
                        [
                            'id'       => 'totals',
                            'sources'  => ['visitors', 'views'],
                            'group_by' => [],
                            'format'   => 'flat',
                            'compare'  => false,
                        ],
                    ],
                    'columnMapping' => [
                        'country'         => 'country_name',
                        'visitors'        => 'visitors',
                        'views'           => 'views',
                        'viewsPerVisitor' => 'visitors',
                        'bounceRate'      => 'bounce_rate',
                        'sessionDuration' => 'avg_session_duration',
                    ],
                ],
                'columns'             => [
                    [
                        'key'            => 'country',
                        'title'          => __('Country', 'wp-statistics'),
                        'type'           => 'location',
                        'priority'       => 'primary',
                        'sortable'       => false,
                        'cardPosition'   => 'header',
                        'linkTo'         => '/country/$countryCode',
                        'linkParamField' => 'country_code',
                    ],
                    [
                        'key'         => 'visitors',
                        'title'       => __('Visitors', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.visitors',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'views',
                        'title'       => __('Views', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.views',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'                   => 'viewsPerVisitor',
                        'title'                 => __('Views/Visitor', 'wp-statistics'),
                        'type'                  => 'computed-ratio',
                        'priority'              => 'secondary',
                        'sortable'              => false,
                        'comparable'            => true,
                        'mobileLabel'           => __('V/Visitor', 'wp-statistics'),
                        'numerator'             => 'views',
                        'denominator'           => 'visitors',
                        'previousNumerator'     => 'previous.views',
                        'previousDenominator'   => 'previous.visitors',
                        'decimals'              => 1,
                    ],
                    [
                        'key'         => 'bounceRate',
                        'title'       => __('Bounce Rate', 'wp-statistics'),
                        'type'        => 'percentage',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.bounce_rate',
                        'size'        => 'bounceRate',
                        'mobileLabel' => __('Bounce', 'wp-statistics'),
                        'decimals'    => 0,
                    ],
                    [
                        'key'         => 'sessionDuration',
                        'title'       => __('Avg. Duration', 'wp-statistics'),
                        'type'        => 'duration',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.avg_session_duration',
                        'size'        => 'duration',
                        'mobileLabel' => __('Duration', 'wp-statistics'),
                    ],
                ],
                'defaultSort'         => ['id' => 'visitors', 'desc' => true],
                'perPage'             => 25,
                'defaultHiddenColumns' => ['bounceRate', 'sessionDuration'],
                'columnConfig'        => [
                    'baseColumns'        => ['country_code', 'country_name'],
                    'columnDependencies' => [
                        'country'         => ['country_code', 'country_name'],
                        'visitors'        => ['visitors'],
                        'views'           => ['views'],
                        'viewsPerVisitor' => ['visitors', 'views'],
                        'bounceRate'      => ['bounce_rate'],
                        'sessionDuration' => ['avg_session_duration'],
                    ],
                ],
                'defaultApiColumns'   => [
                    'country_code',
                    'country_name',
                    'visitors',
                    'views',
                    'bounce_rate',
                    'avg_session_duration',
                ],
                'emptyStateMessage'   => __('No countries found for the selected period', 'wp-statistics'),
                'export'              => [
                    'sources'  => ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
                    'group_by' => ['country'],
                    'context'  => 'countries',
                    'columns'  => ['country_code', 'country_name'],
                ],
            ],

            'referrers' => [
                'title'               => __('Referrers', 'wp-statistics'),
                'context'             => 'referrers',
                'filterGroup'         => 'referrals',
                'dataSource'          => [
                    'sources'       => ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
                    'group_by'      => ['referrer'],
                    'columnMapping' => [
                        'domain'          => 'referrer_domain',
                        'name'            => 'referrer_name',
                        'channel'         => 'referrer_channel',
                        'visitors'        => 'visitors',
                        'views'           => 'views',
                        'sessionDuration' => 'avg_session_duration',
                        'bounceRate'      => 'bounce_rate',
                        'pagesPerSession' => 'pages_per_session',
                    ],
                ],
                'columns'             => [
                    [
                        'key'         => 'domain',
                        'title'       => __('Domain', 'wp-statistics'),
                        'type'        => 'referrer',
                        'priority'    => 'primary',
                        'sortable'    => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'      => 'name',
                        'title'    => __('Source Name', 'wp-statistics'),
                        'type'     => 'text',
                        'priority' => 'secondary',
                        'sortable' => false,
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'visitors',
                        'title'       => __('Visitors', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.visitors',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'views',
                        'title'       => __('Views', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.views',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'sessionDuration',
                        'title'       => __('Avg. Duration', 'wp-statistics'),
                        'type'        => 'duration',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.avg_session_duration',
                        'size'        => 'duration',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'bounceRate',
                        'title'       => __('Bounce Rate', 'wp-statistics'),
                        'type'        => 'percentage',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.bounce_rate',
                        'size'        => 'bounceRate',
                        'cardPosition' => 'body',
                        'decimals'    => 0,
                    ],
                    [
                        'key'         => 'pagesPerSession',
                        'title'       => __('Pages/Session', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.pages_per_session',
                        'size'        => 'viewsPerSession',
                        'cardPosition' => 'body',
                        'decimals'    => 1,
                    ],
                ],
                'defaultSort'         => ['id' => 'visitors', 'desc' => true],
                'perPage'             => 25,
                'defaultHiddenColumns' => ['sessionDuration', 'bounceRate', 'pagesPerSession'],
                'columnConfig'        => [
                    'baseColumns'        => ['referrer_id', 'referrer_domain', 'referrer_name', 'referrer_channel'],
                    'columnDependencies' => [
                        'domain'          => ['referrer_domain', 'referrer_channel'],
                        'name'            => ['referrer_name'],
                        'visitors'        => ['visitors'],
                        'views'           => ['views'],
                        'sessionDuration' => ['avg_session_duration'],
                        'bounceRate'      => ['bounce_rate'],
                        'pagesPerSession' => ['pages_per_session'],
                    ],
                ],
                'defaultApiColumns'   => [
                    'referrer_id',
                    'referrer_domain',
                    'referrer_name',
                    'referrer_channel',
                    'visitors',
                    'views',
                    'avg_session_duration',
                    'bounce_rate',
                    'pages_per_session',
                ],
                'emptyStateMessage'   => __('No referrers found for the selected period', 'wp-statistics'),
                'export'              => [
                    'sources'  => ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
                    'group_by' => ['referrer'],
                    'context'  => 'referrers',
                    'columns'  => ['referrer_domain', 'referrer_name', 'referrer_channel'],
                ],
            ],

            'search-engines' => [
                'title'               => __('Search Engines', 'wp-statistics'),
                'context'             => 'search-engines',
                'filterGroup'         => 'referrals',
                'dataSource'          => [
                    'queryId'       => 'table',
                    'queries'       => [
                        [
                            'id'      => 'chart',
                            'chart'   => 'search_engine_chart',
                        ],
                        [
                            'id'          => 'table',
                            'sources'     => ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
                            'group_by'    => ['referrer'],
                            'format'      => 'table',
                            'show_totals' => false,
                            'compare'     => true,
                        ],
                    ],
                    'columnMapping' => [
                        'domain'          => 'referrer_domain',
                        'name'            => 'referrer_name',
                        'channel'         => 'referrer_channel',
                        'visitors'        => 'visitors',
                        'views'           => 'views',
                        'sessionDuration' => 'avg_session_duration',
                        'bounceRate'      => 'bounce_rate',
                        'pagesPerSession' => 'pages_per_session',
                    ],
                ],
                'columns'             => [
                    [
                        'key'          => 'domain',
                        'title'        => __('Domain', 'wp-statistics'),
                        'type'         => 'referrer',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'          => 'name',
                        'title'        => __('Source Name', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'secondary',
                        'sortable'     => false,
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'visitors',
                        'title'       => __('Visitors', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.visitors',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'views',
                        'title'       => __('Views', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.views',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'sessionDuration',
                        'title'       => __('Avg. Duration', 'wp-statistics'),
                        'type'        => 'duration',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.avg_session_duration',
                        'size'        => 'duration',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'bounceRate',
                        'title'       => __('Bounce Rate', 'wp-statistics'),
                        'type'        => 'percentage',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.bounce_rate',
                        'size'        => 'bounceRate',
                        'cardPosition' => 'body',
                        'decimals'    => 0,
                    ],
                    [
                        'key'         => 'pagesPerSession',
                        'title'       => __('Pages/Session', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.pages_per_session',
                        'size'        => 'viewsPerSession',
                        'cardPosition' => 'body',
                        'decimals'    => 1,
                    ],
                ],
                'defaultSort'         => ['id' => 'visitors', 'desc' => true],
                'perPage'             => 25,
                'defaultHiddenColumns' => ['sessionDuration', 'bounceRate', 'pagesPerSession'],
                'columnConfig'        => [
                    'baseColumns'        => ['referrer_id', 'referrer_domain', 'referrer_name', 'referrer_channel'],
                    'columnDependencies' => [
                        'domain'          => ['referrer_domain', 'referrer_channel'],
                        'name'            => ['referrer_name'],
                        'visitors'        => ['visitors'],
                        'views'           => ['views'],
                        'sessionDuration' => ['avg_session_duration'],
                        'bounceRate'      => ['bounce_rate'],
                        'pagesPerSession' => ['pages_per_session'],
                    ],
                ],
                'defaultApiColumns'   => [
                    'referrer_id',
                    'referrer_domain',
                    'referrer_name',
                    'referrer_channel',
                    'visitors',
                    'views',
                    'avg_session_duration',
                    'bounce_rate',
                    'pages_per_session',
                ],
                'chart'               => [
                    'queryId'          => 'chart',
                    'title'            => __('Search Engines', 'wp-statistics'),
                    'compareMetricKey' => 'total',
                ],
                'emptyStateMessage'   => __('No search engine referrers found for the selected period', 'wp-statistics'),
                'export'              => [
                    'sources'  => ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
                    'group_by' => ['referrer'],
                    'context'  => 'search-engines',
                    'columns'  => ['referrer_domain', 'referrer_name', 'referrer_channel'],
                ],
            ],

            'source-categories' => [
                'title'               => __('Source Categories', 'wp-statistics'),
                'context'             => 'source-categories',
                'filterGroup'         => 'referrals',
                'hideFilters'         => true,
                'dataSource'          => [
                    'queryId'       => 'table',
                    'queries'       => [
                        [
                            'id'      => 'chart',
                            'chart'   => 'source_category_chart',
                        ],
                        [
                            'id'          => 'table',
                            'sources'     => ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
                            'group_by'    => ['referrer_channel'],
                            'format'      => 'table',
                            'show_totals' => false,
                            'compare'     => true,
                        ],
                    ],
                    'columnMapping' => [
                        'sourceCategory'  => 'referrer_channel',
                        'sessionDuration' => 'avg_session_duration',
                        'bounceRate'      => 'bounce_rate',
                        'pagesPerSession' => 'pages_per_session',
                    ],
                ],
                'columns'             => [
                    [
                        'key'          => 'referrer_channel',
                        'title'        => __('Source Category', 'wp-statistics'),
                        'type'         => 'source-category',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'         => 'visitors',
                        'title'       => __('Visitors', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.visitors',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'views',
                        'title'       => __('Views', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'primary',
                        'comparable'  => true,
                        'previousKey' => 'previous.views',
                        'size'        => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'sessionDuration',
                        'title'       => __('Avg. Duration', 'wp-statistics'),
                        'type'        => 'duration',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.avg_session_duration',
                        'size'        => 'duration',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'         => 'bounceRate',
                        'title'       => __('Bounce Rate', 'wp-statistics'),
                        'type'        => 'percentage',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.bounce_rate',
                        'size'        => 'bounceRate',
                        'cardPosition' => 'body',
                        'decimals'    => 0,
                    ],
                    [
                        'key'         => 'pagesPerSession',
                        'title'       => __('Pages/Session', 'wp-statistics'),
                        'type'        => 'numeric',
                        'priority'    => 'secondary',
                        'comparable'  => true,
                        'previousKey' => 'previous.pages_per_session',
                        'size'        => 'viewsPerSession',
                        'cardPosition' => 'body',
                        'decimals'    => 1,
                    ],
                ],
                'defaultSort'         => ['id' => 'visitors', 'desc' => true],
                'perPage'             => 25,
                'defaultHiddenColumns' => ['sessionDuration', 'bounceRate', 'pagesPerSession'],
                'columnConfig'        => [
                    'baseColumns'        => ['referrer_channel'],
                    'columnDependencies' => [
                        'referrer_channel' => ['referrer_channel'],
                        'visitors'         => ['visitors'],
                        'views'            => ['views'],
                        'sessionDuration'  => ['avg_session_duration'],
                        'bounceRate'       => ['bounce_rate'],
                        'pagesPerSession'  => ['pages_per_session'],
                    ],
                ],
                'defaultApiColumns'   => [
                    'referrer_channel',
                    'visitors',
                    'views',
                    'avg_session_duration',
                    'bounce_rate',
                    'pages_per_session',
                ],
                'chart'               => [
                    'queryId'          => 'chart',
                    'title'            => __('Source Categories', 'wp-statistics'),
                    'compareMetricKey' => 'total',
                ],
                'emptyStateMessage'   => __('No source categories found for the selected period', 'wp-statistics'),
                'export'              => [
                    'sources'  => ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
                    'group_by' => ['referrer_channel'],
                    'context'  => 'source-categories',
                    'columns'  => ['referrer_channel'],
                ],
            ],
        ];
    }

    /**
     * Geographic overview page config.
     *
     * Conditionally includes top-regions widget based on user's timezone country.
     *
     * @return array
     */
    private function getGeographicOverviewConfig()
    {
        $userCountry     = Country::getByTimeZone();
        $userCountryName = !empty($userCountry) ? Country::getName($userCountry) : '';
        $showRegions     = !empty($userCountry) && $userCountry !== 'US';

        // Build queries — conditionally include top_regions for non-US users
        $queries = [
            [
                'id'          => 'metrics_top_country',
                'sources'     => ['visitors'],
                'group_by'    => ['country'],
                'columns'     => ['country_code', 'country_name', 'visitors'],
                'per_page'    => 1,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'flat',
                'show_totals' => false,
                'compare'     => false,
            ],
            [
                'id'          => 'metrics_top_region',
                'sources'     => ['visitors'],
                'group_by'    => ['region'],
                'columns'     => ['region_name', 'visitors'],
                'per_page'    => 1,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'flat',
                'show_totals' => false,
                'compare'     => false,
            ],
            [
                'id'          => 'metrics_top_city',
                'sources'     => ['visitors'],
                'group_by'    => ['city'],
                'columns'     => ['city_name', 'visitors'],
                'per_page'    => 1,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'flat',
                'show_totals' => false,
                'compare'     => false,
            ],
            [
                'id'          => 'countries_map',
                'sources'     => ['visitors', 'views'],
                'group_by'    => ['country'],
                'columns'     => ['country_code', 'country_name', 'visitors', 'views'],
                'per_page'    => 250,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
                'compare'     => false,
            ],
            [
                'id'          => 'top_countries',
                'sources'     => ['visitors'],
                'group_by'    => ['country'],
                'columns'     => ['country_code', 'country_name', 'visitors'],
                'per_page'    => 5,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
            ],
            [
                'id'          => 'top_cities',
                'sources'     => ['visitors'],
                'group_by'    => ['city'],
                'columns'     => ['city_name', 'country_code', 'country_name', 'visitors'],
                'per_page'    => 5,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
            ],
            [
                'id'          => 'top_european_countries',
                'sources'     => ['visitors'],
                'group_by'    => ['country'],
                'columns'     => ['country_code', 'country_name', 'visitors'],
                'filters'     => [
                    [
                        'key'      => 'continent',
                        'operator' => 'is',
                        'value'    => 'EU',
                    ],
                ],
                'per_page'    => 5,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
            ],
            [
                'id'          => 'top_us_states',
                'sources'     => ['visitors'],
                'group_by'    => ['region'],
                'columns'     => ['region_name', 'country_code', 'country_name', 'visitors'],
                'filters'     => [
                    [
                        'key'      => 'country',
                        'operator' => 'is',
                        'value'    => 'US',
                    ],
                ],
                'per_page'    => 5,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
            ],
            [
                'id'          => 'visitors_by_continent',
                'sources'     => ['visitors'],
                'group_by'    => ['continent'],
                'columns'     => ['continent', 'continent_name', 'visitors'],
                'per_page'    => 7,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
            ],
        ];

        // Add top_regions query when user country is detected and not US
        if ($showRegions) {
            $queries[] = [
                'id'          => 'top_regions',
                'sources'     => ['visitors'],
                'group_by'    => ['region'],
                'columns'     => ['region_name', 'country_code', 'country_name', 'visitors'],
                'filters'     => [
                    [
                        'key'      => 'country',
                        'operator' => 'is',
                        'value'    => $userCountry,
                    ],
                ],
                'per_page'    => 5,
                'order_by'    => 'visitors',
                'order'       => 'DESC',
                'format'      => 'table',
                'show_totals' => true,
            ];
        }

        // Build widgets
        $widgets = [
            ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
            [
                'id'          => 'global-map',
                'type'        => 'map',
                'label'       => __('Global Visitor Distribution', 'wp-statistics'),
                'defaultSize' => 12,
                'queryId'     => 'countries_map',
                'mapConfig'   => [
                    'title'              => __('Global Visitor Distribution', 'wp-statistics'),
                    'metric'             => 'visitors',
                    'enableCityDrilldown' => true,
                    'enableMetricToggle' => true,
                    'availableMetrics'   => [
                        ['value' => 'visitors', 'label' => __('Visitors', 'wp-statistics')],
                        ['value' => 'views', 'label' => __('Views', 'wp-statistics')],
                    ],
                ],
            ],
            [
                'id'            => 'top-countries',
                'type'          => 'bar-list',
                'label'         => __('Top Countries', 'wp-statistics'),
                'defaultSize'   => 6,
                'queryId'       => 'top_countries',
                'labelField'    => 'country_name',
                'valueField'    => 'visitors',
                'iconType'      => 'country',
                'iconSlugField' => 'country_code',
                'columnHeaders' => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                'linkTo'        => '/country/$countryCode',
                'linkParamField' => 'country_code',
                'link'          => ['to' => '/countries'],
            ],
            [
                'id'            => 'top-cities',
                'type'          => 'bar-list',
                'label'         => __('Top Cities', 'wp-statistics'),
                'defaultSize'   => 6,
                'queryId'       => 'top_cities',
                'labelField'    => 'city_name',
                'valueField'    => 'visitors',
                'iconType'      => 'country',
                'iconSlugField' => 'country_code',
                'columnHeaders' => ['left' => __('City', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                'link'          => ['to' => '/cities'],
            ],
            [
                'id'            => 'european-countries',
                'type'          => 'bar-list',
                'label'         => __('Top European Countries', 'wp-statistics'),
                'defaultSize'   => 6,
                'queryId'       => 'top_european_countries',
                'labelField'    => 'country_name',
                'valueField'    => 'visitors',
                'iconType'      => 'country',
                'iconSlugField' => 'country_code',
                'columnHeaders' => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                'linkTo'        => '/country/$countryCode',
                'linkParamField' => 'country_code',
                'link'          => ['to' => '/european-countries'],
            ],
            [
                'id'            => 'us-states',
                'type'          => 'bar-list',
                'label'         => __('Top US States', 'wp-statistics'),
                'defaultSize'   => 6,
                'queryId'       => 'top_us_states',
                'labelField'    => 'region_name',
                'valueField'    => 'visitors',
                'columnHeaders' => ['left' => __('State', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                'link'          => ['to' => '/us-states'],
            ],
            [
                'id'            => 'visitors-by-continent',
                'type'          => 'bar-list',
                'label'         => __('Visitors by Continent', 'wp-statistics'),
                'defaultSize'   => 6,
                'queryId'       => 'visitors_by_continent',
                'labelField'    => 'continent_name',
                'valueField'    => 'visitors',
                'columnHeaders' => ['left' => __('Continent', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
            ],
        ];

        // Add top-regions widget when user country is detected and not US
        if ($showRegions) {
            $topRegionsLabel = $userCountryName
                // translators: %s is the country name
                ? sprintf(__('Top Regions of %s', 'wp-statistics'), $userCountryName)
                : __('Top Regions', 'wp-statistics');

            $widgets[] = [
                'id'            => 'top-regions',
                'type'          => 'bar-list',
                'label'         => $topRegionsLabel,
                'defaultSize'   => 6,
                'queryId'       => 'top_regions',
                'labelField'    => 'region_name',
                'valueField'    => 'visitors',
                'columnHeaders' => ['left' => __('Region', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                'link'          => ['to' => '/country-regions'],
            ];
        }

        return [
            'type'             => 'overview',
            'pageId'           => 'geographic-overview',
            'title'            => __('Geographic Overview', 'wp-statistics'),
            'filterGroup'      => 'geographic',
            'hideFilters'      => true,
            'showFilterButton' => false,
            'queries'          => $queries,
            'metrics'          => [
                ['id' => 'topCountry', 'label' => __('Top Country', 'wp-statistics'), 'queryId' => 'metrics_top_country', 'valueField' => 'country_name'],
                ['id' => 'topRegion', 'label' => __('Top Region', 'wp-statistics'), 'queryId' => 'metrics_top_region', 'valueField' => 'region_name'],
                ['id' => 'topCity', 'label' => __('Top City', 'wp-statistics'), 'queryId' => 'metrics_top_city', 'valueField' => 'city_name'],
            ],
            'widgets'          => $widgets,
        ];
    }
}
