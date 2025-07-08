<?php

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Posts\WordCountService;

$postType = get_post_type(Request::get('post_id'));
?>

<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php

        $metrics = [
            [
                'label'  => esc_html__('Visitors', 'wp-statistics'),
                'value'  => Helper::formatNumberWithUnit($data['glance']['visitors']['value']),
                'change' => $data['glance']['visitors']['change']
            ],
            [
                'label'  => esc_html__('Views', 'wp-statistics'),
                'value'  => Helper::formatNumberWithUnit($data['glance']['views']['value']),
                'change' => $data['glance']['views']['change']
            ],
            [
                'label'   => esc_html__('Entry Page', 'wp-statistics'),
                'value'   => Helper::formatNumberWithUnit($data['glance']['entry_page']['value']),
                'change'  => $data['glance']['entry_page']['change'],
                'tooltip' =>  esc_html__('Number of times this content was the first page visited in a session.', 'wp-statistics') ,
            ],
            [
                'label'   => esc_html__('Exit Page', 'wp-statistics'),
                'value'   => Helper::formatNumberWithUnit($data['glance']['exit_page']['value']),
                'change'  => $data['glance']['exit_page']['change'],
                'tooltip' =>  esc_html__('Number of times this content was the last page viewed before a session ended.', 'wp-statistics') ,
            ],
            [
                'label'   => esc_html__('Bounce Rate', 'wp-statistics'),
                'value'   => $data['glance']['bounce_rate']['value'],
                'change'  => $data['glance']['bounce_rate']['change'],
                'tooltip' =>  esc_html__('Percentage of single-page sessions that began and ended on this content.', 'wp-statistics') ,
            ],
            [
                'label'   => esc_html__('Exit Rate', 'wp-statistics'),
                'value'   => $data['glance']['exit_rate']['value'],
                'change'  => $data['glance']['exit_rate']['change'],
                'tooltip' =>  esc_html__('Percentage of total views that ended on this content.', 'wp-statistics') ,
            ]
        ];

        if (WordCountService::isActive()) {
            $metrics[] = [
                'label'   => esc_html__('Words', 'wp-statistics'),
                'value'   => Helper::formatNumberWithUnit($data['glance']['words']['value']),
                'tooltip' => sprintf(esc_html__('Total number of words in this %s.', 'wp-statistics'), strtolower($postType)),
            ];
        }

        if (post_type_supports($postType, 'comments')) {
            $metrics[] = [
                'label'   => esc_html__('Comments', 'wp-statistics'),
                'value'   => Helper::formatNumberWithUnit($data['glance']['comments']['value']),
                'change'  => $data['glance']['comments']['change'],
                'tooltip' => sprintf(esc_html__('Approved comments on this %s.', 'wp-statistics'), strtolower($postType)),
            ];
        }

        View::load("components/objects/glance-card", ['metrics' => $metrics , 'two_column' => true]);

        $operatingSystems = [
            'title'     => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their operating systems.', 'wp-statistics'),
            'unique_id' => 'content_operating_systems'
        ];
        View::load("components/charts/horizontal-bar", $operatingSystems);

        $browsers = [
            'title'     => esc_html__('Browsers', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their web browsers.', 'wp-statistics'),
            'unique_id' => 'content_browsers'
        ];
        View::load("components/charts/horizontal-bar", $browsers);

        $deviceModels = [
            'title'     => esc_html__('Device Models', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device models.', 'wp-statistics'),
            'unique_id' => 'content_device_models'
        ];
        View::load("components/charts/horizontal-bar", $deviceModels);

        $deviceUsage = [
            'title'     => esc_html__('Device Usage', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device types.', 'wp-statistics'),
            'unique_id' => 'content_device_usage'
        ];
        View::load("components/charts/horizontal-bar", $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = [
            'title'       => esc_html__('Performance', 'wp-statistics'),
            'type'        => 'single',
            'data'        => $data['performance']
        ];
        View::load("components/charts/performance", $performance);

        $summary = [
            'title'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip' => esc_html__('From today to last year, a breakdown of visitors and views.', 'wp-statistics'),
            'data'    => $data['visits_summary']
        ];
        View::load("components/tables/summary", $summary);

        $topCountries = [
            'tooltip' => esc_html__('The countries from which the most visitors are coming.', 'wp-statistics'),
            'data'    => $data['visitors_country']
        ];
        View::load("components/tables/top-countries", $topCountries);

        do_action('wp_statistics_single_content_search_console_widgets');

        $engines = [
            'title'     => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip'   => esc_html__('Search engine traffic over the selected period.', 'wp-statistics'),
            'unique_id' => 'content-search-engines-chart'
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