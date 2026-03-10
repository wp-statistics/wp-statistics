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
        $userCountry = Country::getByTimeZone();

        return [
            'overview' => $this->getOverviewConfig(),

            'devices-overview' => [
                'type'             => 'overview',
                'pageId'           => 'devices-overview',
                'title'            => __('Devices Overview', 'wp-statistics'),
                'filterGroup'      => 'devices',
                'hideFilters'      => true,
                'showFilterButton' => false,
                'queries'          => [
                    $this->topOneQuery('metrics_top_browser', ['browser'], ['browser_name', 'visitors']),
                    $this->topOneQuery('metrics_top_os', ['os'], ['os_name', 'visitors']),
                    $this->topOneQuery('metrics_top_device', ['device_type'], ['device_type_name', 'visitors']),
                    $this->topBrowsersQuery(),
                    $this->topOsQuery(),
                    $this->topDeviceCategoriesQuery(),
                ],
                'metrics'          => [
                    ['id' => 'top-browser', 'label' => __('Top Browser', 'wp-statistics'), 'queryId' => 'metrics_top_browser', 'valueField' => 'browser_name'],
                    ['id' => 'top-operating-system', 'label' => __('Top Operating System', 'wp-statistics'), 'queryId' => 'metrics_top_os', 'valueField' => 'os_name'],
                    ['id' => 'top-device-category', 'label' => __('Top Device Category', 'wp-statistics'), 'queryId' => 'metrics_top_device', 'valueField' => 'device_type_name'],
                ],
                'widgets'          => [
                    ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                    $this->topBrowsersWidget(['defaultSize' => 6]),
                    $this->topOsWidget(['defaultSize' => 6]),
                    $this->topDeviceCategoriesWidget(['defaultSize' => 6]),
                ],
            ],

            'geographic-overview' => $this->getGeographicOverviewConfig($userCountry),

            'referrals-overview' => $this->getReferralsOverviewConfig(),

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
                    $this->visitorsColumn(),
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
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    $this->viewsPerVisitorColumn(),
                    $this->bounceRateColumn(),
                    $this->sessionDurationColumn(),
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
                        'key'       => 'name',
                        'dataField' => 'referrer_name',
                        'title'     => __('Source Name', 'wp-statistics'),
                        'type'      => 'text',
                        'priority'  => 'secondary',
                        'sortable'  => false,
                        'cardPosition' => 'body',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    $this->sessionDurationColumn(['cardPosition' => 'body']),
                    $this->bounceRateColumn(['cardPosition' => 'body']),
                    $this->pagesPerSessionColumn(),
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
                'headerFilter'        => ['type' => 'search-type'],
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
                        'dataField'    => 'referrer_name',
                        'title'        => __('Source Name', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'secondary',
                        'sortable'     => false,
                        'cardPosition' => 'body',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    $this->sessionDurationColumn(['cardPosition' => 'body']),
                    $this->bounceRateColumn(['cardPosition' => 'body']),
                    $this->pagesPerSessionColumn(),
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

            'visitors-overview'      => $this->getVisitorsOverviewConfig(),
            'page-insights-overview' => $this->getPageInsightsOverviewConfig(),

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
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    $this->sessionDurationColumn(['cardPosition' => 'body']),
                    $this->bounceRateColumn(['cardPosition' => 'body']),
                    $this->pagesPerSessionColumn(),
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

            '404-pages' => [
                'title'            => __('404 Pages', 'wp-statistics'),
                'context'          => '404_pages',
                'filterGroup'      => 'views',
                'hideFilters'      => true,
                'dataSource'       => [
                    'queryId' => '404_pages',
                    'queries' => [
                        [
                            'id'          => '404_pages',
                            'sources'     => ['views'],
                            'group_by'    => ['page'],
                            'columns'     => ['page_uri', 'views'],
                            'format'      => 'table',
                            'show_totals' => false,
                            'compare'     => false,
                            'filters'     => [['key' => 'post_type', 'operator' => 'is', 'value' => '404']],
                        ],
                    ],
                ],
                'columns'          => [
                    [
                        'key'          => 'page_uri',
                        'title'        => __('URL', 'wp-statistics'),
                        'type'         => 'uri',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'          => 'views',
                        'title'        => __('Views', 'wp-statistics'),
                        'type'         => 'numeric',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'size'         => 'views',
                        'cardPosition' => 'body',
                    ],
                ],
                'defaultSort'       => ['id' => 'views', 'desc' => true],
                'perPage'           => 20,
                'emptyStateMessage' => __('No 404 pages found for the selected period', 'wp-statistics'),
            ],

            'operating-systems' => [
                'title'            => __('Operating Systems', 'wp-statistics'),
                'context'          => 'operating-systems',
                'filterGroup'      => 'visitors',
                'dataSource'       => [
                    'queryId' => 'operating_systems',
                    'queries' => [
                        [
                            'id'          => 'operating_systems',
                            'sources'     => ['visitors'],
                            'group_by'    => ['os'],
                            'columns'     => ['os_name', 'os_id', 'visitors'],
                            'format'      => 'table',
                            'show_totals' => false,
                        ],
                    ],
                ],
                'columns'          => [
                    [
                        'key'          => 'os_name',
                        'title'        => __('Operating System', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    $this->visitorsColumn(),
                ],
                'defaultSort'       => ['id' => 'visitors', 'desc' => true],
                'perPage'           => 25,
                'emptyStateMessage' => __('No operating systems found for the selected period', 'wp-statistics'),
            ],

            'cities' => [
                'title'                => __('Cities', 'wp-statistics'),
                'context'              => 'cities',
                'filterGroup'          => 'visitors',
                'dataSource'           => [
                    'queryId' => 'cities',
                    'queries' => [
                        [
                            'id'          => 'cities',
                            'sources'     => ['visitors', 'views'],
                            'group_by'    => ['city'],
                            'format'      => 'table',
                            'show_totals' => false,
                        ],
                    ],
                    'columnMapping' => [
                        'country' => 'country_name',
                    ],
                ],
                'columns'              => [
                    [
                        'key'          => 'city_name',
                        'title'        => __('City', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'          => 'city_region_name',
                        'title'        => __('Region', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'secondary',
                        'sortable'     => false,
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'            => 'country',
                        'title'          => __('Country', 'wp-statistics'),
                        'type'           => 'location',
                        'priority'       => 'secondary',
                        'sortable'       => false,
                        'cardPosition'   => 'body',
                        'linkTo'         => '/country/$countryCode',
                        'linkParamField' => 'country_code',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                ],
                'defaultSort'          => ['id' => 'visitors', 'desc' => true],
                'perPage'              => 25,
                'defaultHiddenColumns' => [],
                'columnConfig'         => [
                    'baseColumns'        => ['city_id', 'city_name'],
                    'columnDependencies' => [
                        'city_name'        => ['city_id', 'city_name'],
                        'city_region_name' => ['city_region_name'],
                        'country'          => ['country_code', 'country_name'],
                        'visitors'         => ['visitors'],
                        'views'            => ['views'],
                    ],
                ],
                'defaultApiColumns'    => [
                    'city_id',
                    'city_name',
                    'city_region_name',
                    'country_code',
                    'country_name',
                    'visitors',
                    'views',
                ],
                'emptyStateMessage'    => __('No cities found for the selected period', 'wp-statistics'),
            ],

            'timezones' => [
                'title'                => __('Timezones', 'wp-statistics'),
                'context'              => 'timezones',
                'filterGroup'          => 'visitors',
                'dataSource'           => [
                    'queryId' => 'timezones',
                    'queries' => [
                        [
                            'id'          => 'timezones',
                            'sources'     => ['visitors', 'views'],
                            'group_by'    => ['timezone'],
                            'format'      => 'table',
                            'show_totals' => false,
                        ],
                    ],
                ],
                'columns'              => [
                    [
                        'key'          => 'timezone_name',
                        'title'        => __('Timezone', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                ],
                'defaultSort'          => ['id' => 'visitors', 'desc' => true],
                'perPage'              => 25,
                'defaultHiddenColumns' => [],
                'columnConfig'         => [
                    'baseColumns'        => ['timezone_id', 'timezone_name', 'timezone_offset'],
                    'columnDependencies' => [
                        'timezone_name' => ['timezone_id', 'timezone_name', 'timezone_offset'],
                        'visitors'      => ['visitors'],
                        'views'         => ['views'],
                    ],
                ],
                'defaultApiColumns'    => [
                    'timezone_id',
                    'timezone_name',
                    'timezone_offset',
                    'visitors',
                    'views',
                ],
                'emptyStateMessage'    => __('No timezones found for the selected period', 'wp-statistics'),
            ],

            'us-states'           => $this->getUsStatesConfig(),
            'european-countries'  => $this->getEuropeanCountriesConfig(),
            'country-regions'     => $this->getCountryRegionsConfig($userCountry),
            'social-media'        => $this->getSocialMediaConfig(),

            'search-terms' => [
                'title'            => __('Search Terms', 'wp-statistics'),
                'context'          => 'search_terms',
                'filterGroup'      => 'visitors',
                'hideFilters'      => true,
                'dataSource'       => [
                    'sources'  => ['searches'],
                    'group_by' => ['search_term'],
                ],
                'columns'          => [
                    [
                        'key'          => 'search_term',
                        'title'        => __('Search Term', 'wp-statistics'),
                        'type'         => 'text',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'          => 'searches',
                        'title'        => __('Searches', 'wp-statistics'),
                        'type'         => 'numeric',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'size'         => 'views',
                        'cardPosition' => 'body',
                    ],
                ],
                'defaultSort'      => ['id' => 'searches', 'desc' => true],
                'defaultApiColumns' => [
                    'search_term',
                    'searches',
                ],
                'emptyStateMessage' => __('No data available for the selected period', 'wp-statistics'),
            ],

            'top-pages' => [
                'title'                => __('Top Pages', 'wp-statistics'),
                'context'              => 'top_pages',
                'filterGroup'          => 'views',
                'customFilters'        => ['page', 'resource_id', 'post_type', 'author'],
                'dataSource'           => [
                    'sources'       => ['visitors', 'views', 'bounce_rate', 'avg_time_on_page', 'published_content'],
                    'group_by'      => ['page'],
                    'columnMapping' => [
                        'bounceRate'      => 'bounce_rate',
                        'sessionDuration' => 'avg_time_on_page',
                        'publishedDate'   => 'published_date',
                    ],
                ],
                'columns'              => [
                    [
                        'key'          => 'page',
                        'title'        => __('Page', 'wp-statistics'),
                        'type'         => 'page-link',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    [
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
                    ],
                    [
                        'key'          => 'bounceRate',
                        'dataField'    => 'bounce_rate',
                        'title'        => __('Bounce Rate', 'wp-statistics'),
                        'type'         => 'percentage',
                        'priority'     => 'secondary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.bounce_rate',
                        'mobileLabel'  => __('Bounce', 'wp-statistics'),
                    ],
                    [
                        'key'          => 'sessionDuration',
                        'dataField'    => 'avg_time_on_page',
                        'title'        => __('Avg. Time on Page', 'wp-statistics'),
                        'type'         => 'duration',
                        'priority'     => 'secondary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.avg_time_on_page',
                        'mobileLabel'  => __('Time on Page', 'wp-statistics'),
                    ],
                    [
                        'key'          => 'publishedDate',
                        'dataField'    => 'published_date',
                        'title'        => __('Published Date', 'wp-statistics'),
                        'type'         => 'date',
                        'priority'     => 'secondary',
                        'mobileLabel'  => __('Published', 'wp-statistics'),
                    ],
                ],
                'defaultSort'          => ['id' => 'views', 'desc' => true],
                'defaultHiddenColumns'      => ['viewsPerVisitor', 'bounceRate', 'publishedDate'],
                'defaultComparisonColumns'  => ['visitors'],
                'columnConfig'         => [
                    'baseColumns'        => ['page_uri', 'page_title'],
                    'columnDependencies' => [
                        'page'            => ['page_uri', 'page_title', 'page_wp_id', 'page_type', 'resource_id'],
                        'visitors'        => ['visitors'],
                        'views'           => ['views'],
                        'viewsPerVisitor' => ['visitors', 'views'],
                        'bounceRate'      => ['bounce_rate'],
                        'sessionDuration' => ['avg_time_on_page'],
                        'publishedDate'   => ['published_date'],
                    ],
                ],
                'defaultApiColumns'    => [
                    'page_uri',
                    'page_title',
                    'page_wp_id',
                    'page_type',
                    'resource_id',
                    'visitors',
                    'views',
                    'bounce_rate',
                    'avg_time_on_page',
                    'published_date',
                ],
                'emptyStateMessage'    => __('No pages found for the selected period', 'wp-statistics'),
            ],

            'top-authors' => [
                'title'                => __('Top Authors', 'wp-statistics'),
                'context'              => 'top_authors',
                'filterGroup'          => 'content',
                'customFilters'        => ['post_type'],
                'dataSource'           => [
                    'sources'       => ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page'],
                    'group_by'      => ['author'],
                    'columnMapping' => [
                        'published'       => 'published_content',
                        'bounceRate'      => 'bounce_rate',
                        'sessionDuration' => 'avg_time_on_page',
                    ],
                ],
                'columns'              => [
                    [
                        'key'          => 'author',
                        'title'        => __('Author', 'wp-statistics'),
                        'type'         => 'author',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    [
                        'key'          => 'published',
                        'dataField'    => 'published_content',
                        'title'        => __('Published', 'wp-statistics'),
                        'type'         => 'numeric',
                        'priority'     => 'primary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.published_content',
                        'size'         => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'                 => 'viewsPerContent',
                        'title'               => __('Views/Content', 'wp-statistics'),
                        'type'                => 'computed-ratio',
                        'priority'            => 'secondary',
                        'sortable'            => false,
                        'comparable'          => true,
                        'mobileLabel'         => __('V/Content', 'wp-statistics'),
                        'numerator'           => 'views',
                        'denominator'         => 'published_content',
                        'previousNumerator'   => 'previous.views',
                        'previousDenominator' => 'previous.published_content',
                    ],
                    [
                        'key'          => 'bounceRate',
                        'dataField'    => 'bounce_rate',
                        'title'        => __('Bounce Rate', 'wp-statistics'),
                        'type'         => 'percentage',
                        'priority'     => 'secondary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.bounce_rate',
                        'mobileLabel'  => __('Bounce', 'wp-statistics'),
                    ],
                    [
                        'key'          => 'sessionDuration',
                        'dataField'    => 'avg_time_on_page',
                        'title'        => __('Avg. Time on Page', 'wp-statistics'),
                        'type'         => 'duration',
                        'priority'     => 'secondary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.avg_time_on_page',
                        'mobileLabel'  => __('Time on Page', 'wp-statistics'),
                    ],
                ],
                'defaultSort'              => ['id' => 'views', 'desc' => true],
                'defaultHiddenColumns'     => ['viewsPerContent', 'bounceRate'],
                'defaultComparisonColumns' => ['visitors'],
                'columnConfig'             => [
                    'baseColumns'        => ['author_id', 'author_name', 'author_avatar'],
                    'columnDependencies' => [
                        'author'          => ['author_id', 'author_name', 'author_avatar'],
                        'visitors'        => ['visitors'],
                        'views'           => ['views'],
                        'published'       => ['published_content'],
                        'viewsPerContent' => ['views', 'published_content'],
                        'bounceRate'      => ['bounce_rate'],
                        'sessionDuration' => ['avg_time_on_page'],
                    ],
                ],
                'defaultApiColumns'    => [
                    'author_id',
                    'author_name',
                    'author_avatar',
                    'visitors',
                    'views',
                    'published_content',
                    'bounce_rate',
                    'avg_time_on_page',
                ],
                'emptyStateMessage'    => __('No authors found for the selected period', 'wp-statistics'),
            ],

            'top-categories' => [
                'title'                => __('Top Categories', 'wp-statistics'),
                'context'              => 'top_categories',
                'filterGroup'          => 'content',
                'hideFilters'          => true,
                'headerFilter'         => [
                    'type'           => 'taxonomy',
                    'premiumOnly'    => true,
                    'apiFilterField' => 'taxonomy_type',
                ],
                'dataSource'           => [
                    'sources'       => ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page'],
                    'group_by'      => ['taxonomy'],
                    'columnMapping' => [
                        'published'       => 'published_content',
                        'bounceRate'      => 'bounce_rate',
                        'sessionDuration' => 'avg_time_on_page',
                    ],
                ],
                'columns'              => [
                    [
                        'key'          => 'term',
                        'title'        => __('Term Name', 'wp-statistics'),
                        'type'         => 'term',
                        'priority'     => 'primary',
                        'sortable'     => false,
                        'cardPosition' => 'header',
                    ],
                    $this->visitorsColumn(),
                    $this->viewsColumn(),
                    [
                        'key'          => 'published',
                        'dataField'    => 'published_content',
                        'title'        => __('Published', 'wp-statistics'),
                        'type'         => 'numeric',
                        'priority'     => 'primary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.published_content',
                        'size'         => 'views',
                        'cardPosition' => 'body',
                    ],
                    [
                        'key'                 => 'viewsPerContent',
                        'title'               => __('Views/Content', 'wp-statistics'),
                        'type'                => 'computed-ratio',
                        'priority'            => 'secondary',
                        'sortable'            => false,
                        'comparable'          => true,
                        'mobileLabel'         => __('V/Content', 'wp-statistics'),
                        'numerator'           => 'views',
                        'denominator'         => 'published_content',
                        'previousNumerator'   => 'previous.views',
                        'previousDenominator' => 'previous.published_content',
                    ],
                    [
                        'key'          => 'bounceRate',
                        'dataField'    => 'bounce_rate',
                        'title'        => __('Bounce Rate', 'wp-statistics'),
                        'type'         => 'percentage',
                        'priority'     => 'secondary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.bounce_rate',
                        'mobileLabel'  => __('Bounce', 'wp-statistics'),
                    ],
                    [
                        'key'          => 'sessionDuration',
                        'dataField'    => 'avg_time_on_page',
                        'title'        => __('Avg. Time on Page', 'wp-statistics'),
                        'type'         => 'duration',
                        'priority'     => 'secondary',
                        'comparable'   => true,
                        'previousKey'  => 'previous.avg_time_on_page',
                        'mobileLabel'  => __('Time on Page', 'wp-statistics'),
                    ],
                ],
                'defaultSort'              => ['id' => 'views', 'desc' => true],
                'defaultHiddenColumns'     => ['viewsPerContent', 'bounceRate'],
                'defaultComparisonColumns' => ['visitors'],
                'columnConfig'             => [
                    'baseColumns'        => ['term_id', 'term_name'],
                    'columnDependencies' => [
                        'term'            => ['term_id', 'term_name'],
                        'visitors'        => ['visitors'],
                        'views'           => ['views'],
                        'published'       => ['published_content'],
                        'viewsPerContent' => ['views', 'published_content'],
                        'bounceRate'      => ['bounce_rate'],
                        'sessionDuration' => ['avg_time_on_page'],
                    ],
                ],
                'defaultApiColumns'    => [
                    'term_id',
                    'term_name',
                    'visitors',
                    'views',
                    'published_content',
                    'bounce_rate',
                    'avg_time_on_page',
                ],
                'emptyStateMessage'    => __('No categories found for the selected period', 'wp-statistics'),
            ],

            'author-pages' => [
                'title'            => __('Author Pages', 'wp-statistics'),
                'context'          => 'page-insights',
                'filterGroup'      => 'content',
                'dataSource'       => [
                    'queryId' => 'author_pages',
                    'queries' => [
                        [
                            'id'          => 'author_pages',
                            'sources'     => ['views'],
                            'group_by'    => ['page'],
                            'format'      => 'table',
                            'show_totals' => false,
                            'filters'     => [['key' => 'post_type', 'operator' => 'is', 'value' => 'author_archive']],
                        ],
                    ],
                ],
                'columns'          => [
                    [
                        'key'          => 'page',
                        'title'        => __('Author', 'wp-statistics'),
                        'type'         => 'page-link',
                        'sortable'     => false,
                        'priority'     => 'primary',
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'          => 'views',
                        'title'        => __('Views', 'wp-statistics'),
                        'type'         => 'numeric',
                        'sortable'     => false,
                        'priority'     => 'primary',
                        'cardPosition' => 'body',
                    ],
                ],
                'defaultSort'      => ['id' => 'views', 'desc' => true],
                'emptyStateMessage' => __('No author pages found for the selected period', 'wp-statistics'),
            ],

            'category-pages' => [
                'title'            => __('Category Pages', 'wp-statistics'),
                'context'          => 'page-insights',
                'filterGroup'      => 'categories',
                'hideFilters'      => true,
                'headerFilter'     => [
                    'type'           => 'taxonomy',
                    'apiFilterField' => 'post_type',
                ],
                'dataSource'       => [
                    'sources'  => ['views'],
                    'group_by' => ['page'],
                ],
                'columns'          => [
                    [
                        'key'          => 'page',
                        'title'        => __('Term Page', 'wp-statistics'),
                        'type'         => 'page-link',
                        'sortable'     => false,
                        'priority'     => 'primary',
                        'cardPosition' => 'header',
                    ],
                    [
                        'key'          => 'views',
                        'title'        => __('Views', 'wp-statistics'),
                        'type'         => 'numeric',
                        'sortable'     => false,
                        'priority'     => 'primary',
                        'cardPosition' => 'body',
                    ],
                ],
                'defaultSort'       => ['id' => 'views', 'desc' => true],
                'emptyStateMessage' => __('No term pages found for the selected period', 'wp-statistics'),
            ],

            'single-category' => $this->getSingleCategoryConfig(),
            'single-content'  => $this->getSingleContentConfig(),
            'single-url'      => $this->getSingleUrlConfig(),

            'authors-overview'    => $this->getAuthorsOverviewConfig(),
            'content-overview'    => $this->getContentOverviewConfig(),
            'categories-overview' => $this->getCategoriesOverviewConfig(),

            'visitors'            => $this->getVisitorsConfig(),
            'top-visitors'        => $this->getTopVisitorsConfig(),
            'referred-visitors'   => $this->getReferredVisitorsConfig(),
            'logged-in-users'     => $this->getLoggedInUsersConfig(),
        ];
    }

    /**
     * US States report config.
     *
     * @return array
     */
    private function getUsStatesConfig()
    {
        return [
            'title'                => __('US States', 'wp-statistics'),
            'context'              => 'us-states',
            'filterGroup'          => 'visitors',
            'dataSource'           => [
                'queryId' => 'us_states',
                'queries' => [
                    [
                        'id'          => 'us_states',
                        'sources'     => ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
                        'group_by'    => ['region'],
                        'format'      => 'table',
                        'show_totals' => false,
                        'filters'     => [['key' => 'country', 'operator' => 'is', 'value' => 'US']],
                    ],
                ],
                'columnMapping' => [
                    'region'          => 'region_name',
                    'bounceRate'      => 'bounce_rate',
                    'sessionDuration' => 'avg_session_duration',
                ],
            ],
            'columns'              => [
                [
                    'key'          => 'region',
                    'dataField'    => 'region_name',
                    'title'        => __('State', 'wp-statistics'),
                    'type'         => 'text',
                    'priority'     => 'primary',
                    'sortable'     => false,
                    'cardPosition' => 'header',
                ],
                $this->visitorsColumn(),
                $this->viewsColumn(),
                $this->viewsPerVisitorColumn(),
                $this->bounceRateColumn(),
                $this->sessionDurationColumn(),
            ],
            'defaultSort'          => ['id' => 'visitors', 'desc' => true],
            'perPage'              => 25,
            'defaultHiddenColumns' => ['bounceRate', 'sessionDuration'],
            'columnConfig'         => [
                'baseColumns'        => ['region_code', 'region_name'],
                'columnDependencies' => [
                    'region'          => ['region_code', 'region_name'],
                    'visitors'        => ['visitors'],
                    'views'           => ['views'],
                    'viewsPerVisitor' => ['visitors', 'views'],
                    'bounceRate'      => ['bounce_rate'],
                    'sessionDuration' => ['avg_session_duration'],
                ],
            ],
            'defaultApiColumns'    => [
                'region_code',
                'region_name',
                'visitors',
                'views',
                'bounce_rate',
                'avg_session_duration',
            ],
            'emptyStateMessage'    => __('No US states found for the selected period', 'wp-statistics'),
        ];
    }

    /**
     * European Countries report config.
     *
     * @return array
     */
    private function getEuropeanCountriesConfig()
    {
        return [
            'title'                => __('European Countries', 'wp-statistics'),
            'context'              => 'european-countries',
            'filterGroup'          => 'visitors',
            'dataSource'           => [
                'queryId' => 'countries',
                'queries' => [
                    [
                        'id'          => 'countries',
                        'sources'     => ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
                        'group_by'    => ['country'],
                        'format'      => 'table',
                        'show_totals' => false,
                        'filters'     => [['key' => 'continent', 'operator' => 'is', 'value' => 'EU']],
                    ],
                ],
                'columnMapping' => [
                    'country'         => 'country_name',
                    'bounceRate'      => 'bounce_rate',
                    'sessionDuration' => 'avg_session_duration',
                ],
            ],
            'columns'              => [
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
                $this->visitorsColumn(),
                $this->viewsColumn(),
                $this->viewsPerVisitorColumn(),
                $this->bounceRateColumn(),
                $this->sessionDurationColumn(),
            ],
            'defaultSort'          => ['id' => 'visitors', 'desc' => true],
            'perPage'              => 25,
            'defaultHiddenColumns' => ['bounceRate', 'sessionDuration'],
            'columnConfig'         => [
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
            'defaultApiColumns'    => [
                'country_code',
                'country_name',
                'visitors',
                'views',
                'bounce_rate',
                'avg_session_duration',
            ],
            'emptyStateMessage'    => __('No European countries found for the selected period', 'wp-statistics'),
        ];
    }

    /**
     * Country Regions report config.
     *
     * Shows regions for the user's timezone country.
     *
     * @return array
     */
    private function getCountryRegionsConfig($userCountry)
    {
        $userCountryName = !empty($userCountry) ? Country::getName($userCountry) : '';

        return [
            'title'                => !empty($userCountryName)
                ? sprintf(__('Regions of %s', 'wp-statistics'), $userCountryName)
                : __('Regions', 'wp-statistics'),
            'context'              => 'country-regions',
            'filterGroup'          => 'visitors',
            'enabled'              => !empty($userCountry),
            'dataSource'           => [
                'queryId' => 'country_regions',
                'queries' => [
                    [
                        'id'          => 'country_regions',
                        'sources'     => ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
                        'group_by'    => ['region'],
                        'format'      => 'table',
                        'show_totals' => false,
                        'filters'     => !empty($userCountry)
                            ? [['key' => 'country', 'operator' => 'is', 'value' => $userCountry]]
                            : [],
                    ],
                ],
                'columnMapping' => [
                    'region'          => 'region_name',
                    'bounceRate'      => 'bounce_rate',
                    'sessionDuration' => 'avg_session_duration',
                ],
            ],
            'columns'              => [
                [
                    'key'          => 'region',
                    'dataField'    => 'region_name',
                    'title'        => __('Region', 'wp-statistics'),
                    'type'         => 'text',
                    'priority'     => 'primary',
                    'sortable'     => false,
                    'cardPosition' => 'header',
                ],
                $this->visitorsColumn(),
                $this->viewsColumn(),
                $this->viewsPerVisitorColumn(),
                $this->bounceRateColumn(),
                $this->sessionDurationColumn(),
            ],
            'defaultSort'          => ['id' => 'visitors', 'desc' => true],
            'perPage'              => 25,
            'defaultHiddenColumns' => ['bounceRate', 'sessionDuration'],
            'columnConfig'         => [
                'baseColumns'        => ['region_code', 'region_name'],
                'columnDependencies' => [
                    'region'          => ['region_code', 'region_name'],
                    'visitors'        => ['visitors'],
                    'views'           => ['views'],
                    'viewsPerVisitor' => ['visitors', 'views'],
                    'bounceRate'      => ['bounce_rate'],
                    'sessionDuration' => ['avg_session_duration'],
                ],
            ],
            'defaultApiColumns'    => [
                'region_code',
                'region_name',
                'visitors',
                'views',
                'bounce_rate',
                'avg_session_duration',
            ],
            'emptyStateMessage'    => __('No regions found for the selected period', 'wp-statistics'),
        ];
    }

    /**
     * Social Media report config.
     *
     * @return array
     */
    private function getSocialMediaConfig()
    {
        return [
            'title'                => __('Social Media', 'wp-statistics'),
            'context'              => 'social-media',
            'filterGroup'          => 'referrals',
            'headerFilter'         => ['type' => 'social-type'],
            'dataSource'           => [
                'queryId' => 'table',
                'queries' => [
                    [
                        'id'    => 'chart',
                        'chart' => 'social_media_chart',
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
                    'sessionDuration' => 'avg_session_duration',
                    'bounceRate'      => 'bounce_rate',
                    'pagesPerSession' => 'pages_per_session',
                ],
            ],
            'columns'              => [
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
                    'dataField'    => 'referrer_name',
                    'title'        => __('Source Name', 'wp-statistics'),
                    'type'         => 'text',
                    'priority'     => 'secondary',
                    'sortable'     => false,
                    'cardPosition' => 'body',
                ],
                $this->visitorsColumn(),
                $this->viewsColumn(),
                $this->sessionDurationColumn(['cardPosition' => 'body']),
                $this->bounceRateColumn(['cardPosition' => 'body']),
                $this->pagesPerSessionColumn(),
            ],
            'defaultSort'          => ['id' => 'visitors', 'desc' => true],
            'perPage'              => 25,
            'defaultHiddenColumns' => ['sessionDuration', 'bounceRate', 'pagesPerSession'],
            'columnConfig'         => [
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
            'defaultApiColumns'    => [
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
            'chart'                => [
                'queryId'          => 'chart',
                'title'            => __('Social Media', 'wp-statistics'),
                'compareMetricKey' => 'total',
            ],
            'emptyStateMessage'    => __('No social media referrers found for the selected period', 'wp-statistics'),
        ];
    }

    /**
     * Geographic overview page config.
     *
     * Conditionally includes top-regions widget based on user's timezone country.
     *
     * @return array
     */
    private function getGeographicOverviewConfig($userCountry)
    {
        $userCountryName = !empty($userCountry) ? Country::getName($userCountry) : '';
        $showRegions     = !empty($userCountry) && $userCountry !== 'US';

        // Build queries — conditionally include top_regions for non-US users
        $queries = [
            $this->topOneQuery('metrics_top_country', ['country'], ['country_code', 'country_name', 'visitors']),
            $this->topOneQuery('metrics_top_region', ['region'], ['region_name', 'visitors']),
            $this->topOneQuery('metrics_top_city', ['city'], ['city_name', 'visitors']),
            $this->topCountriesQuery('countries_map', [
                'sources' => ['visitors', 'views'],
                'columns' => ['country_code', 'country_name', 'visitors', 'views'],
                'per_page' => 250,
                'compare' => false,
            ]),
            $this->topCountriesQuery(),
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
            $this->topCountriesQuery('top_european_countries', [
                'filters' => [
                    ['key' => 'continent', 'operator' => 'is', 'value' => 'EU'],
                ],
            ]),
            $this->topByVisitorsQuery('top_us_states', ['region'], ['region_name', 'country_code', 'country_name', 'visitors'], [
                'filters' => [['key' => 'country', 'operator' => 'is', 'value' => 'US']],
            ]),
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
            $queries[] = $this->topByVisitorsQuery('top_regions', ['region'], ['region_name', 'country_code', 'country_name', 'visitors'], [
                'filters' => [['key' => 'country', 'operator' => 'is', 'value' => $userCountry]],
            ]);
        }

        // Build widgets
        $widgets = [
            ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
            $this->mapWidget('countries_map', [
                'id'        => 'global-map',
                'label'     => __('Global Visitor Distribution', 'wp-statistics'),
                'mapConfig' => [
                    'title'               => __('Global Visitor Distribution', 'wp-statistics'),
                    'metric'              => 'visitors',
                    'enableCityDrilldown' => true,
                    'enableMetricToggle'  => true,
                    'availableMetrics'    => [
                        ['value' => 'visitors', 'label' => __('Visitors', 'wp-statistics')],
                        ['value' => 'views', 'label' => __('Views', 'wp-statistics')],
                    ],
                ],
            ]),
            $this->topCountriesWidget(['defaultSize' => 6]),
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
            $this->topCountriesWidget([
                'id'          => 'european-countries',
                'label'       => __('Top European Countries', 'wp-statistics'),
                'defaultSize' => 6,
                'queryId'     => 'top_european_countries',
                'link'        => ['to' => '/european-countries'],
            ]),
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

    /**
     * Visitors overview page config.
     *
     * @return array
     */
    private function getVisitorsOverviewConfig()
    {
        return [
            'type'        => 'overview',
            'pageId'      => 'visitors-overview',
            'title'       => __('Visitors Overview', 'wp-statistics'),
            'filterGroup' => 'visitors',
            'queries'     => [
                // Aggregate metrics (totals-based with comparison)
                $this->metricsQuery('metrics', ['visitors', 'views', 'sessions', 'avg_session_duration', 'bounce_rate', 'pages_per_session']),
                // Top Country (items-based)
                $this->topOneQuery('metrics_top_country', ['country'], ['country_name', 'visitors']),
                // Top Referrer (items-based)
                $this->topOneQuery('metrics_top_referrer', ['referrer'], ['referrer_name', 'visitors']),
                // Top Search Term (items-based)
                [
                    'id'          => 'metrics_top_search',
                    'sources'     => ['searches'],
                    'group_by'    => ['search_term'],
                    'columns'     => ['search_term', 'searches'],
                    'per_page'    => 1,
                    'order_by'    => 'searches',
                    'order'       => 'DESC',
                    'format'      => 'flat',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Logged-in visitors (for share percentage computation)
                $this->metricsQuery('metrics_logged_in', ['visitors'], [
                    'filters' => [['key' => 'logged_in', 'operator' => 'is', 'value' => '1']],
                ]),
                // Traffic trends chart (timeframe-dependent group_by)
                $this->trafficTrendsQuery(),
                // Top Countries
                $this->topCountriesQuery(),
                // Device Type
                $this->topDeviceCategoriesQuery('device_type', [
                    'columns' => ['device_type_name', 'device_type_id', 'visitors'],
                ]),
                // Operating Systems
                $this->topOsQuery('operating_systems', [
                    'columns' => ['os_name', 'os_id', 'visitors'],
                ]),
                // Top Referrers
                $this->topReferrersQuery(),
                // Top Visitors (DataTable widget rendered via JS registration)
                [
                    'id'          => 'top_visitors',
                    'sources'     => ['visitors'],
                    'group_by'    => ['visitor'],
                    'columns'     => [
                        'visitor_id', 'visitor_hash', 'ip_address',
                        'user_id', 'user_login', 'user_email', 'user_role',
                        'total_views', 'country_code', 'country_name',
                        'region_name', 'city_name', 'os_name', 'browser_name',
                        'browser_version', 'device_type_name', 'referrer_domain',
                        'referrer_channel', 'entry_page', 'entry_page_title',
                        'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id',
                        'exit_page', 'exit_page_title', 'exit_page_type',
                        'exit_page_wp_id', 'exit_page_resource_id',
                    ],
                    'per_page'    => 10,
                    'order_by'    => 'total_views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Countries Map
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
            ],
            'metrics'     => [
                ['id' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'visitors', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'views', 'label' => __('Views', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'views', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'session-duration', 'label' => __('Session Duration', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'avg_session_duration', 'source' => 'totals', 'format' => 'duration'],
                ['id' => 'views-per-session', 'label' => __('Views/Session', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'pages_per_session', 'source' => 'totals', 'format' => 'decimal'],
                ['id' => 'top-country', 'label' => __('Top Country', 'wp-statistics'), 'queryId' => 'metrics_top_country', 'valueField' => 'country_name'],
                ['id' => 'top-referrer', 'label' => __('Top Referrer', 'wp-statistics'), 'queryId' => 'metrics_top_referrer', 'valueField' => 'referrer_name'],
                ['id' => 'top-search-term', 'label' => __('Top Search Term', 'wp-statistics'), 'queryId' => 'metrics_top_search', 'valueField' => 'search_term', 'decode' => true],
                [
                    'id'       => 'logged-in-share',
                    'label'    => __('Logged-in Share', 'wp-statistics'),
                    'queryId'  => 'metrics_logged_in',
                    'valueField' => 'visitors',
                    'source'   => 'computed',
                    'format'   => 'percentage',
                    'computed' => [
                        'type'               => 'share_percentage',
                        'numeratorQueryId'   => 'metrics_logged_in',
                        'numeratorField'     => 'visitors',
                        'denominatorQueryId' => 'metrics',
                        'denominatorField'   => 'visitors',
                    ],
                ],
            ],
            'widgets'     => [
                ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                $this->trafficTrendsWidget(),
                $this->topReferrersWidget([
                    'defaultSize'         => 6,
                    'labelFallbackFields' => ['referrer_domain', 'referrer_channel'],
                ]),
                $this->topCountriesWidget([
                    'defaultSize' => 6,
                ]),
                [
                    'id'            => 'device-type',
                    'type'          => 'bar-list',
                    'label'         => __('Device Type', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'device_type',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'device',
                    'iconSlugField' => 'device_type_name',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                    'link'          => ['to' => '/browsers'],
                ],
                [
                    'id'            => 'operating-systems',
                    'type'          => 'bar-list',
                    'label'         => __('Operating Systems', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'operating_systems',
                    'labelField'    => 'os_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'os',
                    'iconSlugField' => 'os_name',
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                    'link'          => ['to' => '/operating-systems'],
                ],
                // Insert JS-registered widgets here (e.g. OverviewTopVisitors DataTable)
                ['id' => '$registered', 'type' => 'registered', 'defaultSize' => 12],
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
            ],
        ];
    }

    /**
     * Page insights overview page config.
     *
     * @return array
     */
    private function getPageInsightsOverviewConfig()
    {
        return [
            'type'        => 'overview',
            'pageId'      => 'page-insights-overview',
            'title'       => __('Pages Overview', 'wp-statistics'),
            'filterGroup' => 'views',
            'queries'     => [
                // Aggregate metrics
                $this->metricsQuery('metrics', ['visitors', 'views', 'bounce_rate', 'avg_time_on_page']),
                // Top Page (items-based)
                [
                    'id'          => 'metrics_top_page',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_title', 'views'],
                    'per_page'    => 1,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'format'      => 'flat',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Top Pages List
                [
                    'id'          => 'top_pages',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_uri', 'page_title', 'page_type', 'page_wp_id', 'resource_id', 'views'],
                    'per_page'    => 5,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // 404 Pages
                [
                    'id'          => 'pages_404',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_uri', 'views'],
                    'filters'     => [['key' => 'post_type', 'operator' => 'is', 'value' => '404']],
                    'per_page'    => 5,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // By Category
                [
                    'id'          => 'by_category',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_title', 'resource_id', 'views'],
                    'filters'     => [['key' => 'post_type', 'operator' => 'is', 'value' => 'category']],
                    'per_page'    => 5,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // By Author
                [
                    'id'          => 'by_author',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_title', 'resource_id', 'views'],
                    'filters'     => [['key' => 'post_type', 'operator' => 'is', 'value' => 'author_archive']],
                    'per_page'    => 5,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // Top Entry Pages (premium query — data consumed by premium registered widgets)
                [
                    'id'          => 'top_entry_pages',
                    'sources'     => ['sessions'],
                    'group_by'    => ['entry_page'],
                    'columns'     => ['page_uri', 'page_title', 'page_type', 'page_wp_id', 'resource_id', 'sessions'],
                    'per_page'    => 5,
                    'order_by'    => 'sessions',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // Top Exit Pages (premium query — data consumed by premium registered widgets)
                [
                    'id'          => 'top_exit_pages',
                    'sources'     => ['sessions'],
                    'group_by'    => ['exit_page'],
                    'columns'     => ['page_uri', 'page_title', 'page_type', 'page_wp_id', 'resource_id', 'sessions'],
                    'per_page'    => 5,
                    'order_by'    => 'sessions',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
            ],
            'metrics'     => [
                ['id' => 'total-views', 'label' => __('Total Views', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'views', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'bounce-rate', 'label' => __('Bounce Rate', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'bounce_rate', 'source' => 'totals', 'format' => 'percentage'],
                ['id' => 'avg-time-on-page', 'label' => __('Avg Time on Page', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'avg_time_on_page', 'source' => 'totals', 'format' => 'duration'],
                ['id' => 'top-page', 'label' => __('Top Page', 'wp-statistics'), 'queryId' => 'metrics_top_page', 'valueField' => 'page_title'],
            ],
            'widgets'     => [
                ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                [
                    'id'            => 'top-pages',
                    'type'          => 'bar-list',
                    'label'         => __('Top Pages', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'top_pages',
                    'labelField'    => 'page_title',
                    'labelFallbackFields' => ['page_uri'],
                    'valueField'    => 'views',
                    'columnHeaders' => ['left' => __('Page', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                    'linkType'      => 'analytics-route',
                    'link'          => ['to' => '/top-pages'],
                ],
                [
                    'id'            => '404-pages',
                    'type'          => 'bar-list',
                    'label'         => __('404 Pages', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'pages_404',
                    'labelField'    => 'page_uri',
                    'valueField'    => 'views',
                    'columnHeaders' => ['left' => __('Page', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                    'link'          => ['to' => '/404-pages'],
                ],
                [
                    'id'             => 'by-category',
                    'type'           => 'bar-list',
                    'label'          => __('Category Pages', 'wp-statistics'),
                    'defaultSize'    => 6,
                    'queryId'        => 'by_category',
                    'labelField'     => 'page_title',
                    'valueField'     => 'views',
                    'columnHeaders'  => ['left' => __('Category', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                    'linkTo'         => '/url/$resourceId',
                    'linkParamField' => 'resource_id',
                    'link'           => ['to' => '/category-pages'],
                ],
                [
                    'id'             => 'by-author',
                    'type'           => 'bar-list',
                    'label'          => __('Author Pages', 'wp-statistics'),
                    'defaultSize'    => 6,
                    'queryId'        => 'by_author',
                    'labelField'     => 'page_title',
                    'valueField'     => 'views',
                    'columnHeaders'  => ['left' => __('Author', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                    'linkTo'         => '/url/$resourceId',
                    'linkParamField' => 'resource_id',
                    'link'           => ['to' => '/author-pages'],
                ],
                // Insert JS-registered widgets here (premium entry/exit pages)
                ['id' => '$registered', 'type' => 'registered', 'defaultSize' => 6],
            ],
        ];
    }

    /**
     * Referrals overview page config.
     *
     * @return array
     */
    private function getReferralsOverviewConfig()
    {
        $notDirectFilter = ['key' => 'referrer_channel', 'operator' => 'is_not', 'value' => 'direct'];

        return [
            'type'             => 'overview',
            'pageId'           => 'referrals-overview',
            'title'            => __('Referrals Overview', 'wp-statistics'),
            'filterGroup'      => 'referrals',
            'queries'          => [
                // Referred visitors count (excludes direct traffic)
                $this->metricsQuery('metrics', ['visitors'], ['filters' => [$notDirectFilter]]),
                // Top referrer name
                [
                    'id'          => 'metrics_top_referrer',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'referrer_domain', 'visitors'],
                    'filters'     => [$notDirectFilter],
                    'per_page'    => 1,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'flat',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Top search engine
                [
                    'id'          => 'metrics_top_search_engine',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'visitors'],
                    'filters'     => [['key' => 'referrer_channel', 'operator' => 'is', 'value' => 'search']],
                    'per_page'    => 1,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'flat',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Top social media
                [
                    'id'          => 'metrics_top_social',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'visitors'],
                    'filters'     => [['key' => 'referrer_channel', 'operator' => 'is', 'value' => 'social']],
                    'per_page'    => 1,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'flat',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Top entry page
                [
                    'id'          => 'metrics_top_entry_page',
                    'sources'     => ['visitors'],
                    'group_by'    => ['entry_page'],
                    'columns'     => ['page_title', 'page_uri', 'visitors'],
                    'filters'     => [$notDirectFilter],
                    'per_page'    => 1,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'flat',
                    'show_totals' => false,
                    'compare'     => false,
                ],
                // Traffic trends chart (timeframe-dependent group_by)
                $this->trafficTrendsQuery('traffic_trends', ['filters' => [$notDirectFilter]]),
                // Top referrers list
                $this->topReferrersQuery('top_referrers', ['filters' => [$notDirectFilter]]),
                // Top source categories
                [
                    'id'          => 'top_source_categories',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer_channel'],
                    'columns'     => ['referrer_channel', 'visitors'],
                    'filters'     => [$notDirectFilter],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // Top search engines
                [
                    'id'          => 'top_search_engines',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'referrer_domain', 'visitors'],
                    'filters'     => [['key' => 'referrer_channel', 'operator' => 'is', 'value' => 'search']],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // Top social media
                [
                    'id'          => 'top_social_media',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'referrer_domain', 'visitors'],
                    'filters'     => [['key' => 'referrer_channel', 'operator' => 'is', 'value' => 'social']],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                // Top countries
                $this->topCountriesQuery('top_countries', ['filters' => [$notDirectFilter]]),
                // Top operating systems
                $this->topOsQuery('top_operating_systems', ['filters' => [$notDirectFilter]]),
                // Top device categories
                $this->topDeviceCategoriesQuery('top_device_categories', ['filters' => [$notDirectFilter]]),
            ],
            'metrics'          => [
                ['id' => 'referredVisitors', 'label' => __('Referred Visitors', 'wp-statistics'), 'queryId' => 'metrics', 'valueField' => 'visitors', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'topReferrer', 'label' => __('Top Referrer', 'wp-statistics'), 'queryId' => 'metrics_top_referrer', 'valueField' => 'referrer_name'],
                ['id' => 'topSearchEngine', 'label' => __('Top Search Engine', 'wp-statistics'), 'queryId' => 'metrics_top_search_engine', 'valueField' => 'referrer_name'],
                ['id' => 'topSocialMedia', 'label' => __('Top Social Media', 'wp-statistics'), 'queryId' => 'metrics_top_social', 'valueField' => 'referrer_name'],
                ['id' => 'topEntryPage', 'label' => __('Top Entry Page', 'wp-statistics'), 'queryId' => 'metrics_top_entry_page', 'valueField' => 'page_title'],
            ],
            'widgets'          => [
                ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                $this->trafficTrendsWidget(),
                [
                    'id'                  => 'top-referrers',
                    'type'                => 'bar-list',
                    'label'               => __('Top Referrers', 'wp-statistics'),
                    'defaultSize'         => 12,
                    'queryId'             => 'top_referrers',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain', 'referrer_channel'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'             => 'top-source-categories',
                    'type'           => 'bar-list',
                    'label'          => __('Top Source Categories', 'wp-statistics'),
                    'defaultSize'    => 6,
                    'queryId'        => 'top_source_categories',
                    'labelField'     => 'referrer_channel',
                    'labelTransform' => 'source-category',
                    'valueField'     => 'visitors',
                    'columnHeaders'  => ['left' => __('Category', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'                  => 'top-search-engines',
                    'type'                => 'bar-list',
                    'label'               => __('Top Search Engines', 'wp-statistics'),
                    'defaultSize'         => 6,
                    'queryId'             => 'top_search_engines',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Search Engine', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'                  => 'top-social-media',
                    'type'                => 'bar-list',
                    'label'               => __('Top Social Media', 'wp-statistics'),
                    'defaultSize'         => 6,
                    'queryId'             => 'top_social_media',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Social Network', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
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
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-device-categories',
                    'type'          => 'bar-list',
                    'label'         => __('Top Device Categories', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'top_device_categories',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
            ],
        ];
    }

    /**
     * Single Category detail page config.
     *
     * @return array
     */
    private function getSingleCategoryConfig()
    {
        return [
            'type'        => 'detail',
            'pageId'      => 'single-category',
            'title'       => __('Category', 'wp-statistics'),
            'filterGroup' => 'individual-category',
            'hideFilters' => true,
            'entityParam' => 'termId',
            'filterField' => 'taxonomy',
            'backLink'    => '/categories',
            'backLabel'   => __('Back to Categories', 'wp-statistics'),
            'entityInfo'  => [
                'queryId'           => 'category_info',
                'nameField'         => 'term_name',
                'fallbackAction'    => 'wp_statistics_get_term_info',
                'fallbackParam'     => 'term_id',
                'fallbackNameField' => 'name',
            ],
            'queries'     => [
                [
                    'id'          => 'category_info',
                    'sources'     => ['visitors', 'views', 'published_content'],
                    'group_by'    => ['taxonomy'],
                    'columns'     => ['term_id', 'term_name', 'term_slug', 'taxonomy_type', 'visitors', 'views', 'published_content'],
                    'format'      => 'table',
                    'per_page'    => 10,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'show_totals' => false,
                    'compare'     => true,
                ],
                $this->metricsQuery('category_metrics', ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page']),
                $this->trafficTrendsQuery(),
                [
                    'id'          => 'top_content',
                    'sources'     => ['visitors', 'views', 'comments'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_wp_id', 'page_title', 'page_uri', 'page_type', 'visitors', 'views', 'comments', 'published_date'],
                    'format'      => 'table',
                    'per_page'    => 15,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'show_totals' => false,
                    'compare'     => true,
                ],
                $this->topReferrersQuery('top_referrers', [
                    'filters' => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                    ],
                    'compare' => true,
                ]),
                [
                    'id'          => 'top_search_engines',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_domain', 'referrer_name', 'visitors'],
                    'filters'     => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                        ['key' => 'referrer_channel', 'operator' => 'contains', 'value' => 'search'],
                    ],
                    'format'      => 'table',
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'show_totals' => true,
                    'compare'     => true,
                ],
                $this->topCountriesQuery('top_countries', ['compare' => true]),
                $this->topBrowsersQuery('top_browsers', ['compare' => true]),
                $this->topOsQuery('top_operating_systems', ['compare' => true]),
                $this->topDeviceCategoriesQuery('top_device_categories', ['compare' => true]),
            ],
            'metrics'     => [
                [
                    'id'         => 'contents',
                    'label'      => __('Contents', 'wp-statistics'),
                    'queryId'    => 'category_metrics',
                    'valueField' => 'published_content',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'visitors',
                    'label'      => __('Visitors', 'wp-statistics'),
                    'queryId'    => 'category_metrics',
                    'valueField' => 'visitors',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'views',
                    'label'      => __('Views', 'wp-statistics'),
                    'queryId'    => 'category_metrics',
                    'valueField' => 'views',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'bounce-rate',
                    'label'      => __('Bounce Rate', 'wp-statistics'),
                    'queryId'    => 'category_metrics',
                    'valueField' => 'bounce_rate',
                    'source'     => 'totals',
                    'format'     => 'percentage',
                ],
                [
                    'id'         => 'avg-time-on-page',
                    'label'      => __('Avg. Time on Page', 'wp-statistics'),
                    'queryId'    => 'category_metrics',
                    'valueField' => 'avg_time_on_page',
                    'source'     => 'totals',
                    'format'     => 'duration',
                ],
            ],
            'widgets'     => [
                [
                    'id'          => 'metrics',
                    'label'       => __('Metrics Overview', 'wp-statistics'),
                    'type'        => 'metrics',
                    'defaultSize' => 12,
                ],
                $this->trafficTrendsWidget(['label' => __('Performance', 'wp-statistics'), 'defaultSize' => 8]),
                [
                    'id'                    => 'traffic-summary',
                    'label'                 => __('Traffic Summary', 'wp-statistics'),
                    'type'                  => 'traffic-summary',
                    'defaultSize'           => 4,
                    'trafficSummaryConfig'  => [
                        'sources' => ['visitors', 'views'],
                        'metrics' => [
                            ['key' => 'visitors', 'label' => __('Visitors', 'wp-statistics')],
                            ['key' => 'views', 'label' => __('Views', 'wp-statistics')],
                        ],
                    ],
                ],
                [
                    'id'                  => 'top-content',
                    'label'               => __('Top Content', 'wp-statistics'),
                    'type'                => 'tabbed-bar-list',
                    'defaultSize'         => 12,
                    'queryId'             => 'top_content',
                    'tabbedBarListConfig' => [
                        'linkType'           => 'analytics-route',
                        'labelField'         => 'page_title',
                        'labelFallbackField' => 'page_uri',
                        'tabs'               => [
                            [
                                'id'            => 'popular',
                                'label'         => __('Most Popular', 'wp-statistics'),
                                'columnHeaders' => [
                                    'left'  => __('Content', 'wp-statistics'),
                                    'right' => __('Views', 'wp-statistics'),
                                ],
                                'sortBy'        => 'views',
                                'valueField'    => 'views',
                                'valueSuffix'   => __('views', 'wp-statistics'),
                                'maxItems'      => 5,
                            ],
                            [
                                'id'             => 'commented',
                                'label'          => __('Most Commented', 'wp-statistics'),
                                'columnHeaders'  => [
                                    'left'  => __('Content', 'wp-statistics'),
                                    'right' => __('Comments', 'wp-statistics'),
                                ],
                                'sortBy'         => 'comments',
                                'valueField'     => 'comments',
                                'valueSuffix'    => __('comments', 'wp-statistics'),
                                'filterField'    => 'comments',
                                'filterMinValue' => 1,
                                'showComparison' => false,
                                'maxItems'       => 5,
                            ],
                            [
                                'id'             => 'recent',
                                'label'          => __('Most Recent', 'wp-statistics'),
                                'columnHeaders'  => [
                                    'left'  => __('Content', 'wp-statistics'),
                                    'right' => __('Views', 'wp-statistics'),
                                ],
                                'sortBy'         => 'published_date',
                                'sortType'       => 'date',
                                'valueField'     => 'views',
                                'valueSuffix'    => __('views', 'wp-statistics'),
                                'showComparison' => false,
                                'maxItems'       => 5,
                            ],
                        ],
                    ],
                ],
                [
                    'id'            => 'top-referrers',
                    'label'         => __('Top Referrers', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_referrers',
                    'labelField'    => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-search-engines',
                    'label'         => __('Top Search Engines', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_search_engines',
                    'labelField'    => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Search Engine', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-countries',
                    'label'         => __('Top Countries', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_countries',
                    'labelField'    => 'country_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'country',
                    'iconSlugField' => 'country_code',
                    'columnHeaders' => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                    'linkTo'        => '/country/$countryCode',
                    'linkParamField' => 'country_code',
                ],
                [
                    'id'            => 'top-browsers',
                    'label'         => __('Top Browsers', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_browsers',
                    'labelField'    => 'browser_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'browser',
                    'iconSlugField' => 'browser_name',
                    'columnHeaders' => ['left' => __('Browser', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-operating-systems',
                    'label'         => __('Top Operating Systems', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_operating_systems',
                    'labelField'    => 'os_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'os',
                    'iconSlugField' => 'os_name',
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-device-categories',
                    'label'         => __('Top Device Categories', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_device_categories',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'device',
                    'iconSlugField' => 'device_type_name',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
            ],
        ];
    }

    /**
     * Single Content detail page config.
     *
     * @return array
     */
    private function getSingleContentConfig()
    {
        return [
            'type'             => 'detail',
            'pageId'           => 'single-content',
            'title'            => __('Content', 'wp-statistics'),
            'filterGroup'      => 'individual-content',
            'hideFilters'      => false,
            'showFilterButton' => true,
            'entityParam'      => 'postId',
            'filterField'      => 'resource_id',
            'backLink'         => '/content',
            'backLabel'        => __('Back to Content', 'wp-statistics'),
            'entityInfo'       => [
                'queryId'   => 'post_info',
                'nameField' => 'page_title',
            ],
            'entityMeta'       => [
                'queryId' => 'post_info',
            ],
            'queries'          => [
                $this->metricsQuery('content_metrics', ['visitors', 'views', 'bounce_rate', 'avg_time_on_page', 'entry_page', 'exit_page', 'exit_rate', 'comments']),
                [
                    'id'          => 'post_info',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_uri', 'page_title', 'page_wp_id', 'page_type', 'post_type_label', 'published_date', 'modified_date', 'comments', 'author_id', 'author_name', 'thumbnail_url', 'permalink', 'cached_terms'],
                    'format'      => 'table',
                    'per_page'    => 1,
                    'show_totals' => false,
                    'compare'     => false,
                ],
                $this->trafficTrendsQuery(),
                $this->topReferrersQuery('top_referrers', [
                    'filters' => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                    ],
                    'compare' => true,
                ]),
                [
                    'id'          => 'top_search_engines',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_domain', 'referrer_name', 'visitors'],
                    'filters'     => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                        ['key' => 'referrer_channel', 'operator' => 'contains', 'value' => 'search'],
                    ],
                    'format'      => 'table',
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'show_totals' => true,
                    'compare'     => true,
                ],
                $this->topCountriesQuery('top_countries', ['compare' => true]),
                $this->topBrowsersQuery('top_browsers', ['compare' => true]),
                $this->topOsQuery('top_operating_systems', ['compare' => true]),
                $this->topDeviceCategoriesQuery('top_device_categories', ['compare' => true]),
            ],
            'metrics'          => [
                [
                    'id'         => 'visitors',
                    'label'      => __('Visitors', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'visitors',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'views',
                    'label'      => __('Views', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'views',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'avg-time-on-page',
                    'label'      => __('Avg. Time on Page', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'avg_time_on_page',
                    'source'     => 'totals',
                    'format'     => 'duration',
                ],
                [
                    'id'         => 'bounce-rate',
                    'label'      => __('Bounce Rate', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'bounce_rate',
                    'source'     => 'totals',
                    'format'     => 'percentage',
                ],
                [
                    'id'         => 'entry-page',
                    'label'      => __('Entry Page', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'entry_page',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'exit-page',
                    'label'      => __('Exit Page', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'exit_page',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'exit-rate',
                    'label'      => __('Exit Rate', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'exit_rate',
                    'source'     => 'totals',
                    'format'     => 'percentage',
                ],
                [
                    'id'         => 'comments',
                    'label'      => __('Comments', 'wp-statistics'),
                    'queryId'    => 'content_metrics',
                    'valueField' => 'comments',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
            ],
            'widgets'          => [
                [
                    'id'          => 'metrics',
                    'label'       => __('Metrics Overview', 'wp-statistics'),
                    'type'        => 'metrics',
                    'defaultSize' => 12,
                ],
                $this->trafficTrendsWidget(['defaultSize' => 8]),
                [
                    'id'                   => 'traffic-summary',
                    'label'                => __('Traffic Summary', 'wp-statistics'),
                    'type'                 => 'traffic-summary',
                    'defaultSize'          => 4,
                    'trafficSummaryConfig' => [
                        'sources' => ['visitors', 'views'],
                        'metrics' => [
                            ['key' => 'visitors', 'label' => __('Visitors', 'wp-statistics')],
                            ['key' => 'views', 'label' => __('Views', 'wp-statistics')],
                        ],
                    ],
                ],
                [
                    'id'            => 'top-referrers',
                    'label'         => __('Top Referrers', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_referrers',
                    'labelField'    => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-search-engines',
                    'label'         => __('Top Search Engines', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_search_engines',
                    'labelField'    => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Search Engine', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-countries',
                    'label'         => __('Top Countries', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_countries',
                    'labelField'    => 'country_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'country',
                    'iconSlugField' => 'country_code',
                    'linkTo'        => '/country/$countryCode',
                    'linkParamField' => 'country_code',
                    'columnHeaders' => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-browsers',
                    'label'         => __('Top Browsers', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_browsers',
                    'labelField'    => 'browser_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'browser',
                    'iconSlugField' => 'browser_name',
                    'columnHeaders' => ['left' => __('Browser', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-operating-systems',
                    'label'         => __('Top Operating Systems', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_operating_systems',
                    'labelField'    => 'os_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'os',
                    'iconSlugField' => 'os_name',
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-device-categories',
                    'label'         => __('Top Device Categories', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_device_categories',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'device',
                    'iconSlugField' => 'device_type_name',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
            ],
        ];
    }

    /**
     * Single URL detail page config.
     *
     * @return array
     */
    private function getSingleUrlConfig()
    {
        return [
            'type'             => 'detail',
            'pageId'           => 'single-url',
            'title'            => __('URL Report', 'wp-statistics'),
            'filterGroup'      => 'individual-content',
            'hideFilters'      => false,
            'showFilterButton' => true,
            'entityParam'      => 'resourceId',
            'filterField'      => 'resource_pk',
            'backLink'         => '/top-pages',
            'backLabel'        => __('Back to Top Pages', 'wp-statistics'),
            'entityInfo'       => [
                'queryId'            => 'url_info',
                'nameField'          => 'page_title',
                'nameFallbackField'  => 'page_uri',
            ],
            'titleBadge'       => [
                'field'   => 'page_type',
                'labels'  => [
                    'home'              => __('Home Page', 'wp-statistics'),
                    'search'            => __('Search Results', 'wp-statistics'),
                    '404'               => __('404 Page', 'wp-statistics'),
                    'archive'           => __('Archive', 'wp-statistics'),
                    'date_archive'      => __('Date Archive', 'wp-statistics'),
                    'post_type_archive' => __('Post Type Archive', 'wp-statistics'),
                    'feed'              => __('Feed', 'wp-statistics'),
                    'loginpage'         => __('Login Page', 'wp-statistics'),
                    'author_archive'    => __('Author Archive', 'wp-statistics'),
                    'category'          => __('Category Archive', 'wp-statistics'),
                    'post_tag'          => __('Tag Archive', 'wp-statistics'),
                ],
            ],
            'externalLink'     => [
                'field'   => 'permalink',
            ],
            'contentRedirect'  => [
                'wpIdField'    => 'page_wp_id',
                'typeField'    => 'page_type',
                'excludeTypes' => [
                    'home', 'search', '404', 'archive', 'date_archive',
                    'post_type_archive', 'feed', 'loginpage', 'author_archive',
                    'category', 'post_tag', 'unknown',
                ],
                'targetRoute'  => '/content/$postId',
                'targetParam'  => 'postId',
            ],
            'queries'          => [
                $this->metricsQuery('url_metrics', ['visitors', 'views', 'bounce_rate', 'avg_time_on_page', 'entry_page', 'exit_page', 'exit_rate']),
                [
                    'id'          => 'url_info',
                    'sources'     => ['views'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_uri', 'page_title', 'page_wp_id', 'page_type', 'resource_id', 'permalink'],
                    'format'      => 'table',
                    'per_page'    => 1,
                    'show_totals' => false,
                    'compare'     => false,
                ],
                $this->trafficTrendsQuery(),
                $this->topReferrersQuery('top_referrers', [
                    'filters' => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                    ],
                    'compare' => true,
                ]),
                [
                    'id'          => 'top_search_engines',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_domain', 'referrer_name', 'visitors'],
                    'filters'     => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                        ['key' => 'referrer_channel', 'operator' => 'contains', 'value' => 'search'],
                    ],
                    'format'      => 'table',
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'show_totals' => true,
                    'compare'     => true,
                ],
                $this->topCountriesQuery('top_countries', ['compare' => true]),
                $this->topBrowsersQuery('top_browsers', ['compare' => true]),
                $this->topOsQuery('top_operating_systems', ['compare' => true]),
                $this->topDeviceCategoriesQuery('top_device_categories', ['compare' => true]),
            ],
            'metrics'          => [
                [
                    'id'         => 'visitors',
                    'label'      => __('Visitors', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'visitors',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'views',
                    'label'      => __('Views', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'views',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'avg-time-on-page',
                    'label'      => __('Avg. Time on Page', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'avg_time_on_page',
                    'source'     => 'totals',
                    'format'     => 'duration',
                ],
                [
                    'id'         => 'bounce-rate',
                    'label'      => __('Bounce Rate', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'bounce_rate',
                    'source'     => 'totals',
                    'format'     => 'percentage',
                ],
                [
                    'id'         => 'entry-page',
                    'label'      => __('Entry Page', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'entry_page',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'exit-page',
                    'label'      => __('Exit Page', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'exit_page',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'exit-rate',
                    'label'      => __('Exit Rate', 'wp-statistics'),
                    'queryId'    => 'url_metrics',
                    'valueField' => 'exit_rate',
                    'source'     => 'totals',
                    'format'     => 'percentage',
                ],
            ],
            'widgets'          => [
                [
                    'id'          => 'metrics',
                    'label'       => __('Metrics Overview', 'wp-statistics'),
                    'type'        => 'metrics',
                    'defaultSize' => 12,
                ],
                $this->trafficTrendsWidget(['defaultSize' => 8]),
                [
                    'id'                   => 'traffic-summary',
                    'label'                => __('Traffic Summary', 'wp-statistics'),
                    'type'                 => 'traffic-summary',
                    'defaultSize'          => 4,
                    'trafficSummaryConfig' => [
                        'sources' => ['visitors', 'views'],
                        'metrics' => [
                            ['key' => 'visitors', 'label' => __('Visitors', 'wp-statistics')],
                            ['key' => 'views', 'label' => __('Views', 'wp-statistics')],
                        ],
                    ],
                ],
                [
                    'id'            => 'top-referrers',
                    'label'         => __('Top Referrers', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_referrers',
                    'labelField'    => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-search-engines',
                    'label'         => __('Top Search Engines', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_search_engines',
                    'labelField'    => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'    => 'visitors',
                    'columnHeaders' => ['left' => __('Search Engine', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'             => 'top-countries',
                    'label'          => __('Top Countries', 'wp-statistics'),
                    'type'           => 'bar-list',
                    'defaultSize'    => 6,
                    'queryId'        => 'top_countries',
                    'labelField'     => 'country_name',
                    'valueField'     => 'visitors',
                    'iconType'       => 'country',
                    'iconSlugField'  => 'country_code',
                    'linkTo'         => '/country/$countryCode',
                    'linkParamField' => 'country_code',
                    'columnHeaders'  => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-browsers',
                    'label'         => __('Top Browsers', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_browsers',
                    'labelField'    => 'browser_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'browser',
                    'iconSlugField' => 'browser_name',
                    'columnHeaders' => ['left' => __('Browser', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-operating-systems',
                    'label'         => __('Top Operating Systems', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_operating_systems',
                    'labelField'    => 'os_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'os',
                    'iconSlugField' => 'os_name',
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-device-categories',
                    'label'         => __('Top Device Categories', 'wp-statistics'),
                    'type'          => 'bar-list',
                    'defaultSize'   => 6,
                    'queryId'       => 'top_device_categories',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'device',
                    'iconSlugField' => 'device_type_name',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
            ],
        ];
    }

    /**
     * Authors overview page config.
     *
     * @return array
     */
    private function getAuthorsOverviewConfig()
    {
        return [
            'type'             => 'overview',
            'pageId'           => 'authors-overview',
            'title'            => __('Authors', 'wp-statistics'),
            'filterGroup'      => 'content',
            'showFilterButton' => true,
            'defaultFilters'   => [
                ['field' => 'post_type', 'operator' => 'is', 'value' => 'post'],
            ],
            'queries'          => [
                $this->metricsQuery('author_metrics', ['published_content', 'active_authors', 'visitors', 'views']),
                [
                    'id'       => 'top_authors',
                    'sources'  => ['visitors', 'views', 'published_content', 'comments'],
                    'group_by' => ['author'],
                    'columns'  => ['author_id', 'author_name', 'author_avatar', 'visitors', 'views', 'published_content', 'comments'],
                    'per_page' => 20,
                    'order_by' => 'views',
                    'order'    => 'DESC',
                    'format'   => 'table',
                    'show_totals' => true,
                    'compare'  => true,
                ],
            ],
            'metrics'          => [
                ['id' => 'published-content', 'label' => __('Published Content', 'wp-statistics'), 'queryId' => 'author_metrics', 'valueField' => 'published_content', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'active-authors', 'label' => __('Active Authors', 'wp-statistics'), 'queryId' => 'author_metrics', 'valueField' => 'active_authors', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'queryId' => 'author_metrics', 'valueField' => 'visitors', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'views', 'label' => __('Views', 'wp-statistics'), 'queryId' => 'author_metrics', 'valueField' => 'views', 'source' => 'totals', 'format' => 'compact_number'],
                [
                    'id'       => 'views-per-author',
                    'label'    => __('Views per Author', 'wp-statistics'),
                    'queryId'  => 'author_metrics',
                    'valueField' => 'views',
                    'source'   => 'computed',
                    'format'   => 'compact_number',
                    'computed' => [
                        'type'               => 'ratio',
                        'numeratorQueryId'   => 'author_metrics',
                        'numeratorField'     => 'views',
                        'denominatorQueryId' => 'author_metrics',
                        'denominatorField'   => 'active_authors',
                    ],
                ],
                [
                    'id'       => 'avg-posts-per-author',
                    'label'    => __('Avg. Posts per Author', 'wp-statistics'),
                    'queryId'  => 'author_metrics',
                    'valueField' => 'published_content',
                    'source'   => 'computed',
                    'format'   => 'decimal',
                    'computed' => [
                        'type'               => 'ratio',
                        'numeratorQueryId'   => 'author_metrics',
                        'numeratorField'     => 'published_content',
                        'denominatorQueryId' => 'author_metrics',
                        'denominatorField'   => 'active_authors',
                    ],
                ],
            ],
            'widgets'          => [
                ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                [
                    'id'                  => 'top-authors',
                    'type'                => 'tabbed-bar-list',
                    'label'               => __('Top Authors', 'wp-statistics'),
                    'defaultSize'         => 12,
                    'queryId'             => 'top_authors',
                    'tabbedBarListConfig' => [
                        'labelField'      => 'author_name',
                        'labelFallbackField' => 'author_id',
                        'iconType'        => 'author-avatar',
                        'iconField'       => 'author_avatar',
                        'linkTo'          => '/author/$authorId',
                        'linkParamField'  => 'author_id',
                        'tabs'            => [
                            [
                                'id'            => 'views',
                                'label'         => __('Views', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Author', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'        => 'views',
                                'valueField'    => 'views',
                                'valueSuffix'   => __('views', 'wp-statistics'),
                                'link'          => ['href' => '/top-authors?order_by=views&order=desc', 'title' => __('See all', 'wp-statistics')],
                            ],
                            [
                                'id'            => 'publishing',
                                'label'         => __('Publishing', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Author', 'wp-statistics'), 'right' => __('Contents', 'wp-statistics')],
                                'sortBy'        => 'published_content',
                                'valueField'    => 'published_content',
                                'valueSuffix'   => __('contents', 'wp-statistics'),
                                'link'          => ['href' => '/top-authors?order_by=published&order=desc', 'title' => __('See all', 'wp-statistics')],
                            ],
                            [
                                'id'             => 'views-per-post',
                                'label'          => __('Views per Post', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Author', 'wp-statistics'), 'right' => __('Views/Post', 'wp-statistics')],
                                'sortBy'         => '_computed',
                                'valueField'     => 'views',
                                'computedField'  => ['numerator' => 'views', 'denominator' => 'published_content'],
                                'valueFormat'    => 'decimal',
                                'showComparison' => false,
                                'link'           => ['href' => '/top-authors', 'title' => __('See all', 'wp-statistics')],
                            ],
                            [
                                'id'             => 'comments-per-post',
                                'label'          => __('Comments per Post', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Author', 'wp-statistics'), 'right' => __('Comments/Post', 'wp-statistics')],
                                'sortBy'         => '_computed',
                                'valueField'     => 'comments',
                                'computedField'  => ['numerator' => 'comments', 'denominator' => 'published_content'],
                                'valueFormat'    => 'decimal',
                                'showComparison' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Content overview page config.
     *
     * @return array
     */
    private function getContentOverviewConfig()
    {
        return [
            'type'             => 'overview',
            'pageId'           => 'content-overview',
            'title'            => __('Content', 'wp-statistics'),
            'filterGroup'      => 'content',
            'showFilterButton' => true,
            'defaultFilters'   => [
                ['field' => 'post_type', 'operator' => 'is', 'value' => 'post'],
            ],
            'queries'          => [
                $this->metricsQuery('content_metrics', ['visitors', 'views', 'bounce_rate', 'avg_time_on_page', 'published_content', 'comments']),
                $this->trafficTrendsQuery('traffic_trends', [
                    'sources' => ['visitors', 'views', 'published_content'],
                ]),
                [
                    'id'       => 'top_content',
                    'sources'  => ['visitors', 'views', 'published_content', 'comments'],
                    'group_by' => ['page'],
                    'format'   => 'table',
                    'show_totals' => false,
                    'compare'  => true,
                    'per_page' => 15,
                    'order_by' => 'views',
                    'order'    => 'DESC',
                    'columns'  => ['page_uri', 'page_title', 'page_wp_id', 'page_type', 'visitors', 'views', 'comments', 'published_date'],
                ],
                $this->topReferrersQuery('top_referrers', [
                    'filters' => [['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => '']],
                    'compare' => true,
                ]),
                [
                    'id'       => 'top_search_engines',
                    'sources'  => ['visitors'],
                    'group_by' => ['referrer'],
                    'columns'  => ['referrer_domain', 'referrer_name', 'visitors'],
                    'filters'  => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                        ['key' => 'referrer_channel', 'operator' => 'contains', 'value' => 'search'],
                    ],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                    'compare'     => true,
                ],
                $this->topCountriesQuery('top_countries', ['compare' => true]),
                $this->topBrowsersQuery('top_browsers', ['compare' => true]),
                $this->topOsQuery('top_operating_systems', ['compare' => true]),
                $this->topDeviceCategoriesQuery('top_device_categories', ['compare' => true]),
            ],
            'metrics'          => [
                ['id' => 'published-content', 'label' => __('Published Content', 'wp-statistics'), 'queryId' => 'content_metrics', 'valueField' => 'published_content', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'queryId' => 'content_metrics', 'valueField' => 'visitors', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'views', 'label' => __('Views', 'wp-statistics'), 'queryId' => 'content_metrics', 'valueField' => 'views', 'source' => 'totals', 'format' => 'compact_number'],
                [
                    'id'       => 'views-per-content',
                    'label'    => __('Views per Content', 'wp-statistics'),
                    'queryId'  => 'content_metrics',
                    'valueField' => 'views',
                    'source'   => 'computed',
                    'format'   => 'decimal',
                    'computed' => [
                        'type'               => 'ratio',
                        'numeratorQueryId'   => 'content_metrics',
                        'numeratorField'     => 'views',
                        'denominatorQueryId' => 'content_metrics',
                        'denominatorField'   => 'published_content',
                    ],
                ],
                ['id' => 'bounce-rate', 'label' => __('Bounce Rate', 'wp-statistics'), 'queryId' => 'content_metrics', 'valueField' => 'bounce_rate', 'source' => 'totals', 'format' => 'percentage'],
                ['id' => 'avg-time-on-page', 'label' => __('Avg. Time on Page', 'wp-statistics'), 'queryId' => 'content_metrics', 'valueField' => 'avg_time_on_page', 'source' => 'totals', 'format' => 'duration'],
                ['id' => 'comments', 'label' => __('Comments', 'wp-statistics'), 'queryId' => 'content_metrics', 'valueField' => 'comments', 'source' => 'totals', 'format' => 'compact_number'],
                [
                    'id'       => 'avg-comments-per-content',
                    'label'    => __('Avg. Comments per Content', 'wp-statistics'),
                    'queryId'  => 'content_metrics',
                    'valueField' => 'comments',
                    'source'   => 'computed',
                    'format'   => 'decimal',
                    'computed' => [
                        'type'               => 'ratio',
                        'numeratorQueryId'   => 'content_metrics',
                        'numeratorField'     => 'comments',
                        'denominatorQueryId' => 'content_metrics',
                        'denominatorField'   => 'published_content',
                    ],
                ],
            ],
            'widgets'          => [
                ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                $this->trafficTrendsWidget([
                    'label'       => __('Content Performance', 'wp-statistics'),
                    'chartConfig' => [
                        'metrics' => [
                            ['key' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'color' => 'var(--chart-1)'],
                            ['key' => 'views', 'label' => __('Views', 'wp-statistics'), 'color' => 'var(--chart-2)'],
                            ['key' => 'published_content', 'label' => __('Published Content', 'wp-statistics'), 'color' => 'var(--chart-3)', 'type' => 'bar'],
                        ],
                        'timeframeSupport' => true,
                    ],
                ]),
                [
                    'id'                  => 'top-content',
                    'type'                => 'tabbed-bar-list',
                    'label'               => __('Top Content', 'wp-statistics'),
                    'defaultSize'         => 12,
                    'queryId'             => 'top_content',
                    'tabbedBarListConfig' => [
                        'labelField'         => 'page_title',
                        'labelFallbackField' => 'page_uri',
                        'linkType'           => 'analytics-route',
                        'tabs'               => [
                            [
                                'id'            => 'popular',
                                'label'         => __('Most Popular', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Content', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'        => 'views',
                                'valueField'    => 'views',
                                'valueSuffix'   => __('views', 'wp-statistics'),
                                'link'          => ['href' => '/top-pages?order_by=views&order=desc', 'title' => __('See all', 'wp-statistics')],
                            ],
                            [
                                'id'             => 'commented',
                                'label'          => __('Most Commented', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Content', 'wp-statistics'), 'right' => __('Comments', 'wp-statistics')],
                                'sortBy'         => 'comments',
                                'valueField'     => 'comments',
                                'valueSuffix'    => __('comments', 'wp-statistics'),
                                'showComparison' => false,
                                'filterField'    => 'comments',
                                'filterMinValue' => 1,
                            ],
                            [
                                'id'             => 'recent',
                                'label'          => __('Most Recent', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Content', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'         => 'published_date',
                                'sortType'       => 'date',
                                'valueField'     => 'views',
                                'valueSuffix'    => __('views', 'wp-statistics'),
                                'showComparison' => false,
                                'link'           => ['href' => '/top-pages?order_by=publishedDate&order=desc', 'title' => __('See all', 'wp-statistics')],
                            ],
                        ],
                    ],
                ],
                [
                    'id'                  => 'top-referrers',
                    'type'                => 'bar-list',
                    'label'               => __('Top Referrers', 'wp-statistics'),
                    'defaultSize'         => 6,
                    'queryId'             => 'top_referrers',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'                  => 'top-search-engines',
                    'type'                => 'bar-list',
                    'label'               => __('Top Search Engines', 'wp-statistics'),
                    'defaultSize'         => 6,
                    'queryId'             => 'top_search_engines',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Search Engine', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'             => 'top-countries',
                    'type'           => 'bar-list',
                    'label'          => __('Top Countries', 'wp-statistics'),
                    'defaultSize'    => 6,
                    'queryId'        => 'top_countries',
                    'labelField'     => 'country_name',
                    'valueField'     => 'visitors',
                    'iconType'       => 'country',
                    'iconSlugField'  => 'country_code',
                    'columnHeaders'  => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                    'linkTo'         => '/country/$countryCode',
                    'linkParamField' => 'country_code',
                ],
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
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-device-categories',
                    'type'          => 'bar-list',
                    'label'         => __('Top Device Categories', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'top_device_categories',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'device',
                    'iconSlugField' => 'device_type_name',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
            ],
        ];
    }

    /**
     * Categories overview page config.
     *
     * @return array
     */
    private function getCategoriesOverviewConfig()
    {
        return [
            'type'             => 'overview',
            'pageId'           => 'categories-overview',
            'title'            => __('Categories', 'wp-statistics'),
            'filterGroup'      => 'categories',
            'hideFilters'      => true,
            'showFilterButton' => false,
            'queries'          => [
                $this->metricsQuery('category_metrics', ['published_content', 'visitors', 'views', 'bounce_rate', 'avg_time_on_page']),
                $this->trafficTrendsQuery('traffic_trends', [
                    'sources' => ['visitors', 'views', 'published_content'],
                ]),
                [
                    'id'       => 'top_terms',
                    'sources'  => ['visitors', 'views', 'published_content'],
                    'group_by' => ['taxonomy'],
                    'columns'  => ['term_id', 'term_name', 'term_slug', 'visitors', 'views', 'published_content'],
                    'per_page' => 10,
                    'order_by' => 'views',
                    'order'    => 'DESC',
                    'format'   => 'table',
                    'show_totals' => true,
                    'compare'  => true,
                ],
                [
                    'id'       => 'top_content',
                    'sources'  => ['visitors', 'views', 'comments'],
                    'group_by' => ['page'],
                    'columns'  => ['page_wp_id', 'page_title', 'page_uri', 'page_type', 'visitors', 'views', 'comments', 'published_date'],
                    'per_page' => 15,
                    'order_by' => 'views',
                    'order'    => 'DESC',
                    'format'   => 'table',
                    'show_totals' => false,
                    'compare'  => true,
                ],
                [
                    'id'       => 'top_authors',
                    'sources'  => ['visitors', 'views', 'published_content'],
                    'group_by' => ['author'],
                    'columns'  => ['author_id', 'author_name', 'author_avatar', 'visitors', 'views', 'published_content'],
                    'per_page' => 15,
                    'order_by' => 'views',
                    'order'    => 'DESC',
                    'format'   => 'table',
                    'show_totals' => true,
                    'compare'  => true,
                ],
                $this->topReferrersQuery('top_referrers', [
                    'filters' => [['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => '']],
                    'compare' => true,
                ]),
                [
                    'id'       => 'top_search_engines',
                    'sources'  => ['visitors'],
                    'group_by' => ['referrer'],
                    'columns'  => ['referrer_domain', 'referrer_name', 'visitors'],
                    'filters'  => [
                        ['key' => 'referrer_domain', 'operator' => 'is_not_empty', 'value' => ''],
                        ['key' => 'referrer_channel', 'operator' => 'contains', 'value' => 'search'],
                    ],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                    'compare'     => true,
                ],
                $this->topCountriesQuery('top_countries', ['compare' => true]),
                $this->topBrowsersQuery('top_browsers', ['compare' => true]),
                $this->topOsQuery('top_operating_systems', ['compare' => true]),
                $this->topDeviceCategoriesQuery('top_device_categories', ['compare' => true]),
            ],
            'metrics'          => [
                ['id' => 'published-content', 'label' => __('Published Content', 'wp-statistics'), 'queryId' => 'category_metrics', 'valueField' => 'published_content', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'queryId' => 'category_metrics', 'valueField' => 'visitors', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'views', 'label' => __('Views', 'wp-statistics'), 'queryId' => 'category_metrics', 'valueField' => 'views', 'source' => 'totals', 'format' => 'compact_number'],
                ['id' => 'bounce-rate', 'label' => __('Bounce Rate', 'wp-statistics'), 'queryId' => 'category_metrics', 'valueField' => 'bounce_rate', 'source' => 'totals', 'format' => 'percentage'],
                ['id' => 'avg-time-on-page', 'label' => __('Avg. Time on Page', 'wp-statistics'), 'queryId' => 'category_metrics', 'valueField' => 'avg_time_on_page', 'source' => 'totals', 'format' => 'duration'],
            ],
            'widgets'          => [
                ['id' => 'metrics', 'type' => 'metrics', 'label' => __('Metrics Overview', 'wp-statistics'), 'defaultSize' => 12],
                $this->trafficTrendsWidget([
                    'label'       => __('Performance', 'wp-statistics'),
                    'chartConfig' => [
                        'metrics' => [
                            ['key' => 'visitors', 'label' => __('Visitors', 'wp-statistics'), 'color' => 'var(--chart-1)'],
                            ['key' => 'views', 'label' => __('Views', 'wp-statistics'), 'color' => 'var(--chart-2)'],
                            ['key' => 'published_content', 'label' => __('Published Content', 'wp-statistics'), 'color' => 'var(--chart-3)', 'type' => 'bar'],
                        ],
                        'timeframeSupport' => true,
                    ],
                ]),
                [
                    'id'                  => 'top-terms',
                    'type'                => 'tabbed-bar-list',
                    'label'               => __('Top Terms', 'wp-statistics'),
                    'defaultSize'         => 12,
                    'queryId'             => 'top_terms',
                    'tabbedBarListConfig' => [
                        'labelField'         => 'term_name',
                        'labelFallbackField' => 'term_slug',
                        'tabs'               => [
                            [
                                'id'            => 'views',
                                'label'         => __('Views', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Term', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'        => 'views',
                                'valueField'    => 'views',
                                'valueSuffix'   => __('views', 'wp-statistics'),
                                'link'          => ['href' => '/top-categories?order_by=views&order=desc', 'title' => __('See all', 'wp-statistics')],
                            ],
                            [
                                'id'            => 'contents',
                                'label'         => __('Contents', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Term', 'wp-statistics'), 'right' => __('Contents', 'wp-statistics')],
                                'sortBy'        => 'published_content',
                                'valueField'    => 'published_content',
                                'valueSuffix'   => __('contents', 'wp-statistics'),
                                'link'          => ['href' => '/top-categories?order_by=published&order=desc', 'title' => __('See all', 'wp-statistics')],
                            ],
                        ],
                    ],
                ],
                [
                    'id'                  => 'top-content',
                    'type'                => 'tabbed-bar-list',
                    'label'               => __('Top Content', 'wp-statistics'),
                    'defaultSize'         => 12,
                    'queryId'             => 'top_content',
                    'tabbedBarListConfig' => [
                        'labelField'         => 'page_title',
                        'labelFallbackField' => 'page_uri',
                        'linkType'           => 'analytics-route',
                        'tabs'               => [
                            [
                                'id'            => 'popular',
                                'label'         => __('Most Popular', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Content', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'        => 'views',
                                'valueField'    => 'views',
                                'valueSuffix'   => __('views', 'wp-statistics'),
                            ],
                            [
                                'id'             => 'commented',
                                'label'          => __('Most Commented', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Content', 'wp-statistics'), 'right' => __('Comments', 'wp-statistics')],
                                'sortBy'         => 'comments',
                                'valueField'     => 'comments',
                                'valueSuffix'    => __('comments', 'wp-statistics'),
                                'showComparison' => false,
                                'filterField'    => 'comments',
                                'filterMinValue' => 1,
                            ],
                            [
                                'id'             => 'recent',
                                'label'          => __('Most Recent', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Content', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'         => 'published_date',
                                'sortType'       => 'date',
                                'valueField'     => 'views',
                                'valueSuffix'    => __('views', 'wp-statistics'),
                                'showComparison' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'id'                  => 'top-authors',
                    'type'                => 'tabbed-bar-list',
                    'label'               => __('Top Authors', 'wp-statistics'),
                    'defaultSize'         => 12,
                    'queryId'             => 'top_authors',
                    'tabbedBarListConfig' => [
                        'labelField'         => 'author_name',
                        'labelFallbackField' => 'author_id',
                        'iconType'           => 'author-avatar',
                        'iconField'          => 'author_avatar',
                        'linkTo'             => '/author/$authorId',
                        'linkParamField'     => 'author_id',
                        'tabs'               => [
                            [
                                'id'            => 'views',
                                'label'         => __('Views', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Author', 'wp-statistics'), 'right' => __('Views', 'wp-statistics')],
                                'sortBy'        => 'views',
                                'valueField'    => 'views',
                                'valueSuffix'   => __('views', 'wp-statistics'),
                            ],
                            [
                                'id'            => 'publishing',
                                'label'         => __('Publishing', 'wp-statistics'),
                                'columnHeaders' => ['left' => __('Author', 'wp-statistics'), 'right' => __('Contents', 'wp-statistics')],
                                'sortBy'        => 'published_content',
                                'valueField'    => 'published_content',
                                'valueSuffix'   => __('contents', 'wp-statistics'),
                            ],
                            [
                                'id'             => 'engagement',
                                'label'          => __('Engagement', 'wp-statistics'),
                                'columnHeaders'  => ['left' => __('Author', 'wp-statistics'), 'right' => __('Views/Content', 'wp-statistics')],
                                'sortBy'         => '_computed',
                                'valueField'     => 'views',
                                'computedField'  => ['numerator' => 'views', 'denominator' => 'published_content'],
                                'valueFormat'    => 'decimal',
                                'showComparison' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'id'                  => 'top-referrers',
                    'type'                => 'bar-list',
                    'label'               => __('Top Referrers', 'wp-statistics'),
                    'defaultSize'         => 6,
                    'queryId'             => 'top_referrers',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Referrer', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'                  => 'top-search-engines',
                    'type'                => 'bar-list',
                    'label'               => __('Top Search Engines', 'wp-statistics'),
                    'defaultSize'         => 6,
                    'queryId'             => 'top_search_engines',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => ['left' => __('Search Engine', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'             => 'top-countries',
                    'type'           => 'bar-list',
                    'label'          => __('Top Countries', 'wp-statistics'),
                    'defaultSize'    => 6,
                    'queryId'        => 'top_countries',
                    'labelField'     => 'country_name',
                    'valueField'     => 'visitors',
                    'iconType'       => 'country',
                    'iconSlugField'  => 'country_code',
                    'columnHeaders'  => ['left' => __('Country', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                    'linkTo'         => '/country/$countryCode',
                    'linkParamField' => 'country_code',
                ],
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
                    'columnHeaders' => ['left' => __('OS', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
                [
                    'id'            => 'top-device-categories',
                    'type'          => 'bar-list',
                    'label'         => __('Top Device Categories', 'wp-statistics'),
                    'defaultSize'   => 6,
                    'queryId'       => 'top_device_categories',
                    'labelField'    => 'device_type_name',
                    'valueField'    => 'visitors',
                    'iconType'      => 'device',
                    'iconSlugField' => 'device_type_name',
                    'columnHeaders' => ['left' => __('Device', 'wp-statistics'), 'right' => __('Visitors', 'wp-statistics')],
                ],
            ],
        ];
    }

    /**
     * Visitors report config.
     *
     * @return array
     */
    private function getVisitorsConfig()
    {
        return [
            'type'        => 'table',
            'title'       => esc_html__('Visitors', 'wp-statistics'),
            'context'     => 'visitors',
            'filterGroup' => 'visitors',
            'perPage'     => 25,
            'defaultSort' => ['id' => 'lastVisit', 'desc' => true],
            'dataSource'  => [
                'sources'       => ['visitors', 'avg_session_duration', 'bounce_rate', 'pages_per_session', 'visitor_status'],
                'group_by'      => ['visitor'],
                'columnMapping' => [
                    'lastVisit'       => 'last_visit',
                    'visitorInfo'     => 'visitor_id',
                    'totalViews'      => 'total_views',
                    'totalSessions'   => 'total_sessions',
                    'sessionDuration' => 'avg_session_duration',
                    'viewsPerSession' => 'pages_per_session',
                    'bounceRate'      => 'bounce_rate',
                    'visitorStatus'   => 'visitor_status',
                ],
            ],
            'columns' => [
                ['key' => 'visitorInfo', 'title' => esc_html__('Visitor', 'wp-statistics'), 'type' => 'visitor-info', 'dataField' => 'visitor_id', 'priority' => 'primary'],
                ['key' => 'location', 'title' => esc_html__('Location', 'wp-statistics'), 'type' => 'location', 'priority' => 'secondary', 'linkTo' => '/country/$countryCode', 'linkParamField' => 'country_code'],
                ['key' => 'lastVisit', 'title' => esc_html__('Last Visit', 'wp-statistics'), 'type' => 'last-visit', 'dataField' => 'last_visit', 'priority' => 'primary'],
                ['key' => 'totalViews', 'title' => esc_html__('Views', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'total_views', 'priority' => 'primary', 'comparable' => true, 'previousKey' => 'previous.total_views'],
                ['key' => 'totalSessions', 'title' => esc_html__('Sessions', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'total_sessions', 'priority' => 'secondary', 'comparable' => true, 'previousKey' => 'previous.total_sessions'],
                ['key' => 'sessionDuration', 'title' => esc_html__('Session Duration', 'wp-statistics'), 'type' => 'duration', 'dataField' => 'avg_session_duration', 'priority' => 'primary', 'comparable' => true, 'previousKey' => 'previous.avg_session_duration'],
                ['key' => 'referrer', 'title' => esc_html__('Referrer', 'wp-statistics'), 'type' => 'referrer', 'priority' => 'primary'],
                ['key' => 'journey', 'title' => esc_html__('Journey', 'wp-statistics'), 'type' => 'journey', 'priority' => 'secondary'],
                ['key' => 'viewsPerSession', 'title' => esc_html__('Views/Session', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'pages_per_session', 'priority' => 'secondary', 'decimals' => 1, 'comparable' => true, 'previousKey' => 'previous.pages_per_session'],
                ['key' => 'bounceRate', 'title' => esc_html__('Bounce Rate', 'wp-statistics'), 'type' => 'percentage', 'dataField' => 'bounce_rate', 'priority' => 'secondary', 'comparable' => true, 'previousKey' => 'previous.bounce_rate'],
                ['key' => 'visitorStatus', 'title' => esc_html__('Status', 'wp-statistics'), 'type' => 'visitor-status', 'dataField' => 'visitor_status', 'priority' => 'secondary'],
            ],
            'defaultHiddenColumns' => ['location', 'totalSessions', 'journey', 'viewsPerSession', 'bounceRate', 'visitorStatus'],
            'defaultApiColumns'    => ['visitor_id', 'visitor_hash', 'ip_address', 'last_visit', 'first_visit', 'total_views', 'avg_session_duration'],
            'columnConfig' => [
                'baseColumns'        => ['visitor_id', 'visitor_hash'],
                'columnDependencies' => [
                    'visitorInfo'     => ['ip_address', 'country_code', 'country_name', 'region_name', 'city_name', 'os_name', 'browser_name', 'browser_version', 'device_type_name', 'user_id', 'user_login', 'user_email', 'user_role'],
                    'lastVisit'       => ['last_visit', 'first_visit'],
                    'totalViews'      => ['total_views'],
                    'totalSessions'   => ['total_sessions'],
                    'sessionDuration' => ['avg_session_duration'],
                    'referrer'        => ['referrer_domain', 'referrer_channel'],
                    'journey'         => ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id', 'exit_page', 'exit_page_title', 'exit_page_type', 'exit_page_wp_id', 'exit_page_resource_id'],
                    'viewsPerSession' => ['pages_per_session'],
                    'bounceRate'      => ['bounce_rate'],
                    'visitorStatus'   => ['visitor_status', 'first_visit'],
                    'location'        => ['region_name', 'city_name'],
                ],
            ],
            'emptyStateMessage' => esc_html__('No visitors found for the selected period', 'wp-statistics'),
        ];
    }

    /**
     * Top Visitors report config.
     *
     * Extends visitors config with different columns (entry/exit page instead of journey),
     * higher per-page count, and sort by views.
     *
     * @return array
     */
    private function getTopVisitorsConfig()
    {
        $config = $this->getVisitorsConfig();

        $config['title']       = esc_html__('Top Visitors', 'wp-statistics');
        $config['context']     = 'top_visitors';
        $config['perPage']     = 50;
        $config['defaultSort'] = ['id' => 'totalViews', 'desc' => true];

        // Top visitors doesn't need 'visitors' source (already implied by group_by)
        $config['dataSource']['sources'] = ['avg_session_duration', 'bounce_rate', 'pages_per_session', 'visitor_status'];

        // Replace journey with entry/exit page columns
        $config['columns'] = [
            ['key' => 'visitorInfo', 'title' => esc_html__('Visitor', 'wp-statistics'), 'type' => 'visitor-info', 'dataField' => 'visitor_id', 'priority' => 'primary'],
            ['key' => 'location', 'title' => esc_html__('Location', 'wp-statistics'), 'type' => 'location', 'priority' => 'secondary', 'linkTo' => '/country/$countryCode', 'linkParamField' => 'country_code'],
            ['key' => 'totalViews', 'title' => esc_html__('Views', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'total_views', 'priority' => 'primary', 'comparable' => true, 'previousKey' => 'previous.total_views'],
            ['key' => 'totalSessions', 'title' => esc_html__('Sessions', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'total_sessions', 'priority' => 'primary', 'comparable' => true, 'previousKey' => 'previous.total_sessions'],
            ['key' => 'sessionDuration', 'title' => esc_html__('Session Duration', 'wp-statistics'), 'type' => 'duration', 'dataField' => 'avg_session_duration', 'priority' => 'primary', 'comparable' => true, 'previousKey' => 'previous.avg_session_duration'],
            ['key' => 'lastVisit', 'title' => esc_html__('Last Visit', 'wp-statistics'), 'type' => 'last-visit', 'dataField' => 'last_visit', 'priority' => 'primary'],
            ['key' => 'referrer', 'title' => esc_html__('Referrer', 'wp-statistics'), 'type' => 'referrer', 'priority' => 'secondary'],
            ['key' => 'entryPage', 'title' => esc_html__('Entry Page', 'wp-statistics'), 'type' => 'entry-page', 'dataField' => 'entry_page', 'priority' => 'secondary'],
            ['key' => 'exitPage', 'title' => esc_html__('Exit Page', 'wp-statistics'), 'type' => 'entry-page', 'dataField' => 'exit_page', 'priority' => 'secondary'],
            ['key' => 'viewsPerSession', 'title' => esc_html__('Views/Session', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'pages_per_session', 'priority' => 'secondary', 'decimals' => 1, 'comparable' => true, 'previousKey' => 'previous.pages_per_session'],
            ['key' => 'bounceRate', 'title' => esc_html__('Bounce Rate', 'wp-statistics'), 'type' => 'percentage', 'dataField' => 'bounce_rate', 'priority' => 'secondary', 'comparable' => true, 'previousKey' => 'previous.bounce_rate'],
            ['key' => 'visitorStatus', 'title' => esc_html__('Status', 'wp-statistics'), 'type' => 'visitor-status', 'dataField' => 'visitor_status', 'priority' => 'secondary'],
        ];

        $config['defaultHiddenColumns'] = ['location', 'referrer', 'entryPage', 'exitPage', 'viewsPerSession', 'bounceRate', 'visitorStatus'];
        $config['defaultApiColumns']    = ['visitor_id', 'visitor_hash', 'ip_address', 'total_views', 'total_sessions', 'avg_session_duration'];

        // Replace journey dependency with entry/exit page dependencies
        unset($config['columnConfig']['columnDependencies']['journey']);
        $config['columnConfig']['columnDependencies']['entryPage'] = ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'];
        $config['columnConfig']['columnDependencies']['exitPage']  = ['exit_page', 'exit_page_title', 'exit_page_type', 'exit_page_wp_id', 'exit_page_resource_id'];

        return $config;
    }

    /**
     * Referred Visitors report config.
     *
     * Extends the visitors config with locked/hardcoded filters
     * to exclude direct traffic.
     *
     * @return array
     */
    private function getReferredVisitorsConfig()
    {
        $config = $this->getVisitorsConfig();
        $config['title']             = esc_html__('Referred Visitors', 'wp-statistics');
        $config['context']           = 'referred_visitors';
        $config['emptyStateMessage'] = esc_html__('No referred visitors found for the selected period', 'wp-statistics');
        $config['lockedFilters']     = [
            [
                'id'       => 'referrer_channel-locked',
                'label'    => esc_html__('Traffic Channel', 'wp-statistics'),
                'operator' => esc_html__('is not', 'wp-statistics'),
                'value'    => esc_html__('Direct', 'wp-statistics'),
            ],
        ];
        $config['hardcodedFilters'] = [
            [
                'id'          => 'referrer_channel-hardcoded',
                'label'       => esc_html__('Traffic Channel', 'wp-statistics'),
                'operator'    => esc_html__('is not', 'wp-statistics'),
                'rawOperator' => 'is_not',
                'value'       => esc_html__('Direct', 'wp-statistics'),
                'rawValue'    => 'direct',
            ],
        ];
        return $config;
    }

    /**
     * Logged-in Users report config.
     *
     * Extends the visitors config with logged-in user specific columns,
     * locked/hardcoded filters, and a chart for traffic trends.
     *
     * @return array
     */
    private function getLoggedInUsersConfig()
    {
        $config = $this->getVisitorsConfig();

        $config['title']       = esc_html__('Logged-in Users', 'wp-statistics');
        $config['context']     = 'logged_in_users';

        // Replace columns — logged-in users has page instead of journey, no sessions column
        $config['columns'] = [
            ['key' => 'visitorInfo', 'title' => esc_html__('Visitor', 'wp-statistics'), 'type' => 'visitor-info', 'dataField' => 'visitor_id', 'priority' => 'primary'],
            ['key' => 'location', 'title' => esc_html__('Location', 'wp-statistics'), 'type' => 'location', 'priority' => 'secondary', 'linkTo' => '/country/$countryCode', 'linkParamField' => 'country_code'],
            ['key' => 'lastVisit', 'title' => esc_html__('Last Visit', 'wp-statistics'), 'type' => 'last-visit', 'dataField' => 'last_visit', 'priority' => 'primary'],
            ['key' => 'page', 'title' => esc_html__('Page', 'wp-statistics'), 'type' => 'entry-page', 'dataField' => 'entry_page', 'priority' => 'primary'],
            ['key' => 'totalViews', 'title' => esc_html__('Views', 'wp-statistics'), 'type' => 'numeric', 'dataField' => 'total_views', 'priority' => 'primary', 'comparable' => true, 'previousKey' => 'previous.total_views'],
            ['key' => 'referrer', 'title' => esc_html__('Referrer', 'wp-statistics'), 'type' => 'referrer', 'priority' => 'secondary'],
            ['key' => 'entryPage', 'title' => esc_html__('Entry Page', 'wp-statistics'), 'type' => 'entry-page', 'dataField' => 'entry_page', 'priority' => 'secondary'],
        ];

        $config['defaultHiddenColumns'] = ['location', 'entryPage'];
        $config['defaultApiColumns']    = ['visitor_id', 'visitor_hash', 'ip_address', 'last_visit', 'total_views', 'entry_page', 'entry_page_title'];

        // Replace column dependencies — no journey, sessions, bounceRate, viewsPerSession, visitorStatus
        $config['columnConfig']['columnDependencies'] = [
            'visitorInfo'  => ['ip_address', 'country_code', 'country_name', 'region_name', 'city_name', 'os_name', 'browser_name', 'browser_version', 'device_type_name', 'user_id', 'user_login', 'user_email', 'user_role'],
            'lastVisit'    => ['last_visit'],
            'page'         => ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
            'referrer'     => ['referrer_domain', 'referrer_channel'],
            'entryPage'    => ['entry_page', 'entry_page_title', 'entry_page_type', 'entry_page_wp_id', 'entry_page_resource_id'],
            'totalViews'   => ['total_views'],
            'location'     => ['region_name', 'city_name'],
        ];

        // Use batch queries: table + chart
        $config['dataSource'] = [
            'queryId'       => 'logged_in_users',
            'queries'       => [
                [
                    'id'       => 'logged_in_users',
                    'sources'  => ['visitors'],
                    'group_by' => ['visitor'],
                    'format'   => 'table',
                ],
                [
                    'id'       => 'logged_in_trends',
                    'sources'  => ['visitors'],
                    'group_by' => ['date'],
                    'format'   => 'chart',
                ],
            ],
            'columnMapping' => [
                'visitorInfo' => 'visitor_id',
                'lastVisit'   => 'last_visit',
                'page'        => 'entry_page',
                'referrer'    => 'referrer_domain',
                'entryPage'   => 'entry_page',
                'totalViews'  => 'total_views',
            ],
        ];

        // Chart config
        $config['chart'] = [
            'queryId' => 'logged_in_trends',
            'metrics' => [
                ['key' => 'visitors', 'label' => esc_html__('Visitors', 'wp-statistics'), 'color' => 'var(--chart-1)'],
            ],
        ];

        // Locked filter (displayed as read-only in filter panel)
        $config['lockedFilters'] = [
            [
                'id'       => 'logged_in-locked',
                'label'    => esc_html__('User Type', 'wp-statistics'),
                'operator' => esc_html__('is', 'wp-statistics'),
                'value'    => esc_html__('Logged-in', 'wp-statistics'),
            ],
        ];

        // Hardcoded filter (always applied to API requests)
        $config['hardcodedFilters'] = [
            [
                'id'          => 'logged_in-hardcoded',
                'label'       => esc_html__('User Type', 'wp-statistics'),
                'operator'    => esc_html__('is', 'wp-statistics'),
                'rawOperator' => 'is',
                'value'       => esc_html__('Logged-in', 'wp-statistics'),
                'rawValue'    => '1',
            ],
        ];

        $config['emptyStateMessage'] = esc_html__('No logged-in users found for the selected period', 'wp-statistics');

        return $config;
    }

    /**
     * Main overview dashboard config.
     *
     * @return array
     */
    private function getOverviewConfig()
    {
        return [
            'type'             => 'overview',
            'pageId'           => 'overview',
            'title'            => __('Overview', 'wp-statistics'),
            'filterGroup'      => 'overview',
            'hideFilters'      => true,
            'hideDateRange'    => true,
            'queries'          => [
                [
                    'id'          => 'metrics',
                    'sources'     => ['visitors', 'views', 'sessions', 'avg_session_duration', 'bounce_rate', 'pages_per_session', 'online_visitors', 'searches'],
                    'group_by'    => [],
                    'format'      => 'flat',
                    'show_totals' => true,
                ],
                $this->trafficTrendsQuery(),
                [
                    'id'          => 'top_pages',
                    'sources'     => ['views', 'visitors'],
                    'group_by'    => ['page'],
                    'columns'     => ['page_uri', 'page_title', 'page_type', 'page_wp_id', 'resource_id', 'visitors', 'views'],
                    'per_page'    => 5,
                    'order_by'    => 'views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                $this->topReferrersQuery(),
                $this->topCountriesQuery(),
                [
                    'id'          => 'top_search_engines',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'referrer_domain', 'visitors'],
                    'filters'     => ['referrer_channel' => ['is' => 'search']],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                [
                    'id'          => 'top_social_media',
                    'sources'     => ['visitors'],
                    'group_by'    => ['referrer'],
                    'columns'     => ['referrer_name', 'referrer_domain', 'visitors'],
                    'filters'     => ['referrer_channel' => ['is' => 'social']],
                    'per_page'    => 5,
                    'order_by'    => 'visitors',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => true,
                ],
                $this->topBrowsersQuery('top_browsers', ['columns' => ['browser_name', 'browser_id', 'visitors']]),
                [
                    'id'          => 'top_visitors',
                    'sources'     => ['visitors'],
                    'group_by'    => ['visitor'],
                    'columns'     => ['visitor_id', 'visitor_hash', 'ip_address', 'user_id', 'user_login', 'total_views', 'country_code', 'country_name', 'os_name', 'browser_name', 'device_type_name', 'referrer_domain'],
                    'per_page'    => 5,
                    'order_by'    => 'total_views',
                    'order'       => 'DESC',
                    'format'      => 'table',
                    'show_totals' => false,
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
                $this->topOsQuery('top_os', ['columns' => ['os_name', 'os_id', 'visitors']]),
                $this->topDeviceCategoriesQuery('top_device_categories', ['columns' => ['device_type_name', 'device_type_id', 'visitors']]),
            ],
            'metrics'          => [
                [
                    'id'         => 'visitors',
                    'label'      => __('Visitors', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'visitors',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'views',
                    'label'      => __('Views', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'views',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'session-duration',
                    'label'      => __('Session Duration', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'avg_session_duration',
                    'source'     => 'totals',
                    'format'     => 'duration',
                ],
                [
                    'id'         => 'views-per-session',
                    'label'      => __('Views/Session', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'pages_per_session',
                    'source'     => 'totals',
                    'format'     => 'decimal',
                ],
                [
                    'id'         => 'bounce-rate',
                    'label'      => __('Bounce Rate', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'bounce_rate',
                    'source'     => 'totals',
                    'format'     => 'percentage',
                ],
                [
                    'id'         => 'online-visitors',
                    'label'      => __('Online Visitors', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'online_visitors',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
                [
                    'id'         => 'searches',
                    'label'      => __('Searches', 'wp-statistics'),
                    'queryId'    => 'metrics',
                    'valueField' => 'searches',
                    'source'     => 'totals',
                    'format'     => 'compact_number',
                ],
            ],
            'widgets'          => [
                [
                    'id'             => 'metrics-overview',
                    'label'          => __('Metrics Overview', 'wp-statistics'),
                    'type'           => 'metrics',
                    'defaultSize'    => 12,
                    'allowedSizes'   => [4, 6, 8, 12],
                ],
                $this->trafficTrendsWidget(['allowedSizes' => [6, 8, 12]]),
                [
                    'id'                  => 'top-pages',
                    'label'               => __('Top Pages', 'wp-statistics'),
                    'type'                => 'bar-list',
                    'defaultSize'         => 6,
                    'allowedSizes'        => [4, 6, 8, 12],
                    'queryId'             => 'top_pages',
                    'labelField'          => 'page_title',
                    'labelFallbackFields' => ['page_uri'],
                    'valueField'          => 'visitors',
                    'linkType'            => 'analytics-route',
                    'columnHeaders'       => [
                        'left'  => __('Page', 'wp-statistics'),
                        'right' => __('Visitors', 'wp-statistics'),
                    ],
                    'link'                => ['to' => '/top-pages'],
                ],
                $this->topReferrersWidget(['defaultSize' => 6, 'allowedSizes' => [4, 6, 8, 12]]),
                $this->topCountriesWidget(['defaultSize' => 6, 'allowedSizes' => [4, 6, 8, 12]]),
                [
                    'id'                  => 'top-search-engines',
                    'label'               => __('Top Search Engines', 'wp-statistics'),
                    'type'                => 'bar-list',
                    'defaultSize'         => 6,
                    'allowedSizes'        => [4, 6, 8, 12],
                    'queryId'             => 'top_search_engines',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => [
                        'left'  => __('Search Engine', 'wp-statistics'),
                        'right' => __('Visitors', 'wp-statistics'),
                    ],
                    'link'                => ['to' => '/search-engines'],
                ],
                $this->topBrowsersWidget(['defaultSize' => 6, 'defaultVisible' => false, 'allowedSizes' => [4, 6, 8, 12]]),
                [
                    'id'                  => 'top-visitors',
                    'label'               => __('Top Visitors', 'wp-statistics'),
                    'type'                => 'bar-list',
                    'defaultSize'         => 6,
                    'defaultVisible'      => false,
                    'allowedSizes'        => [4, 6, 8, 12],
                    'queryId'             => 'top_visitors',
                    'labelField'          => 'user_login',
                    'labelFallbackFields' => ['ip_address', 'visitor_hash'],
                    'valueField'          => 'total_views',
                    'iconType'            => 'country',
                    'iconSlugField'       => 'country_code',
                    'columnHeaders'       => [
                        'left'  => __('Visitor', 'wp-statistics'),
                        'right' => __('Views', 'wp-statistics'),
                    ],
                    'link'                => ['to' => '/top-visitors'],
                ],
                [
                    'id'                  => 'top-social-media',
                    'label'               => __('Top Social Media', 'wp-statistics'),
                    'type'                => 'bar-list',
                    'defaultSize'         => 6,
                    'defaultVisible'      => false,
                    'allowedSizes'        => [4, 6, 8, 12],
                    'queryId'             => 'top_social_media',
                    'labelField'          => 'referrer_name',
                    'labelFallbackFields' => ['referrer_domain'],
                    'valueField'          => 'visitors',
                    'columnHeaders'       => [
                        'left'  => __('Social Media', 'wp-statistics'),
                        'right' => __('Visitors', 'wp-statistics'),
                    ],
                    'link'                => ['to' => '/social-media'],
                ],
                [
                    'id'             => 'top-cities',
                    'label'          => __('Top Cities', 'wp-statistics'),
                    'type'           => 'bar-list',
                    'defaultSize'    => 6,
                    'defaultVisible' => false,
                    'allowedSizes'   => [4, 6, 8, 12],
                    'queryId'        => 'top_cities',
                    'labelField'     => 'city_name',
                    'valueField'     => 'visitors',
                    'iconType'       => 'country',
                    'iconSlugField'  => 'country_code',
                    'columnHeaders'  => [
                        'left'  => __('City', 'wp-statistics'),
                        'right' => __('Visitors', 'wp-statistics'),
                    ],
                    'link'           => ['to' => '/cities'],
                ],
                $this->topOsWidget(['id' => 'top-os', 'queryId' => 'top_os', 'defaultSize' => 6, 'defaultVisible' => false, 'allowedSizes' => [4, 6, 8, 12]]),
                $this->topDeviceCategoriesWidget(['defaultSize' => 6, 'defaultVisible' => false, 'allowedSizes' => [4, 6, 8, 12], 'iconType' => 'device', 'iconSlugField' => 'device_type_name']),
            ],
            'widgetCategories' => [
                [
                    'label'   => __('Visitor Insights', 'wp-statistics'),
                    'widgets' => ['metrics-overview', 'traffic-trends', 'top-visitors'],
                ],
                [
                    'label'   => __('Content', 'wp-statistics'),
                    'widgets' => ['top-pages'],
                ],
                [
                    'label'   => __('Referrals', 'wp-statistics'),
                    'widgets' => ['top-referrers', 'top-search-engines', 'top-social-media'],
                ],
                [
                    'label'   => __('Geographic', 'wp-statistics'),
                    'widgets' => ['top-countries', 'top-cities'],
                ],
                [
                    'label'   => __('Devices', 'wp-statistics'),
                    'widgets' => ['top-browsers', 'top-os', 'top-device-categories'],
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Reusable query builders
    // -----------------------------------------------------------------------

    /**
     * Build a "top N by visitors" query for any dimension.
     */
    private function topByVisitorsQuery(string $id, array $groupBy, array $columns, array $overrides = []): array
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

    private function topCountriesQuery(string $id = 'top_countries', array $overrides = []): array
    {
        return $this->topByVisitorsQuery($id, ['country'], ['country_code', 'country_name', 'visitors'], $overrides);
    }

    private function topBrowsersQuery(string $id = 'top_browsers', array $overrides = []): array
    {
        return $this->topByVisitorsQuery($id, ['browser'], ['browser_name', 'visitors'], $overrides);
    }

    private function topOsQuery(string $id = 'top_operating_systems', array $overrides = []): array
    {
        return $this->topByVisitorsQuery($id, ['os'], ['os_name', 'visitors'], $overrides);
    }

    private function topDeviceCategoriesQuery(string $id = 'top_device_categories', array $overrides = []): array
    {
        return $this->topByVisitorsQuery($id, ['device_type'], ['device_type_name', 'visitors'], $overrides);
    }

    private function topReferrersQuery(string $id = 'top_referrers', array $overrides = []): array
    {
        return $this->topByVisitorsQuery($id, ['referrer'], ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'], $overrides);
    }

    /**
     * Build a traffic trends chart query.
     */
    private function trafficTrendsQuery(string $id = 'traffic_trends', array $overrides = []): array
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
    private function metricsQuery(string $id, array $sources, array $overrides = []): array
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
    private function topOneQuery(string $id, array $groupBy, array $columns): array
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
    // Reusable widget builders
    // -----------------------------------------------------------------------

    private function topCountriesWidget(array $overrides = []): array
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

    private function topBrowsersWidget(array $overrides = []): array
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

    private function topOsWidget(array $overrides = []): array
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

    private function topDeviceCategoriesWidget(array $overrides = []): array
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

    private function topReferrersWidget(array $overrides = []): array
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

    private function trafficTrendsWidget(array $overrides = []): array
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

    private function mapWidget(string $queryId = 'top_countries', array $overrides = []): array
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
    // Reusable table column builders
    // -----------------------------------------------------------------------

    private function visitorsColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'visitors',
            'title'        => __('Visitors', 'wp-statistics'),
            'type'         => 'numeric',
            'priority'     => 'primary',
            'comparable'   => true,
            'previousKey'  => 'previous.visitors',
            'size'         => 'views',
            'cardPosition' => 'body',
        ], $overrides);
    }

    private function viewsColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'views',
            'title'        => __('Views', 'wp-statistics'),
            'type'         => 'numeric',
            'priority'     => 'primary',
            'comparable'   => true,
            'previousKey'  => 'previous.views',
            'size'         => 'views',
            'cardPosition' => 'body',
        ], $overrides);
    }

    private function bounceRateColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'bounceRate',
            'dataField'    => 'bounce_rate',
            'title'        => __('Bounce Rate', 'wp-statistics'),
            'type'         => 'percentage',
            'priority'     => 'secondary',
            'comparable'   => true,
            'previousKey'  => 'previous.bounce_rate',
            'size'         => 'bounceRate',
            'mobileLabel'  => __('Bounce', 'wp-statistics'),
            'decimals'     => 0,
        ], $overrides);
    }

    private function sessionDurationColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'sessionDuration',
            'dataField'    => 'avg_session_duration',
            'title'        => __('Avg. Duration', 'wp-statistics'),
            'type'         => 'duration',
            'priority'     => 'secondary',
            'comparable'   => true,
            'previousKey'  => 'previous.avg_session_duration',
            'size'         => 'duration',
            'mobileLabel'  => __('Duration', 'wp-statistics'),
        ], $overrides);
    }

    private function viewsPerVisitorColumn(array $overrides = []): array
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

    private function pagesPerSessionColumn(array $overrides = []): array
    {
        return array_merge([
            'key'          => 'pagesPerSession',
            'dataField'    => 'pages_per_session',
            'title'        => __('Pages/Session', 'wp-statistics'),
            'type'         => 'numeric',
            'priority'     => 'secondary',
            'comparable'   => true,
            'previousKey'  => 'previous.pages_per_session',
            'size'         => 'viewsPerSession',
            'cardPosition' => 'body',
            'decimals'     => 1,
        ], $overrides);
    }
}
