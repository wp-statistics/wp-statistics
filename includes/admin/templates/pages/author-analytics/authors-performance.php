<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\Posts\WordCountService;

$postType             = Request::get('pt', 'post');
$postTypeNameSingular = Helper::getPostTypeName($postType, true);
$postTypeNamePlural   = Helper::getPostTypeName($postType);
?>

<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
        $metrics = [
            [
                /* translators: %s: string value */
                'label'  => sprintf(esc_html__('Published %s', 'wp-statistics'), $postTypeNamePlural),
                'value'  => Helper::formatNumberWithUnit($data['glance']['posts']['value']),
                'change' => $data['glance']['posts']['change']
            ],
            [
                'label'  => esc_html__('Active Authors', 'wp-statistics'),
                'value'  => Helper::formatNumberWithUnit($data['glance']['authors']['value']),
                'change' => $data['glance']['authors']['change']
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
                'label' => esc_html__('Words', 'wp-statistics'),
                'value' => Helper::formatNumberWithUnit($data['glance']['words']['value']),
            ];

            $metrics[] = [
                /* translators: %s: string value */
                'label' => sprintf(esc_html__('Avg. words per %s', 'wp-statistics'), $postTypeNamePlural),
                'value' => Helper::formatNumberWithUnit($data['glance']['words_avg']['value']),
            ];
        }

        if (post_type_supports($postType, 'comments')) {
            $metrics[] = [
                'label'  => esc_html__('Comments', 'wp-statistics'),
                'value'  => Helper::formatNumberWithUnit($data['glance']['comments']['value']),
                'change' => $data['glance']['comments']['change']
            ];

            $metrics[] = [
                /* translators: %s: string value */
                'label'  => sprintf(esc_html__('Avg. comments per %s', 'wp-statistics'), $postTypeNamePlural),
                'value'  => Helper::formatNumberWithUnit($data['glance']['comments_avg']['value']),
                'change' => $data['glance']['comments_avg']['change']
            ];
        }

        View::load("components/objects/glance-card", ['metrics' => $metrics, 'two_column' => true]);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        Admin_Template::get_template(['layout/author-analytics/publishing-overview'], [
            'title'       => esc_html__('Publishing Overview', 'wp-statistics'),
            'tooltip'     => sprintf(
                /* translators: %s: string value */
                esc_html__('Darker squares mean more %s published, last 12 months.', 'wp-statistics'),
                strtolower($postTypeNamePlural)
            ),
            'description' => esc_html__('Last 12 Months', 'wp-statistics'),
        ]);

        Admin_Template::get_template(['layout/author-analytics/top-authors'], [
            'title'   => esc_html__('Top Authors', 'wp-statistics'),
            'tooltip' => sprintf(
                /* translators: %1$s: string value */
                esc_html__('Ranked by views. Views per %1$s: views divided by how many had a view.', 'wp-statistics'),
                strtolower($postTypeNameSingular)
            ),
            'data'    => $data
        ]);

        Admin_Template::get_template(['layout/author-analytics/published-posts'], [
            /* translators: %s: string value */
            'title'   => sprintf(esc_html__('Views/Published %s', 'wp-statistics'), $postTypeNamePlural),
            'tooltip' => sprintf(
                /* translators: %1$s: string value */
                esc_html__('Each dot is an author: %1$s published against views of those %1$s.', 'wp-statistics'),
                strtolower($postTypeNamePlural)
            )
        ]);
        ?>
    </div>
</div>