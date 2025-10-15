<?php
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Posts\WordCountService;
?>

<div class="metabox-holder wps-category-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $metrics = [
                [
                    'label'  => esc_html__('Published Contents', 'wp-statistics'),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['posts']['value']),
                    'change' => $data['glance']['posts']['change']
                ],
                [
                    'label'  => esc_html__('Visitors', 'wp-statistics'),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['visitors']['value']),
                    'change' => $data['glance']['visitors']['change']
                ],
                [
                    'label'  => esc_html__('Views', 'wp-statistics'),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['views']['value']),
                    'change' => $data['glance']['views']['change']
                ]
            ];

            if (WordCountService::isActive()) {
                $metrics[] = [
                    'label'  => esc_html__('Words', 'wp-statistics'),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['words']['value']),
                ];

                $metrics[] = [
                    'label'  => esc_html__('Avg. words per content', 'wp-statistics'),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['words_avg']['value']),
                ];
            }

            $metrics[] = [
                'label'  => esc_html__('Comments', 'wp-statistics'),
                'value'  => Helper::formatNumberWithUnit($data['glance']['comments']['value']),
                'change' => $data['glance']['comments']['change']
            ];

            $metrics[] = [
                'label'  => esc_html__('Avg. comments per content', 'wp-statistics'),
                'value'  => Helper::formatNumberWithUnit($data['glance']['comments_avg']['value']),
                'change' => $data['glance']['comments_avg']['change']
            ];

            View::load("components/objects/glance-card", ['metrics' => $metrics]);

            $operatingSystems = [
                'title'     => esc_html__('Operating Systems', 'wp-statistics'),
                'tooltip'   => esc_html__('Distribution of visitors by their operating systems.', 'wp-statistics'),
                'unique_id' => 'category_operating_systems'
            ];
            View::load("components/charts/horizontal-bar", $operatingSystems);

            $browsers = [
                'title'     => esc_html__('Browsers', 'wp-statistics'),
                'tooltip'   => esc_html__('Distribution of visitors by their web browsers.', 'wp-statistics'),
                'unique_id' => 'category_browsers'
            ];
            View::load("components/charts/horizontal-bar", $browsers);

            $deviceModels = [
                'title'     => esc_html__('Device Models', 'wp-statistics'),
                'tooltip'   => esc_html__('Distribution of visitors by their device models.', 'wp-statistics'),
                'unique_id' => 'category_device_models'
            ];
            View::load("components/charts/horizontal-bar", $deviceModels);

            $deviceUsage = [
                'title'     => esc_html__('Device Usage', 'wp-statistics'),
                'tooltip'   => esc_html__('Distribution of visitors by their device types.', 'wp-statistics'),
                'unique_id' => 'category_device_usage'
            ];
            View::load("components/charts/horizontal-bar", $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = [
            'title'       => esc_html__('Performance', 'wp-statistics'),
            'type'        => 'categorySingle',
            'data'        => $data['performance']
        ];
        View::load("components/charts/performance", $performance);

        $topPick = [
            'title'   => esc_html__('Top Contents', 'wp-statistics'),
            'tooltip' => esc_html__('Shows the most popular, most commented, and most recent content with this term.', 'wp-statistics'),
            'data'    => $data['posts']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-picks'], $topPick);

        $summary = [
            'title'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip' => esc_html__('Summary of views and visitors over various time periods, including today, yesterday, the last 7 days, and the last 30 days.', 'wp-statistics'),
            'data'    => $data['visits_summary']
        ];
        View::load("components/tables/summary", $summary);

        $topCountries = [
            'tooltip' => esc_html__('The countries from which the most visitors are coming.', 'wp-statistics'),
            'data'    => $data['visitors_country']
        ];
        View::load("components/tables/top-countries", $topCountries);

        $engines = [
            'title'     => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip'   => esc_html__('Search engine traffic over the selected period.', 'wp-statistics'),
            'unique_id' => 'category-search-engines-chart'
        ];
        View::load("components/charts/search-engines", $engines);

        $topReferring = [
            'tooltip' => esc_html__('The top referring domains.', 'wp-statistics'),
            'data'    => $data['referrers']
        ];
        View::load("components/tables/top-referring", $topReferring);
        ?>
    </div>

</div>