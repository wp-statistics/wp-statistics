<?php
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Posts\WordCountService;

$postType         = Request::get('tab', 'post');
$postTypeSingular = Helper::getPostTypeName($postType, true);
$postTypePlural   = Helper::getPostTypeName($postType);
?>

<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $metrics = [
                [
                    'label'  => sprintf(esc_html__('Published %s', 'wp-statistics'), $postTypePlural),
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
                    'value'  => Helper::formatNumberWithUnit($data['glance']['words']['value'])
                ];
                $metrics[] = [
                    'label'  => sprintf(esc_html__('Avg. words per %s', 'wp-statistics'), $postTypeSingular),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['words_avg']['value'])
                ];
            }

            if (post_type_supports($postType, 'comments')) {
                $metrics[] = [
                    'label'  => esc_html__('Comments', 'wp-statistics'),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['comments']['value']),
                    'change' => $data['glance']['comments']['change']
                ];

                $metrics[] = [
                    'label'  => sprintf(esc_html__('Avg. comments per %s', 'wp-statistics'), $postTypeSingular),
                    'value'  => Helper::formatNumberWithUnit($data['glance']['comments_avg']['value']),
                    'change' => $data['glance']['comments_avg']['change']
                ];
            }

            View::load("components/objects/glance-card", ['metrics' => $metrics]);

            $categories = [
                'title'      => esc_html__('Top Categories', 'wp-statistics'),
                'tooltip'    => sprintf(esc_html__('The most popular categories by number of published %s.', 'wp-statistics'), strtolower($postTypePlural)),
                'taxonomies' => $data['taxonomies']
            ];
            Admin_Template::get_template(['layout/content-analytics/top-categories'], $categories);

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
                'title' => esc_html__('Performance', 'wp-statistics'),
                'type'  => 'post-type',
                'data'  => $data['performance']
            ];
            View::load("components/charts/performance", $performance);

            $topPages = [
                'title'   => sprintf(esc_html__('Top %s', 'wp-statistics'), $postTypePlural),
                'tooltip' => sprintf(esc_html__('Displays the most popular, most commented, and most recent  %s in the selected period.', 'wp-statistics'), strtolower($postTypePlural)),
                'data'    => $data['posts']
            ];
            Admin_Template::get_template(['layout/content-analytics/top-picks'], $topPages);

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