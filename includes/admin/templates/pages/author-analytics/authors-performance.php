<?php
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\Posts\WordCountService;

$postType               = Request::get('pt', 'post');
$postTypeNameSingular   = Helper::getPostTypeName($postType, true);
$postTypeNamePlural     = Helper::getPostTypeName($postType);
?>

<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php

        $metrics = [
            [
                'label'  => esc_html__('Published Posts', 'wp-statistics'),
                'value'  => '2',
                'change' => '4'
            ],
            [
                'label'  => esc_html__('Active Authors', 'wp-statistics'),
                'value'  => '2',
                'change' => '4'
            ],
            [
                'label'  => esc_html__('Visitors', 'wp-statistics'),
                'value'  => '2',
                'change' => '-4'
            ],
            [
                'label'  => esc_html__('Views', 'wp-statistics'),
                'value'  => '2',
                'change' => '-4'
            ]
        ];

        $additionalMetrics = [];
        if (WordCountService::isActive()) {
            $additionalMetrics[] = [
                'label'  => esc_html__('Words', 'wp-statistics'),
                'value'  => '2',
             ];
            $additionalMetrics[] = [
                'label'  => esc_html__('Avg. words per post', 'wp-statistics'),
                'value'  => '2',
                'change' => '-4'
             ];
        }

        if (post_type_supports($postType, 'comments')) {
            $additionalMetrics[] = [
                'label'  => esc_html__('Comments', 'wp-statistics'),
                'value'  => '2',
                'change' => '-4'
            ];
            $additionalMetrics[] = [
                'label'  => esc_html__('Avg. comments per post', 'wp-statistics'),
                'value'  => '2',
                'change' => '-4'
            ];
        }

        $metrics = array_merge($metrics, $additionalMetrics);
        View::load("components/objects/glance-card", ['metrics' => $metrics, 'two_column' => true]);

        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            Admin_Template::get_template(['layout/author-analytics/publishing-overview'], [
                'title'         => esc_html__('Publishing Overview', 'wp-statistics'),
                'tooltip'       => sprintf(
                    esc_html__('This heatmap displays the publishing activity of authors over the past 12 months. Darker squares represent more published %s.', 'wp-statistics'),
                    strtolower($postTypeNamePlural)
                ),
                'description'   => esc_html__('Last 12 Months', 'wp-statistics'),
                'data'          => $data
            ]);

            Admin_Template::get_template(['layout/author-analytics/top-authors'], [
                'title'    => esc_html__('Top Authors', 'wp-statistics'),
                'tooltip'  => sprintf(
                    esc_html__('This section ranks authors based on various performance metrics such as views, publishing frequency, comments per %1$s, and average words per %1$s. Use the tabs to switch between different metrics to see how each author is performing.', 'wp-statistics'),
                    strtolower($postTypeNameSingular)
                ),
                'data'     => $data
            ]);

            Admin_Template::get_template(['layout/author-analytics/published-posts'], [
                'title'     => sprintf(esc_html__('Views/Published %s', 'wp-statistics'), $postTypeNamePlural),
                'tooltip'   => sprintf(
                    esc_html__('This scatter plot shows the relationship between the number of %1$s published by an author and the number of views those %1$s have received. Each point represents an author.', 'wp-statistics'),
                    strtolower($postTypeNamePlural)
                ),
                'data'      => $data
            ]);
        ?>
    </div>
</div>