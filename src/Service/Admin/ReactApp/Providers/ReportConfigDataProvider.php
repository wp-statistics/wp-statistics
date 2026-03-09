<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

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
        ];
    }
}
